# HTTP Headers API

This API encapsulates HTTP request headers received from client and response headers to send back, offering an ability to bind them for cache and CORS validation. That task can be achieved using following steps:

- **[configuration](#configuration)**: setting up an XML file where cache/CORS validation policies are configured
- **[initialization](#initialization)**: using [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/blob/master/src/Wrapper.php) to read above XML into a [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php), read HTTP request headers into a [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) then initialize [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php), encapsulating HTTP response headers logic.
- **[validation](#validation)**: using above to perform cache/CORS validation and set [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) accordingly
- **[display](#display)**: sending back response to caller using [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) headers compiled above (or set individually by user)

API is fully PSR-4 compliant, only requiring PHP7.1+ interpreter and SimpleXML extension. To quickly see how it works, check:

- **[installation](#installation)**: describes how to install API on your computer, in light of steps above
- **[unit tests](#unit-tests)**: API has 100% Unit Test coverage, using [UnitTest API](https://github.com/aherne/unit-testing) instead of PHPUnit for greater flexibility
- **[examples](#examples)**: shows a deep example of API functionality based on unit tests


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

Now that policies have been configured, they can be bound to request and response using  [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/blob/master/src/Wrapper.php), which creates then works with three objects:

- [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php): encapsulates validation policies detected from XML
- [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php): encapsulates HTTP request headers received from client in accordance to [RFC-7231](https://tools.ietf.org/html/rfc7231) specification
- [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php): encapsulates HTTP response headers to send back to client in accordance to [RFC-7231](https://tools.ietf.org/html/rfc7231) specification

Once set, [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php) and [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) become immutable (since *the past cannot be changed*). [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php) will only be used internally while [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) will only expose getters. [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php), on the other hand, is only instanced while setting remains in developer's responsibility. This is because there is no default linking between request and response headers, unless you are performing **[validation](#validation)**. In light of above, public methods defined by [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/blob/master/src/Wrapper.php) are:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | \SimpleXMLElement $xml, string $requestedPage, array $requestHeaders | void | Creates Policy based on XML and requested page, sets up Request object based on request headers and initializes Response object |
| getRequest| void | [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) | Gets object encapsulating HTTP request headers received |
| validateCache | [Lucinda\Headers\Cacheable](https://github.com/aherne/headers-api/blob/master/src/Cacheable.php) $cacheable, string $requestMethod | int | Performs HTTP cache validation based on user-defined Cacheable representation of requested resource  |
| validateCORS | string $origin = null | void | Performs CORS request validation based on user-defined origin (*PROTOCOL://HOSTNAME*, eg: https://www.google.com). If none provided, *Access-Control-Allow-Origin* will equal "*" (all origins supported)! |
| getResponse | void | [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) | Gets object encapsulating HTTP response headers to send back |

### Request

As stated above, class [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) encapsulates HTTP request headers received from client. Each method inside (minus *__construct*) corresponds to a header:

| Method | Arguments | Returns | Description | Header |
| --- | --- | --- | --- | --- |
| __construct | array $headers | void | Reads headers received from client | - |
| getAccept() | void | array | Gets content types accepted by client | [Accept](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept) |
| getAcceptCharset() | void | array | Gets charsets accepted by client| [Accept-Charset](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Charset) |
| getAcceptEncoding() | void | array | Gets encodings accepted by client | [Accept-Encoding](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding) |
| getAcceptLanguage() | void | array | Gets languages accepted by client | [Accept-Language](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language) |
| getTE() | void | array | Gets transfer encodings accepted by client | [TE](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/TE) |
| getAuthorization() | void | ?[Lucinda\Headers\Request\Authorization](https://github.com/aherne/headers-api/blob/master/src/Request/Authorization.php) | Gets credentials for user authentication | [Authorization](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Authorization) |
| getCacheControl() | void | ?[Lucinda\Headers\Request\CacheControl](https://github.com/aherne/headers-api/blob/master/src/Request/CacheControl.php) | Gets HTTP caching settings requested by client | [Cache-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control) |
| getDNT() | void | bool | Gets whether or not client does not want to be tracked | [DNT](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/DNT) |
| getDate() | void | ?int | Gets UNIX timestamp of date request came with | [Date](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date) |
| getExpect() | void | bool | Gets whether client is about to send a large request | [Expect](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect) |
| getSaveData() | void | bool | ... | [Save-Data](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Save-Data) |
| getForwardedIP() | void | ?string | Gets origin IP that got forwarded by proxy | [X-Forwarded-For](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For) |
| getForwardedProxy() | void | ?string | Gets proxy IP that forwarded client, if present | [X-Forwarded-For](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For) |
| getForwardedHost() | void | ?string | Gets origin Host that got forwarded by proxy  | [X-Forwarded-Host](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Host) |
| getForwardedProtocol() | void | ?string | Gets origin protocol that got forwarded by proxy | [X-Forwarded-Proto](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Proto) |
| getFrom() | void | ?string | Gets email address of client | [From](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/From) |
| getHost() | void | ?string | Gets hostname requested by client | [Host](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Host) |
| getIfRangeDate() | void | ?int | Gets UNIX timestamp of range condition, if present  | [If-Range](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Range) |
| getIfRangeEtag() | void | ?string | Gets ETag of range condition, if present | [If-Range](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Range) |
| getRange() | void | ?[Lucinda\Headers\Request\Range](https://github.com/aherne/headers-api/blob/master/src/Request/Range.php) | Gets bytes range requested by client from a big document | [Range](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range) |
| getReferer() | void | ?string | Gets address of the previous web page from which a link to the currently requested page was followed | [Referer](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer) |
| getUserAgent() | void | ?string | Gets signature of client browser | [User-Agent](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent) |
| getWantDigest() | void | array | Gets details of digest client wants in response | [Want-Digest](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Want-Digest) |
| getIfMatch() | void | ?string | Gets ETag that must condition response to be sent only if matches that of requested resource | [If-Match](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Match) |
| getIfNoneMatch() | void | ?string | Gets ETag that must condition response to be sent only if it does not match that of requested resource | [If-None-Match](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match) |
| getIfModifiedSince() | void | ?int | Gets UNIX timestamp that must condition response to be sent only if matches that of requested resource | [If-Modified-Since](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since) |
| getIfUnmodifiedSince() | void | ?int | Gets UNIX timestamp that must condition response to be sent only if it does not match that of requested resource | [If-Unmodified-Since](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Unmodified-Since) |
| getAccessControlRequestHeaders() | void | array | Gets headers that will be requested later as part of a **CORS** preliminary request | [Access-Control-Request-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Headers) |
| getAccessControlRequestMethod() | void | ?string | Gets HTTP method that will be used later as part of a **CORS** preliminary request | [Access-Control-Request-Method](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Method) |
| getOrigin() | void | ?string | Gets client hostname to validate access to requested resource, sent automatically in **CORS** preliminary request | [Origin](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin) |
| getCustomHeaders() | void | array | Gets all non-standard headers requested by client as header name:value array | (any) |

Following limitations apply:

- weak or multiple ETags are not supported

### Response

As stated above, class [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) encapsulates HTTP response headers to send back. Each method inside (minus *toArray*) corresponds to a header:

| Method | Arguments | Returns | Description | Header |
| --- | --- | --- | --- | --- |
| addAcceptPatch | string $mimeType, string $charset=null | void | Adds a content type for whom PATCH requests are accepted | [Accept-Patch](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Patch) |
| setAcceptRanges | bool $value | void | Sets whether or not range requests are accepted | [Accept-Ranges](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Ranges) |
| addAllow | string $requestMethod | void | Sets a request method server accepts for requested resource | [Allow](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Allow) |
| addClearSiteData | string $directive = "*" | void | Sets a browsing data (cookies, storage, cache) to be cleared on client | [Clear-Site-Data](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Clear-Site-Data) |
| setCacheControl | void | [Lucinda\Headers\Response\CacheControl](https://github.com/aherne/headers-api/blob/master/src/Response/CacheControl.php) | Sets HTTP caching settings to be used by client | [Cache-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control) |
| setContentDisposition | string $type | [Lucinda\Headers\Response\ContentDisposition](https://github.com/aherne/headers-api/blob/master/src/Response/ContentDisposition.php) | Sets how content will be displayed (inline or attachment) | [Content-Disposition](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition) |
| addContentEncoding | string $contentEncoding | void | Adds an encoding applied in compressing response | [Content-Encoding](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding) |
| addContentLanguage | string $language | void | Adds a language to associate response with  | [Content-Language](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Language) |
| setContentLength | int $length | void | Sets byte length of response | [Content-Length](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Length) |
| setContentLocation | string $url | void | Sets alternate uri for the returned data | [Content-Location](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Location) |
| setContentRange | string $unit = "bytes", int $start = null, int $end = null, int $size = null | void | Sets returning document range | [Content-Range](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Range) |
| setContentType | string $mimeType, string $charset = null | void | Sets content type of response | [Content-Type](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type) |
| setContentTypeOptions | void | void | Indicates that content types should not be changed or followed (anti-sniffing solution) | [X-Content-Type-Options](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options) |
| setCrossOriginResourcePolicy | string $option | void | Sets policy to block no-cors cross-origin/cross-site requests to the given resource | [Cross-Origin-Resource-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Resource-Policy) |
| addDigest | string $algorithm, string $value | void | Adds a digest to response | [Digest](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Digest) |
| setEtag | string $value | void | Sets ETag to associate response to requested resource with | [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag) |
| setExpirationTime | int $unixTime | void | Sets UNIX time by which response to be cached by client browser should require revalidation (**deprecated**) | [Expires](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expires) |
| setLastModifiedTime | int $unixTime | void | Sets UNIX time requested resource was last modified, to associate response with  | [Last-Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified) |
| setLocation | string $url | void | Sets url client should redirect to. | [Location](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Location) |
| setReferrerPolicy | string $option | void | Sets much Referer information should be included with requests. | [Referrer-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy) |
| setRentryAfterDate | int $unixTime | void | Sets UNIX time client should wait before making a follow-up request | [Rentry-After](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Rentry-After) |
| setRentryAfterDelay | int $delay | void | Sets seconds client should wait before making a follow-up request | [Rentry-After](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Rentry-After) |
| setSourceMap | string $url | void | Links response to a source map enabling the browser to present the reconstructed original in the debugger. | [SourceMap](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/SourceMap) |
| setStrictTransportSecurity | bool $includeSubdomains = false, bool $preload = false | void | Informs client that current website only accepts HTTPS | [Strict-Transport-Security](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security) |
| addTimingAllowOrigin | string $url = "*" | void | Adds an origins allowed to see values from Resource Timing API | [Timing-Allow-Origin](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Timing-Allow-Origin) |
| setTk | string $status | void | Sets tracking status that applied to the corresponding request | [Tk](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Tk) |
| setTrailer | string $headerNames | void | Allows the sender to include additional fields at the end of chunked messages | [Trailer](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Trailer) |
| addTransferEncoding | string $contentEncoding | void | Adds form of encoding used to safely transfer the payload body to the user. | [Transfer-Encoding](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Transfer-Encoding) |
| addVary | string $headerName = "*" | void | Adds a request header to decide in future whether a cached response can be used | [Vary](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Vary) |
| setWWWAuthenticate | string $type, string $realm="" | [Lucinda\Headers\Response\WwwAuthenticate](https://github.com/aherne/headers-api/blob/master/src/Response/WwwAuthenticate.php) |  Defines the authentication method that should be used to gain access to a resource | [WWW-Authenticate](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/WWW-Authenticate) |
| setDNSPrefetchControl | bool $value = true | void | Activates DNS prefetching on client | [X-DNS-Prefetch-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-DNS-Prefetch-Control) |
| setFrameOptions | string $option | void | Indicates whether or not a browser should be allowed to render a page in a frame / iframe / embed / object | [X-Frame-Options](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options) |
| setAccessControlAllowCredentials | void | void | Answers to **CORS** request by signaling credentials are to be exposed | [Access-Control-Allow-Credentials](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials) |
| addAccessControlAllowHeader | string $headerName | void | Adds allowed request header to answer a **CORS** request | [Access-Control-Allow-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers) |
| addAccessControlAllowMethod | string $requestMethod | void | Adds allowed request method to answer a **CORS** request | [Access-Control-Allow-Methods](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods) |
| setAccessControlAllowOrigin | string $origin = "*" | void | Sets allowed origin to answer a **CORS** request | [Access-Control-Allow-Origin](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin) |
| addAccessControlExposeHeaders | string $headerName = "*" | void | Adds response header client should expose to answer a **CORS** request | [Access-Control-Expose-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers) |
| setAccessControlMaxAge | int $duration | void | Sets how long response to a CORS request should be cached (in seconds) | [Access-Control-Max-Age](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age) |
| setCustomHeader | string $name, string $value | void | Sets a custom header by name and value (this **may trigger CORS requests**) | (any) |
| toArray | void | array | Converts all headers set to a name:value array ready to be sent back to client | - |

Following limitations apply:

- weak or multiple ETags are not supported

## Validation

Obviously, developers need to *know* headers received from client and *set* headers to send back in response, but the way they link depends on your application. There are some particular cases, however, in which request and response headers (and HTTP status) are bound logically:

- **[cache validation](#cache-validation)**: validating [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) headers based on [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php) in order to *communicate with client browser cache* and set [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) headers in accordance to    
- **[CORS validation](#CORS-validation)**: validating [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) headers based on [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php) in order to *answer a CORS request* and set [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) headers in accordance to [CORS](https://fetch.spec.whatwg.org/#cors-protocol) protocol specifications

### Cache Validation

The purpose of cache validation is to communicate with client browser's cache based on headers and make your site display instantly whenever possible. The language of communication is identified by [RFC-7232](https://tools.ietf.org/html/rfc7232) and [RFC-7234](https://tools.ietf.org/html/rfc7234) specifications both your site (via this API) and your browser must obey.

#### How Cache Validation Works

The way it works is too complex to be written here, so what follows next only covers the typical use case. HTTP standard allows you following simple method of communication based on conditional headers:

- client-server: give me X page @ your site
- server-client: here is my response to page X using [200 OK](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/200) status header, identified uniquely by value of [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag) response header (or last modified at GMT date defined by value of [Last-Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified) response header)
- client: ok, I've received response and saved to my cache and link request with [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag) (or [Last-Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified)).
- ...(some time passes)...
- client-server: give me X page @ your site once again. I'll also send formerly received  [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag)/[Last-Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified) that matched it as [If-None-Match](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match)/[If-Modified-Since](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since) request headers, so that you check if page has changed or not!
- server-client: response remained the same, so I'll save your bandwidth and only answer with a [304 Not Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/304) status header and no response body
- client: ok, so I'll display page from my browser's cache
- ...(some time passes)...
- client-server: give me X page @ your site once again plus same request headers
- server-client: response has changed, so this time I'll answer with a [200 OK](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/200) status header, full response body, along with the new [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag) (or [Last-Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified)
- client: ok, I've received response and saved to my cache and link request with the new [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag) (or [Last-Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified)).

Above method has a disadvantage by assuming cache to be *stale*, thus requiring a server roundtrip to check if requested resource has changed (*revalidation*). HTTP standard thus comes with an alternate fastest solution that uses [Cache-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control) response header:

- client-server: give me X page @ your site
- server-client: here is my response to page X and assume it as *fresh* for X seconds defined by **max-age** directive of [Cache-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control)
- client: ok, I've received response and saved to my cache. On future requests to same page, I won't ask server unless X seconds have passed and display response from cache!

The two methods of communication described above are not mutually exclusive. Mature applications use both, with different policies based on page requested: some pages can be assumed to be stale by default, others allow some freshness and finally a few may not even be compatible with caching because output changes on every request

#### How Is Cache Validation Implemented

To set cache-related response headers, using following [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) methods:

| Header | Method |
| --- | --- |
| [Cache-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control) | setCacheControl |
| [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag) | setEtag |
| [Last-Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified) | setLastModifiedTime |

To read cache-related request headers, using following [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) methods:

| Header | Method |
| --- | --- |
| [Cache-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control) | getCacheControl |
| [If-Match](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Match) | getIfMatch |
| [If-None-Match](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match) | getIfNoneMatch |
| [If-Modified-Since](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since) | getIfModifiedSince |
| [If-Unmodified-Since](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Unmodified-Since) | getIfUnmodifiedSince |

Fortunately, all of this is done automatically by API once you are running *validateCache* method of [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/blob/master/src/Wrapper.php) object. This method:

- configures [Cache-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control) response header based on namesake request header and XML settings encapsulated by [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php)
- sets [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag) response header based on [Lucinda\Headers\Cacheable](https://github.com/aherne/headers-api/blob/master/src/Cacheable.php) representation of requested resource, if exists
- sets [Last-Modified](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified) response header based on [Lucinda\Headers\Cacheable](https://github.com/aherne/headers-api/blob/master/src/Cacheable.php) representation of requested resource, if exists
- reads cache-related request headers, matches them with [Lucinda\Headers\Cacheable](https://github.com/aherne/headers-api/blob/master/src/Cacheable.php) representations and returns [HTTP status code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status) according to RFC specifications

So from developers' perspective you only need to:

- (optional) set up *no_cache* and *cache_expiration* XML tag attributes according to **[configuration](#configuration)** stage
- (mandatory) implement a [Lucinda\Headers\Cacheable](https://github.com/aherne/headers-api/blob/master/src/Cacheable.php) representation of requested resource (the way it can be converted to an etag or last time it was modified)
- (mandatory) use http status code returned by *validateCache* when response is rendered

Possible http status codes returned are:

- [200](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/200): this means response is ok and must come along with a body
- [304](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/304): this means response has not modified and must come without a body
- [412](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/412): this means a conditional header has failed, thus application should exit with an error

### CORS Validation

**[CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)** preliminary request is triggered automatically by client browser when it encounteres a situation where *preflight* is mandated by security reasons. 

#### How CORS Validation Works

What triggers a preflight request falls outside the scope of this API documentation. If you want to learn more, check:

- [simple requests](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#Simple_requests), to understand how can a preflight request be avoided
- [preflighted requests](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#Preflighted_requests), to understand what triggers a preflight request and what happens next

#### How Is CORS Validation Implemented

To set CORS-related response headers, using following [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) methods:

| Header | Method |
| --- | --- |
| [Access-Control-Allow-Credentials](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials) | setAccessControlAllowCredentials |
| [Access-Control-Allow-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers) | addAccessControlAllowHeader | 
| [Access-Control-Allow-Methods](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods) | addAccessControlAllowMethod | 
| [Access-Control-Allow-Origin](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin) | setAccessControlAllowOrigin |
| [Access-Control-Expose-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers) | addAccessControlExposeHeaders |
| [Access-Control-Max-Age](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age) | setAccessControlMaxAge |

To read CORS-related request headers, using following [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) methods:

| Header | Method |
| --- | --- |
| [Access-Control-Request-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Headers) | getAccessControlRequestHeaders |
| [Access-Control-Request-Method](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Method) | getAccessControlRequestMethod |
| [Origin](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin) | getOrigin |

Fortunately, all of this is done automatically by API once you are running *validateCORS* method of [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/blob/master/src/Wrapper.php) object. This method:

- requires developers to put origin hostname (eg: https://www.google.com) as argument. This cannot be set in XML since it may differ by development environment! If none is provided, any [Origin](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin) is considered valid!
- sets CORS response headers based on *allow_credentials*, *cors_max_age*, *allowed_request_headers*, *allowed_response_headers*  XML attributes encapsulated by [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php) and CORS request headers received

## Display

Once response headers, status and body (if any) become available, you are finally able to send headers back to client. Example:

```php
$wrapper = new Wrapper("configuration.xml", $_SERVER["REQUEST_URI"], getallheaders());
// developer now reads request headers, sets response headers, compiles $responseBody then applies cache validation:
$httpStatus = $wrapper->validateCache(new MyCacheable($responseBody), $_SERVER["REQUEST_METHOD"]);
// now response is ready for display
http_response_code(httpStatus);
$headers = $wrapper->getResponse()->toArray();
foreach ($headers as $name=>$value) {
    header($name.": ".$value);
}
if ($httpStatus!=304) {
    echo $responseBody;
}
```

## Installation

First choose a folder, associate it to a domain then write this command there using console:

```console
composer require lucinda/headers
```

Then create a *configuration.xml* file holding configuration settings (see [configuration](#configuration) above) and a *index.php* file (see [initialization](#initialization) and [display](#display)) in project root with following code:

```php
require(__DIR__."/vendor/autoload.php");
$wrapper = new Wrapper("configuration.xml", $_SERVER["REQUEST_URI"], getallheaders());
// if request is CORS, response can be done immediately
if ($_SERVER["REQUEST_METHOD"]=="OPTIONS") {
    $wrapper->validateCORS((!empty($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER["SERVER_NAME"]);
    $headers = $wrapper->getResponse()->toArray();
    foreach ($headers as $name=>$value) {
        header($name.": ".$value);
    }
    exit();
}
// developer reads request headers, sets response headers, compiles $responseBody
// developer creates a Lucinda\Headers\Cacheable instance (MyCacheable), able to convert $responseBody into an ETag string, then performs cache validation
$httpStatus = $wrapper->validateCache(new MyCacheable($responseBody), $_SERVER["REQUEST_METHOD"]);
// now response is ready for display
http_response_code(httpStatus);
$headers = $wrapper->getResponse()->toArray();
foreach ($headers as $name=>$value) {
    header($name.": ".$value);
}
if ($httpStatus!=304) {
    echo $responseBody;
}
```

Then make sure domain is available to world-wide-web and all request that point to it are rerouted to index.php:

```
RewriteEngine on
RewriteRule ^(.*)$ index.php
```

## Unit Tests

For tests and examples, check following files/folders in API sources:

- [test.php](https://github.com/aherne/headers-api/blob/master/test.php): runs unit tests in console
- [unit-tests.xml](https://github.com/aherne/headers-api/blob/master/unit-tests.xml): sets up unit tests and mocks "loggers" tag
- [tests](https://github.com/aherne/headers-api/blob/master/tests): unit tests for classes from [src](https://github.com/aherne/headers-api/blob/master/src) folder

## Examples

To see examples how request headers are parsed by [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php), check its matching [UnitTest](https://github.com/aherne/headers-api/blob/master/tests/RequestTest.php). To see how response headers are set by [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php), check its matching [UnitTest](https://github.com/aherne/headers-api/blob/master/tests/ResponseTest.php). To see detailed examples of each headers and understand them in greatest detail, there is no better documentation than the one provided by [Mozilla](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers)!