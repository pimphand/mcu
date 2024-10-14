<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ImportCompleted
{
    use Dispatchable, SerializesModels;

    public $clientId;

    public function __construct($clientId)
    {
        $this->clientId = $clientId;
    }

    public function broadcastOn()
    {
        return new Channel('import-completed');
    }
}
