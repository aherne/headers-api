# HTTP Headers API

`lucinda/headers` is a small PHP library for:

- parsing request headers into a typed API
- building response headers through validated setters
- applying cache validation rules
- applying CORS policy rules from XML configuration

The package is intentionally modular. It does not try to own your controller, router, response emitter, or framework lifecycle. Instead, it gives you focused objects you can compose where headers matter.

## Installation

```bash
composer require lucinda/headers
```

Requirements:

- PHP `^8.1`
- `ext-SimpleXML`

## What Exists Today

The runtime centers around three classes:

- `Lucinda\Headers\Request`: parses incoming request headers
- `Lucinda\Headers\Response`: accumulates response headers and exports them with `toArray()`
- `Lucinda\Headers\Wrapper`: binds XML policy, request headers, and response generation for cache/CORS workflows

Supporting services and contracts:

- `Lucinda\Headers\Policy`: in-memory header policy loaded from XML
- `Lucinda\Headers\PolicyLocator`: resolves global and route-specific policy
- `Lucinda\Headers\CacheValidator`: evaluates conditional cache headers
- `Lucinda\Headers\CorsValidator`: evaluates CORS policy and preflight-related rules
- `Lucinda\Headers\Cacheable`: contract for resources that can expose `ETag` and `Last-Modified` representations

Exceptions:

- `Lucinda\Headers\ConfigurationException`: invalid or incomplete XML policy
- `Lucinda\Headers\UserException`: invalid response-header input supplied by application code

## Integration Flow

Most integrations follow this order:

1. Load XML policy.
2. Build a `Wrapper` with the requested route and raw request headers.
3. Read parsed request headers from `Wrapper::getRequest()`.
4. Apply cache validation and/or CORS validation.
5. Add any extra response headers through `Wrapper::getResponse()`.
6. Emit `Response::toArray()` using your own HTTP layer.

Minimal example:

```php
<?php

use Lucinda\Headers\Wrapper;

$xml = simplexml_load_file(__DIR__."/configuration.xml");
$wrapper = new Wrapper($xml, $_SERVER["REQUEST_URI"], getallheaders());

$responseBody = "Hello world!";
$httpStatus = $wrapper->validateCache(
    new class($responseBody) implements \Lucinda\Headers\Cacheable {
        public function __construct(
            private string $body,
            private int $time = 1700000000
        ) {
        }

        public function getEtag(): string
        {
            return sha1($this->body);
        }

        public function getTime(): int
        {
            return $this->time;
        }
    },
    $_SERVER["REQUEST_METHOD"]
);

$wrapper->validateCors();

http_response_code($httpStatus);
foreach ($wrapper->getResponse()->toArray() as $name => $value) {
    header($name.": ".$value);
}

if ($httpStatus != 304) {
    echo $responseBody;
}
```

## XML Configuration

`Wrapper` expects an XML document with a mandatory `<headers>` node and an optional `<routes>` node.

### Global Policy

```xml
<headers
    no_cache="0"
    cache_expiration="600"
    allow_credentials="1"
    cors_max_age="300"
    allowed_origins="https://app.example.com, https://admin.example.com"
    allowed_methods="GET,POST,OPTIONS"
    allowed_request_headers="X-Custom-Header, Authorization"
    allowed_response_headers="Content-Length, X-Revision"/>
```

Supported attributes on `<headers>`:

- `no_cache`: disables wrapper-driven cache headers for every route
- `cache_expiration`: response `Cache-Control: max-age=...` value
- `allow_credentials`: enables `Access-Control-Allow-Credentials: true`
- `cors_max_age`: response `Access-Control-Max-Age`
- `allowed_origins`: comma-separated list of allowed CORS origins, or `*`
- `allowed_methods`: comma-separated list of allowed CORS methods
- `allowed_request_headers`: comma-separated request headers allowed during preflight
- `allowed_response_headers`: comma-separated response headers exposed to browser code

### Route Overrides

Route nodes override the same settings on a per-route basis.

```xml
<xml>
    <headers
        cache_expiration="600"
        allow_credentials="1"
        allowed_origins="https://app.example.com"/>
    <routes>
        <route id="/login" no_cache="1"/>
        <route
            id="/admin"
            allow_credentials="0"
            cors_max_age="60"
            allowed_origins="https://admin.example.com"
            allowed_methods="PUT,DELETE"
            allowed_request_headers="X-Admin-Header"
            allowed_response_headers="X-Admin-Token"/>
    </routes>
</xml>
```

Notes based on current implementation:

