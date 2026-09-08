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

];
