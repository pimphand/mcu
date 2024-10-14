<?php

namespace App\Listeners;

use App\Events\ImportCompleted;
use Pusher\Pusher;

class ImportCompletedListener
{
    public function handle(ImportCompleted $event)
    {
        $options = [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true
        ];

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $pusher->trigger('notification-channel', 'import-completed', [
            'clientId' => $event->clientId,
            'message' => 'Import completed successfully!'
        ]);
    }
}
