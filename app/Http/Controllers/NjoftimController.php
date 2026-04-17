<?php

namespace App\Http\Controllers;

use App\Http\Resources\NjoftimResource;
use App\Http\Traits\ApiResponse;
use App\Models\Njoftim;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NjoftimController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $notifications = Njoftim::where('USER_ID', Auth::id())
            ->orderByDesc('CREATED_AT')
            ->get();

        return $this->success(
            NjoftimResource::collection($notifications),
            'Njoftimet u morën me sukses.'
        );
    }

    public function unreadCount(): JsonResponse
    {
        $count = Njoftim::where('USER_ID', Auth::id())
            ->where('NJOF_IS_READ', false)
            ->count();

        return $this->success(['count' => $count], 'OK');
    }

    public function markAsRead(int $id): JsonResponse
    {
        $notification = Njoftim::where('NJOF_ID', $id)
            ->where('USER_ID', Auth::id())
            ->firstOrFail();

        $notification->update([
            'NJOF_IS_READ' => true,
            'NJOF_READ_AT' => now(),
        ]);

        return $this->success(new NjoftimResource($notification), 'Njoftimi u shënua si i lexuar.');
    }

    public function markAllAsRead(): JsonResponse
    {
        Njoftim::where('USER_ID', Auth::id())
            ->where('NJOF_IS_READ', false)
            ->update([
                'NJOF_IS_READ' => true,
                'NJOF_READ_AT' => now(),
            ]);

        return $this->success(null, 'Të gjitha njoftimet u shënuan si të lexuara.');
    }
}
