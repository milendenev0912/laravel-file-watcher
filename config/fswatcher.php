<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Path to Watch
    |--------------------------------------------------------------------------
    |
    | The directory the watcher will monitor. Default is storage/app/watch.
    |
    */
    'path' => env('FSWATCHER_PATH', storage_path('app/watch')),

    /*
    |--------------------------------------------------------------------------
    | Poll Interval
    |--------------------------------------------------------------------------
    |
    | Number of seconds between scans. For development/testing 2 is fine.
    |
    */
    'poll_interval' => (int) env('FSWATCHER_POLL', 2),

    /*
    |--------------------------------------------------------------------------
    | Ignore Patterns
    |--------------------------------------------------------------------------
    |
    | Regex patterns for files/folders to ignore while scanning.
    |
    */
    'ignore' => [
        '/\/\.git\//',
        '/\/vendor\//',
        '/\/node_modules\//',
        '/\.DS_Store$/',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Event Handlers
    |--------------------------------------------------------------------------
    |
    | Map "<extension>.<event>" to the handler class.
    | Example: "jpg.created" => JpgOptimizeHandler::class
    |
    */
    'handlers' => [
        'jpg.created'  => \App\Services\Handlers\JpgOptimizeHandler::class,
        'jpeg.created' => \App\Services\Handlers\JpgOptimizeHandler::class,

        'json.created'  => \App\Services\Handlers\JsonPostHandler::class,
        'json.modified' => \App\Services\Handlers\JsonPostHandler::class,

        'txt.created'  => \App\Services\Handlers\TxtAppendHandler::class,
        'txt.modified' => \App\Services\Handlers\TxtAppendHandler::class,

        'zip.created' => \App\Services\Handlers\ZipExtractHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Deletion Handler
    |--------------------------------------------------------------------------
    |
    | Any file deletion will trigger this handler unless ignored.
    |
    */
    'deletion_handler' => \App\Services\Handlers\MemeReplaceOnDeleteHandler::class,

    /*
    |--------------------------------------------------------------------------
    | External API Endpoints
    |--------------------------------------------------------------------------
    */
    'post_url'  => env('FSWATCHER_POST_URL', 'https://fswatcher.requestcatcher.com/'),
    'bacon_url' => env('FSWATCHER_BACON_URL', 'https://baconipsum.com/api/?type=meat-and-filler'),
    'meme_url'  => env('FSWATCHER_MEME_URL', 'https://meme-api.com/gimme'),

    /*
    |--------------------------------------------------------------------------
    | Guard TTL
    |--------------------------------------------------------------------------
    |
    | Number of seconds the RecentActionGuard will consider a file “guarded”
    | to avoid infinite event loops.
    |
    */
    'guard_ttl' => 8,
];
