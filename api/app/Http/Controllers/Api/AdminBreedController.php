<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Breed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBreedController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $breed = Breed::create($this->validated($request));

        return response()->json(['data' => $breed->fresh()], 201);
    }

    /**
     * @return array{data: Breed}
     */
    public function update(Request $request, string $id): array
    {
        $breed = Breed::findOrFail($id);
        $breed->update($this->validated($request, $breed));

        return ['data' => $breed->fresh()];
    }

    public function destroy(string $id): JsonResponse
    {
        Breed::findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array{code: string, name: ?string}
     */
    private function validated(Request $request, ?Breed $breed = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:breeds,code'.($breed ? ','.$breed->id : '')],
            'name' => 'nullable|string|max:255',
        ]);
    }
}
