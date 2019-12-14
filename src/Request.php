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
    private $accept = [];
    private $acceptCharset = [];
    private $acceptEncoding = [];
    private $acceptLanguage = [];
    private $acceptedTransferEncodings = [];
    private $authorization;
    private $cacheControl;
    private $doNotTrack = false;
    private $date;
    private $digestEncryptions = [];
    private $expectContinue = false;
    private $originalIP;
    private $originalProxy;
    private $originalHostName;
    private $originalProtocol;
    private $email;
    private $hostName;
    private $ifMatch;
    private $ifModifiedSince;
    private $ifNoneMatch;
    private $ifUnmodifiedSince;
    private $ifRangeDate;
    private $ifRangeEtag;
    private $range;
    private $referrer;
    private $saveData = false;
    private $userAgent;
    private $customHeaders = [];
    
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
                case "Authorization":
                    $this->authorization = new Authorization($value);
                    break;
                case "Cache-Control":
                    $this->cacheControl = new CacheControl($value);
                    break;
                case "Connection":
                    // this header is handled by web server directly
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
                    preg_match_all("/(gzip|compress|deflate|br|identity)/", $value, $matches);
                    $this->acceptedTransferEncodings = (!empty($matches[1])?$matches[1]:[]);
                    break;
                case "Upgrade-Insecure-Requests":
                    // this header is handled by web server directly
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
     * @return string Value of valid etag or null if etag is empty / multiple / weak.
     */
    private function _validateEtag(string $headerValue): string
    {
        $etag = trim(str_replace('"', '', $headerValue));
        $etag = str_replace(array("-gzip","-gunzip"), "", $etag); // hardcoding: remove gzip & gunzip added to each etag by apache2
        if (!$etag || stripos($etag, "w/") !== false || stripos($etag, ",") !== false) {
            return null;
        }
        return $etag;
    }
    
    /**
     * Validates numeric header value
     *
     * @param string $headerValue
     * @return integer Value of valid number or null if value is not numeric.
     */
    private function _validateNumber(string $headerValue): int
    {
        if (!is_numeric($headerValue)) {
            return null;
        }
        $output = (integer) $headerValue;
        // overflow protection
        if ($output< 0) {
            $output= -1;
        }
        if ($output> 2147483648) {
            $output= 2147483648;
        }
        return $output;
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
        preg_match_all("/([a-zA-Z0-9\:\-]+)/", $value, $matches);
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
        return $this->acceptedMimeTypes;
    }
    
    /**
     * Gets accepted charsets from HTTP header: Accept-Charset
     * 
     * @return string[]
     */
    public function getAcceptCharset(): array
    {
        return $this->acceptedCharsets;
    }
    
    /**
     * Gets accepted encodings from HTTP header: Accept-Encoding
     *
     * @return string[]
     */
    public function getAcceptedEncoding(): array
    {
        return $this->acceptedEncodings;
    }
    
    /**
     * Gets accepted languages from HTTP header: Accept-Language
     *
     * @return string[]
     */
    public function getAcceptedLanguage(): array
    {
        return $this->acceptedLanguages;
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
     * @return Authorization
     */
    public function getAuthorization(): Authorization
    {
        return $this->authorization;
    }
    
    /**
     * Gets value of HTTP header: Cache-Control
     *
     * @return CacheControl
     */
    public function getCacheControl(): CacheControl
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
     * @return int
     */
    public function getDate(): int
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
        return $this->isLowBandwidth();
    }
    
    /**
     * Gets value of IP from HTTP headers: Forwarded, X-Forwarded-For
     *
     * @return string
     */
    public function getForwardedIP(): string
    {
        return $this->originalIP;
    }
    
    /**
     * Gets value of proxy from HTTP headers: Forwarded, X-Forwarded-For
     *
     * @return string
     */
    public function getForwardedProxy(): string
    {
        return $this->originalProxy;
    }
    
    /**
     * Gets value of host from HTTP header: Forwarded
     * 
     * @return string
     */
    public function getForwardedHost(): string
    {
        return $this->originalHostName;
    }
    
    /**
     * Gets value of protocol from HTTP header: Forwarded
     *
     * @return string
     */
    public function getForwardedProtocol(): string
    {
        return $this->originalProtocol;
    }
    
    /**
     * Gets client email from HTTP header: From
     *
     * @return string
     */
    public function getFrom(): string
    {
        return $this->email;
    }
    
    /**
     * Gets value of HTTP header: Host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->hostName;
    }
    
    /**
     * Gets value of date from HTTP header: If-Range
     *
     * @return int
     */
    public function getIfRangeDate(): int
    {
        return $this->ifRangeDate;
    }
    
    /**
     * Gets value of etag from HTTP header: If-Range
     *
     * @return string
     */
    public function getIfRangeEtag(): string
    {
        return $this->ifRangeEtag;
    }
    
    /**
     * Gets value of HTTP header: Range
     *
     * @return Range
     */
    public function getRange(): Range
    {
        return $this->range;
    }
    
    /**
     * Gets source URL from HTTP header: Referer
     *
     * @return string
     */
    public function getReferer(): string
    {
        return $this->referrer;
    }
    
    /**
     * Gets value of HTTP header: UserAgent
     *
     * @return string
     */
    public function getUserAgent(): string
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
     * @return string
     */
    public function getIfMatch(): string
    {
        return $this->ifMatch;
    }
    
    /**
     * Gets value of HTTP header: If-Modified-Since
     *
     * @return int
     */
    public function getIfModifiedSince(): int
    {
        return $this->ifModifiedSince;
    }
    
    /**
     * Gets value of HTTP header: If-None-Match
     *
     * @return string
     */
    public function getIfNoneMatch(): string
    {
        return $this->ifNoneMatch;
    }
    
    /**
     * Gets value of HTTP header: If-Unmodified-Since
     *
     * @return int
     */
    public function getIfUnmodifiedSince(): int
    {
        return $this->ifModifiedSince;
    }
    
    /**
     * Gets headers not present in IETF specifications
     *
     * @return array
     */
    public function getCustomHeaders(): array
    {
        return $this->customHeaders;
    }
}
