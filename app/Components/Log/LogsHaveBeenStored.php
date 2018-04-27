<?php

namespace App\Components\Log;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogsHaveBeenStored
{
    use Dispatchable, SerializesModels;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $user;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $logs;

    /**
     * @var string
     */
    protected $type;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param \Illuminate\Database\Eloquent\Collection $logs
     * @param string $type
     */
    public function __construct(Authenticatable $user, $logs, $type)
    {
        $this->user = $user;
        $this->logs = $logs;
        $this->type = $type;
    }
}
