<?php

namespace Lucinda\Headers;

use Lucinda\Headers\Request\Method;
use Lucinda\Headers\Response\ContentDisposition;
use Lucinda\Headers\Response\StrictTransportSecurity;
use Lucinda\Headers\Response\WwwAuthenticate;
use Lucinda\Headers\Response\CacheControl;

/**
 * Encapsulates writing HTTP response headers according to specifications EXCEPT those already handled
 * by web server: Connection, Keep-Alive, Date, Server
 */
class Response
{
    // IGNORED: Content-Security-Policy, Content-Security-Policy-Report-Only
    /**
     * @var string[]
     */
    private array $acceptPatch = [];
    private ?string $acceptRanges = null;
    /**
     * @var string[]
     */
    private array $allow = [];
    private ?CacheControl $cacheControl = null;
    /**
     * @var string[]
     */
    private array $clearSiteData = [];
    private ?ContentDisposition $contentDisposition = null;
    /**
     * @var string[]
     */
    private array $contentEncoding = [];
    /**
     * @var string[]
     */
    private array $contentLanguage = [];
    private ?int $contentLength = null;
    private ?string $contentLocation = null;
    private ?string $contentRange = null;
    private ?string $contentType = null;
    private ?string $crossOriginResourcePolicy = null;
    /**
     * @var string[]
     */
    private array $digest = []; // <> Want-Digest
    private ?string $etag = null; // <> If-Match, If-None-Match
    private ?string $expires = null;
    private ?string $lastModified = null; // <> If-Modified-Since, If-Unmodified-Since
    private ?string $location = null;
    private ?string $referrerPolicy = null;
    private string|int|null $rentryAfter = null;
    private ?string $sourceMap = null;
    private ?StrictTransportSecurity $strictTransportSecurity = null;
    /**
     * @var string[]
     */
    private array $timingAllowOrigin=[];
    private ?string $tk = null; // <> DNT
    private ?string $trailer = null;
    /**
     * @var string[]
     */
    private array $transferEncoding=[]; // <> TE
    /**
     * @var string[]
     */
    private array $vary = [];
    private ?WwwAuthenticate $wwwAuthenticate = null; // <> Authorization
    private ?string $xContentTypeOptions = null;
    private ?string $xDNSPrefetchControl = null;
    private ?string $xFrameOptions = null;
    /**
     * @var array<string,string>
     */
    private array $customHeaders = [];
    private ?string $allowCredentials = null;
    /**
     * @var string[]
     */
    private array $allowHeaders = []; // <> Access-Control-Request-Headers
    /**
     * @var string[]
     */
    private array $allowMethods = [];
    private ?string $allowOrigin = null; // <> Origin
    /**
     * @var string[]
     */
    private array $exposeHeaders = [];
    private ?int $maxAge = null;

    /**
     * Sets one of values of HTTP header: Accept-Patch
     *
     * @param string $mimeType
     * @param string $charset
     */
    public function addAcceptPatch(string $mimeType, string $charset=null): void
    {
        $this->acceptPatch[] = $mimeType.($charset ? ";charset=".$charset : "");
    }

    /**
     * Sets value of HTTP header: Accept-Ranges
     *
     * @param bool $value
     */
    public function setAcceptRanges(bool $value): void
    {
        $this->acceptRanges = ($value ? "bytes" : "none");
    }

    /**
     * Sets one of values of HTTP header: Allow
     *
     * @param  string $requestMethod
     * @throws UserException
     */
    public function addAllow(string $requestMethod): void
    {
        if (Method::tryFrom($requestMethod)===null) {
            throw new UserException("Invalid value for header: Allow");
        }
        $this->allow[] = $requestMethod;
    }

    /**
     * Delegates setting value of HTTP header: Cache-Control
     *
     * @return CacheControl
     */
    public function setCacheControl(): CacheControl
    {
        $cacheControl = new CacheControl();
        $this->cacheControl = $cacheControl;
        return $cacheControl;
    }

