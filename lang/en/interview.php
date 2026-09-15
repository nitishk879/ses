<?php

/*
 * Messages the interview controllers and services return.
 *
 * These keys were already being called with `__('interview.*')` by the
 * controllers, but the only interview language file was `common/interviews.php`
 * — a different namespace — so every one of them rendered as the raw key
 * string. An API answering `{"message": "interview.answer_already_exists"}` is
 * a bug the client has to work around, so this file closes that gap; the
 * enum labels stay where they are, in `common/interviews.php`.
 */
return [
    // Resource lifecycle
    'attempt_deleted' => 'Interview attempt deleted.',
    'question_deleted' => 'Question deleted.',
    'question_already_asked' => 'This question has already been asked and cannot be deleted.',
    'answer_already_exists' => 'An answer for this question already exists.',
    'answer_saved' => 'Answer saved.',
    'evaluation_queued' => 'Evaluation has been queued.',

    // Call outcomes
    'no_answer' => 'The candidate did not answer.',
    'call_failed' => 'The call could not be completed.',
    'no_transcript' => 'The call ended without recording anything the candidate said.',
    'poll_timeout' => 'Stopped waiting for this call to finish; its outcome is unknown.',
    'max_attempts_reached' => 'The maximum number of call attempts has been reached.',
    'slot_missed' => 'The scheduled time passed before the interview could be started.',

    // Preconditions
    'phone_not_dialable' => 'Talent :talent has no phone number in a dialable format.',
    'jd_not_parsed' => 'This project has no parsed job description yet.',
    'resume_not_parsed' => 'This candidate has no parsed resume yet.',
    'all_lines_busy' => 'All interview lines were busy; the call was not placed.',
    'ai_unavailable' => 'The AI interview service could not be reached.',
    'already_in_progress' => 'This attempt has already asked questions and cannot be restarted; create a new attempt instead.',

    'question_type' => [
        'core' => 'Core',
        'jd_specific' => 'Job specific',
        'candidate_specific' => 'Candidate specific',
        'follow_up' => 'Follow up',
    ],

    // ── Invitation and slot selection (tasks 5-7) ───────────────────────────
    'slot_status' => [
        'offered' => 'Offered',
        'selected' => 'Selected',
        'expired' => 'Expired',
        'released' => 'Released',
    ],

    'invitation_expired' => 'This invitation has expired.',
    'invitation_not_answered' => 'The candidate did not choose a time before the invitation expired.',
    'link_not_recognised' => 'This link is no longer valid. It may already have been used, or it may have expired.',
    'all_slots_passed' => 'All of the times offered have now passed.',
    'slot_no_longer_available' => 'That time is no longer available.',
    'slot_just_taken' => 'That time was taken a moment ago. Please choose another.',
    'slot_not_for_this_interview' => 'That time does not belong to this invitation.',
    'already_scheduled_notice' => 'Your interview is already scheduled for :date.',
    'session_expired_retry' => 'Your session timed out while the page was open. Please choose a time again.',

    'slot_time_unreadable' => '\x27:value\x27 is not a time this can read. Please pick a date and time.',

    'slot_time_in_past' => ':value has already passed. Please choose a time in the future.',

    'no_slots_chosen' => 'No interview times were chosen. Fill in at least one, or leave them all empty to have them chosen automatically.',

    'mail' => [
        'subject' => 'Interview invitation — :project',
        'greeting' => 'Hello :name,',
        'heading' => 'You have been shortlisted',
        'intro' => 'We would like to invite you to a short screening interview for :project.',
        'about' => 'The interview is automated and takes about :minutes minutes. You will receive a phone call at the time you choose.',
        'slots_heading' => 'Available times',
        'cta' => 'Choose a time',
        'expires' => 'Please choose by :date.',
        'none_suitable' => 'If none of these times work for you, simply ignore this email and a recruiter will be in touch to arrange another.',
        'recorded_notice' => 'The call will be recorded so your answers can be reviewed.',
        'regards' => 'Thank you,',
        'button_fallback' => 'If the button above does not work, copy and paste this address into your browser:',
    ],

    // ── Recruiter dashboard (task 16) ───────────────────────────────────────
    'dashboard' => [
        'title' => 'Interviews',
        'subtitle' => 'Screening interviews for your projects — invitations, schedules and results.',
        'detail_title' => 'Interview detail',
        'back' => 'Back to interviews',

        'actions' => 'Actions',
        'run_matching' => 'Run matching',
        'run_matching_help' => 'Parses the job description and any unread CVs, then scores every candidate against this project. Runs in the background.',
        'invite_shortlist' => 'Invite shortlist',
        'invite_help' => 'Emails everyone at or above the match score, offering three interview times.',
        'invite_confirm' => 'This sends real email to candidates. Continue?',
        'project' => 'Project',
        'choose_project' => 'Choose a project…',
        'slot_times' => 'Times to offer (optional)',
        'slot_times_help' => 'Leave empty and three times are chosen automatically. Fill any of them in and exactly those are offered — including today. Times are in :zone and must be in the future.',
        'threshold' => 'Min score',
        'run' => 'Run',
        'send' => 'Send',

        'matching_queued' => 'Matching queued. :count CV(s) are being read; refresh in a minute.',

        // Choosing which DenAI dashboard bot conducts the calls.
        'interview_bot' => 'Interview bot',
        'interview_bot_help' => 'Which bot calls the candidates for this project. Create and word the bot on the DenAI dashboard; this only chooses one.',
        'bot' => 'Bot',
        'no_bot' => '— No bot (use the generated script only) —',
        'save_bot' => 'Save',
        'bot_assigned' => 'Interview bot saved for this project.',
        'bot_cleared' => 'Interview bot cleared. Calls will use the generated script only.',
        'bot_id_placeholder' => 'Bot id from the dashboard URL',
        'bot_list_unavailable' => 'The bot list could not be loaded from the DenAI dashboard. Paste the id from the bot URL instead.',
        'no_shortlist' => 'Nobody is at or above :threshold for this project yet.',
        'invited' => 'Invited :count candidate(s).',
        'invited_with_failures' => 'Invited :count candidate(s). Some could not be reached: :failures',

        'stat_total' => 'Total',
        'stat_awaiting_reply' => 'Awaiting reply',
        'stat_scheduled' => 'Scheduled',
        'stat_completed' => 'Completed',
        'stat_needs_attention' => 'Needs attention',

        'search' => 'Candidate',
        'search_placeholder' => 'Name or email',
        'status' => 'Status',
        'all_statuses' => 'All statuses',
        'all_projects' => 'All projects',
        'filter' => 'Filter',
        'clear' => 'Clear',

        'candidate' => 'Candidate',
        'unknown_candidate' => 'Unknown candidate',
        'match' => 'Match',
        'scheduled' => 'Scheduled for',
        'result' => 'Result',
        'view' => 'View',
        'duration' => 'Duration',

        'empty' => 'No interviews yet.',
        'empty_help' => 'Run matching on a project, then invite the shortlist.',

        'slots' => 'Times offered',
        'questions' => 'Questions asked',
        'no_questions' => 'No questions have been planned for this interview yet.',
        'transcript' => 'Transcript',
        'no_transcript' => 'No transcript — the call has not happened, or nobody spoke.',
        'recording' => 'Recording',
        'interviewer' => 'Interviewer',
        'pii_notice' => 'Personal details are masked in stored transcripts.',

        'evaluation' => 'Evaluation',
        'no_evaluation' => 'Not evaluated yet. An interview is scored once it has produced a transcript.',
        'technical_fit' => 'Technical fit',
        'jd_fit' => 'Job fit',
        'communication' => 'Communication',
        'coverage' => 'Answered',
        'evidence' => 'Evidence',
        'prompt_version' => 'rubric',
        'attempts' => 'Call attempts',
    ],

    'page' => [
        'title' => 'Interview scheduling',
        'choose_title' => 'Choose your interview time',
        'choose_intro' => 'Pick one of the times below for your screening interview for :project.',
        'duration_note' => 'The interview is automated and takes about :minutes minutes. We will call you at the time you choose.',
        'times_shown_in' => 'All times are shown in :timezone.',
        'confirm_button' => 'Confirm this time',
        'confirmed_title' => 'Your interview is booked',
        'confirmed_intro' => 'We will call you at the time above about :project.',
        'confirmed_what_happens' => 'Please be somewhere quiet with a good signal. If you miss the call we will try again.',
        'unavailable_title' => 'This link is no longer usable',
        'unavailable_next' => 'If you would still like to take part, reply to the invitation email and a recruiter will arrange a new time.',
        'recorded_notice' => 'The call will be recorded so your answers can be reviewed.',
        'footer_note' => 'You received this link because you were shortlisted for a role. If that was not you, you can ignore it.',
    ],
];
