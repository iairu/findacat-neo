<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FakePedigree;
use App\Services\ReferenceData;
use Illuminate\Http\JsonResponse;

class AdminSeedController extends Controller
{
    /** Add a generated multi-generation pedigree for demos and manual testing. */
    public function cats(FakePedigree $pedigree): JsonResponse
    {
        return response()->json(['data' => $pedigree->seed()], 201);
    }

    /** Load the full breed list and EMS colour catalogue; only missing rows are added. */
    public function reference(ReferenceData $reference): JsonResponse
    {
        return response()->json(['data' => $reference->load()], 201);
    }
}
