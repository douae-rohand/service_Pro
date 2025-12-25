<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SseController extends Controller
{
    /**
     * Stream SSE events
     */
    public function stream(Request $request)
    {
        $type = $request->query('type'); // 'admin' or 'intervenant'
        $id = $request->query('id'); // Required for intervenant

        // Disable buffering
        if (ob_get_level()) ob_end_clean();
        
        return response()->stream(function() use ($type, $id) {
            set_time_limit(0); // Prevent timeout
            
            try {
                // Initial connection headers
                echo "retry: 2000\n"; // Reconnect every 2 seconds if lost
                echo "event: connected\n";
                echo "data: {\"message\": \"Connected to SSE stream ({$type})\"}\n\n";
                flush();

                // Main Loop
                while (true) {
                    if (connection_aborted()) {
                        break;
                    }

                    if ($type === 'admin') {
                        $this->handleAdminStream();
                    } elseif ($type === 'intervenant' && $id) {
                        $this->handleIntervenantStream($id);
                    }

                    // Keep-alive ping
                    echo ": keep-alive\n\n";
                    flush();

                    sleep(1); // Check every second
                }
            } catch (\Exception $e) {
                Log::error("SSE Stream Error: " . $e->getMessage());
                // We cannot return a JSON response here as headers are already sent
                // Just close the stream
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no' // Important for Nginx
        ]);
    }

    private function handleAdminStream()
    {
        // Check for "new request" signal
        // We use a timestamp to avoid sending the same event multiple times if needed, 
        // OR we just forget the key if we want to consume it.
        // For simplicity: If key exists, send event and delete key.
        // Warning: Race condition if multiple admins?
        // Better approach: The signal is "there is a new request since X".
        // But for this simple implementation: Just consume the signal.
        
        // Actually, if we have multiple admins, consuming it means only one sees it.
        // Better: The signal stays for 60 seconds (TTL). We send it if we haven't sent it yet.
        // But "session" state in PHP stream is tricky without external store.
        
        // Let's use a simple "consume" approach for now assuming 1 active admin usually, 
        // OR use a "last_event_id" mechanism. 
        // To make it robust without DB, let's just check if the cache key exists.
        // Logic: Controller puts 'admin_new_request' = timestamp.
        // Stream tracks $lastTimestamp. If cache > $lastTimestamp, send event.
        
        static $lastMsgTime = 0;
        
        $signalTime = Cache::get('admin_new_request');
        
        if ($signalTime && $signalTime > $lastMsgTime) {
            $lastMsgTime = $signalTime;
            
            echo "event: new_request\n";
            echo "data: {\"message\": \"Nouvelle demande d'activation !\", \"timestamp\": {$signalTime}}\n\n";
            flush();
        }
    }

    private function handleIntervenantStream($intervenantId)
    {
        static $lastMsgTime = 0;

        // Key: "intervenant_request_update_{$id}"
        // Value: JSON or Array with 'status', 'service', 'timestamp'
        $data = Cache::get("intervenant_request_update_{$intervenantId}");

        if ($data && isset($data['timestamp']) && $data['timestamp'] > $lastMsgTime) {
            $lastMsgTime = $data['timestamp'];
            
            echo "event: request_update\n";
            echo "data: " . json_encode($data) . "\n\n";
            flush();
        }
    }
}
