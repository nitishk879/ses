<?php

namespace App\Listeners;

use App\Events\SavedProjectEvent;
use App\Notifications\SendNotificationSavedProject;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class SendSavedProjectNotification
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
    public function handle(SavedProjectEvent $event): void
    {
        $user = Auth::user();

        $user->notify(new SendNotificationSavedProject($event->project));
    }
}
