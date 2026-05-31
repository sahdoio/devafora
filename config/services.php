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

    'youtube' => [
        // DevAfora channel — used to list the latest uploads on the home page.
        'devafora_channel' => env('YOUTUBE_DEVAFORA_CHANNEL_ID', 'UCOqSuTadeEwf4aamjeYOrMQ'),
        // Optional YouTube Data API v3 key. When set it's used first (reliable
        // from any IP); otherwise we fall back to the public RSS feed, which can
        // be throttled/blocked on datacenter IPs.
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

    'giscus' => [
        // GitHub Discussions-based comments (https://giscus.app). These values are
        // public (they end up in the client) — generate them on giscus.app.
        'repo' => env('GISCUS_REPO'),
        'repo_id' => env('GISCUS_REPO_ID'),
        'category' => env('GISCUS_CATEGORY', 'Announcements'),
        'category_id' => env('GISCUS_CATEGORY_ID'),
    ],

];
