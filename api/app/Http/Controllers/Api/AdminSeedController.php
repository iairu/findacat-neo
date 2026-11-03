<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FakePedigree;
use Illuminate\Http\JsonResponse;

class AdminSeedController extends Controller
{
    /** Add a generated multi-generation pedigree for demos and manual testing. */
    public function cats(FakePedigree $pedigree): JsonResponse
    {
        return response()->json(['data' => $pedigree->seed()], 201);
    }
}