    /**
     * Sets value of HTTP header: Clear-Site-Data
     *
     * @param  string $directive
     * @throws UserException
     */
    public function addClearSiteData(string $directive = "*"): void
    {
        if (!in_array(
            $directive,
            [
            "cache",
            "cookies",
            "storage",
            "executionContexts",
            "*"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: Clear-Site-Data");
        }
        $this->clearSiteData[] = $directive;
    }

    /**
     * Delegates setting value of HTTP header: Content-Disposition
     *
     * @param  string $type
     * @return ContentDisposition|null
     * @throws UserException
     */
    public function setContentDisposition(string $type): ?ContentDisposition
    {
        if (!in_array(
            $type,
            [
            "inline",
            "attachment"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: Content-Disposition");
        }
        $contentDisposition = new ContentDisposition($type);
        $this->contentDisposition = $contentDisposition;
        return $contentDisposition;
    }

    /**
     * Sets one of values of HTTP header: Content-Encoding
     *
     * @param  string $contentEncoding
     * @throws UserException
     */
    public function addContentEncoding(string $contentEncoding): void
    {
        if (!in_array(
            $contentEncoding,
            [
            "gzip",
            "compress",
            "deflate",
            "identity",
            "br"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: Content-Disposition");
        }
        $this->contentEncoding[] = $contentEncoding;
    }

    /**
     * Sets one of values of HTTP header: Content-Language
     *
     * @param  string $language
     * @throws UserException
     */
    public function addContentLanguage(string $language): void
    {
        if (preg_match("/^[a-z]{2}(\-[A-Z]{2})?$/", $language)!==1) {
            throw new UserException("Invalid value for header: Content-Language");
        }
        $this->contentLanguage[] = $language;
    }

    /**
     * Sets value of HTTP header: Content-Length
     *
     * @param int $length
     */
    public function setContentLength(int $length): void
    {
        $this->contentLength = $length;
    }

    /**
     * Sets value of HTTP header: Content-Location
     *
     * @param string $url
     */
    public function setContentLocation(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UserException("Invalid value for header: Content-Location");
        }
        $this->contentLocation = $url;
    }

    /**
     * Sets value of HTTP header: Content-Range
     *
     * @param string $unit
     * @param int    $start
     * @param int    $end
     * @param int    $size
     */
    public function setContentRange(string $unit = "bytes", int $start = null, int $end = null, int $size = null): void
    {
        if ($start && $end) {
            $this->contentRange = $unit." ".$start."-".$end."/".($size ? $size : "*");
        } elseif ($size) {
            $this->contentRange = $unit." */".$size;
        } else {
            throw new UserException("Invalid arguments for header: Content-Range");
        }
    }

    /**
     * Sets value of HTTP header: Content-Type
     *
     * @param  string      $mimeType
     * @param  string|null $charset
     * @throws UserException
     */
    public function setContentType(string $mimeType, string $charset = null): void
    {
        if (preg_match("/^(application|audio|example|font|image|model|text|video)\/(.*)$/", $mimeType)!==1) {
            throw new UserException("Invalid value for header: Content-Type");
        }
        $this->contentType = $mimeType.($charset ? "; charset=".$charset : "");
    }

    /**
     * Sets value of HTTP header: Cross-Origin-Resource-Policy
     *
     * @param  string $option
     * @throws UserException
     */
    public function setCrossOriginResourcePolicy(string $option): void
    {
        if (!in_array(
            $option,
            [
            "same-site",
            "same-origin",
            "cross-site"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: Cross-Origin-Resource-Policy");
        }
        $this->crossOriginResourcePolicy = $option;
    }

    /**
     * Sets one of values of HTTP header: Digest
     *
     * @param  string $algorithm
     * @param  string $value
     * @throws UserException
     */
    public function addDigest(string $algorithm, string $value): void
    {
        if (!in_array(
            $algorithm,
            [
            "MD5",
            "UNIXsum",
            "UNIXcksum",
            "SHA",
            "SHA-256",
            "SHA-512"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: Digest");
        }
        $this->digest[] = $algorithm."=".$value;
    }

    /**
     * Sets value of HTTP header: ETag
     *
     * @param string $value
     */
    public function setEtag(string $value): void
    {
        $this->etag = '"'.$value.'"';
    }

    /**
     * Sets value of HTTP header: Expires
     *
     * @param int $unixTime
     */
    public function setExpirationTime(int $unixTime): void
    {
        $this->expires = gmdate("D, d M Y H:i:s T", $unixTime);
    }

    /**
     * Sets value of HTTP header: Last-Modified
     *
     * @param int $unixTime
     */
    public function setLastModifiedTime(int $unixTime): void
    {
        $this->lastModified = gmdate("D, d M Y H:i:s T", $unixTime);
    }

    /**
     * Sets value of HTTP header: Location
     *
     * @param  string $url
     * @throws UserException
     */
    public function setLocation(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UserException("Invalid value for header: Location");
        }
        $this->location = $url;
    }

    /**
     * Sets value of HTTP header: Referrer-Policy
     *
     * @param  string $option
     * @throws UserException
     */
    public function setReferrerPolicy(string $option): void
    {
        if (!in_array(
            $option,
            [
            "no-referrer",
            "no-referrer-when-downgrade",
            "origin",
            "origin-when-cross-origin",
            "same-origin",
            "strict-origin",
            "strict-origin-when-cross-origin",
            "unsafe-url"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: Referrer-Policy");
        }
        $this->referrerPolicy = $option;
    }

    /**
     * Sets date value of HTTP header: Rentry-After
     *
     * @param int $unixTime
     */
    public function setRentryAfterDate(int $unixTime): void
    {
        $this->rentryAfter = gmdate("D, d M Y H:i:s T", $unixTime);
    }

    /**
     * Sets delay value of HTTP header: Rentry-After
     *
     * @param int $delay
     */
    public function setRentryAfterDelay(int $delay): void
    {
        $this->rentryAfter = $delay;
    }

    /**
     * Sets value of HTTP header: Source-Map
     *
     * @param  string $url
     * @throws UserException
     */
    public function setSourceMap(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UserException("Invalid value for header: Source-Map");
        }
        $this->sourceMap = $url;
    }

    /**
     * Sets value of HTTP header: Strict-Transport-Security
     */
    public function setStrictTransportSecurity(): StrictTransportSecurity
    {
        $this->strictTransportSecurity = new StrictTransportSecurity();
        return $this->strictTransportSecurity;
    }

    /**
     * Sets value of HTTP header: Timing-Allow-Origin
     *
     * @param  string $url
     * @throws UserException
     */
    public function addTimingAllowOrigin(string $url = "*"): void
    {
        if ($url!="*" && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UserException("Invalid value for header: Timing-Allow-Origin");
        }
        $this->timingAllowOrigin[] = $url;
    }

    /**
     * Delegates setting value of HTTP header: Tk
     *
     * @param  string $status
     * @throws UserException
     */
    public function setTk(string $status): void
    {
        if (!in_array(
            $status,
            [
            "!",
            "?",
            "G",
            "N",
            "T",
            "C",
            "P",
            "D",
            "U"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: Tk");
        }
        $this->tk = $status;
    }

    /**
     * Sets value of HTTP header: Trailer
     *
     * @param string $headerNames
     */
    public function setTrailer(string $headerNames): void
    {
        $this->trailer = $headerNames;
    }

    /**
     * Sets one of values of HTTP header: Transfer-Encoding
     *
     * @param  string $contentEncoding
     * @throws UserException
     */
    public function addTransferEncoding(string $contentEncoding): void
    {
        if (!in_array(
            $contentEncoding,
            [
            "gzip",
            "compress",
            "deflate",
            "identity",
            "chunked"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: Transfer-Encoding");
        }
        $this->transferEncoding[] = $contentEncoding;
    }

    /**
     * Sets one of values of HTTP header: Transfer-Encoding
     *
     * @param string $headerName
     */
    public function addVary(string $headerName = "*"): void
    {
        $this->vary[] = $headerName;
    }

    /**
     * Delegates setting value of HTTP header: WWW-Authenticate
     *
     * @param  string $type
     * @param  string $realm
     * @return WwwAuthenticate
     * @throws UserException
     */
    public function setWWWAuthenticate(string $type, string $realm=""): WwwAuthenticate
    {
        if (!in_array(
            $type,
            [
            "Basic",
            "Bearer",
            "Digest",
            "HOBA",
            "Mutual",
            "Negotiate",
            "OAuth",
            "SCRAM-SHA-1",
            "SCRAM-SHA-256",
            "vapid"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: WWW-Authenticate");
        }
        $authenticate = new WwwAuthenticate($type, $realm);
        $this->wwwAuthenticate = $authenticate;
        return $authenticate;
    }

    /**
     * Sets value of HTTP header: X-Content-Type-Options
     */
    public function setContentTypeOptions(): void
    {
        $this->xContentTypeOptions = "nosniff";
    }

    /**
     * Sets one of values of HTTP header: X-DNS-Prefetch-Control
     *
     * @param bool $value
     */
    public function setDNSPrefetchControl(bool $value = true): void
    {
        $this->xDNSPrefetchControl = ($value ? "on" : "off");
    }

    /**
     * Sets one of values of HTTP header: X-Frame-Options
     *
     * @param  string $option
     * @throws UserException
     */
    public function setFrameOptions(string $option): void
    {
        if (!in_array(
            $option,
            [
            "deny",
            "same-origin"
            ]
        )
        ) {
            throw new UserException("Invalid value for header: X-Frame-Options");
        }
        $this->xFrameOptions = $option;
    }

    /**
     * Sets a header not present in IETF specifications
     *
     * @param string $name
     * @param string $value
     */
    public function setCustomHeader(string $name, string $value): void
    {
        $this->customHeaders[$name] = $value;
    }

    /**
     * Sets value of HTTP header: Access-Control-Allow-Credentials
     */
    public function setAccessControlAllowCredentials(): void
    {
        $this->allowCredentials = "true";
    }

    /**
     * Sets value of HTTP header: Access-Control-Allow-Headers
     *
     * @param string $headerName
     */
    public function addAccessControlAllowHeader(string $headerName): void
    {
        $this->allowHeaders[] = $headerName;
    }

    /**
     * Sets value of HTTP header: Access-Control-Allow-Method
     *
     * @param  string $requestMethod
     * @throws UserException
     */
    public function addAccessControlAllowMethod(string $requestMethod): void
    {
        if (Method::tryFrom($requestMethod)===null) {
            throw new UserException("Invalid value for header: Access-Control-Allow-Method");
        }
        $this->allowMethods[] = $requestMethod;
    }

    /**
     * Sets value of HTTP header: Access-Control-Allow-Origin
     *
     * @param  string $origin
     * @throws UserException
     */
    public function setAccessControlAllowOrigin(string $origin = "*"): void
    {
        if ($origin!="*" && !filter_var($origin, FILTER_VALIDATE_URL)) {
            throw new UserException("Invalid value for header: Access-Control-Allow-Origin");
        }
        $this->allowOrigin = $origin;
    }

    /**
     * Sets value of HTTP header: Access-Control-Expose-Headers
     *
     * @param string $headerName
     */
    public function addAccessControlExposeHeader(string $headerName = "*"): void
    {
        $this->exposeHeaders[] = $headerName;
    }

    /**
     * Sets value of HTTP header: Access-Control-Max-Age
     *
     * @param int $duration
     */
    public function setAccessControlMaxAge(int $duration): void
    {
        $this->maxAge = $duration;
    }

    /**
     * Gets all response headers as key-value pairs.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        $correspondences = [
            'Accept-Patch' => 'acceptPatch',
            'Accept-Ranges' => 'acceptRanges',
            'Access-Control-Allow-Credentials' => 'allowCredentials',
            'Access-Control-Allow-Headers' => 'allowHeaders',
            'Access-Control-Allow-Methods' => 'allowMethods',
            'Access-Control-Allow-Origin' => 'allowOrigin',
            'Access-Control-Expose-Headers' => 'exposeHeaders',
            'Access-Control-Max-Age' => 'maxAge',
            'Allow' => 'allow',
            'Cache-Control' => 'cacheControl',
            'Clear-Site-Data' => 'clearSiteData',
            'Content-Disposition' => 'contentDisposition',
            'Content-Encoding' => 'contentEncoding',
            'Content-Language' => 'contentLanguage',
            'Content-Length' => 'contentLength',
            'Content-Location' => 'contentLocation',
            'Content-Range' => 'contentRange',
            'Content-Type' => 'contentType',
            'Cross-Origin-Resource-Policy' => 'crossOriginResourcePolicy',
            'Digest' => 'digest',
            'ETag' => 'etag',
            'Expires' => 'expires',
            'Last-Modified' => 'lastModified',
            'Location' => 'location',
            'Referrer-Policy' => 'referrerPolicy',
            'Rentry-After' => 'rentryAfter',
            'Source-Map' => 'sourceMap',
            'Strict-Transport-Security' => 'strictTransportSecurity',
            'Timing-Allow-Origin' => 'timingAllowOrigin',
            'Tk' => 'tk',
            'Trailer' => 'trailer',
            'Transfer-Encoding' => 'transferEncoding',
            'Vary' => 'vary',
            'WWW-Authenticate' => 'wwwAuthenticate',
            'X-Content-Type-Options' => 'xContentTypeOptions',
            'X-DNS-Prefetch-Control' => 'xDNSPrefetchControl',
            'X-Frame-Options' => 'xFrameOptions'
        ];

        $response = [];
        foreach ($correspondences as $header=>$field) {
            $value = $this->$field;
            if ($value) {
                if (is_object($value)) {
                    $response[$header] = $value->toString();
                } elseif (is_array($value)) {
                    if ($header == "Clear-Site-Data") {
                        $response[$header] = "\"".implode("\", \"", $value)."\"";
                    } else {
                        $response[$header] = implode(", ", $value);
                    }
                } else {
                    $response[$header] = $value;
                }
            }
        }
        if ($this->customHeaders) {
            $response = array_merge($response, $this->customHeaders);
        }
        return $response;
    }
}
