<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSettingController extends Controller
{
    /** Show or hide the "demo data only" banner on the cats page. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate(['demo_banner' => ['required', 'boolean']]);

        DB::table('app_settings')->upsert(
            [['name' => 'demo_banner', 'value' => $data['demo_banner'] ? '1' : '0']],
            ['name'],
            ['value'],
        );

        return response()->json(['demo_banner' => (bool) $data['demo_banner']]);
    }
}
