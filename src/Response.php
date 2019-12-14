<?php
namespace Lucinda\Headers;

use Lucinda\Headers\Response\ContentDisposition;
use Lucinda\Headers\Response\WwwAuthenticate;
use Lucinda\Headers\Response\CacheControl;

/**
 * Encapsulates writing HTTP response headers according to specifications EXCEPT those already handled 
 * by web server: Connection, Keep-Alive, Date, Server
 */
class Response
{
    // IGNORED: Content-Security-Policy, Content-Security-Policy-Report-Only
    private $acceptPatch = [];    
    private $acceptRanges;
    private $allow = [];
    private $cacheControl;
    private $clearSiteData = [];
    private $contentDisposition;
    private $contentEncoding = [];
    private $contentLanguage = [];
    private $contentLength;
    private $contentLocation;
    private $contentRange;
    private $contentType;
    private $crossOriginResourcePolicy;
    private $digest = []; // <> Want-Digest
    private $etag; // <> If-Match, If-None-Match
    private $expires;
    private $lastModified; // <> If-Modified-Since, If-Unmodified-Since
    private $location;
    private $referrerPolicy;
    private $rentryAfter;
    private $sourceMap;
    private $strictTransportSecurity;
    private $timingAllowOrigin=[];
    private $tk; // <> DNT
    private $trailer=[];
    private $transferEncoding=[]; // <> TE
    private $vary = [];
    private $WWWAuthenticate; // <> Authorization
    private $xContentTypeOptions;
    private $xDNSPrefetchControl;
    private $xFrameOptions;    
        
    /**
     * Sets one of values of HTTP header: Accept-Patch
     *
     * @param string $mimeType
     * @param string $charset
     */
    public function addAcceptPatch(string $mimeType, string $charset=null): void
    {
        $this->acceptPatch[] = $mimeType.($charset?";".$charset:"");
    }    
    
    /**
     * Sets value of HTTP header: Accept-Ranges
     *
     * @param string $type
     */
    public function setAcceptRanges(string $type): void
    {
        if (!in_array($type, ["bytes","none"])) {
            return;
        }
        $this->acceptRanges = $type;
    }
    
    /**
     * Sets one of values of HTTP header: Allow
     *
     * @param string $requestMethod
     */
    public function addAllow(string $requestMethod): void
    {
        if (!in_array($requestMethod, ["GET","HEAD","POST","PUT","DELETE","CONNECT","OPTIONS","TRACE","PATCH"])) {
            return;
        }
        $this->allow[] = $requestMethod;
    }
    
    /**
     * Delegates setting value of HTTP header: Cache-Control
     *
     * @return CacheControl
     */
    public function setCacheControl(): void
    {
        $cacheControl = new CacheControl();
        $this->cacheControl = $cacheControl;
        return $cacheControl;
    }
    
    /**
     * Sets value of HTTP header: Clear-Site-Data
     *
     * @param string $directive
     */
    public function addClearSiteData(string $directive = "*"): void
    {
        if (!in_array($directive, ["cache", "cookies","storage","executionContexts","*"])) {
            return;
        }
        $this->clearSiteData[] = $directive;
    }
    
    /**
     * Delegates setting value of HTTP header: Content-Disposition
     *
     * @param string $type
     * @return ContentDisposition
     */
    public function setContentDisposition(string $type): void
    {
        if (!in_array($type, ["inline", "attachment"])) {
            return;
        }
        $contentDisposition = new ContentDisposition($type);
        $this->contentDisposition = $contentDisposition;
        return $contentDisposition;
    }
    
    /**
     * Sets one of values of HTTP header: Content-Encoding
     *
     * @param string $language
     */
    public function addContentEncoding(string $contentEncoding): void
    {
        if (!in_array($contentEncoding, ["gzip", "compress", "deflate", "identity", "br"])) {
            return;
        }
        $this->contentEncoding[] = $contentEncoding;
    }
    
