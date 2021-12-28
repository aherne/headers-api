<?php
namespace Lucinda\Headers;

use Lucinda\Headers\Request\Authorization;
use Lucinda\Headers\Request\Range;
use Lucinda\Headers\Request\CacheControl;

/**
 * Encapsulates reading HTTP request headers received from client according to specifications EXCEPT those already handled
 * by web server (Connection, Keep-Alive)
 */
class Request
{
    private array $accept = [];
    private array $acceptCharset = [];
    private array $acceptEncoding = [];
    private array $acceptLanguage = [];
    private array $acceptedTransferEncodings = [];
    private ?Authorization $authorization = null;
    private ?CacheControl $cacheControl = null;
    private bool $doNotTrack = false;
    private ?int $date = null;
    private array $digestEncryptions = [];
    private bool $expectContinue = false;
    private ?string $originalIP = null;
    private ?string $originalProxy = null;
    private ?string $originalHostName = null;
    private ?string $originalProtocol = null;
    private ?string $email = null;
    private ?string $hostName = null;
    private ?string $ifMatch = null;
    private ?int $ifModifiedSince = null;
    private ?string $ifNoneMatch = null;
    private ?int $ifUnmodifiedSince = null;
    private ?int $ifRangeDate = null;
    private ?string $ifRangeEtag = null;
    private ?Range $range = null;
    private ?string $referrer = null;
    private bool $saveData = false;
    private ?string $userAgent = null;
    private array $accessControlRequestHeaders = [];
    private ?string $accessControlRequestMethod = null;
    private ?string $origin = null;
    private array $customHeaders = [];
    
    /**
     * Reads headers received
     *
     * @param string[string] $headers
     */
    public function __construct(array $headers)
    {
        foreach ($headers as $name=>$value) {
            $matches = [];
            switch ($name) {
                case "Accept":
                    preg_match_all("/((application|audio|example|font|image|model|text|video)\/[a-zA-Z0-9\+\*\-]+)/", $value, $matches);
                    $this->accept = (!empty($matches[1])?$matches[1]:[]);
                    break;
                case "Accept-Charset":
                    preg_match_all("/([a-zA-Z0-9\+\*\-]+)(;q\=[0-9\.]+)?/", $value, $matches);
                    $this->acceptCharset = (!empty($matches[1])?$matches[1]:[]);
                    break;
                case "Accept-Encoding":
                    preg_match_all("/(gzip|compress|deflate|br|identity)/", $value, $matches);
                    $this->acceptEncoding = (!empty($matches[1])?$matches[1]:[]);
                    break;
                case "Accept-Language":
                    preg_match_all("/([a-z]{2}(\-[A-Z]{2})?)(;q\=[0-9\.]+)?/", $value, $matches);
                    $this->acceptLanguage = (!empty($matches[1])?$matches[1]:[]);
                    break;
                case "Access-Control-Request-Headers":
                    $matches = [];
                    preg_match_all("/\s*([^,]+)\s*/", $value, $matches);
                    $this->accessControlRequestHeaders = (!empty($matches[1])?$matches[1]:[]);
                    break;
                case "Access-Control-Request-Method":
                    $value = trim($value);
                    if (in_array($value, ["GET","HEAD","POST","PUT","DELETE","CONNECT","OPTIONS","TRACE","PATCH"])) {
                        $this->accessControlRequestMethod = $value;
                    }
                    break;
                case "Authorization":
                    $this->authorization = new Authorization($value);
                    break;
                case "Cache-Control":
                    $this->cacheControl = new CacheControl($value);
                    break;
                case "Connection":
                    // this header is handled by web server directly
                    break;
                case "Cookie":
                    // this header is handled by php directly
                    break;
                case "DNT":
                    $this->doNotTrack = (bool) $value;
                    break;
                case "Date":
                    $date = strtotime($value);
                    $this->date = ($date!==false?$date:null);
                    break;
                case "Expect":
                    $this->expectContinue = ($value=="100-continue");
                    break;
                case "Forwarded":
                    $this->setForwarded($value);
                    break;
                case "From":
                    $this->email = (filter_var($value, FILTER_VALIDATE_EMAIL)?$value:null);
                    break;
                case "Host":
                    $this->hostName = trim($value);
                    break;
                case "If-Match":
                    $this->ifMatch = $this->_validateEtag($value);
                    break;
                case "If-Modified-Since":
                    $date = strtotime($value);
                    $this->ifModifiedSince = ($date!==false?$date:null);
                    break;
                case "If-None-Match":
                    $this->ifNoneMatch = $this->_validateEtag($value);
                    break;
                case "If-Unmodified-Since":
                    $date = strtotime($value);
                    $this->ifUnmodifiedSince = ($date!==false?$date:null);
                    break;
                case "If-Range":
                    $date = strtotime($value);
                    if ($date!==false) {
                        $this->ifRangeDate = $date;
                    } else {
                        $this->ifRangeEtag = $this->_validateEtag($value);
                    }
                    break;
                case "Keep-Alive":
                    // this header is handled by web server directly
                    break;
                case "Origin":
                    $this->origin = $value;
                    break;
                case "Pragma":
                    if ($this->cacheControl==null && $value=="no-cache") {
                        $this->cacheControl = new CacheControl("no-cache");
                    }
                    break;
                case "Range":
                    $this->range = new Range($value);
                    break;
                case "Referer":
                    $this->referrer = trim($value);
                    break;
                case "Save-Data":
                    $this->saveData = ($value=="on");
                    break;
                case "TE":
                    preg_match_all("/(gzip|compress|deflate|trailers)/", $value, $matches);
                    $this->acceptedTransferEncodings = (!empty($matches[1])?$matches[1]:[]);
                    break;
                case "Upgrade-Insecure-Requests":
                    // this header is handled by web server directly
                    break;
                case "User-Agent":
                    $this->userAgent = trim($value);
                    break;
                case "Want-Digest":
                    preg_match_all("/([a-zA-Z\-0-9]+)(;q\=[0-9\.]+)?/", $value, $matches);
                    $this->digestEncryptions = (!empty($matches[1])?$matches[1]:[]);
                    break;
                case "X-Forwarded-For":
                    $this->setForwardedFor($value);
                    break;
                case "X-Forwarded-Host":
                    $this->originalHostName = $value;
                    break;
                case "X-Forwarded-Proto":
                    $this->originalProtocol = $value;
                    break;
                default:
                    $this->customHeaders[$name] = $value;
                    break;
            }
        }
    }
    
