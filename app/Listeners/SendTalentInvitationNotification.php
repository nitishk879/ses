<?php

namespace App\Listeners;

use App\Events\TalentInvitationEvent;
use App\Models\Company;
use App\Models\User;
use App\Notifications\TalentInvitationSend;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTalentInvitationNotification
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
    public function handle(TalentInvitationEvent $event): void
    {
        $invites = $event->project->talents->pluck('company_id')->toArray();
        $company = Company::whereIn('id', $invites)->firstOrFail();

        $company->notify(new TalentInvitationSend($event->project));
    }
}
