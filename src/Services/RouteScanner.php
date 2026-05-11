<?php

namespace PrivateEvent\Apidox\Services;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use ReflectionMethod;

class RouteScanner
{
    /**
     * Returns a list of documented endpoints, grouped by base resource.
     *
     * Each endpoint:
     * [
     *   'methods' => ['GET'], 'uri' => '/api/v1/foo', 'name' => 'api.foo',
     *   'summary' => '...', 'description' => '...',
     *   'middleware' => [...], 'auth' => bool, 'rate_limit' => '60,1',
     *   'parameters' => ['path' => [...], 'body' => [...], 'query' => [...]],
     *   'anchor' => 'api-v1-foo-get',
     * ]
     */
    public function scan(): array
    {
        $prefixes = (array) config('apidox.scan.prefixes', ['api/v1']);
        $excludes = (array) config('apidox.scan.exclude_uris', []);

        $endpoints = [];

        foreach (RouteFacade::getRoutes() as $route) {
            /** @var Route $route */
            $uri = $route->uri();

            if (! $this->matchesPrefix($uri, $prefixes)) {
                continue;
            }

            if ($this->isExcluded($uri, $excludes)) {
                continue;
            }

            foreach ($route->methods() as $method) {
                if (in_array(strtoupper($method), ['HEAD', 'OPTIONS'], true)) {
                    continue;
                }

                $endpoints[] = $this->describeRoute($route, $method);
            }
        }

        return $endpoints;
    }

    private function matchesPrefix(string $uri, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($uri, ltrim($prefix, '/'))) {
                return true;
            }
        }

        return false;
    }

    private function isExcluded(string $uri, array $excludes): bool
    {
        foreach ($excludes as $pattern) {
            if (Str::is(ltrim($pattern, '/'), $uri)) {
                return true;
            }
        }

        return false;
    }

    private function describeRoute(Route $route, string $method): array
    {
        $action = $route->getActionName();
        $middleware = $this->normalizeMiddleware($route->gatherMiddleware());
        [$summary, $description] = $this->extractDocblock($action);

        return [
            'methods' => [strtoupper($method)],
            'uri' => '/'.$route->uri(),
            'name' => $route->getName(),
            'summary' => $summary,
            'description' => $description,
            'middleware' => $middleware,
            'auth' => $this->detectAuth($middleware),
            'rate_limit' => $this->detectRateLimit($middleware),
            'parameters' => [
                'path' => $this->extractPathParameters($route),
                'body' => $this->extractFormRequestRules($action, $method),
            ],
            'anchor' => Str::slug($method.' '.$route->uri(), '-'),
        ];
    }

    private function normalizeMiddleware(array $middleware): array
    {
        return array_values(array_filter($middleware, fn ($m) => is_string($m)));
    }

    private function detectAuth(array $middleware): ?string
    {
        foreach ($middleware as $m) {
            if (str_starts_with($m, 'auth:sanctum')) {
                return 'Bearer (Sanctum)';
            }
            if (str_starts_with($m, 'auth:api')) {
                return 'API token';
            }
            if (str_starts_with($m, 'auth.basic')) {
                return 'HTTP Basic';
            }
            if ($m === 'auth') {
                return 'Session';
            }
        }

        return null;
    }

    private function detectRateLimit(array $middleware): ?string
    {
        foreach ($middleware as $m) {
            if (str_starts_with($m, 'throttle:')) {
                return substr($m, strlen('throttle:'));
            }
        }

        return null;
    }

    private function extractPathParameters(Route $route): array
    {
        return collect($route->parameterNames())
            ->map(fn ($name) => [
                'name' => $name,
                'in' => 'path',
                'required' => true,
                'type' => 'string',
            ])
            ->all();
    }

    private function extractDocblock(string $action): array
    {
        [$class, $method] = $this->parseAction($action);

        if (! $class || ! $method || ! class_exists($class) || ! method_exists($class, $method)) {
            return [null, null];
        }

        try {
            $reflection = new ReflectionMethod($class, $method);
        } catch (\Throwable $e) {
            return [null, null];
        }

        $doc = $reflection->getDocComment();
        if (! $doc) {
            return [null, null];
        }

        // Strip /** ... */ and leading "* "
        $lines = preg_split('/\R/', $doc);
        $cleaned = [];
        foreach ($lines as $line) {
            $line = preg_replace('/^\s*\/?\*+\/?\s?/', '', $line);
            $line = rtrim($line);
            if ($line === '' || str_starts_with($line, '@')) {
                if (! empty($cleaned)) {
                    break;
                }
                continue;
            }
            $cleaned[] = $line;
        }

        if (empty($cleaned)) {
            return [null, null];
        }

        $summary = array_shift($cleaned);
        $description = empty($cleaned) ? null : trim(implode("\n", $cleaned));

        return [$summary, $description];
    }

    private function extractFormRequestRules(string $action, string $httpMethod): array
    {
        [$class, $method] = $this->parseAction($action);

        if (! $class || ! $method) {
            return [];
        }

        try {
            $reflection = new ReflectionMethod($class, $method);
        } catch (\Throwable $e) {
            return [];
        }

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            if (! $type || $type->isBuiltin()) {
                continue;
            }

            $typeName = method_exists($type, 'getName') ? $type->getName() : (string) $type;

            if (! class_exists($typeName)) {
                continue;
            }

            if (! is_subclass_of($typeName, FormRequest::class)) {
                continue;
            }

            try {
                /** @var FormRequest $instance */
                $instance = new $typeName();
                $rules = method_exists($instance, 'rules') ? $instance->rules() : [];

                return $this->formatRules(is_array($rules) ? $rules : []);
            } catch (\Throwable $e) {
                return [];
            }
        }

        return [];
    }

    private function formatRules(array $rules): array
    {
        $params = [];

        foreach ($rules as $field => $rule) {
            if (str_contains($field, '.')) {
                continue; // skip nested for now
            }

            $ruleStr = is_array($rule) ? implode('|', array_map(fn ($r) => is_string($r) ? $r : get_debug_type($r), $rule)) : (string) $rule;
            $tokens = explode('|', $ruleStr);

            $params[] = [
                'name' => $field,
                'in' => 'body',
                'required' => in_array('required', $tokens, true),
                'type' => $this->inferType($tokens),
                'rules' => $ruleStr,
            ];
        }

        return $params;
    }

    private function inferType(array $tokens): string
    {
        foreach ($tokens as $t) {
            $t = strtolower(trim($t));
            if (in_array($t, ['integer', 'int'], true)) {
                return 'integer';
            }
            if ($t === 'numeric') {
                return 'number';
            }
            if ($t === 'boolean' || $t === 'bool') {
                return 'boolean';
            }
            if ($t === 'array') {
                return 'array';
            }
            if ($t === 'date' || $t === 'datetime') {
                return 'string (date)';
            }
            if ($t === 'email') {
                return 'string (email)';
            }
            if ($t === 'url') {
                return 'string (url)';
            }
            if ($t === 'file' || $t === 'image') {
                return 'file';
            }
            if (str_starts_with($t, 'in:')) {
                return 'enum';
            }
        }

        return 'string';
    }

    private function parseAction(string $action): array
    {
        if (! str_contains($action, '@')) {
            return [null, null];
        }

        return explode('@', $action, 2);
    }
}
