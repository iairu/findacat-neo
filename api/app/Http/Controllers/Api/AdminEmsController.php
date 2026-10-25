<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmsColor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEmsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $color = EmsColor::create($this->validated($request));

        return response()->json(['data' => $color->fresh()], 201);
    }

    /**
     * @return array{data: EmsColor}
     */
    public function update(Request $request, string $id): array
    {
        $color = EmsColor::findOrFail($id);
        $color->update($this->validated($request));

        return ['data' => $color->fresh()];
    }

    public function destroy(string $id): JsonResponse
    {
        EmsColor::findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array{code: string, english_name: ?string, breed_id: ?string}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => 'required|string|max:40',
            'english_name' => 'nullable|string|max:255',
            'breed_id' => 'nullable|string|exists:breeds,id',
        ]);
    }
}
