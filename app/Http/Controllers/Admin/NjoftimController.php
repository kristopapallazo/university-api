<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Njoftim;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NjoftimController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'             => 'required|string|max:200',
            'body'              => 'required|string',
            'type'              => ['required', Rule::in(['info', 'sukses', 'paralajmerim'])],
            'recipient_role'    => ['nullable', Rule::in(['student', 'pedagog', 'admin', 'all'])],
            'recipient_user_id' => 'nullable|integer|exists:users,id',
        ]);

        $adminId = Auth::id();

        if (! empty($data['recipient_user_id'])) {
            Njoftim::create([
                'USER_ID'          => $data['recipient_user_id'],
                'NJOF_TITULL'      => $data['title'],
                'NJOF_TEKST'       => $data['body'],
                'NJOF_TIPI'        => $data['type'],
                'SENT_BY_ADMIN_ID' => $adminId,
            ]);

            return $this->success(null, 'Njoftimi u dërgua me sukses.');
        }

        $role = $data['recipient_role'] ?? 'all';
        $query = User::query();

        if ($role !== 'all') {
            $query->where('role', $role);
        }

        $users = $query->pluck('id');

        $rows = $users->map(fn ($userId) => [
            'USER_ID'          => $userId,
            'NJOF_TITULL'      => $data['title'],
            'NJOF_TEKST'       => $data['body'],
            'NJOF_TIPI'        => $data['type'],
            'SENT_BY_ADMIN_ID' => $adminId,
            'NJOF_IS_READ'     => false,
            'CREATED_AT'       => now(),
            'UPDATED_AT'       => now(),
        ])->toArray();

        foreach (array_chunk($rows, 500) as $chunk) {
            Njoftim::insert($chunk);
        }

        return $this->success(null, 'Njoftimet u dërguan me sukses tek ' . count($rows) . ' përdorues.');
    }
}
