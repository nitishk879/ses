<?php

namespace App\Listeners;

use App\Events\SkillMatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSkillMatchNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SkillMatch $event): void
    {
        //
    }
}
