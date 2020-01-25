# HTTP Headers API

This API encapsulates HTTP request headers received from client and response headers to send back, offering an ability to bind them for cache and CORS validation. That task can be achieved using following steps:

- **[configuration](#configuration)**: setting up an XML file where cache/CORS validation policies are configured
- **[initialization](#initialization)**: using [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/Wrapper.php) to read above XML into a [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/Policy.php), read HTTP request headers into a [Lucinda\Headers\Request](https://github.com/aherne/headers-api/Request.php) then initialize [Lucinda\Headers\Response](https://github.com/aherne/headers-api/Response.php), encapsulating HTTP response headers logic.
- **[validation](#validation)**: binding [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/Policy.php) to [Lucinda\Headers\Request](https://github.com/aherne/headers-api/Request.php) in order to perform cache/CORS validation and set response headers via [Lucinda\Headers\Response](https://github.com/aherne/headers-api/Response.php)
- **[display](#display)**: sending back response to caller using [Lucinda\Headers\Response](https://github.com/aherne/headers-api/Response.php) headers compiled above (or set individually by user)

API is fully PSR-4 compliant, only requiring PHP7.1+ interpreter and SimpleXML extension. To quickly see how it works, check:

- **[installation](#installation)**: describes how to install API on your computer, in light of steps above
- **[unit tests](#unit-tests)**: API has 100% Unit Test coverage, using [UnitTest API](https://github.com/aherne/unit-testing) instead of PHPUnit for greater flexibility
- **[example](#examples)**: shows a deep example of API functionality based on unit tests


## Configuration

To configure this API you must have a XML with following tags inside:

- **[headers](#headers)**: (mandatory) configures the api globally
- **[routes](#routes)**: (optional) configures API based on route requested, required for CORS requests validation

### Headers

Maximal syntax of this tag is:

```xml
<headers no_cache="..." cache_expiration="..." allow_credentials="..." cors_max_age="..." allowed_request_headers="..." allowed_response_headers="..."/>
```

Where:

- **headers**: (mandatory) holds global header validation policies
    - *no_cache*: (optional) disables HTTP caching for all pages in site (can be 0 or 1; 0 is default), unless specifically activated in [route](#routes) matching page requested
    - *cache_expiration*: (optional) duration in seconds all page responses in site will be cached without revalidation (must be a positive number)
    - *allow_credentials*: (optional) whether or not credentials are allowed in CORS requests (can be 0 or 1; 0 is default)
    - *cors_max_age*: (optional) duration in seconds CORS responses will be cached (must be a positive number) 
    - *allowed_request_headers*: (~optional) list of non-standard request headers your site support separated by commas. If none are provided and a CORS *Access-Control-Request-Headers* is requested, headers listed there are assumed as supported!
    - *allowed_response_headers*: (optional) list of response headers to expose separated by commas
    
Example:

```xml
<headers no_cache="1" cache_expiration="10" allow_credentials="1" cors_max_age="5" allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests" allowed_response_headers="Content-Length, X-Kuma-Revision"/>
```

### Routes

Minimal syntax of this tag is:

```xml
<routes roles="...">
    <route url="..." roles="..."/>
    ...
</routes>
```

Where:

- **routes**: (mandatory) holds list of site routes, each identified by a **route** tag
    - **route**: (mandatory) holds policies about a specific route
        - *url*: (mandatory) page relative url (eg: administration)
        - *no_cache*: (optional) disables HTTP caching for respective route (can be 0 or 1; 0 is default)
        - *cache_expiration*: (optional) duration in seconds respective route responses in site will be cached without revalidation (must be a positive number)
        - *allowed_methods*: (optional) list of HTTP request methods supported by respective route. If none are provided and a CORS *Access-Control-Request-Method* is requested, that method is assumed as supported!

## Initialization

Now that policies have been configured, they can be bound to request and response using  [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/Wrapper.php), which produces three objects:

- [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/Policy.php): encapsulates validation policies detected from XML
- [Lucinda\Headers\Request](https://github.com/aherne/headers-api/Request.php): encapsulates HTTP request headers received from client in accordance to [RFC-7231](https://tools.ietf.org/html/rfc7231) specification
- [Lucinda\Headers\Response](https://github.com/aherne/headers-api/Response.php): encapsulates HTTP response headers to send back to client in accordance to [RFC-7231](https://tools.ietf.org/html/rfc7231) specification

While [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/Policy.php) and [Lucinda\Headers\Request](https://github.com/aherne/headers-api/Request.php) become immutable once detected (first will not be exposed to developers, while second will only expose getters), [Lucinda\Headers\Response](https://github.com/aherne/headers-api/Response.php) is only instanced but not populated by default. Obviously, developers need to *know* headers received from client and *set* headers to send back in response, but the way they link depends on application. There are two particular situations, however, in which request and response headers (and HTTP status) are bound:

- **cache validation**: linking request headers *Cache-Control*, *If-Match*/*If-None-Match*, *If-Unmodified-Since*/*If-Modified-Since* to response headers *Cache-Control*, *ETag*, *Last-Modified* as well as a HTTP response status in accordance to [RFC-7232](https://tools.ietf.org/html/rfc7232) and [RFC-7234](https://tools.ietf.org/html/rfc7234) specifications
- **CORS validation**: linking request headers *Origin*, *Access-Control-Request-Method*, *Access-Control-Request-Headers* to response headers *Access-Control-Allow-Origin*, *Access-Control-Allow-Methods*, *Access-Control-Allow-Headers* in accordance to [CORS](https://fetch.spec.whatwg.org/#cors-protocol) protocol

In light of above, public methods defined by [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/Wrapper.php) are:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | \SimpleXMLElement $xml, string $requestedPage, array $requestHeaders | void | Creates Policy based on XML and requested page, sets up Request object based on request headers and initializes Response object |
| getRequest| void | [Lucinda\Headers\Request](https://github.com/aherne/headers-api/Request.php) | Gets object encapsulating HTTP request headers received |
| validateCache | [Lucinda\Headers\Cacheable](https://github.com/aherne/headers-api/Cacheable.php) $cacheable, string $requestMethod | int | Performs HTTP cache validation based on user-defined Cacheable representation of requested resource  |
| validateCORS | string $origin = null | void | Performs CORS request validation based on user-defined origin (*PROTOCOL://HOSTNAME*, eg: https://www.google.com). If none provided, *Access-Control-Allow-Origin* will equal "*" (all origins supported)! |
| getResponse | void | [Lucinda\Headers\Response](https://github.com/aherne/headers-api/Response.php) | Gets object encapsulating HTTP response headers to send back |

