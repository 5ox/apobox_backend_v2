<?php

return [

    'dsn' => env('SENTRY_LARAVEL_DSN'),

    'release' => trim(exec('git log --pretty="%h" -n1 HEAD') ?: ''),

    'environment' => env('APP_ENV', 'production'),

    'breadcrumbs' => [
        'logs' => true,
        'cache' => true,
        'livewire' => false,
        'sql_queries' => true,
        'sql_bindings' => true,
        'queue_info' => true,
        'command_info' => true,
        'http_client_requests' => true,
    ],

    'tracing' => [
        'queue_job_transactions' => env('SENTRY_TRACE_QUEUE_ENABLED', false),
        'queue_jobs' => true,
        'sql_queries' => true,
        'sql_origin' => true,
        'views' => true,
        'http_client_requests' => true,
        'redis_commands' => env('SENTRY_TRACE_REDIS_COMMANDS', false),
        'default_integrations' => true,
        'missing_routes' => true,
    ],

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),

    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

];
