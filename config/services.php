<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => 'http://example.com/callback-url',
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT_URL'),
    ],

    /*
     * ses-ai-service — JD/resume structuring and candidate match scoring.
     *
     * Runs against the H200 vLLM, so there is no per-call vendor cost; the
     * timeout is generous because a long resume is several sequential model
     * calls. `catalog_path` is where `ses:export-skill-catalog` writes the
     * sub-category taxonomy the parser maps skills onto.
     */
    'ai_parser' => [
        'url' => env('AI_PARSER_URL', 'http://localhost:8100'),
        'secret' => env('AI_PARSER_SECRET'),
        'timeout' => env('AI_PARSER_TIMEOUT', 120),
        'catalog_path' => env('AI_PARSER_CATALOG_PATH', storage_path('app/skill_catalog.json')),
        'resume_disk' => env('AI_PARSER_RESUME_DISK', 'local'),

        // Business-specific skill synonyms, merged on top of the service's
        // built-in defaults. Add terms here as they show up in `unmapped`.
        'aliases' => [],
    ],

    /*
     * Automated screening interviews.
     *
     * Shares `ai_parser`'s URL and secret — it is the same service — but the
     * call-placing half needs settings of its own, and none of them have a
     * safe default: `from_number` is a number we pay for, and guessing a
     * region for an un-prefixed phone number means possibly dialling a
     * stranger. Anything unset here disables the feature rather than
     * approximating it.
     */
    'interview' => [
        // Master switch. Off means interviews are planned and stored but never
        // dialled — the correct state for an environment with no telephony.
        'enabled' => env('INTERVIEW_ENABLED', false),

        // The outbound number the candidate sees. E.164.
        'from_number' => env('INTERVIEW_FROM_NUMBER'),

        // Assumed only for stored numbers that carry no country code. Every
        // talent number in the database today is in that state.
        'default_phone_region' => env('INTERVIEW_DEFAULT_PHONE_REGION', 'JP'),

        // Target call length. The AI service derives the question count from
        // this; the voicebot is handed 1.2x it as a hard cut.
        'duration_seconds' => env('INTERVIEW_DURATION_SECONDS', 300),

        'language' => env('INTERVIEW_LANGUAGE', 'japanese'),

        /*
         * Polling. The voicebot has no post-call webhook, so a placed call is
         * followed by asking. `max_attempts * interval` must comfortably
         * exceed the hard call cap plus teardown, or a normal call would be
         * abandoned as timed out while it is still running.
         */
        'poll_interval_seconds' => env('INTERVIEW_POLL_INTERVAL_SECONDS', 20),
        // How long finished evaluations are collected before one summary email.
        'evaluation_digest_minutes' => env('INTERVIEW_EVALUATION_DIGEST_MINUTES', 20),

        'poll_max_attempts' => env('INTERVIEW_POLL_MAX_ATTEMPTS', 45),

        // Retries for a call nobody answered. Distinct from job retries: this
        // counts times we phoned the candidate, which is a thing they notice.
        'max_attempts' => env('INTERVIEW_MAX_ATTEMPTS', 3),
        'retry_delay_minutes' => env('INTERVIEW_RETRY_DELAY_MINUTES', 60),

        // How far past its slot a scheduled interview may still be started.
        // Beyond this the candidate has been waiting too long for an unheralded
        // call to be welcome, and it is rescheduled instead.
        'dispatch_grace_minutes' => env('INTERVIEW_DISPATCH_GRACE_MINUTES', 15),

        /*
         * Invitation and slot offering (tasks 5-7).
         *
         * The shape of the calendar lives here because it is a business
         * decision, not a technical one: when a client's screening hours
         * change, nobody should have to deploy code.
         */
        'invitation' => [
            // Match score at or above which a candidate is shortlisted. The
            // task sheet calls this "recruiter-defined", so it is a setting
            // rather than a constant — and the score each invitation was
            // issued on is recorded, so tuning it stays explainable.
            'min_match_score' => env('INTERVIEW_MIN_MATCH_SCORE', 70),

            'slots_offered' => env('INTERVIEW_SLOTS_OFFERED', 3),

            /*
             * How long a *pinned* offer stays answerable.
             *
             * Applies only when a recruiter typed specific times: those are
             * particular windows that go stale, so the link dies with them.
             * A calendar invitation ignores this and expires with its window
             * instead — see `horizon_days`.
             */
            'offer_valid_hours' => env('INTERVIEW_OFFER_VALID_HOURS', 72),

            /*
             * Never offer a time sooner than this. A slot forty minutes away
             * is one the candidate cannot get somewhere quiet for.
             *
             * Two hours rather than a day: the calendar is the candidate's own
             * choice now, and a 24-hour floor silently removed today and most
             * of tomorrow from a fortnight that was advertised as starting
             * immediately.
             */
            'lead_time_hours' => env('INTERVIEW_LEAD_TIME_HOURS', 2),

            /*
             * How many days of calendar an invitation opens, counting the day
             * it was sent. This is the fortnight: it sizes the window the
             * candidate books inside *and* the life of the link, because a
             * link that outlives its last bookable day is a link that opens on
             * an empty page.
             */
            'horizon_days' => env('INTERVIEW_HORIZON_DAYS', 14),

            /*
             * The hours a candidate may book inside, in their timezone.
             *
             * 8am to 8pm, which is wider than office hours on purpose: this is
             * a five-minute automated call to somebody who in most cases
             * currently has a job, and the evening is when they can take it.
             * The last window *ends* at the closing hour, so 8-20 on a
             * half-hour grid offers 08:00 through 19:30.
             *
             * Slot length is the spacing between bookings, not the call
             * length — the call is ~5 minutes and the rest is margin, because
             * with a single concurrent line an overrun delays the next
             * candidate.
             */
            'business_start_hour' => env('INTERVIEW_BUSINESS_START_HOUR', 8),
            'business_end_hour' => env('INTERVIEW_BUSINESS_END_HOUR', 20),
            'slot_minutes' => env('INTERVIEW_SLOT_MINUTES', 30),

            /*
             * Off by default. Nobody from SES attends these calls — a bot
             * dials and the recruiter reads the result afterwards — so a
             * Saturday morning costs us nothing and is the slot a working
             * candidate is most likely to take. Turning it on greys weekends
             * out of the calendar rather than shortening the fortnight.
             */
            'skip_weekends' => env('INTERVIEW_SKIP_WEEKENDS', false),

            // Used when the interview carries no timezone of its own. SES
            // stores none against a user yet.
            'timezone' => env('INTERVIEW_TIMEZONE', 'Asia/Tokyo'),
        ],
    ],

];
