<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\PerformanceEvaluationService;
use Carbon\Carbon;

class EvaluatePerformance extends Command
{
    protected $signature = 'evaluate:performance {--month=} {--year=} {--user=}';
    protected $description = 'Generate smart performance evaluation using Gemini AI';

    public function handle(PerformanceEvaluationService $evaluator)
    {
        $month = $this->option('month') ?? Carbon::now()->month;
        $year = $this->option('year') ?? Carbon::now()->year;
        $userId = $this->option('user');

        $this->info("Evaluating performance for Month: $month, Year: $year");

        $query = User::where('is_active', true)
                     ->whereNotNull('division_id')
                     ->whereHas('role', function($q) {
                         $q->whereIn('slug', ['karyawan']);
                     });

        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $bar = $this->output->createProgressBar(count($users));

        foreach ($users as $user) {
            $evaluator->evaluateUser($user, $month, $year);
            $bar->advance();
            sleep(10); // Prevent Gemini rate limits (max 15 RPM for free tier)
        }

        $bar->finish();
        $this->newLine();
        $this->info('Performance evaluation completed!');
    }
}
