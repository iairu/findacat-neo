<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FakePedigree;
use App\Services\ReferenceData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSeedController extends Controller
{
    /** Add a generated multi-generation pedigree of the requested size for demos and manual testing. */
    public function cats(Request $request, FakePedigree $pedigree): JsonResponse
    {
        $data = $request->validate([
            'count' => ['nullable', 'integer', 'min:'.FakePedigree::MIN_CATS, 'max:'.FakePedigree::MAX_CATS],
        ]);

        return response()->json(['data' => $pedigree->seed((int) ($data['count'] ?? FakePedigree::DEFAULT_CATS))], 201);
    }

    /** Load the full breed list and EMS colour catalogue; only missing rows are added. */
    public function reference(ReferenceData $reference): JsonResponse
    {
        return response()->json(['data' => $reference->load()], 201);
    }
}
