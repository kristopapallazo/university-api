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

    /**
     * List notifications
     *
     * Returns all notifications for the authenticated user, ordered newest first.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [{"id": 1, "title": "Njoftim i ri", "body": "Teksti i njoftimit.", "type": "info", "isRead": false, "readAt": null, "createdAt": "2026-04-17T10:00:00.000000Z"}],
     *   "message": "Njoftimet u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
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

    /**
     * Unread notification count
     *
     * Returns the count of unread notifications for the authenticated user.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @response 200 {"data": {"count": 3}, "message": "OK", "status": 200}
     */
    public function unreadCount(): JsonResponse
    {
        $count = Njoftim::where('USER_ID', Auth::id())
            ->where('NJOF_IS_READ', false)
            ->count();

        return $this->success(['count' => $count], 'OK');
    }

    /**
     * Mark notification as read
     *
     * Marks a single notification as read. Only the owning user can mark their own notifications.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @urlParam id integer required The notification ID. Example: 1
     *
     * @response 200 {"data": {"id": 1, "title": "Njoftim i ri", "body": "Teksti i njoftimit.", "type": "info", "isRead": true, "readAt": "2026-04-17T10:05:00.000000Z", "createdAt": "2026-04-17T10:00:00.000000Z"}, "message": "Njoftimi u sh\u00ebnua si i lexuar.", "status": 200}
     * @response 404 {"data": null, "message": "Rekordi nuk u gjet.", "status": 404}
     */
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

    /**
     * Mark all notifications as read
     *
     * Marks all unread notifications of the authenticated user as read.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @response 200 {"data": null, "message": "T\u00eb gjitha njoftimet u sh\u00ebnuan si t\u00eb lexuara.", "status": 200}
     */
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
