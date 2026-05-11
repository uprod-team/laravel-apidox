<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $config['branding']['title'] }} — {{ $config['branding']['name'] }}</title>
    <link rel="stylesheet" href="{{ route('apidox.assets.css') }}">
</head>
<body class="apidox">
    <div class="apidox-container">

        <header class="apidox-header">
            <div class="apidox-eyebrow">API · stable</div>
            <h1 class="apidox-title">{{ $config['branding']['title'] }}</h1>
            @if ($config['branding']['tagline'])
                <p class="apidox-lead">{{ $config['branding']['tagline'] }}</p>
            @endif
        </header>

        <div class="apidox-layout">

            {{-- TOC --}}
            <aside class="apidox-toc">
                <nav>
                    <a href="#auth" class="apidox-toc-link">Authentification</a>
                    <a href="#base-url" class="apidox-toc-link">URL de base</a>
                    <a href="#endpoints" class="apidox-toc-link">Endpoints</a>
                    @foreach ($endpoints as $ep)
                        <a href="#{{ $ep['anchor'] }}" class="apidox-toc-sublink">
                            <span class="apidox-method apidox-method-{{ strtolower($ep['methods'][0]) }}">{{ $ep['methods'][0] }}</span>
                            {{ $ep['uri'] }}
                        </a>
                    @endforeach
                    @if ($config['webhooks']['enabled'])
                        <a href="#webhooks" class="apidox-toc-link">Webhooks</a>
                    @endif
                    <a href="#rate-limit" class="apidox-toc-link">Rate limits</a>
                    @if (! empty($config['support']['email']))
                        <a href="#support" class="apidox-toc-link">Support</a>
                    @endif
                </nav>
            </aside>

            <main class="apidox-main">

                {{-- BASE URL --}}
                <section id="base-url" class="apidox-section">
                    <h2 class="apidox-h2">URL de base</h2>
                    <p>Toutes les requêtes utilisent HTTPS et acceptent / retournent du JSON.</p>
                    <pre class="apidox-code"><code>{{ $config['base_url'] }}</code></pre>
                </section>

                {{-- AUTH --}}
                @if ($config['auth']['type'] !== 'none')
                    <section id="auth" class="apidox-section">
                        <h2 class="apidox-h2">Authentification</h2>
                        <p>
                            @if ($config['auth']['type'] === 'bearer')
                                L'API utilise des <strong>tokens Bearer</strong>. Ajoutez le header
                                <code>{{ $config['auth']['header_name'] }}: Bearer &lt;token&gt;</code>
                                à chaque requête.
                            @elseif ($config['auth']['type'] === 'api_key')
                                L'API utilise une <strong>clé API</strong> à passer dans le header
                                <code>{{ $config['auth']['header_name'] }}</code>.
                            @endif
                        </p>

                        @if (! empty($config['auth']['instructions']))
                            <div class="apidox-callout apidox-callout-amber">
                                {!! e($config['auth']['instructions']) !!}
                            </div>
                        @endif

                        <h3 class="apidox-h3">Exemple</h3>
                        <pre class="apidox-code"><code>curl -H "{{ $config['auth']['header_name'] }}: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     {{ $config['base_url'] }}/...</code></pre>
                    </section>
                @endif

                {{-- ENDPOINTS --}}
                <section id="endpoints" class="apidox-section">
                    <h2 class="apidox-h2">Endpoints</h2>
                    <p>{{ count($endpoints) }} endpoint(s) disponible(s).</p>
                </section>

                @foreach ($endpoints as $ep)
                    <section id="{{ $ep['anchor'] }}" class="apidox-endpoint">
                        <div class="apidox-endpoint-header">
                            <span class="apidox-method apidox-method-{{ strtolower($ep['methods'][0]) }}">{{ $ep['methods'][0] }}</span>
                            <code class="apidox-endpoint-uri">{{ $ep['uri'] }}</code>
                        </div>

                        @if ($ep['summary'])
                            <h3 class="apidox-endpoint-summary">{{ $ep['summary'] }}</h3>
                        @endif

                        @if ($ep['description'])
                            <p class="apidox-endpoint-description">{{ $ep['description'] }}</p>
                        @endif

                        <div class="apidox-endpoint-meta">
                            @if ($ep['auth'])
                                <span class="apidox-badge apidox-badge-info">Auth : {{ $ep['auth'] }}</span>
                            @else
                                <span class="apidox-badge apidox-badge-muted">Public</span>
                            @endif
                            @if ($ep['rate_limit'])
                                <span class="apidox-badge apidox-badge-muted">Rate : {{ $ep['rate_limit'] }}</span>
                            @endif
                        </div>

                        @if (! empty($ep['parameters']['path']))
                            <h4 class="apidox-h4">Path parameters</h4>
                            <table class="apidox-table">
                                <thead><tr><th>Nom</th><th>Type</th><th>Requis</th></tr></thead>
                                <tbody>
                                    @foreach ($ep['parameters']['path'] as $p)
                                        <tr>
                                            <td><code>{{ $p['name'] }}</code></td>
                                            <td>{{ $p['type'] }}</td>
                                            <td>{{ $p['required'] ? 'oui' : 'non' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if (! empty($ep['parameters']['body']))
                            <h4 class="apidox-h4">Body parameters</h4>
                            <table class="apidox-table">
                                <thead><tr><th>Champ</th><th>Type</th><th>Requis</th><th>Règles</th></tr></thead>
                                <tbody>
                                    @foreach ($ep['parameters']['body'] as $p)
                                        <tr>
                                            <td><code>{{ $p['name'] }}</code></td>
                                            <td>{{ $p['type'] }}</td>
                                            <td>{!! $p['required'] ? '<span class="apidox-required">oui</span>' : 'non' !!}</td>
                                            <td><code class="apidox-code-inline">{{ $p['rules'] }}</code></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <h4 class="apidox-h4">Exemple curl</h4>
                        <pre class="apidox-code"><code>curl -X {{ $ep['methods'][0] }} @if ($ep['auth']) \
     -H "{{ $config['auth']['header_name'] }}: Bearer YOUR_TOKEN" @endif \
     -H "Accept: application/json" \
     "{{ rtrim($config['base_url'], '/').preg_replace('#^/?api/v?\d*/?#', '/', $ep['uri']) }}"</code></pre>
                    </section>
                @endforeach

                {{-- WEBHOOKS --}}
                @if ($config['webhooks']['enabled'])
                    <section id="webhooks" class="apidox-section">
                        <h2 class="apidox-h2">Webhooks</h2>
                        <p>Recevez des notifications HTTP en temps réel quand un événement se produit côté serveur.</p>

                        @if (! empty($config['webhooks']['events']))
                            <h3 class="apidox-h3">Événements</h3>
                            <table class="apidox-table">
                                <thead><tr><th>Event</th><th>Description</th></tr></thead>
                                <tbody>
                                    @foreach ($config['webhooks']['events'] as $event => $desc)
                                        <tr><td><code>{{ $event }}</code></td><td>{{ $desc }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <h3 class="apidox-h3">Vérification de la signature</h3>
                        <p>
                            Chaque appel inclut un header <code>{{ $config['webhooks']['signature_header'] }}: {{ $config['webhooks']['signature_algorithm'] }}=&lt;hmac&gt;</code>
                            calculé sur le body raw avec votre signing secret.
                        </p>

                        <h4 class="apidox-h4">PHP</h4>
                        <pre class="apidox-code"><code>$body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$expected = '{{ $config['webhooks']['signature_algorithm'] }}=' . hash_hmac('{{ $config['webhooks']['signature_algorithm'] }}', $body, YOUR_SIGNING_SECRET);

if (! hash_equals($expected, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}</code></pre>

                        <h3 class="apidox-h3">Retry policy</h3>
                        <p>{{ $config['webhooks']['retry_policy'] }}</p>
                    </section>
                @endif

                {{-- RATE LIMIT --}}
                <section id="rate-limit" class="apidox-section">
                    <h2 class="apidox-h2">Rate limits</h2>
                    <p>{{ $config['rate_limit'] }}. Au-delà : réponse <code>429 Too Many Requests</code>.</p>
                </section>

                {{-- SUPPORT --}}
                @if (! empty($config['support']['email']))
                    <section id="support" class="apidox-section">
                        <h2 class="apidox-h2">Support</h2>
                        <p>
                            Question d'intégration ?
                            <a href="mailto:{{ $config['support']['email'] }}">{{ $config['support']['email'] }}</a>
                        </p>
                    </section>
                @endif

            </main>
        </div>

        <footer class="apidox-footer">
            Documentation générée avec <a href="https://packagist.org/packages/privateevent/laravel" rel="noopener">privateevent/laravel</a>
        </footer>
    </div>
</body>
</html>
