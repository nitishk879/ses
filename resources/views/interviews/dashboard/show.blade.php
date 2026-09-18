@extends('layouts.app')

@section('title', __('interview.dashboard.detail_title'))

@section('content')
<div class="container-fluid container-lg my-4">

    <a href="{{ route('interview-dashboard.index') }}" class="btn btn-link ps-0 mb-2">
        <i class="fa-solid fa-arrow-left me-1"></i>{{ __('interview.dashboard.back') }}
    </a>

    {{-- ── reschedule ─────────────────────────────────────────────────────
         Hidden once the call has happened: rescheduling then would mint a new
         token, wipe the slots and put the status back to "choose a time",
         erasing the record of an interview that actually took place. The
         service refuses it too — this only keeps the button off a screen where
         pressing it can do nothing useful. --}}
    @php
        $reschedulable = ! in_array($interview->status, [
            \App\Enums\InterviewStatus::STARTING,
            \App\Enums\InterviewStatus::IN_PROGRESS,
            \App\Enums\InterviewStatus::COMPLETED,
            \App\Enums\InterviewStatus::EVALUATING,
            \App\Enums\InterviewStatus::EVALUATED,
        ], true);
    @endphp

    @if($reschedulable)
        <div class="card mb-3">
            <div class="card-header py-2"><strong>{{ __('interview.dashboard.reschedule') }}</strong></div>
            <div class="card-body">
                <p class="text-muted small">{{ __('interview.dashboard.reschedule_help') }}</p>

                <form method="POST" action="{{ route('interview-dashboard.reschedule', $interview) }}"
                      onsubmit="return confirm(@js(__('interview.dashboard.reschedule_confirm')))">
                    @csrf
                    <label class="form-label small mb-1">{{ __('interview.dashboard.slot_times') }}</label>
                    <div class="row g-2 mb-1">
                        @for($i = 0; $i < 3; $i++)
                            <div class="col-md-4">
                                <input type="datetime-local" class="form-control"
                                       name="slot_times[]" value="{{ old('slot_times.'.$i) }}">
                            </div>
                        @endfor
                    </div>
                    <div class="form-text mb-3">
                        {{ __('interview.dashboard.slot_times_help', ['zone' => $timezone]) }}
                    </div>
                    <button class="btn btn-warning" type="submit">
                        <i class="fa-solid fa-calendar-days me-1"></i>{{ __('interview.dashboard.reschedule') }}
                    </button>
                </form>

                <script>
                    // Same rule as the invite form: no past times in the picker,
                    // recomputed on focus so a page left open does not still
                    // offer this morning. The server checks it again regardless.
                    (function () {
                        const boxes = document.querySelectorAll('input[name="slot_times[]"]');
                        function stampNow() {
                            const now = new Date(Date.now() - new Date().getTimezoneOffset() * 60000);
                            const min = now.toISOString().slice(0, 16);
                            boxes.forEach(el => el.min = min);
                        }
                        stampNow();
                        boxes.forEach(el => el.addEventListener('focus', stampNow));
                    })();
                </script>
            </div>
        </div>
    @endif

    {{-- ── header ─────────────────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h1 class="h5 mb-1">{{ $interview->talent?->user?->name ?: __('interview.dashboard.unknown_candidate') }}</h1>
                    <div class="text-muted small">{{ $interview->talent?->user?->email }}</div>
                    <div class="mt-2">
                        <span class="badge bg-{{ $interview->status->badgeVariant() }}">
                            {{ \App\Enums\InterviewStatus::toName($interview->status) }}
                        </span>
                        @if($interview->match_score !== null)
                            <span class="badge bg-light text-dark border ms-1">
                                {{ __('interview.dashboard.match') }}: {{ $interview->match_score }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-md-end small">
                    <div class="fw-semibold">{{ $interview->project?->title }}</div>
                    @if($interview->scheduled_at)
                        <div class="text-muted">
                            {{ \App\Support\InterviewTime::full($interview->scheduled_at, $timezone) }}
                            ({{ $timezone }})
                        </div>
                    @endif
                    @if($attempt?->duration_seconds)
                        <div class="text-muted">
                            {{ __('interview.dashboard.duration') }}:
                            {{ gmdate('i:s', $attempt->duration_seconds) }}
                            @if(($attempt->metadata['duration_overrun_seconds'] ?? 0) > 0)
                                {{-- Surfaced rather than rounded away: an overrun means the
                                     budget or the voicebot's cap did not hold. --}}
                                <span class="text-warning">
                                    (+{{ $attempt->metadata['duration_overrun_seconds'] }}s over)
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Which bot conducted this call, recorded at dial time.
                         The project's bot can be reassigned between interviews,
                         so reading it off the project now would name whichever
                         bot is current rather than the one that actually asked
                         these questions. Absent on attempts placed before this
                         was recorded; null means the interview ran on the
                         questions SES generated. --}}
                    @if($attempt && array_key_exists('conducted_by_agent_id', $attempt->metadata ?? []))
                        <div class="text-muted">
                            {{ __('interview.dashboard.conducted_by') }}:
                            @if(filled($attempt->metadata['conducted_by_agent_id']))
                                <code>{{ $attempt->metadata['conducted_by_agent_id'] }}</code>
                            @else
                                {{ __('interview.dashboard.conducted_by_generated') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($interview->failure_reason)
                <div class="alert alert-warning mt-3 mb-0 py-2 small">
                    {{ $interview->failure_reason }}
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3">
        {{-- ── left: the interview itself ──────────────────────────────── --}}
        <div class="col-lg-7">

            {{-- Offered / chosen times --}}
            @if($interview->slots->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header py-2"><strong>{{ __('interview.dashboard.slots') }}</strong></div>
                    <ul class="list-group list-group-flush">
                        @foreach($interview->slots as $slot)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="small">{{ $slot->presentIn($timezone) }}</span>
                                <span class="badge bg-{{ $slot->status->value === 'selected' ? 'success' : 'light' }}
                                             {{ $slot->status->value === 'selected' ? '' : 'text-dark border' }}">
                                    {{ \App\Enums\InterviewSlotStatus::toName($slot->status) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- The interview, as one conversation.

                 This used to be two cards: the planned questions with their
                 answers, and then the raw transcript underneath. That is the
                 same screening printed twice, and it made the recruiter hold
                 the correspondence between them in their own head — which of
                 these turns was question three? Merged here, with the planned
                 questions annotated onto the turns that asked them.

                 @see \App\Support\InterviewConversation --}}
            @php($conversation = $attempt ? \App\Support\InterviewConversation::for($attempt) : null)
            <div class="card mb-3">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <strong>{{ __('interview.dashboard.conversation') }}</strong>
                    @if($attempt?->recording_url)
                        <a href="{{ $attempt->recording_url }}" target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-play me-1"></i>{{ __('interview.dashboard.recording') }}
                        </a>
                    @endif
                </div>

                @if(! $conversation || $conversation->isEmpty())
                    <div class="card-body text-muted small">{{ __('interview.dashboard.no_transcript') }}</div>
                @else
                    @if($conversation->reconstructed)
                        {{-- Said out loud: a stream rebuilt from question/answer pairs is
                             missing whatever fell outside them, and a recruiter must not
                             read it as a complete record of the call. --}}
                        <div class="alert alert-warning rounded-0 mb-0 py-2 small">
                            {{ __('interview.dashboard.conversation_reconstructed') }}
                        </div>
                    @endif

                    <div class="card-body" style="max-height: 32rem; overflow-y: auto;">
                        @foreach($conversation->turns as $turn)
                            <div class="d-flex mb-3 {{ $turn->isBot ? '' : 'justify-content-end' }}">
                                <div style="max-width: 82%;">
                                    <div class="small text-muted mb-1 {{ $turn->isBot ? '' : 'text-end' }}">
                                        {{ $turn->isBot
                                            ? __('interview.dashboard.interviewer')
                                            : __('interview.dashboard.candidate') }}
                                        @if($turn->question)
                                            {{-- The badges are the whole point of merging: they say
                                                 which requirement this turn was probing, so the answer
                                                 below can be read against it rather than on impression. --}}
                                            @if($turn->skillArea())
                                                <span class="badge bg-light text-dark border ms-1">{{ $turn->skillArea() }}</span>
                                            @endif
                                            @if($turn->intent())
                                                <span class="badge bg-light text-dark border">{{ $turn->intent() }}</span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="p-2 rounded-3 {{ $turn->isBot ? 'bg-light' : 'bg-primary-subtle' }}">
                                        <span class="small">{{ $turn->text }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card-footer py-1 d-flex justify-content-between align-items-center">
                        <span class="small text-muted">{{ __('interview.dashboard.pii_notice') }}</span>
                        @if($attempt->questions->isNotEmpty())
                            <span class="small text-muted">
                                {{ __('interview.dashboard.questions_matched', [
                                    'matched' => $conversation->matchedQuestionCount(),
                                    'total' => $attempt->questions->count(),
                                ]) }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ── right: the assessment ───────────────────────────────────── --}}
        <div class="col-lg-5">
            @php($evaluation = $attempt?->evaluation)

            <div class="card mb-3">
                <div class="card-header py-2"><strong>{{ __('interview.dashboard.evaluation') }}</strong></div>

                @if(! $evaluation)
                    <div class="card-body text-muted small">{{ __('interview.dashboard.no_evaluation') }}</div>
                @else
                    <div class="card-body">
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="display-6 fw-semibold">{{ (int) round($evaluation->overall_score) }}</span>
                            <span class="text-muted">/ 100</span>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-secondary">{{ $evaluation->recommendation }}</span>
                            @if(isset($evaluation->metadata['coverage']))
                                {{-- A 78 from a full interview and a 78 from one answered
                                     question are not the same number. This says which. --}}
                                <span class="badge bg-light text-dark border">
                                    {{ __('interview.dashboard.coverage') }}:
                                    {{ $evaluation->metadata['questions_answered'] ?? '?' }}/{{ $evaluation->metadata['questions_asked'] ?? '?' }}
                                </span>
                            @endif
                        </div>

                        @foreach(['technical_fit' => 40, 'jd_fit' => 35, 'communication' => 25] as $criterion => $weight)
                            @php($value = (float) ($evaluation->{$criterion} ?? 0))
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small">
                                    <span>{{ __("interview.dashboard.{$criterion}") }}
                                        <span class="text-muted">({{ $weight }}%)</span></span>
                                    <span class="fw-semibold">{{ (int) round($value) }}</span>
                                </div>
                                <div class="progress" style="height: 6px;" role="progressbar"
                                     aria-valuenow="{{ (int) round($value) }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ max(0, min(100, $value)) }}%"></div>
                                </div>
                            </div>
                        @endforeach

                        @if($evaluation->summary)
                            <p class="small mt-3 mb-0">{{ $evaluation->summary }}</p>
                        @endif
                    </div>

                    @if(filled($evaluation->strengths) || filled($evaluation->gaps))
                        <ul class="list-group list-group-flush">
                            @foreach((array) $evaluation->strengths as $item)
                                <li class="list-group-item py-1 small">
                                    <i class="fa-solid fa-check text-success me-2"></i>{{ $item }}
                                </li>
                            @endforeach
                            @foreach((array) $evaluation->gaps as $item)
                                <li class="list-group-item py-1 small">
                                    <i class="fa-solid fa-xmark text-warning me-2"></i>{{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if(filled($evaluation->evidence))
                        <div class="card-body border-top">
                            <div class="small text-muted mb-2">{{ __('interview.dashboard.evidence') }}</div>
                            @foreach((array) $evaluation->evidence as $item)
                                <div class="mb-2 small">
                                    <div>{{ $item['observation'] ?? '' }}</div>
                                    @if(! empty($item['quote']))
                                        {{-- Every quote was verified against the candidate's own
                                             words before it was stored. --}}
                                        <div class="fst-italic text-muted ps-2 border-start border-2 mt-1">
                                            “{{ $item['quote'] }}”
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="card-footer py-1 small text-muted">
                        {{ $evaluation->provider }} · {{ $evaluation->model }}
                        · {{ __('interview.dashboard.prompt_version') }} {{ $evaluation->prompt_version }}
                    </div>
                @endif
            </div>

            {{-- Attempt history: a retry is a thing a recruiter needs to see. --}}
            @if($interview->attempts->count() > 1 || $attempt)
                <div class="card">
                    <div class="card-header py-2"><strong>{{ __('interview.dashboard.attempts') }}</strong></div>
                    <ul class="list-group list-group-flush">
                        @foreach($interview->attempts as $a)
                            <li class="list-group-item py-2 small">
                                <div class="d-flex justify-content-between">
                                    <span>#{{ $a->attempt_number }} · {{ \App\Enums\InterviewAttemptStatus::toName($a->status) }}</span>
                                    <span class="text-muted">
                                        {{ \App\Support\InterviewTime::short($a->ended_at, $timezone) }}
                                    </span>
                                </div>
                                @if($a->failure_reason)
                                    <div class="text-muted">{{ $a->failure_reason }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