    /**
     * Validates etag if it's strong and single.
     *
     * @param string $headerValue
     * @return ?string Value of valid etag or null if etag is empty / multiple / weak.
     */
    private function _validateEtag(string $headerValue): ?string
    {
        $etag = trim(str_ireplace(array("-gzip", "-gunzip", "w/", "\""), "", $headerValue));
        if (!$etag || stripos($etag, ",") !== false) {
            return null;
        }
        return $etag;
    }
    
    /**
     * Reads value of HTTP header: Forwarded
     *
     * @param string $value
     */
    private function setForwarded(string $value): void
    {
        $matches = [];
        preg_match_all("/([a-zA-Z]+)\=([a-zA-Z0-9\.\:\-]+)/", $value, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $i=>$identifier) {
                $identifier = strtolower($identifier);
                switch ($identifier) {
                    case "by":
                        $this->originalProxy = $matches[2][$i];
                        break;
                    case "for":
                        $this->originalIP = $matches[2][$i];
                        break;
                    case "host":
                        $this->originalHostName = $matches[2][$i];
                        break;
                    case "proto":
                        $this->originalProtocol = $matches[2][$i];
                        break;
                }
            }
        }
    }
    
    /**
     * Reads value of HTTP header: X-Forwarded-For
     *
     * @param string $value
     */
    private function setForwardedFor(string $value): void
    {
        $matches = [];
        preg_match_all("/([a-zA-Z0-9\:\-\.]+)/", $value, $matches);
        if (!empty($matches[1])) {
            $this->originalIP = $matches[1][0];
            if (!empty($matches[1][1])) {
                $this->originalProxy = $matches[1][1];
            }
        }
    }
    
    /**
     * Gets accepted mime types from HTTP header: Accept
     *
     * @return string[]
     */
    public function getAccept(): array
    {
        return $this->accept;
    }
    
    /**
     * Gets accepted charsets from HTTP header: Accept-Charset
     *
     * @return string[]
     */
    public function getAcceptCharset(): array
    {
        return $this->acceptCharset;
    }
    
    /**
     * Gets accepted encodings from HTTP header: Accept-Encoding
     *
     * @return string[]
     */
    public function getAcceptEncoding(): array
    {
        return $this->acceptEncoding;
    }
    
    /**
     * Gets accepted languages from HTTP header: Accept-Language
     *
     * @return string[]
     */
    public function getAcceptLanguage(): array
    {
        return $this->acceptLanguage;
    }
    
    /**
     * Gets accepted transfer encodings from HTTP header: TE
     *
     * @return string[]
     */
    public function getTE(): array
    {
        return $this->acceptedTransferEncodings;
    }
    
    /**
     * Gets value of HTTP header: Authorization
     *
     * @return Authorization|null
     */
    public function getAuthorization(): ?Authorization
    {
        return $this->authorization;
    }
    
    /**
     * Gets value of HTTP header: Cache-Control
     *
     * @return CacheControl|null
     */
    public function getCacheControl(): ?CacheControl
    {
        return $this->cacheControl;
    }
    
    /**
     * Checks existence of HTTP header: DNT
     *
     * @return bool
     */
    public function getDNT(): bool
    {
        return $this->doNotTrack;
    }
    
    /**
     * Gets value of HTTP header: Date
     *
     * @return int|null
     */
    public function getDate(): ?int
    {
        return $this->date;
    }
    
    /**
     * Checks existence of HTTP header: Expect
     *
     * @return bool
     */
    public function getExpect(): bool
    {
        return $this->expectContinue;
    }
    
    /**
     * Checks existence of HTTP header: Save-Data
     *
     * @return bool
     */
    public function getSaveData(): bool
    {
        return $this->saveData;
    }
    
    /**
     * Gets value of IP from HTTP headers: Forwarded, X-Forwarded-For
     *
     * @return string|null
     */
    public function getForwardedIP(): ?string
    {
        return $this->originalIP;
    }
    
    /**
     * Gets value of proxy from HTTP headers: Forwarded, X-Forwarded-For
     *
     * @return string|null
     */
    public function getForwardedProxy(): ?string
    {
        return $this->originalProxy;
    }
    
    /**
     * Gets value of host from HTTP header: Forwarded
     *
     * @return string|null
     */
    public function getForwardedHost(): ?string
    {
        return $this->originalHostName;
    }
    
    /**
     * Gets value of protocol from HTTP header: Forwarded
     *
     * @return string|null
     */
    public function getForwardedProtocol(): ?string
    {
        return $this->originalProtocol;
    }
    
    /**
     * Gets client email from HTTP header: From
     *
     * @return string|null
     */
    public function getFrom(): ?string
    {
        return $this->email;
    }
    
    /**
     * Gets value of HTTP header: Host
     *
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->hostName;
    }
    
    /**
     * Gets value of date from HTTP header: If-Range
     *
     * @return int|null
     */
    public function getIfRangeDate(): ?int
    {
        return $this->ifRangeDate;
    }
    
    /**
     * Gets value of etag from HTTP header: If-Range
     *
     * @return string|null
     */
    public function getIfRangeEtag(): ?string
    {
        return $this->ifRangeEtag;
    }
    
    /**
     * Gets value of HTTP header: Range
     *
     * @return Range|null
     */
    public function getRange(): ?Range
    {
        return $this->range;
    }
    
    /**
     * Gets source URL from HTTP header: Referer
     *
     * @return string|null
     */
    public function getReferer(): ?string
    {
        return $this->referrer;
    }
    
    /**
     * Gets value of HTTP header: UserAgent
     *
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
    
    /**
     * Gets digest encryptions from HTTP header: Want-Digest
     *
     * @return string[]
     */
    public function getWantDigest(): array
    {
        return $this->digestEncryptions;
    }
    
    /**
     * Gets value of HTTP header: If-Match
     *
     * @return string|null
     */
    public function getIfMatch(): ?string
    {
        return $this->ifMatch;
    }
    
    /**
     * Gets value of HTTP header: If-Modified-Since
     *
     * @return int|null
     */
    public function getIfModifiedSince(): ?int
    {
        return $this->ifModifiedSince;
    }
    
    /**
     * Gets value of HTTP header: If-None-Match
     *
     * @return string|null
     */
    public function getIfNoneMatch(): ?string
    {
        return $this->ifNoneMatch;
    }
    
    /**
     * Gets value of HTTP header: If-Unmodified-Since
     *
     * @return int|null
     */
    public function getIfUnmodifiedSince(): ?int
    {
        return $this->ifUnmodifiedSince;
    }
    
    /**
     * Gets value of HTTP header: Access-Control-Request-Headers
     *
     * @return array
     */
    public function getAccessControlRequestHeaders(): array
    {
        return $this->accessControlRequestHeaders;
    }
    
    /**
     * Gets value of HTTP header: Access-Control-Request-Method
     *
     * @return string|null
     */
    public function getAccessControlRequestMethod(): ?string
    {
        return $this->accessControlRequestMethod;
    }
    
    /**
     * Gets value of HTTP header: Origin
     *
     * @return string|null
     */
    public function getOrigin(): ?string
    {
        return $this->origin;
    }
    
    /**
     * Gets headers received that were not present in IETF specifications
     *
     * @return array
     */
    public function getCustomHeaders(): array
    {
        return $this->customHeaders;
    }
}
