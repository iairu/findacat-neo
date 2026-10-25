<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCatController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->validate(['status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])]])['status'] ?? 'pending';

        return Cat::query()->with('owner:id,name,email')->where('moderation_status', $status)
            ->orderBy('created_at')->paginate(50);
    }

    public function approve(string $id): array
    {
        return $this->setStatus($id, 'approved');
    }

    public function reject(string $id): array
    {
        return $this->setStatus($id, 'rejected');
    }

    private function setStatus(string $id, string $status): array
    {
        $cat = Cat::findOrFail($id);
        $cat->update(['moderation_status' => $status]);

        return ['data' => $cat->fresh()];
    }
}
