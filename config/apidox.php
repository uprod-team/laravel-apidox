<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Documentation URL
    |--------------------------------------------------------------------------
    |
    | The route path where the documentation page is served.
    | Set to null to disable the auto-registered route entirely.
    |
    */
    'route' => env('APIDOX_ROUTE', '/developers'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to the documentation route.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Page Branding
    |--------------------------------------------------------------------------
    */
    'branding' => [
        'name' => env('APP_NAME', 'API'),
        'title' => 'Documentation développeurs',
        'tagline' => 'Intégrez nos endpoints REST dans votre application.',
        'logo_svg' => null, // optional inline SVG markup
    ],

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The fully qualified base URL displayed in code samples. Defaults to
    | the app URL plus "/api/v1".
    |
    */
    'base_url' => env('APIDOX_BASE_URL', rtrim(env('APP_URL', 'http://localhost'), '/').'/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Routes to Document
    |--------------------------------------------------------------------------
    |
    | Only routes whose URI starts with one of these prefixes will be picked
    | up by the scanner. Use "api/v1" if you version your API.
    |
    */
    'scan' => [
        'prefixes' => ['api/v1', 'api'],
        'exclude_uris' => ['_ignition/*', 'telescope/*', 'horizon/*'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Documentation
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'type' => 'bearer', // bearer | api_key | none
        'header_name' => 'Authorization',
        'instructions' => 'Contactez-nous pour obtenir un token API. Une UI de self-service sera disponible prochainement.',
        'how_to_get' => null, // markdown-style string
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks Documentation
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'enabled' => false,
        'events' => [
            // 'resource.created' => 'Sent when a new resource is created.',
        ],
        'signature_header' => 'X-Webhook-Signature',
        'signature_algorithm' => 'sha256',
        'retry_policy' => '5 retries with exponential backoff (10s, 60s, 5min, 15min, 1h).',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limit
    |--------------------------------------------------------------------------
    */
    'rate_limit' => '60 requests per minute per token',

    /*
    |--------------------------------------------------------------------------
    | Support
    |--------------------------------------------------------------------------
    */
    'support' => [
        'email' => null,
        'docs_url' => null,
    ],

];
