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

    /**
     * Send notification (Admin)
     *
     * Sends a notification to a specific user or to all users of a given role.
     * If `recipient_user_id` is provided, only that user receives it.
     * Otherwise, every user matching `recipient_role` (or all users if omitted) receives it.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @bodyParam title string required Notification title (max 200 chars). Example: "Rezultate provimi"
     * @bodyParam body string required Notification body text. Example: "Rezultatet e provimit janë publikuar."
     * @bodyParam type string required One of: info, sukses, paralajmerim. Example: "info"
     * @bodyParam recipient_role string optional One of: student, pedagog, admin, all. Defaults to all. Example: "student"
     * @bodyParam recipient_user_id integer optional Send to a single user by user ID. Example: 42
     *
     * @response 200 {"data": null, "message": "Njoftimet u d\u00ebrguan me sukses tek 120 p\u00ebrdorues.", "status": 200}
     * @response 422 {"message": "The given data was invalid.", "errors": {}}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'type' => ['required', Rule::in(['info', 'sukses', 'paralajmerim'])],
            'recipient_role' => ['nullable', Rule::in(['student', 'pedagog', 'admin', 'all'])],
            'recipient_user_id' => 'nullable|integer|exists:users,id',
        ]);

        $adminId = Auth::id();

        if (! empty($data['recipient_user_id'])) {
            Njoftim::create([
                'USER_ID' => $data['recipient_user_id'],
                'NJOF_TITULL' => $data['title'],
                'NJOF_TEKST' => $data['body'],
                'NJOF_TIPI' => $data['type'],
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
            'USER_ID' => $userId,
            'NJOF_TITULL' => $data['title'],
            'NJOF_TEKST' => $data['body'],
            'NJOF_TIPI' => $data['type'],
            'SENT_BY_ADMIN_ID' => $adminId,
            'NJOF_IS_READ' => false,
            'CREATED_AT' => now(),
            'UPDATED_AT' => now(),
        ])->toArray();

        foreach (array_chunk($rows, 500) as $chunk) {
            Njoftim::insert($chunk);
        }

        return $this->success(null, 'Njoftimet u dërguan me sukses tek ' . count($rows) . ' përdorues.');
    }
}
