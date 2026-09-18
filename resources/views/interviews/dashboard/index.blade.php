@extends('layouts.app')

@section('title', __('interview.dashboard.title'))

@section('content')
<div class="container-fluid container-lg my-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">{{ __('interview.dashboard.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('interview.dashboard.subtitle') }}</p>
        </div>

        @if($projects->isNotEmpty())
            {{-- The two operations that used to be artisan commands. --}}
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary" type="button"
                        data-bs-toggle="collapse" data-bs-target="#runActions">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                    {{ __('interview.dashboard.actions') }}
                </button>
            </div>
        @endif
    </div>

    @if($projects->isNotEmpty())
        @php
            // One map, read when the project changes: which bot it already has,
            // and whether matching has produced anything to invite from.
            $interviewSettings = $projects->mapWithKeys(fn ($p) => [
                $p->id => [
                    'agent' => $p->interview_agent_id,
                    'scored' => (int) ($scoredCounts[$p->id] ?? 0),
                ],
            ]);
        @endphp

        <div class="collapse mb-3" id="runActions">
            {{-- One project selector for all three actions.
                 There used to be three, one per form: the same question asked
                 three times, and nothing stopping a recruiter running matching
                 on one project while inviting from another without noticing. --}}
            <div class="card card-body mb-3">
                <label class="form-label fw-semibold mb-1" for="actionProject">
                    {{ __('interview.dashboard.project') }}
                </label>
                <select class="form-select" id="actionProject" required
                        data-required-hint="{{ __('interview.dashboard.choose_project_first') }}"
                        data-scores-hint="{{ __('interview.dashboard.run_matching_first') }}"
                        data-bot-hint="{{ __('interview.dashboard.bot_required') }}"
                        data-unsaved-hint="{{ __('interview.dashboard.bot_unsaved') }}"
                        data-slots-hint="{{ __('interview.dashboard.slot_times_required') }}">
                    <option value="">{{ __('interview.dashboard.choose_project') }}</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected((int) ($filters['project'] ?? 0) === $p->id)>
                            {{ $p->title }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('interview.dashboard.project_applies_to_all') }}</div>
                {{-- Says why the buttons below are dead, rather than leaving a
                     recruiter to work it out from three greyed-out cards. --}}
                <div class="form-text text-warning" data-needs-project>
                    {{ __('interview.dashboard.choose_project_first') }}
                </div>
            </div>

            {{-- Two columns, not three.
                 The three actions are nowhere near equal in size: matching is a
                 single button, the bot is one select, and inviting carries a
                 score, three time pickers and their help. Forcing them into
                 three equal cards with h-100 stretched the two small ones to
                 the height of the large one and left a hand's width of empty
                 card under each button.

                 So the two small ones stack in a narrow column and the large
                 one gets a wide column beside them. Nothing is stretched to
                 match anything else; each card is the height of its contents. --}}
            <div class="row g-3">
                <div class="col-lg-5">
                    {{-- 1. Matching --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <h2 class="h6">{{ __('interview.dashboard.run_matching') }}</h2>
                            <p class="text-muted small mb-3">{{ __('interview.dashboard.run_matching_help') }}</p>
                            <form method="POST" data-project-form
                                  data-action-base="{{ url('interviews/dashboard/match') }}"
                                  action="{{ url('interviews/dashboard/match') }}/0">
                                @csrf
                                <button class="btn btn-primary w-100" type="submit">
                                    {{ __('interview.dashboard.run') }}
                                </button>
                            </form>

                            {{-- Follows the project selector above, like the forms do. --}}
                            <a class="btn btn-outline-primary w-100 mt-2 disabled"
                               data-project-link
                               data-link-base="{{ url('projects') }}"
                               data-link-suffix="matches"
                               href="#">
                                {{ __('interview.match_run.open_screen') }}
                            </a>
                        </div>
                    </div>

                    {{-- 2. Which bot conducts the calls --}}
                    <div class="card">
                        <div class="card-body">
                            <h2 class="h6">{{ __('interview.dashboard.interview_bot') }}</h2>
                            <p class="text-muted small mb-3">{{ __('interview.dashboard.interview_bot_help') }}</p>
                            <form method="POST" data-project-form
                                  data-action-base="{{ url('interviews/dashboard/bot') }}"
                                  action="{{ url('interviews/dashboard/bot') }}/0">
                                @csrf
                                <label class="form-label small mb-1" for="interviewAgentId">
                                    {{ __('interview.dashboard.bot') }}
                                </label>
                                @if(! empty($bots))
                                    <select class="form-select mb-3" id="interviewAgentId" name="interview_agent_id">
                                        <option value="">{{ __('interview.dashboard.no_bot') }}</option>
                                        @foreach($bots as $bot)
                                            <option value="{{ $bot['agent_id'] }}">
                                                {{ $bot['name'] }}@if(! empty($bot['language'])) — {{ $bot['language'] }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    {{-- An unreachable directory is said out loud rather than
                                         rendered as "no bots exist", which would send a
                                         recruiter off to create one that already exists. --}}
                                    <input type="text" class="form-control" id="interviewAgentId"
                                           name="interview_agent_id" maxlength="64"
                                           placeholder="{{ __('interview.dashboard.bot_id_placeholder') }}">
                                    <div class="form-text text-warning mb-3">
                                        {{ __('interview.dashboard.bot_list_unavailable') }}
                                    </div>
                                @endif
                                <button class="btn btn-outline-primary w-100" type="submit">
                                    {{ __('interview.dashboard.save_bot') }}
                                </button>
                                {{-- With a bot assigned, the bot's prompt IS the
                                     interview: SES supplies only the recorded-call
                                     opening, the candidate's facts and the closing,
                                     and every question comes from the dashboard.
                                     The AI service can fall back to generated
                                     questions, but this product does not want that
                                     — so no bot means no invitations, and this says
                                     so where the bot is chosen. --}}
                                <div class="form-text text-warning mt-2" data-no-bot-hint hidden>
                                    {{ __('interview.dashboard.no_bot_warning') }}
                                </div>
                                <div class="form-text text-warning mt-2" data-unsaved-bot-hint hidden>
                                    {{ __('interview.dashboard.bot_unsaved') }}
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- 3. Invite the shortlist --}}
                <div class="col-lg-7">
                    {{-- No h-100: stretching this to match the stacked column on the
                         left is what left a band of empty card under the Send
                         button. Each column is the height of what is in it. --}}
                    <div class="card">
                        <div class="card-body">
                            <h2 class="h6">{{ __('interview.dashboard.invite_shortlist') }}</h2>
                            <p class="text-muted small mb-3">{{ __('interview.dashboard.invite_help') }}</p>
                            <form method="POST" data-project-form data-needs-scores
                                  data-action-base="{{ url('interviews/dashboard/invite') }}"
                                  action="{{ url('interviews/dashboard/invite') }}/0"
                                  onsubmit="return confirm(@js(__('interview.dashboard.invite_confirm')))">
                                @csrf
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-4">
                                        <label class="form-label small mb-1" for="threshold">
                                            {{ __('interview.dashboard.threshold') }}
                                        </label>
                                        <input type="number" class="form-control" id="threshold" name="threshold"
                                               min="0" max="100"
                                               value="{{ config('services.interview.invitation.min_match_score', 70) }}">
                                    </div>
                                </div>

                                {{-- Side by side now that this card is wide: three
                                     stacked pickers were most of its height. --}}
                                <label class="form-label small mb-1">
                                    {{ __('interview.dashboard.slot_times') }}
                                </label>
                                <div class="row g-2 mb-1">
                                    @for($i = 0; $i < 3; $i++)
                                        <div class="col-md-4">
                                            <input type="datetime-local" class="form-control"
                                                   name="slot_times[]" required
                                                   value="{{ old('slot_times.'.$i) }}">
                                        </div>
                                    @endfor
                                </div>
                                <div class="form-text mb-3">
                                    {{ __('interview.dashboard.slot_times_help', [
                                        'zone' => config('services.interview.invitation.timezone', 'Asia/Tokyo'),
                                    ]) }}
                                </div>

                                <button class="btn btn-success w-100" type="submit">
                                    {{ __('interview.dashboard.send') }}
                                </button>
                                {{-- Sits under the button it explains. Hidden the
                                     moment the chosen project has scores. --}}
                                <div class="form-text text-warning mt-2" data-needs-scores-hint hidden>
                                    {{ __('interview.dashboard.run_matching_first') }}
                                </div>
                                <div class="form-text text-warning mt-2" data-no-bot-hint hidden>
                                    {{ __('interview.dashboard.bot_required') }}
                                </div>
                                <div class="form-text text-warning mt-2" data-unsaved-bot-hint hidden>
                                    {{ __('interview.dashboard.bot_unsaved') }}
                                </div>
                                <div class="form-text text-warning mt-2" data-needs-slots-hint hidden>
                                    {{ __('interview.dashboard.slot_times_required') }}
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- /#runActions --}}

        <script>
            (function () {
                const project  = document.getElementById('actionProject');
                const forms    = document.querySelectorAll('[data-project-form]');
                const agentBox = document.getElementById('interviewAgentId');
                const SETTINGS = @json($interviewSettings);

                // Every form posts to the same project, so one selector drives
                // all three action URLs.
                //
                // The three buttons are also held shut until a project is
                // picked. The selector carries `required`, but it sits outside
                // all three forms, so the browser never validates it — pressing
                // Send with nothing chosen posted to `.../invite/0` and route
                // model binding answered with a bare 404. A 404 does not tell a
                // recruiter they forgot to choose a project; it reads as the
                // feature being broken.
                function syncProject() {
                    const chosen   = project.value !== '';
                    const settings = SETTINGS[project.value] || {};
                    // Send needs two things, and they are different in kind.
                    //
                    // Scores: Invite filters on them, so without matching there is
                    // nobody to choose from.
                    //
                    // A bot: a product rule rather than a technical one. The AI
                    // service would run the interview on questions SES generates,
                    // but the questions that matter are the ones written in the
                    // bot's prompt — so an interview without a bot asks the wrong
                    // things. The server refuses it too; this only saves the trip.
                    const scored = chosen && (settings.scored || 0) > 0;

                    // What is SAVED on the project, not what happens to be showing
                    // in the picker. A selection nobody pressed Save on is not the
                    // bot that will conduct the call.
                    const savedAgent = settings.agent || '';
                    const hasBot     = chosen && savedAgent !== '';

                    // ...but a picker showing something different from what is
                    // saved is its own problem: the screen would be promising one
                    // bot while Send used another. The panel read this box and
                    // never listened to it, so choosing "No bot" and not saving
                    // left Send enabled under a dropdown that said there was none.
                    const pending = chosen && agentBox && agentBox.value !== savedAgent;

                    // All three offered times. They used to be optional — empty
                    // meant "generate three" — but an invitation from this panel
                    // now goes out only on slots the recruiter picked, so a blank
                    // one is a missing answer rather than a default.
                    const slots = Array.from(
                        document.querySelectorAll('input[name="slot_times[]"]')
                    );
                    const slotsFilled = slots.length > 0 && slots.every(el => el.value !== '');

                    forms.forEach(function (f) {
                        f.action = f.dataset.actionBase + '/' + (project.value || '0');

                        const submit = f.querySelector('[type="submit"]');
                        if (! submit) return;

                        const needsScores = f.hasAttribute('data-needs-scores');
                        const ok = chosen
                            && (! needsScores || (scored && hasBot && ! pending && slotsFilled));

                        submit.disabled = ! ok;
                        // Name the step that is actually missing. Telling someone
                        // to run matching when what they lack is a bot sends them
                        // to press a button that changes nothing.
                        submit.title = ok ? ''
                            : ! chosen ? (project.dataset.requiredHint || '')
                            : ! scored ? (project.dataset.scoresHint || '')
                            : ! hasBot ? (project.dataset.botHint || '')
                            : pending ? (project.dataset.unsavedHint || '')
                            : (project.dataset.slotsHint || '');
                    });

                    // The link to the full matching screen follows the same
                    // selector. Held shut until a project is chosen, because
                    // /projects/0/matches is a 404 that reads as a broken
                    // feature rather than as a missing answer.
                    document.querySelectorAll('[data-project-link]').forEach(function (a) {
                        a.classList.toggle('disabled', ! chosen);
                        a.href = chosen
                            ? a.dataset.linkBase + '/' + project.value + '/' + a.dataset.linkSuffix
                            : '#';
                        a.title = chosen ? '' : (project.dataset.requiredHint || '');
                    });

                    document.querySelectorAll('[data-needs-project]')
                        .forEach(el => el.hidden = chosen);

                    // Said where the disabled button is, not at the top of the
                    // panel: "why can I not press Send" is asked at the button.
                    document.querySelectorAll('[data-needs-scores-hint]')
                        .forEach(el => el.hidden = ! chosen || scored);

                    // Shown on both cards while the bot is missing: beside Save
                    // Bot, which is where it gets fixed, and under Send, which is
                    // where it is noticed.
                    document.querySelectorAll('[data-no-bot-hint]')
                        .forEach(el => el.hidden = ! chosen || hasBot);

                    document.querySelectorAll('[data-unsaved-bot-hint]')
                        .forEach(el => el.hidden = ! pending);

                    document.querySelectorAll('[data-needs-slots-hint]')
                        .forEach(el => el.hidden = ! chosen || slotsFilled);
                }

                // Two entry points, because they mean different things: changing
                // the project reloads the picker from what that project has saved,
                // while changing the picker only re-judges the gates.
                function selectProject() {
                    if (agentBox) {
                        // Show the bot this project already has, so opening the
                        // panel to change one thing does not save a blank over it.
                        agentBox.value = (SETTINGS[project.value] || {}).agent || '';
                    }
                    syncProject();
                }

                project.addEventListener('change', selectProject);
                agentBox?.addEventListener('change', syncProject);
                // `input` as well as `change`: a datetime-local fires `change`
                // only once the whole value is valid, so clearing one field
                // would otherwise leave Send enabled until focus moved.
                document.querySelectorAll('input[name="slot_times[]"]').forEach(function (el) {
                    el.addEventListener('change', syncProject);
                    el.addEventListener('input', syncProject);
                });
                selectProject();

                // No past times in the picker. Recomputed on focus, not only at
                // page load, so a form left open over lunch does not still
                // offer this morning.
                function stampNow() {
                    const now = new Date(Date.now() - new Date().getTimezoneOffset() * 60000);
                    const min = now.toISOString().slice(0, 16);
                    document.querySelectorAll('input[name="slot_times[]"]').forEach(el => el.min = min);
                }
                stampNow();
                document.querySelectorAll('input[name="slot_times[]"]')
                    .forEach(el => el.addEventListener('focus', stampNow));
            })();
        </script>
    @endif

    {{-- Counts, so the first thing a recruiter sees is what needs them. --}}
    <div class="row g-2 mb-3">
        @foreach([
            'total' => 'secondary',
            'awaiting_reply' => 'info',
            'scheduled' => 'primary',
            'completed' => 'success',
            'needs_attention' => 'warning',
        ] as $key => $variant)
            <div class="col-6 col-md">
                <div class="card h-100 border-{{ $variant }}">
                    <div class="card-body py-2 px-3">
                        <div class="fs-4 fw-semibold">{{ $summary[$key] }}</div>
                        <div class="small text-muted">{{ __("interview.dashboard.stat_{$key}") }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label small mb-1" for="q">{{ __('interview.dashboard.search') }}</label>
            <input type="search" class="form-control" id="q" name="q"
                   value="{{ $filters['q'] ?? '' }}"
                   placeholder="{{ __('interview.dashboard.search_placeholder') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1" for="statusFilter">{{ __('interview.dashboard.status') }}</label>
            <select class="form-select" id="statusFilter" name="status">
                <option value="">{{ __('interview.dashboard.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ \App\Enums\InterviewStatus::toName($status) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1" for="projectFilter">{{ __('interview.dashboard.project') }}</label>
            <select class="form-select" id="projectFilter" name="project">
                <option value="">{{ __('interview.dashboard.all_projects') }}</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" @selected((int) ($filters['project'] ?? 0) === $p->id)>
                        {{ $p->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-outline-primary flex-fill" type="submit">
                {{ __('interview.dashboard.filter') }}
            </button>
            <a class="btn btn-link" href="{{ route('interview-dashboard.index') }}">
                {{ __('interview.dashboard.clear') }}
            </a>
        </div>
    </form>

    @if($interviews->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-1">{{ __('interview.dashboard.empty') }}</p>
                <p class="small text-muted mb-0">{{ __('interview.dashboard.empty_help') }}</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('interview.dashboard.candidate') }}</th>
                            <th>{{ __('interview.dashboard.project') }}</th>
                            <th class="text-center">{{ __('interview.dashboard.match') }}</th>
                            <th>{{ __('interview.dashboard.status') }}</th>
                            <th>{{ __('interview.dashboard.scheduled') }}</th>
                            <th class="text-center">{{ __('interview.dashboard.result') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($interviews as $interview)
                        @php
                            $evaluation = $interview->latestAttempt?->evaluation;
                            $tz = $interview->timezone ?: config('services.interview.invitation.timezone', 'Asia/Tokyo');
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $interview->talent?->user?->name ?: '—' }}</div>
                                <div class="small text-muted">{{ $interview->talent?->user?->email }}</div>
                            </td>
                            <td class="small">{{ $interview->project?->title ?: '—' }}</td>
                            <td class="text-center">
                                @if($interview->match_score !== null)
                                    <span class="badge bg-light text-dark border">{{ $interview->match_score }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $interview->status->badgeVariant() }}">
                                    {{ \App\Enums\InterviewStatus::toName($interview->status) }}
                                </span>
                            </td>
                            <td class="small">
                                @if($interview->scheduled_at)
                                    {{ $interview->scheduled_at->setTimezone($tz)->translatedFormat('D, j M Y H:i') }}
                                    <span class="text-muted">({{ $tz }})</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($evaluation && $evaluation->overall_score !== null)
                                    <span class="fw-semibold">{{ (int) round($evaluation->overall_score) }}</span>
                                    <div class="small text-muted">{{ $evaluation->recommendation }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('interview-dashboard.show', $interview) }}">
                                    {{ __('interview.dashboard.view') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $interviews->links() }}</div>
    @endif
</div>
@endsection
