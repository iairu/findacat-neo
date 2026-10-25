<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->query('q', '');

        return User::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->whereLike('name', '%'.addcslashes($search, '%_\\').'%')
                ->orWhereLike('email', '%'.addcslashes($search, '%_\\').'%')))
            ->orderBy('name')
            ->paginate(50, ['id', 'name', 'email', 'is_admin', 'email_verified_at', 'created_at']);
    }

    /**
     * @return array{data: User}
     */
    public function update(Request $request, string $id): array
    {
        $data = $request->validate(['is_admin' => 'required|boolean']);
        $user = User::findOrFail($id);

        if ($user->is($request->user()) && ! $data['is_admin']) {
            throw ValidationException::withMessages(['is_admin' => 'You cannot remove your own admin rights.']);
        }
        $user->forceFill(['is_admin' => $data['is_admin']])->save();

        return ['data' => $user->only(['id', 'name', 'email', 'is_admin'])];
    }
}
