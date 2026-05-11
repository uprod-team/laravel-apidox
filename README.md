# Apidox

[![Latest Stable Version](https://img.shields.io/packagist/v/privateevent/laravel.svg?style=flat-square)](https://packagist.org/packages/privateevent/laravel)
[![Tests](https://img.shields.io/github/actions/workflow/status/uprod-team/laravel-apidox/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/uprod-team/laravel-apidox/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/privateevent/laravel.svg?style=flat-square)](https://packagist.org/packages/privateevent/laravel)
[![License](https://img.shields.io/packagist/l/privateevent/laravel.svg?style=flat-square)](LICENSE)

> Auto-generated, beautifully styled API documentation page for Laravel apps.

Apidox scans your `routes/api.php`, reads your controller PHPDoc and FormRequest rules,
and renders a clean, opinionated, **Trust & Authority style** documentation page —
ready to share with API consumers.

**Zero config.** Install, hit `/developers`, ship.

```bash
composer require privateevent/laravel
```

That's it. Visit `https://yourapp.test/developers` and your API is documented.

---

## Why

Tools like Scribe, L5-Swagger, or Laravel Knuckles are powerful but heavy:
they require annotations, generate static files, or rely on OpenAPI YAML.

Apidox takes the opposite approach:

- **Runtime.** No build step, no static files. The page reflects your current routes.
- **Convention over configuration.** PHPDoc summary → endpoint summary. FormRequest rules → body params table.
- **Zero CSS dependencies.** Embedded ~10KB stylesheet, no Tailwind required in the host project.
- **Trust & Authority design.** Navy + sky-blue, Lexend headings, monospace code blocks, sticky TOC.

---

## Features

- 🔍 **Route auto-discovery** — Scans `routes/api.php` (configurable prefix)
- 📝 **PHPDoc parsing** — `/** ... */` summary + description on controller methods
- ✅ **FormRequest introspection** — Reads `rules()` to build a parameters table
- 🔒 **Auth detection** — Recognizes `auth:sanctum`, `auth:api`, etc.
- 🚦 **Rate limit detection** — Extracts `throttle:60,1` from route middleware
- 🪝 **Webhooks section** — Configurable, with HMAC signature verification code samples
- 🎨 **Embedded CSS** — Zero dependencies, ~10KB
- 🛣 **Custom route** — Default `/developers`, change via config
- 🌐 **i18n-friendly** — All copy in views (publishable)
- 📦 **Laravel 9 → 13 · PHP 8.0 → 8.4**

---

## Installation

```bash
composer require privateevent/laravel
```

Optional — publish config to customize:

```bash
php artisan vendor:publish --tag=apidox-config
```

Optional — publish views to fully override:

```bash
php artisan vendor:publish --tag=apidox-views
```

---

## Configuration

The default config (`config/apidox.php` after publishing) is documented inline. Highlights:

```php
return [
    'route' => '/developers',        // null to disable
    'middleware' => ['web'],

    'branding' => [
        'name' => env('APP_NAME', 'API'),
        'title' => 'Documentation développeurs',
        'tagline' => 'Intégrez nos endpoints REST dans votre application.',
    ],

    'base_url' => env('APP_URL').'/api/v1',

    'scan' => [
        'prefixes' => ['api/v1', 'api'],
        'exclude_uris' => ['_ignition/*', 'telescope/*'],
    ],

    'auth' => [
        'type' => 'bearer',
        'header_name' => 'Authorization',
        'instructions' => 'Contact us to get a token.',
    ],

    'webhooks' => [
        'enabled' => true,
        'events' => [
            'resource.created' => 'Sent when a new resource is created.',
        ],
        'signature_header' => 'X-Webhook-Signature',
        'signature_algorithm' => 'sha256',
    ],

    'rate_limit' => '60 requests per minute per token',
    'support' => ['email' => 'api@yourcompany.com'],
];
```

---

## Documenting your endpoints

### PHPDoc summary

The first line of your method's `/** ... */` becomes the endpoint summary.
The rest (until the first `@tag`) becomes the description.

```php
class WidgetController
{
    /**
     * Create a new widget.
     *
     * Creates a widget owned by the authenticated user. The widget is
     * immediately visible in the catalog.
     */
    public function store(StoreWidgetRequest $request)
    {
        // ...
    }
}
```

### FormRequest rules → params table

When your controller method type-hints a `FormRequest` subclass, Apidox reads its
`rules()` and renders a parameters table with type inference (`integer`,
`boolean`, `string (email)`, `enum`, `file`, …).

```php
class StoreWidgetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'color' => 'required|in:red,blue,green',
            'quantity' => 'integer|min:1',
        ];
    }
}
```

Renders as:

| Field | Type | Required | Rules |
|---|---|---|---|
| `name` | string | **oui** | `required\|string\|max:120` |
| `color` | enum | **oui** | `required\|in:red,blue,green` |
| `quantity` | integer | non | `integer\|min:1` |

### Auth & rate-limit detection

Apidox reads your route middleware automatically:

```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->post('/api/v1/widgets', [WidgetController::class, 'store']);
```

The endpoint card shows badges: `Auth: Bearer (Sanctum)` · `Rate: 60,1`.

---

## Webhooks

Set `apidox.webhooks.enabled = true` and list your events:

```php
'webhooks' => [
    'enabled' => true,
    'events' => [
        'invoice.paid' => 'Fired when an invoice payment is confirmed.',
        'user.signed_up' => 'Fired on successful registration.',
    ],
    'signature_header' => 'X-MyApp-Signature',
    'signature_algorithm' => 'sha256',
],
```

The Webhooks section renders with code samples for HMAC signature verification.

---

## Customizing the page

Two levels of customization:

1. **Light** — change config (route, branding, auth instructions, support email).
2. **Heavy** — `php artisan vendor:publish --tag=apidox-views` then edit `resources/views/vendor/apidox/index.blade.php`.

The CSS can also be overridden by publishing the assets and editing `public/vendor/apidox/apidox.css`.

---

## Testing

```bash
composer install
composer test
```

---

## License

MIT. See [LICENSE](LICENSE).
