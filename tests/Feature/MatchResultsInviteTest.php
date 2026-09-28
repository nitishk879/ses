<?php

namespace Tests\Feature;

use App\Enums\InterviewStatus;
use App\Livewire\Projects\MatchResults;
use App\Models\AiMatch;
use App\Models\Company;
use App\Models\Interview;
use App\Models\Project;
use App\Models\Talent;
use App\Models\User;
use App\Notifications\InterviewInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Inviting from the matching screen — the only path that sends invitations.
 *
 * It used to be one of two: an "Actions" panel on the interviews dashboard sent
 * the same email from a threshold alone, with no list of who was about to
 * receive it and a batch silently capped at 25. That panel is gone, which makes
 * the assertions here the whole contract rather than half of it.
 *
 * What is actually being pinned down is the count in the confirmation message.
 * InterviewInvitationService::invite() is idempotent — a candidate who already
 * holds a live invitation comes back untouched, without throwing and without
 * sending — so a loop that counts returns reports people it never emailed. A
 * recruiter reading "Invited 5" stops chasing the three who got nothing.
 */
class MatchResultsInviteTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $recruiter;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $company = Company::factory()->create();

        $this->project = Project::factory()->create([
            'company_id' => $company->id,
            'interview_agent_id' => 'bot_abc123',
        ]);

        $this->recruiter = User::factory()->create();
        // isMatchableBy() scopes on the viewer's own company.
        $this->recruiter->company()->save($company);
    }

    /** users.phone is unique, so each candidate needs their own. */
    private int $phoneSeq = 0;

    /** A scored, dialable candidate for this project. */
    private function candidate(int $score = 90): Talent
    {
        $user = User::factory()->create([
            'phone' => sprintf('0901234%04d', ++$this->phoneSeq),
        ]);
        $talent = Talent::factory()->create(['user_id' => $user->id]);

        AiMatch::create([
            'project_id' => $this->project->id,
            'talent_id' => $talent->id,
            'score' => $score,
            'meets_mandatory' => true,
            'unverified_mandatory' => 0,
            'scorer_version' => 'test',
            'jd_source_hash' => str_repeat('0', 64),
            'resume_source_hash' => str_repeat('1', 64),
            'scored_at' => now(),
            'payload' => ['mandatory_results' => []],
        ]);

        return $talent;
    }

    /** Three times in the future, which is what the panel requires. */
    private function slots(): array
    {
        $base = now('Asia/Tokyo')->addDays(2)->setTime(10, 0);

        return [
            $base->format('Y-m-d\TH:i'),
            $base->copy()->addHour()->format('Y-m-d\TH:i'),
            $base->copy()->addHours(2)->format('Y-m-d\TH:i'),
        ];
    }

    private function screen()
    {
        return Livewire::actingAs($this->recruiter)
            ->test(MatchResults::class, ['project' => $this->project]);
    }

    public function test_a_candidate_with_a_live_invitation_is_not_counted_as_sent(): void
    {
        $fresh = $this->candidate();
        $alreadyInvited = $this->candidate();

        // A live invitation: invite() returns this untouched and sends nothing.
        Interview::factory()->create([
            'project_id' => $this->project->id,
            'talent_id' => $alreadyInvited->id,
            'status' => InterviewStatus::SLOT_SELECTION,
            'invitation_token' => str_repeat('a', 64),
            'invitation_sent_at' => now()->subDay(),
        ]);

        $this->screen()
            ->set('slotTimes', $this->slots())
            ->set('selected', [$fresh->id, $alreadyInvited->id])
            ->call('inviteSelected')
            ->assertDispatched('notify', function (string $event, array $params) {
                // One email, and the other one said out loud rather than folded
                // into the total.
                return str_contains($params['message'], '1')
                    && str_contains($params['message'], 'not emailed again');
            });

        Notification::assertSentTimes(InterviewInvitation::class, 1);
        Notification::assertSentTo($fresh->user, InterviewInvitation::class);
        Notification::assertNotSentTo($alreadyInvited->user, InterviewInvitation::class);
    }

    public function test_a_batch_of_only_already_invited_candidates_sends_nothing(): void
    {
        $talent = $this->candidate();

        Interview::factory()->create([
            'project_id' => $this->project->id,
            'talent_id' => $talent->id,
            'status' => InterviewStatus::SLOT_SELECTION,
            'invitation_token' => str_repeat('b', 64),
        ]);

        $this->screen()
            ->set('slotTimes', $this->slots())
            ->set('selected', [$talent->id])
            ->call('inviteSelected');

        Notification::assertNothingSent();
    }

    public function test_an_expired_invitation_is_invitable_again(): void
    {
        $talent = $this->candidate();

        // Cancelled and token-less: REINVITABLE, so this one *should* be
        // emailed. The badge on screen has to agree with that, or it tells the
        // recruiter the opposite of what the button will do.
        Interview::factory()->create([
            'project_id' => $this->project->id,
            'talent_id' => $talent->id,
            'status' => InterviewStatus::CANCELLED,
            'invitation_token' => null,
        ]);

        $this->screen()
            ->set('slotTimes', $this->slots())
            ->set('selected', [$talent->id])
            ->call('inviteSelected');

        Notification::assertSentTo($talent->user, InterviewInvitation::class);
    }

    public function test_nothing_is_sent_without_an_interview_bot(): void
    {
        $this->project->update(['interview_agent_id' => null]);

        $talent = $this->candidate();

        $this->screen()
            ->set('slotTimes', $this->slots())
            ->set('selected', [$talent->id])
            ->call('inviteSelected');

        Notification::assertNothingSent();
    }

    public function test_nothing_is_sent_while_the_bot_picker_is_unsaved(): void
    {
        $talent = $this->candidate();

        $this->screen()
            // Chosen in the box, never saved. Sending now would call the
            // candidate with the bot the project still holds, while the screen
            // promised a different one.
            ->set('interviewAgentId', 'bot_someone_else')
            ->set('slotTimes', $this->slots())
            ->set('selected', [$talent->id])
            ->call('inviteSelected');

        Notification::assertNothingSent();
    }

    public function test_nothing_is_sent_until_all_three_times_are_chosen(): void
    {
        $talent = $this->candidate();

        $this->screen()
            ->set('slotTimes', [$this->slots()[0], '', ''])
            ->set('selected', [$talent->id])
            ->call('inviteSelected');

        Notification::assertNothingSent();
    }

    public function test_saving_a_bot_records_it_on_the_project(): void
    {
        $this->screen()
            ->set('interviewAgentId', 'bot_xyz789')
            ->call('saveBot');

        $this->assertSame('bot_xyz789', $this->project->fresh()->interview_agent_id);
    }

    public function test_a_pasted_url_is_rejected_as_a_bot_id(): void
    {
        $this->screen()
            ->set('interviewAgentId', 'https://dashboard.example/agents/abc')
            ->call('saveBot');

        // Left exactly as it was — a rejected id must not clear a working one.
        $this->assertSame('bot_abc123', $this->project->fresh()->interview_agent_id);
    }

    public function test_another_companys_project_cannot_be_matched(): void
    {
        $outsider = User::factory()->create();
        $outsider->company()->save(Company::factory()->create());

        Livewire::actingAs($outsider)
            ->test(MatchResults::class, ['project' => $this->project])
            ->assertForbidden();
    }

    /**
     * The duplicated dashboard endpoints are gone, not merely unlinked.
     *
     * Hiding the button would have left a POST that still sends real email to
     * anyone who knew the path.
     */
    public function test_the_dashboard_invite_and_match_endpoints_no_longer_exist(): void
    {
        foreach (['invite', 'match', 'bot'] as $action) {
            $this->actingAs($this->recruiter)
                ->post("interviews/dashboard/{$action}/{$this->project->id}")
                ->assertNotFound();
        }

        Notification::assertNothingSent();
    }
}