- route overrides replace the configured lists for origins, methods, request headers, and response headers
- if no `allowed_methods` are configured, the preflight request method is echoed back
- if no `allowed_request_headers` are configured and preflight asks for headers, `*` is used
- if the response varies by origin, `Wrapper::validateCors()` adds `Vary: Origin`

## Cache Validation

Cache validation is driven by:

- request headers parsed by `Request`
- XML policy loaded into `Policy`
- a user-supplied `Cacheable` implementation

`Cacheable` is deliberately small:

```php
interface Cacheable
{
    public function getEtag(): string;
    public function getTime(): int;
}
```

`Wrapper::validateCache(Cacheable $cacheable, string $requestMethod): int`:

- evaluates conditional request headers such as `If-Match`, `If-None-Match`, `If-Modified-Since`, and `If-Unmodified-Since`
- applies cache policy from XML when caching is enabled
- sets response headers like `Cache-Control`, `ETag`, and `Last-Modified`
- returns an HTTP status code, typically `200`, `304`, or `412`

When `no_cache="1"` applies to the current route, wrapper-driven cache headers are skipped.

## CORS Validation

`Wrapper::validateCors(?string $origin = null): void` builds CORS response headers from:

- the request `Origin`
- preflight headers such as `Access-Control-Request-Method` and `Access-Control-Request-Headers`
- XML policy

Current behavior:

- if `allowed_origins` is configured, the request origin must match that policy
- `allowed_origins="*"` returns `*` when credentials are disabled
- when credentials are enabled, wildcard policy reflects the request origin instead of returning `*`
- if no `allowed_origins` are configured, the optional method argument acts as a fallback legacy origin source
- route-level CORS policy overrides global policy

Headers that may be set automatically:

- `Access-Control-Allow-Origin`
- `Access-Control-Allow-Credentials`
- `Access-Control-Allow-Methods`
- `Access-Control-Allow-Headers`
- `Access-Control-Expose-Headers`
- `Access-Control-Max-Age`
- `Vary: Origin`

## Working Directly With Request

If you do not need `Wrapper`, you can use `Request` directly:

```php
$request = new \Lucinda\Headers\Request(getallheaders());

$origin = $request->getOrigin();
$ifNoneMatch = $request->getIfNoneMatch();
$range = $request->getRange();
$authorization = $request->getAuthorization();
```

The class currently exposes parsed accessors for:

- content negotiation headers such as `Accept`, `Accept-Charset`, `Accept-Encoding`, `Accept-Language`
- conditional cache headers
- `Authorization`
- `Range` and `If-Range`
- forwarding headers such as `Forwarded`, `X-Forwarded-For`, `X-Forwarded-Host`, `X-Forwarded-Proto`
- CORS request headers
- miscellaneous headers such as `Host`, `Referer`, `User-Agent`, `Want-Digest`, `DNT`, `Save-Data`
- unknown headers through `getCustomHeaders()`

## Working Directly With Response

`Response` validates many standard response headers and exports them through `toArray()`.

Example:

```php
$response = new \Lucinda\Headers\Response();
$response->setContentType("application/json", "UTF-8");
$response->setContentLength(123);
$response->setRetryAfterDelay(30);
$response->setFrameOptions("deny");

$cacheControl = $response->setCacheControl();
$cacheControl->setPublic();
$cacheControl->setMaxAge(600);

$headers = $response->toArray();
```

The response API includes helpers for:

- cache headers
- content headers
- CORS headers
- `WWW-Authenticate`
- `Strict-Transport-Security`
- `Referrer-Policy`
- `Tk`
- `Vary`
- custom headers via `setCustomHeader()`

Some details worth knowing from the current source:

- `Retry-After` has correctly named methods `setRetryAfterDate()` and `setRetryAfterDelay()`
- older typoed aliases `setRentryAfterDate()` and `setRentryAfterDelay()` are still present for compatibility
- zero-like response values such as `Retry-After: 0` and `Access-Control-Max-Age: 0` are preserved by `toArray()`

## Testing

This repository uses [`lucinda/unit-testing`](https://github.com/aherne/unit-testing), not PHPUnit.

Run the suite with:

```bash
php test.php
```

The tests in `tests/` are written against the current `src/` API and use validator assertions from `Lucinda\UnitTest\Validator\...`.

## Practical Constraints

A few behaviors are important when integrating the package:

- XML must contain a `<headers>` node or `ConfigurationException` is thrown
- response setters validate allowed values and may throw `UserException`
- `Response::setContentRange()` accepts range starts at `0`
- `Strict-Transport-Security` currently serializes as `max-age=31536000` with optional `includeSubDomains` and `preload`
- `Response::toArray()` is the intended final handoff point to your actual HTTP emitter

## License

MIT
