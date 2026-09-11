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
];
