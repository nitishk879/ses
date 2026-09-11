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

    'mail' => [
        'subject' => 'Interview invitation — :project',
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
