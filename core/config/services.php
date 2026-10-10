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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'parsebot' => [
        'api_key' => env('PARSE_BOT_API_KEY'),
        'scraper_url' => env('PARSE_BOT_SCRAPER_URL', 'https://api.parse.bot/scraper/406cfd10-b189-4fe9-a006-9e79e55c4856'),
        'snapshot_version' => 14,
    ],
    'geoapify' => [
        'key' => env('GEOAPIFY_API_KEY', env('Geo')),
    ],

];
