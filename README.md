# HTTP Headers API

This API encapsulates HTTP request headers and HTTP response headers based on IETF specifications (https://tools.ietf.org/html/rfc7231) via following classes:

- Request: reads HTTP request headers according to IETF specifications (https://tools.ietf.org/html/rfc7231) then offers getter/header
- Response: sets HTTP response headers according to IETF specifications (https://tools.ietf.org/html/rfc7231) via setter/header
- PreflightRequest: reads HTTP request headers that came from a preflight (https://developer.mozilla.org/en-US/docs/Glossary/CORS) OPTIONS request then offers getter/header
- PreflightResponse: sets HTTP response headers that answer a prefligh (https://developer.mozilla.org/en-US/docs/Glossary/CORS) OPTIONS request via setter/header

In addition of that, it is able to perform cache validation by matching resource requested to request headers via following classes:

- Cacheable: interface for converting any resource requested (eg: page) to an *etag* and *last modified date* in order to become subject of caching later on
- CacheValidator: performs validation of Cacheable representation of requested resource based on IETF specifications of cache-related headers encapsulated by Request already