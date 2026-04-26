<?php

namespace App\Http\Controllers;

use App\Models\Njoftim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationStreamController extends Controller
{
    // How often (seconds) the loop checks for new notifications
    private const POLL_INTERVAL = 5;

    // How long (seconds) to keep the connection alive — Railway closes idle connections
    // Keep below PHP_MAX_EXECUTION_TIME on Railway (currently 30s → we'll bump it)
    private const MAX_LIFETIME = 50;

    public function stream(Request $request): StreamedResponse
    {
        $user = Auth::user();

        return new StreamedResponse(function () use ($user) {
            // Turn off output buffering so events reach the client immediately
            if (ob_get_level()) {
                ob_end_clean();
            }

            $startedAt   = time();
            $lastEventId = 0;  // tracks the last notification ID we already sent

            while (true) {
                // Stop after MAX_LIFETIME seconds — the frontend will reconnect automatically
                if ((time() - $startedAt) >= self::MAX_LIFETIME) {
                    $this->send('close', ['reason' => 'reconnect']);
                    break;
                }

                // Check if the client has disconnected (user closed the tab, etc.)
                if (connection_aborted()) {
                    break;
                }

                $cacheKey = "sse_notify_{$user->id}";

                // Only hit the DB if the model event (or bulk insert) set our flag.
                // This avoids a DB query every 5 seconds when nothing has changed.
                if (Cache::get($cacheKey)) {
                    Cache::forget($cacheKey);

                    $notifications = Njoftim::where('USER_ID', $user->id)
                        ->where('NJOF_ID', '>', $lastEventId)
                        ->orderBy('NJOF_ID')
                        ->get();

                    foreach ($notifications as $notification) {
                        $this->send('notification', [
                            'id'        => $notification->NJOF_ID,
                            'title'     => $notification->NJOF_TITULL,
                            'body'      => $notification->NJOF_TEKST,
                            'type'      => $notification->NJOF_TIPI,
                            'isRead'    => (bool) $notification->NJOF_IS_READ,
                            'createdAt' => (string) $notification->CREATED_AT,
                        ]);
                        $lastEventId = $notification->NJOF_ID;
                    }
                } else {
                    // No signal — send a comment to keep the connection alive.
                    // Comments start with ":" and are ignored by the client as data,
                    // but prevent proxies/Railway from closing the idle connection.
                    echo ": ping\n\n";
                }

                flush();
                sleep(self::POLL_INTERVAL);
            }
        }, 200, [
            // These headers tell the browser this is an SSE stream, not a normal response
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',   // disables Nginx buffering (Railway uses Nginx)
            'Connection'        => 'keep-alive',
        ]);
    }

    // Formats and echoes one SSE message
    private function send(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
    }
}
