<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cat;
use App\Services\Inbreeding;
use App\Services\Pedigree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CatController extends Controller
{
    private const LIST_COLUMNS = ['id', 'name', 'sex', 'born_on', 'died_on', 'breed_code', 'ems_code', 'title_prefix', 'title_suffix', 'photo', 'reg_number', 'sire_id', 'dam_id'];

    private const EDITABLE = [
        'name', 'sex', 'sire_id', 'dam_id', 'born_on', 'died_on', 'title_prefix', 'title_suffix', 'alt_name',
        'print_name_1', 'print_name_2', 'breed_code', 'ems_code', 'chip_number', 'genetic_tests', 'reg_number',
        'reg_number_last', 'reg_number_2', 'reg_number_3', 'breeding_station', 'breeder', 'owner_name',
        'country_code', 'country_of_origin', 'country', 'notes', 'ownership_notes', 'personal_info', 'photo',
        'vet_confirmation',
    ];

    public function __construct(private Pedigree $pedigree) {}

    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => 'nullable|string|max:255',
            'exact' => 'nullable|boolean',
            'sex' => ['nullable', Rule::in([Cat::MALE, Cat::FEMALE])],
            'breed_code' => 'nullable|string|max:20',
            'ems_code' => 'nullable|string|max:40',
            'born' => 'nullable|string|max:10',
            'reg_number' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        $exact = (bool) ($data['exact'] ?? false);
        $match = fn ($query, string $column, string $value) => $exact
            ? $query->where($column, $value)
            : $query->where($column, 'like', '%'.addcslashes($value, '%_\\').'%');

        $query = Cat::query()->approved();
        if (! empty($data['q'])) {
            $match($query, 'name', $data['q']);
        }
        if (! empty($data['sex'])) {
            $query->where('sex', $data['sex']);
        }
        if (! empty($data['breed_code'])) {
            $query->where('breed_code', $data['breed_code']);
        }
        if (! empty($data['ems_code'])) {
            $match($query, 'ems_code', $data['ems_code']);
        }
        if (! empty($data['born'])) {
            $query->where('born_on', 'like', addcslashes($data['born'], '%_\\').'%');
        }
        if (! empty($data['reg_number'])) {
            $query->where(fn ($q) => $match($q, 'reg_number', $data['reg_number'])
                ->orWhere(fn ($q2) => $match($q2, 'reg_number_last', $data['reg_number'])));
        }

        return $query->orderBy('name')->paginate($data['per_page'] ?? 24, self::LIST_COLUMNS);
    }

    public function show(Request $request, string $id): array
    {
        $cat = $this->findVisible($request, $id);
        $cat->load(['sire:id,name', 'dam:id,name', 'owner:id,name']);
        $children = $cat->children()->approved()->orderBy('born_on')->get(['id', 'name', 'sex', 'born_on']);

        return [
            'data' => array_merge($cat->toArray(), [
                'inbreeding' => Inbreeding::forCats([$cat->id])->coefficient($cat->id),
                'children' => $children,
                'can_edit' => $this->canEdit($request, $cat),
            ]),
        ];
    }

    public function pedigree(Request $request, string $id): array
    {
        $cat = $this->findVisible($request, $id);
        $generations = (int) $request->query('generations', 4);

        return ['data' => $this->pedigree->tree($cat, $generations)];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $this->checkParents($data, null);

        $cat = Cat::create($data + ['owner_id' => $request->user()->id]);

        return response()->json(['data' => $cat->fresh()], 201);
    }

    public function update(Request $request, string $id): array
    {
        $cat = Cat::findOrFail($id);
        abort_unless($this->canEdit($request, $cat), 403);

        $data = $this->validated($request, partial: true);
        $this->checkParents($data + $cat->only(['sex', 'sire_id', 'dam_id']), $cat);
        $cat->update($data);

        return ['data' => $cat->fresh()];
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $cat = Cat::findOrFail($id);
        abort_unless($this->canEdit($request, $cat), 403);
        $cat->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes|required' : 'required';
        $text = 'nullable|string|max:255';
        $rules = [
            'name' => "$req|string|max:255",
            'sex' => [$partial ? 'sometimes' : 'required', Rule::in([Cat::MALE, Cat::FEMALE])],
            'sire_id' => 'nullable|string|exists:cats,id',
            'dam_id' => 'nullable|string|exists:cats,id',
            'born_on' => 'nullable|date|before_or_equal:today',
            'died_on' => 'nullable|date|after_or_equal:born_on|before_or_equal:today',
            'notes' => 'nullable|string|max:5000',
            'ownership_notes' => 'nullable|string|max:5000',
            'personal_info' => 'nullable|string|max:5000',
        ];
        foreach (array_diff(self::EDITABLE, array_keys($rules)) as $field) {
            $rules[$field] = $text;
        }

        return $request->validate($rules);
    }

    /** @param array<string, mixed> $data */
    private function checkParents(array $data, ?Cat $cat): void
    {
        foreach (['sire' => Cat::MALE, 'dam' => Cat::FEMALE] as $role => $sex) {
            $parentId = $data[$role.'_id'] ?? null;
            if ($parentId === null) {
                continue;
            }
            $parent = Cat::findOrFail($parentId);
            if ($parent->sex !== $sex) {
                throw ValidationException::withMessages([$role.'_id' => "The {$role} must be a {$sex} cat."]);
            }
            if ($cat && $this->pedigree->isSelfOrAncestor($cat->id, $parentId)) {
                throw ValidationException::withMessages([$role.'_id' => 'A cat cannot be its own ancestor.']);
            }
        }
        if ($cat && isset($data['sex']) && $data['sex'] !== $cat->sex && $cat->children()->exists()) {
            throw ValidationException::withMessages(['sex' => 'The sex cannot change while the cat has kittens.']);
        }
    }

    private function findVisible(Request $request, string $id): Cat
    {
        $cat = Cat::findOrFail($id);
        abort_unless($cat->moderation_status === 'approved' || $this->canEdit($request, $cat), 404);

        return $cat;
    }

    private function canEdit(Request $request, Cat $cat): bool
    {
        $user = $request->user('sanctum');

        return $user !== null && ($user->is_admin || $cat->owner_id === $user->id);
    }
}
