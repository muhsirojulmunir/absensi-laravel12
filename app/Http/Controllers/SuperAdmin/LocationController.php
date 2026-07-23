<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', $request->get('search', ''));
        $perPage = 10;

        $locations = Location::query();

        if (!empty($query)) {
            $locations->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('latitude', 'like', '%' . $query . '%')
                  ->orWhere('longitude', 'like', '%' . $query . '%');

                $cleanQuery = preg_replace('/[^a-zA-Z0-9]/', '', $query);
                if (strlen($cleanQuery) >= 2 && strlen($cleanQuery) <= 4 && ctype_alpha($cleanQuery)) {
                    $pattern = implode('% ', str_split(strtoupper($cleanQuery))) . '%';
                    $pattern2 = '%' . implode('%', str_split(strtoupper($cleanQuery))) . '%';
                    $q->orWhere('name', 'like', $pattern)
                      ->orWhere('name', 'like', $pattern2);
                }

                $words = array_filter(explode(' ', $query));
                if (count($words) > 1) {
                    $q->orWhere(function($subQ) use ($words) {
                        foreach ($words as $w) {
                            $subQ->where('name', 'like', "%{$w}%");
                        }
                    });
                }
            });
        }

        $locations = $locations->latest()->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $locations->items(),
                'pagination' => [
                    'total' => $locations->total(),
                    'per_page' => $locations->perPage(),
                    'current_page' => $locations->currentPage(),
                    'last_page' => $locations->lastPage(),
                    'from' => $locations->firstItem(),
                    'to' => $locations->lastItem(),
                ]
            ]);
        }

        return view('super-admin.locations.index', compact('locations', 'query'));
    }

    public function create()
    {
        return view('super-admin.locations.create');
    }

    private function extractLatLngFromUrl($url)
    {
        if (empty($url)) return null;

        // Jika URL adalah shortlink, dapatkan final URL (follow redirects)
        if (strpos($url, 'goo.gl') !== false || strpos($url, 'maps.app.goo.gl') !== false) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
        }

        // Ekstrak pola @lat,lng
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        // Ekstrak dari parameter ?q=lat,lng
        if (preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }
        
        // Ekstrak dari parameter ll=lat,lng
        if (preg_match('/ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['latitude' => $matches[1], 'longitude' => $matches[2]];
        }

        return null;
    }

    public function store(Request $request)
    {
        if ($request->filled('google_maps_url') && (!$request->filled('latitude') || !$request->filled('longitude'))) {
            $coords = $this->extractLatLngFromUrl($request->google_maps_url);
            if ($coords) {
                $request->merge([
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude']
                ]);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'radius' => 'required|integer|min:1',
            'google_maps_url' => 'nullable|url',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ], [
            'latitude.required' => 'Gagal mengekstrak koordinat dari link. Masukkan link valid atau isi manual.',
            'longitude.required' => 'Gagal mengekstrak koordinat dari link. Masukkan link valid atau isi manual.'
        ]);

        Location::create($request->all());

        return redirect()->route('super-admin.locations.index')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        return view('super-admin.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        if ($request->filled('google_maps_url') && (!$request->filled('latitude') || !$request->filled('longitude'))) {
            $coords = $this->extractLatLngFromUrl($request->google_maps_url);
            if ($coords) {
                $request->merge([
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude']
                ]);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'radius' => 'required|integer|min:1',
            'google_maps_url' => 'nullable|url',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ], [
            'latitude.required' => 'Gagal mengekstrak koordinat dari link. Masukkan link valid atau isi manual.',
            'longitude.required' => 'Gagal mengekstrak koordinat dari link. Masukkan link valid atau isi manual.'
        ]);

        $location->update($request->all());

        return redirect()->route('super-admin.locations.index')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        try {
            // Karena tabel menggunakan MyISAM (tidak support foreign key cascade),
            // kita perlu set manual location_id user menjadi null sebelum hapus lokasi
            \App\Models\User::where('location_id', $location->id)->update(['location_id' => null]);
            
            // Bersihkan juga dari additional_location_ids
            $usersWithAdditional = \App\Models\User::whereNotNull('additional_location_ids')->get();
            foreach ($usersWithAdditional as $u) {
                if (is_array($u->additional_location_ids) && in_array($location->id, $u->additional_location_ids)) {
                    $u->additional_location_ids = array_values(array_diff($u->additional_location_ids, [$location->id]));
                    $u->save();
                }
            }
            
            $location->delete();
            
            return redirect()->route('super-admin.locations.index')->with('success', 'Lokasi "' . $location->name . '" berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('super-admin.locations.index')->with('error', 'Gagal menghapus lokasi: ' . $e->getMessage());
        }
    }
}
