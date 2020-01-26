# HTTP Headers API

This API encapsulates HTTP request headers received from client and response headers to send back, offering an ability to bind them for cache and CORS validation. That task can be achieved using following steps:

- **[configuration](#configuration)**: setting up an XML file where cache/CORS validation policies are configured
- **[initialization](#initialization)**: using [Lucinda\Headers\Wrapper](https://github.com/aherne/headers-api/blob/master/src/Wrapper.php) to read above XML into a [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php), read HTTP request headers into a [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) then initialize [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php), encapsulating HTTP response headers logic.
- **[validation](#validation)**: using above to perform cache/CORS validation and set [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) accordingly
- **[display](#display)**: sending back response to caller using [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) headers compiled above (or set individually by user)

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
| getAccept() | void | array | Gets content types accepted by client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept">Accept</a> |
| getAcceptCharset() | void | array | Gets charsets accepted by client| <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Charset">Accept-Charset</a> |
| getAcceptEncoding() | void | array | Gets encodings accepted by client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding">Accept-Encoding</a> |
| getAcceptLanguage() | void | array | Gets languages accepted by client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language">Accept-Language</a> |
| getTE() | void | array | Gets transfer encodings accepted by client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/TE">TE</a> |
| getAuthorization() | void | ?[Lucinda\Headers\Request\Authorization](https://github.com/aherne/headers-api/blob/master/src/Request/Authorization.php) | Gets credentials for user authentication | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Authorization">Authorization</a> |
| getCacheControl() | void | ?[Lucinda\Headers\Request\CacheControl](https://github.com/aherne/headers-api/blob/master/src/Request/CacheControl.php) | Gets HTTP caching settings requested by client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control">Cache-Control</a> |
| getDNT() | void | bool | Gets whether or not client does not want to be tracked | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/DNT">DNT</a> |
| getDate() | void | ?int | Gets UNIX timestamp of date request came with | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date">Date</a> |
| getExpect() | void | bool | Gets whether client is about to send a large request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect">Expect</a> |
| getSaveData() | void | bool | ... | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Save-Data">Save-Data</a> |
| getForwardedIP() | void | ?string | Gets origin IP that got forwarded by proxy | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For">X-Forwarded-For</a> |
| getForwardedProxy() | void | ?string | Gets proxy IP that forwarded client, if present | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For">X-Forwarded-For</a> |
| getForwardedHost() | void | ?string | Gets origin Host that got forwarded by proxy  | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Host">X-Forwarded-Host</a> |
| getForwardedProtocol() | void | ?string | Gets origin protocol that got forwarded by proxy | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Proto">X-Forwarded-Proto</a> |
| getFrom() | void | ?string | Gets email address of client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/From">From</a> |
| getHost() | void | ?string | Gets hostname requested by client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Host">Host</a> |
| getIfRangeDate() | void | ?int | Gets UNIX timestamp of range condition, if present  | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Range">If-Range</a> |
| getIfRangeEtag() | void | ?string | Gets ETag of range condition, if present | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Range">If-Range</a> |
| getRange() | void | ?[Lucinda\Headers\Request\Range](https://github.com/aherne/headers-api/blob/master/src/Request/Range.php) | Gets bytes range requested by client from a big document | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range">Range</a> |
| getReferer() | void | ?string | Gets address of the previous web page from which a link to the currently requested page was followed | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer">Referer</a> |
| getUserAgent() | void | ?string | Gets signature of client browser | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent">User-Agent</a> |
| getWantDigest() | void | array | Gets details of digest client wants in response | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Want-Digest">Want-Digest</a> |
| getIfMatch() | void | ?string | Gets ETag that must condition response to be sent only if matches that of requested resource | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Match">If-Match</a> |
| getIfNoneMatch() | void | ?string | Gets ETag that must condition response to be sent only if it does not match that of requested resource | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match">If-None-Match</a> |
| getIfModifiedSince() | void | ?int | Gets UNIX timestamp that must condition response to be sent only if matches that of requested resource | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since">If-Modified-Since</a> |
| getIfUnmodifiedSince() | void | ?int | Gets UNIX timestamp that must condition response to be sent only if it does not match that of requested resource | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Unmodified-Sicne">If-Unmodified-Sicne</a> |
| getAccessControlRequestHeaders() | void | array | Gets headers that will be requested later as part of a **CORS** preliminary request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Headers">Access-Control-Request-Headers</a> |
| getAccessControlRequestMethod() | void | ?string | Gets HTTP method that will be used later as part of a **CORS** preliminary request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Method">Access-Control-Request-Method</a> |
| getOrigin() | void | ?string | Gets client hostname to validate access to requested resource, sent automatically in **CORS** preliminary request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin">Origin</a> |
| getCustomHeaders() | void | array | Gets all non-standard headers requested by client as header name:value array | (any) |

### Response

As stated above, class [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) encapsulates HTTP response headers to send back. Each method inside (minus *toArray*) corresponds to a header:

| Method | Arguments | Returns | Description | Header |
| --- | --- | --- | --- | --- |
| addAcceptPatch | string $mimeType, string $charset=null | void | Adds a content type for whom PATCH requests are accepted | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Patch">Accept-Patch</a> |
| setAcceptRanges | bool $value | void | Sets whether or not range requests are accepted | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Ranges">Accept-Ranges</a> |
| addAllow | string $requestMethod | void | Sets a request method server accepts for requested resource | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Allow">Allow</a> |
| addClearSiteData | string $directive = "*" | void | Sets a browsing data (cookies, storage, cache) to be cleared on client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Clear-Site-Data">Clear-Site-Data</a> |
| setCacheControl | void | [Lucinda\Headers\Response\CacheControl](https://github.com/aherne/headers-api/blob/master/src/Response/CacheControl.php) | Sets HTTP caching settings to be used by client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control">Cache-Control</a> |
| setContentDisposition | string $type | [Lucinda\Headers\Response\ContentDisposition](https://github.com/aherne/headers-api/blob/master/src/Response/ContentDisposition.php) | Sets how content will be displayed (inline or attachment) | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition">Content-Disposition</a> |
| addContentEncoding | string $contentEncoding | void | Adds an encoding applied in compressing response | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding">Content-Encoding</a> |
| addContentLanguage | string $language | void | Adds a language to associate response with  | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Language">Content-Language</a> |
| setContentLength | int $length | void | Sets byte length of response | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Length">Content-Length</a> |
| setContentLocation | string $url | void | Sets alternate uri for the returned data | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Location">Content-Location</a> |
| setContentRange | string $unit = "bytes", int $start = null, int $end = null, int $size = null | void | Sets returning document range | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Range">Content-Range</a> |
| setContentType | string $mimeType, string $charset = null | void | Sets content type of response | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type">Content-Type</a> |
| setContentTypeOptions | void | void | Indicates that content types should not be changed or followed (anti-sniffing solution) | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options">X-Content-Type-Options</a> |
| setCrossOriginResourcePolicy | string $option | void | Sets policy to block no-cors cross-origin/cross-site requests to the given resource | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Resource-Policy">Cross-Origin-Resource-Policy</a> |
| addDigest | string $algorithm, string $value | void | Adds a digest to response | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Digest">Digest</a> |
| setEtag | string $value | void | Sets ETag to associate response to requested resource with | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag">ETag</a> |
| setExpirationTime | int $unixTime | void | Sets UNIX time by which response to be cached by client browser should require revalidation (**deprecated**) | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expires">Expires</a> |
| setLastModifiedTime | int $unixTime | void | Sets UNIX time requested resource was last modified, to associate response with  | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified">Last-Modified</a> |
| setLocation | string $url | void | Sets url client should redirect to. | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Location">Location</a> |
| setReferrerPolicy | string $option | void | Sets much Referer information should be included with requests. | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy">Referrer-Policy</a> |
| setRentryAfterDate | int $unixTime | void | Sets UNIX time client should wait before making a follow-up request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Rentry-After">Rentry-After</a> |
| setRentryAfterDelay | int $delay | void | Sets seconds client should wait before making a follow-up request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Rentry-After">Rentry-After</a> |
| setSourceMap | string $url | void | Links response to a source map enabling the browser to present the reconstructed original in the debugger. | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/SourceMap">SourceMap</a> |
| setStrictTransportSecurity | bool $includeSubdomains = false, bool $preload = false | void | Informs client that current website only accepts HTTPS | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security">Strict-Transport-Security</a> |
| addTimingAllowOrigin | string $url = "*" | void | Adds an origins allowed to see values from Resource Timing API | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Timing-Allow-Origin">Timing-Allow-Origin</a> |
| setTk | string $status | void | Sets tracking status that applied to the corresponding request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Tk">Tk</a> |
| setTrailer | string $headerNames | void | Allows the sender to include additional fields at the end of chunked messages | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Trailer">Trailer</a> |
| addTransferEncoding | string $contentEncoding | void | Adds form of encoding used to safely transfer the payload body to the user. | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Transfer-Encoding">Transfer-Encoding</a> |
| addVary | string $headerName = "*" | void | Adds a request header to decide in future whether a cached response can be used | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Vary">Vary</a> |
| setWWWAuthenticate | string $type, string $realm="" | [Lucinda\Headers\Response\WwwAuthenticate](https://github.com/aherne/headers-api/blob/master/src/Response/WwwAuthenticate.php) |  Defines the authentication method that should be used to gain access to a resource | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/WWW-Authenticate">WWW-Authenticate</a> |
| setDNSPrefetchControl | bool $value = true | void | Activates DNS prefetching on client | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-DNS-Prefetch-Control">X-DNS-Prefetch-Control</a> |
| setFrameOptions | string $option | void | Indicates whether or not a browser should be allowed to render a page in a frame / iframe / embed / object | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options">X-Frame-Options</a> |
| setAccessControlAllowCredentials | void | void | Answers to **CORS** request by signaling credentials are to be exposed | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials">Access-Control-Allow-Credentials</a> |
| addAccessControlAllowHeader | string $headerName | void | Adds allowed request header to answer a **CORS** request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers">Access-Control-Allow-Headers</a> |
| addAccessControlAllowMethod | string $requestMethod | void | Adds allowed request method to answer a **CORS** request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods">Access-Control-Allow-Methods</a> |
| setAccessControlAllowOrigin | string $origin = "*" | void | Sets allowed origin to answer a **CORS** request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin">Access-Control-Allow-Origin</a> |
| addAccessControlExposeHeaders | string $headerName = "*" | void | Adds response header client should expose to answer a **CORS** request | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers">Access-Control-Expose-Headers</a> |
| setAccessControlMaxAge | int $duration | void | Sets how long response to a CORS request should be cached (in seconds) | <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age">Access-Control-Max-Age</a> |
| setCustomHeader | string $name, string $value | void | Sets a custom header by name and value (this **may trigger CORS requests**) | (any) |
| toArray | void | array | Converts all headers set to a name:value array ready to be sent back to client | - |

## Validation

Obviously, developers need to *know* headers received from client and *set* headers to send back in response, but the way they link depends on your application. There are two particular cases, however, in which request and response headers (and HTTP status) are bound logically:

- **[cache validation](#cache-validation)**: validating [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) headers based on [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php) in order to *communicate with client browser cache* and set [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) headers in accordance to    
- **[CORS validation](#CORS-validation)**: validating [Lucinda\Headers\Request](https://github.com/aherne/headers-api/blob/master/src/Request.php) headers based on [Lucinda\Headers\Policy](https://github.com/aherne/headers-api/blob/master/src/Policy.php) in order to *answer a CORS request* and set [Lucinda\Headers\Response](https://github.com/aherne/headers-api/blob/master/src/Response.php) headers in accordance to [CORS](https://fetch.spec.whatwg.org/#cors-protocol) protocol specifications

### Cache Validation

The purpose of cache validation is to communicate with client browser's cache based on headers and make your site display instantly whenever possible. The language of communication is identified by [RFC-7232](https://tools.ietf.org/html/rfc7232) and [RFC-7234](https://tools.ietf.org/html/rfc7234) specifications both your site (via this API) and your browser must obey.

#### How Validation Works

HTTP standard allows you following simple method of communication based on conditional headers:

- client-server: give me X page @ your site
- server-client: here is my response to page X, identified uniquely by value of ETag response header (or last modified at GMT date defined by value of Last-Modified response header)
- client: ok, I've received response and saved to my cache and link it with ETag (or Last-Modified).
- ...(some time passes)...
- client-server: give me X page @ your site once again. I've sent formerly received ETag/Last-Modified that matched it as If-None-Match/If-Modified-Since request headers, so you check if they've changed or not!
- server-client: response remained the same, so I'll save your bandwidth and only answer with a 304 Not Modified status header along with existing ETag/Last-Modified
- client: ok, so I'll display page from my browser's cache
- ...(some time passes)...
- client-server: give me X page @ your site once again. I've sent formerly received ETag/Last-Modified that matched it as If-None-Match/If-Modified-Since request headers, so you check if they've changed or not!
- server-client: response has changed, so this time I'll answer with a 200 OK status header, full response body, along with the new ETag/Last-Modified
- client: ok, I've received response and saved to my cache and link it with the new ETag (or Last-Modified).

Above method has one disadvantage in assuming cache to be *stale*, thus requiring a server roundtrip to check if requested resource has changed. HTTP standard thus comes with an alternate fastest solution:

- client-server: give me X page @ your site
- server-client: here is my response to page X and assume it as *fresh* for Y seconds based on response header *Cache-Control: max-age=Y;* 
- client: ok, I've received response and saved to my cache. On future requests to same page, I won't ask server unless Y seconds have passed and display response from cache!

The two methods of communication described above are not mutually exclusive. Mature applications use both of them, with different policies based on page requested: some pages can be assumed to be stale by default, others allow some freshness and finally a few may not even be compatible with caching (eg: displaying output that changes on every request).




linking request headers *Cache-Control*, *If-Match*/*If-None-Match*, *If-Unmodified-Since*/*If-Modified-Since* to response headers *Cache-Control*, *ETag*, *Last-Modified* as well as a HTTP response status 

linking request headers *Origin*, *Access-Control-Request-Method*, *Access-Control-Request-Headers* to response headers *Access-Control-Allow-Origin*, *Access-Control-Allow-Methods*, *Access-Control-Allow-Headers* in accordance to [CORS](https://fetch.spec.whatwg.org/#cors-protocol) protocol