    /**
     * Sets one of values of HTTP header: Content-Language
     *
     * @param string $language
     */
    public function addContentLanguage(string $language): void
    {
        if (preg_match("/^[a-z]{2}(\-[A-Z]{2})?$/", $language)!==1) {
            return;
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
        $this->contentLocation = $url;
    }
    
    /**
     * Sets value of HTTP header: Content-Range
     *
     * @param string $unit
     * @param int $start
     * @param int $end
     * @param int $size
     */
    public function setContentRange(string $unit = "bytes", int $start = null, int $end = null, int $size = null): void
    {
        if ($start && $end) {
            $this->contentRange = $unit." ".$start."-".$end."/".($size?$size:"*");
        } else if ($size) {
            $this->contentRange = $unit." */".$size;
        }
    }
    
    /**
     * Sets value of HTTP header: Content-Type
     * 
     * @param string $mimeType
     * @param string $charset
     */
    public function setContentType(string $mimeType, string $charset = null): void
    {
        if (preg_match("/^(application|audio|example|font|image|model|text|video)\/(.*)$/", $mimeType)!==1) {
            return;
        }
        $this->contentType = $mimeType.($charset?"; ".$charset:"");
    }
    
    /**
     * Sets value of HTTP header: Cross-Origin-Resource-Policy
     * 
     * @param string $option
     */
    public function setCrossOriginResourcePolicy(string $option): void
    {
        if (!in_array($option, ["same-site", "same-origin", "cross-site"])) {
            return;
        }
        $this->crossOriginResourcePolicy = $option;
    }
    
    /**
     * Sets one of values of HTTP header: Digest
     * 
     * @param string $algorithm
     * @param string $token
     */
    public function addDigest(string $algorithm, string $token): void
    {
        if (!in_array($algorithm, ["MD5", "UNIXsum", "UNIXcksum", "SHA", "SHA-256", "SHA-512"])) {
            return;
        }
        $this->digest[] = $algorithm."=".$token;
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
     * Sets value of HTTP header: Content-Type
     * 
     * @param int $unixTime
     */
    public function setExpirationTime(int $unixTime): void
    {
        $this->expires = date("D, d M Y H:i:s T", $unixTime);
    }
    
    /**
     * Sets value of HTTP header: Last-Modified
     * 
     * @param int $unixTime
     */
    public function setLastModifiedTime(int $unixTime): void
    {
        $this->lastModified = date("D, d M Y H:i:s T", $unixTime);
    }
    
    /**
     * Sets value of HTTP header: Location
     * 
     * @param string $url
     */
    public function setLocation(string $url): void
    {
        $this->location = $url;
    }
    
    /**
     * Sets value of HTTP header: Referrer-Policy
     * 
     * @param string $option
     */
    public function setReferrerPolicy(string $option): void
    {
        if (!in_array($option, ["no-referrer", "no-referrer-when-downgrade", "origin", "origin-when-cross-origin", "same-origin", "strict-origin", "strict-origin-when-cross-origin", "unsafe-url"])) {
            return;
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
        $this->rentryAfter = date("D, d M Y H:i:s T", $unixTime);
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
     * @param string $url
     */
    public function setSourceMap(string $url): void
    {
        $this->sourceMap = $url;
    }
    
    /**
     * Sets value of HTTP header: Strict-Transport-Security
     * 
     * @param bool $includeSubdomains
     * @param bool $preload
     */
    public function setStrictTransportSecurity(bool $includeSubdomains = false, bool $preload = false): void
    {
        $this->strictTransportSecurity = "max-age: 31536000".($includeSubdomains?"; includeSubdomains":"").($preload?"; preload":"");
    }
    
    /**
     * Sets value of HTTP header: Timing-Allow-Origin
     * 
     * @param string $url
     */
    public function addTimingAllowOrigin(string $url = "*"): void
    {
        $this->timingAllowOrigin[] = $url;
    }
    
    /**
     * Delegates setting value of HTTP header: Tk
     * 
     * @param string $status
     */
    public function setTk(string $status): void
    {
        if (!in_array($status, ["!", "?", "G", "N", "T", "C", "P", "D", "U"])) {
            return;
        }
        $this->tk = $status;
    }
    
    /**
     * Sets one of values of HTTP header: Trailer
     * 
     * @param string $headerName
     */
    public function addTrailer(string $headerName = "*"): void
    {
        $this->trailer[] = $headerName;
    }
    
    /**
     * Sets one of values of HTTP header: Transfer-Encoding
     * 
     * @param string $contentEncoding
     */
    public function addTransferEncoding(string $contentEncoding): void
    {
        if (!in_array($contentEncoding, ["gzip", "compress", "deflate", "identity", "chunked"])) {
            return;
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
     * @param string $type
     * @param string $realm
     * @return WwwAuthenticate
     */
    public function setWWWAuthenticate(string $type, string $realm=""): void
    {
        if (!in_array($type, ["Basic","Bearer","Digest","HOBA","Mutual","Negotiate","OAuth","SCRAM-SHA-1","SCRAM-SHA-256","vapid"])) {
            return;
        }
        $authenticate = new WwwAuthenticate($type, $realm);
        $this->WWWAuthenticate = $authenticate;
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
        $this->xDNSPrefetchControl = ($value?"on":"off");
    }
    
    /**
     * Sets one of values of HTTP header: X-Frame-Options
     * 
     * @param string $option
     */
    public function setFrameOptions(string $option): void
    {
        if (!in_array($option, ["deny", "same-origin"])) {
            return;
        }
        $this->xFrameOptions = $option;
    }
    
    /**
     * Gets all response headers as key-value pairs.
     * 
     * @return string[string]
     */
    public function toArray(): array
    {
        $response = [];
        if ($this->acceptPatch) {
            $response["Accept-Patch"] = implode(", ", $this->acceptPatch);
        }
        if ($this->acceptRanges) {
            $response["Accept-Ranges"] = $this->acceptRanges;
        }
        if ($this->allow) {
            $response["Accept-Patch"] = implode(", ", $this->allow);
        }
        if ($this->cacheControl) {
            $response["Cache-Control"] = $this->cacheControl->toString();
        }
        if ($this->clearSiteData) {
            $response["Clear-Site-Data"] = '"'.implode('", "', $this->clearSiteData).'"';
        }
        if ($this->contentDisposition) {
            $response["Content-Disposition"] = $this->contentDisposition->toString();
        }
        if ($this->contentEncoding) {
            $response["Content-Encoding"] = implode(", ", $this->contentEncoding);
        }
        if ($this->contentLanguage) {
            $response["Content-Language"] = implode(", ", $this->contentLanguage);
        }
        if ($this->contentLength) {
            $response["Content-Length"] = $this->contentLength;
        }
        if ($this->contentLocation) {
            $response["Content-Location"] = $this->contentLocation;
        }
        if ($this->contentRange) {
            $response["Content-Range"] = $this->contentRange;
        }
        if ($this->contentType) {
            $response["Content-Type"] = $this->contentType;
        }
        if ($this->crossOriginResourcePolicy) {
            $response["Cross-Origin-Resource-Policy"] = $this->crossOriginResourcePolicy;
        }
        if ($this->digest) {
            $response["Digest"] = implode(", ", $this->digest);
        }
        if ($this->etag) {
            $response["ETag"] = $this->etag;
        }
        if ($this->etag) {
            $response["Expires"] = $this->expires;
        }
        if ($this->lastModified) {
            $response["Last-Modified"] = $this->lastModified;
        }
        if ($this->location) {
            $response["Location"] = $this->location;
        }
        if ($this->referrerPolicy) {
            $response["Referrer-Policy"] = $this->referrerPolicy;
        }
        if ($this->rentryAfter) {
            $response["Rentry-After"] = $this->rentryAfter;
        }
        if ($this->sourceMap) {
            $response["Source-Map"] = $this->sourceMap;
        }
        if ($this->strictTransportSecurity) {
            $response["Strict-Transport-Security"] = $this->strictTransportSecurity;
        }
        if ($this->referrerPolicy) {
            $response["Referrer-Policy"] = $this->referrerPolicy;
        }
        if ($this->timingAllowOrigin) {
            $response["Timing-Allow-Origin"] = implode(", ", $this->timingAllowOrigin);
        }
        if ($this->tk) {
            $response["Tk"] = $this->tk;
        }
        if ($this->trailer) {
            $response["Trailer"] = implode(", ", $this->trailer);
        }
        if ($this->transferEncoding) {
            $response["Transfer-Encoding"] = implode(", ", $this->transferEncoding);
        }
        if ($this->vary) {
            $response["Vary"] = implode(", ", $this->vary);
        }
        if ($this->WWWAuthenticate) {
            $response["WWWAuthenticate"] = $this->WWWAuthenticate->toString();
        }
        if ($this->xContentTypeOptions) {
            $response["X-Content-Type-Options"] = $this->xContentTypeOptions;
        }
        if ($this->xDNSPrefetchControl) {
            $response["X-DNS-Prefetch-Control"] = $this->xDNSPrefetchControl;
        }
        if ($this->xFrameOptions) {
            $response["X-Frame-Options"] = $this->xFrameOptions;
        }
        return $response;
    }
}

