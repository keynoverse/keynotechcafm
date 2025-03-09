<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    |
    | Configure various monitoring and debugging tools for the application.
    |
    */

    'telescope' => [
        'enabled' => env('TELESCOPE_ENABLED', false),
        'prune_hours' => env('TELESCOPE_PRUNE_HOURS', 24),
        'ignore_paths' => [
            'nova-api*',
            'telescope*',
            'horizon*',
            'vendor*',
        ],
        'ignore_commands' => [
            'schedule:run',
            'schedule:finish',
            'package:discover',
            'vendor:publish',
        ],
        'watch' => [
            'queries' => true,
            'jobs' => true,
            'mail' => true,
            'notifications' => true,
            'events' => true,
            'logs' => true,
            'dumps' => true,
            'gates' => true,
            'cache' => true,
            'redis' => true,
            'exceptions' => true,
        ],
    ],

    'debugbar' => [
        'enabled' => env('DEBUGBAR_ENABLED', false),
        'collectors' => [
            'phpinfo' => true,
            'messages' => true,
            'time' => true,
            'memory' => true,
            'exceptions' => true,
            'log' => true,
            'db' => true,
            'views' => true,
            'route' => true,
            'auth' => true,
            'gate' => true,
            'session' => true,
            'request' => true,
            'laravel' => true,
            'events' => true,
            'default_request' => false,
            'symfony_request' => true,
            'mail' => true,
            'logs' => true,
            'files' => false,
            'config' => true,
            'cache' => true,
            'models' => true,
            'livewire' => true,
        ],
    ],

    'logging' => [
        'slow_queries' => [
            'enabled' => true,
            'threshold' => 1000, // milliseconds
        ],
        'error_tracking' => [
            'enabled' => true,
            'report_stacktrace' => true,
            'ignore_exceptions' => [
                'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
                'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException',
            ],
        ],
        'performance' => [
            'enabled' => true,
            'threshold' => 1000, // milliseconds
            'memory_limit' => 128, // MB
        ],
    ],
]; 