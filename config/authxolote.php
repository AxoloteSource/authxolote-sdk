<?php

return [
    /*
    |--------------------------------------------------------------------------
    | URL Configuration
    |--------------------------------------------------------------------------
    |
    | This option defines the URL for the Authxolote API.
    | You can set it in the environment file using the variable AUTHXOLOTE_URL.
    | If not set, it defaults to 'https://api.ejemplo.com'.
    |
    */
    'url' => env('AUTHXOLOTE_URL', 'https://api.ejemplo.com'),

    /*
     |--------------------------------------------------------------------------
     | Authentication Token
     |--------------------------------------------------------------------------
     |
     | This option defines the token used to authenticate requests to the
     | Authxolote API. Set it in the environment file using the variable
     | AUTHXOLOTE_TOKEN. Leave it blank to disable token-based authentication.
     |
     */
    'token' => env('AUTHXOLOTE_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | This option controls whether caching is enabled for the application.
    | When enabled, responses and data can be cached to improve performance
    | Set to true to enable caching or false to disable it.
    |
    */
    'cache' => env('AUTHXOLOTE_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | This option controls whether debug mode is enabled.
    | When enabled, logs will be printed for debugging purposes.
    | Set to true to enable debug logging or false to disable it.
    |
    */
    'debug' => env('AUTHXOLOTE_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Sync User with Database
    |--------------------------------------------------------------------------
    |
    | This option controls whether the user should be looked up in the local
    | database or if the data from the Authxolote API should be used directly.
    | Set to true to search in the database, false to use API data.
    |
    */
    'sync_user' => env('AUTHXOLOTE_SYNC_USER', true),
];
