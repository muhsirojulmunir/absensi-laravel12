<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesInput;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $period     = $request->query('period', 'month');
        $locationId = $request->query('location_id');
        $userId     = $request->query('user_id');
        $month      = $request->query('month', date('Y-m'));

        // ── Base Sales Query ───────────────────────────────────────────────
        $query = SalesInput::with(['user.location'])
            ->where('type', 'sale')
            ->whereHas('user', function ($q) {
                $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_ramayana'));
            });

        // Date Filtering
        [$startDate, $endDate] = $this->resolveDateRange($request, $period, $month);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        if ($locationId) {
            $query->whereHas('user', fn($q) => $q->where('location_id', $locationId));
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $sales        = $query->orderBy('date', 'desc')->get();
        $totalQty     = $sales->sum('qty');
        $totalNominal = $sales->sum('nominal');

        // ── SPG Ranking ────────────────────────────────────────────────────
        // Ambil semua SPG karyawan_ramayana
        $spgQuery = User::with('location')
            ->whereHas('role', fn($q) => $q->where('slug', 'karyawan_ramayana'));

        if ($locationId) {
            $spgQuery->where('location_id', $locationId);
        }

        $allSpg = $spgQuery->get();

        // Buat summary dari sales yang sudah difilter (groupBy user_id)
        $salesGrouped = $sales->groupBy('user_id');

        $spgRanking = $allSpg->map(function ($spg) use ($salesGrouped) {
            $items = $salesGrouped->get($spg->id, collect());
            return [
                'user'          => $spg,
                'total_qty'     => $items->sum('qty'),
                'total_nominal' => $items->sum('nominal'),
                'total_trx'     => $items->count(),
                'transactions'  => $items->sortByDesc('date')->values(),
            ];
        })->sortByDesc('total_nominal')->values();

        $maxNominal = $spgRanking->max('total_nominal') ?: 1;

        // ── Filter Options ─────────────────────────────────────────────────
        $locations = Location::all();
        $users     = User::whereHas('role', fn($q) => $q->where('slug', 'karyawan_ramayana'))->get();

        $routeName = $user->role->slug === 'super-admin'
            ? 'super-admin.sales-reports.index'
            : 'pic_ramayana.sales-reports.index';

        // ── AJAX Response ──────────────────────────────────────────────────
        if ($request->ajax()) {
            $htmlTableBody = view('reports.partials.sales_table_body', compact('sales'))->render();
            $htmlTableFoot = view('reports.partials.sales_table_foot', compact('sales', 'totalQty', 'totalNominal'))->render();
            $htmlSpgSummaryTableBody = view('reports.partials.sales_spg_summary_body', compact('sales', 'userId'))->render();
            $htmlSpgSummaryTableFoot = view('reports.partials.sales_spg_summary_foot', compact('sales', 'totalQty', 'totalNominal', 'userId'))->render();
            $htmlRankingBody = view('reports.partials.sales_ranking_body', compact('spgRanking', 'maxNominal', 'userId'))->render();

            $rankingData = $spgRanking->map(function ($r) {
                return [
                    'user_id'       => $r['user']->id,
                    'name'          => $r['user']->name,
                    'location'      => $r['user']->location?->name ?? '-',
                    'total_qty'     => $r['total_qty'],
                    'total_nominal' => $r['total_nominal'],
                    'total_trx'     => $r['total_trx'],
                    'transactions'  => $r['transactions']->map(function ($t) {
                        return [
                            'date'    => $t->date,
                            'sku'     => $t->sku,
                            'size'    => $t->size,
                            'qty'     => $t->qty,
                            'nominal' => $t->nominal,
                        ];
                    })->values()->all(),
                ];
            })->values()->all();

            return response()->json([
                'totalQty'               => number_format($totalQty, 0, ',', '.'),
                'totalNominal'           => 'Rp ' . number_format($totalNominal, 0, ',', '.'),
                'transactionCount'       => number_format($sales->count(), 0, ',', '.'),
                'htmlTableBody'          => $htmlTableBody,
                'htmlTableFoot'          => $htmlTableFoot,
                'htmlSpgSummaryTableBody'=> $htmlSpgSummaryTableBody,
                'htmlSpgSummaryTableFoot'=> $htmlSpgSummaryTableFoot,
                'htmlRankingBody'        => $htmlRankingBody,
                'hasSpgSummary'          => ($sales->count() > 0 && !$userId),
                'spgRankingData'         => $rankingData,
            ]);
        }

        return view('reports.sales', compact(
            'sales', 'totalQty', 'totalNominal',
            'month', 'locationId', 'userId',
            'locations', 'users', 'routeName',
            'spgRanking', 'maxNominal'
        ));
    }

    /**
     * Resolve date range dari request berdasarkan period.
     */
    private function resolveDateRange(Request $request, string $period, string $month): array
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        if ($startDate || $endDate) {
            return [$startDate, $endDate];
        }

        return match ($period) {
            'today'  => [Carbon::today()->toDateString(), Carbon::today()->toDateString()],
            'week'   => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
            'month'  => [
                Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString(),
                Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString(),
            ],
            default  => [null, null],
        };
    }
}
