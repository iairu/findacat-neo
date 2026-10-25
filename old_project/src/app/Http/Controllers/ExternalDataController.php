<?php

namespace App\Http\Controllers;

use App\Services\PawPedsService;
use Illuminate\Http\Request;

class ExternalDataController extends Controller
{
    protected $pawPedsService;

    public function __construct(PawPedsService $pawPedsService)
    {
        $this->pawPedsService = $pawPedsService;
    }

    /**
     * Fetch cat data from PawPeds
     */
    public function fetchFromPawPeds(Request $request)
    {
        $request->validate([
            'reg_number' => 'required|string',
            'breed' => 'nullable|string|max:10'
        ]);

        $regNumber = $request->input('reg_number');
        $breed = $request->input('breed', 'nfo');

        $data = $this->pawPedsService->fetchCatData($regNumber, $breed);

        if ($data) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data fetched successfully from PawPeds'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No data found or unable to fetch from PawPeds'
        ], 404);
    }

    /**
     * Search PawPeds by name
     */
    public function searchPawPeds(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3',
            'breed' => 'nullable|string|max:10'
        ]);

        $name = $request->input('name');
        $breed = $request->input('breed', 'nfo');

        $results = $this->pawPedsService->searchByName($name, $breed);

        return response()->json([
            'success' => true,
            'results' => $results,
            'count' => count($results)
        ]);
    }

    /**
     * Get database stats
     */
    public function getStats()
    {
        $stats = [
            'total_cats' => \App\Cat::count(),
            'male_cats' => \App\Cat::where('gender_id', 1)->count(),
            'female_cats' => \App\Cat::where('gender_id', 2)->count(),
            'living_cats' => \App\Cat::whereNull('dod')->count(),
            'deceased_cats' => \App\Cat::whereNotNull('dod')->count(),
            'cats_with_photos' => \App\Cat::whereNotNull('photo')->count(),
            'recent_additions' => \App\Cat::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json($stats);
    }
}
