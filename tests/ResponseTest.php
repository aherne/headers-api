<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\Response;
use Lucinda\UnitTest\Validator\Arrays;

class ResponseTest
{
    public function addAcceptPatch()
    {
        $response = new Response();
        $response->addAcceptPatch("application/example");
        $response->addAcceptPatch("text/example", "utf-8");
        return (new Arrays($response->toArray()))
            ->assertEquals(["Accept-Patch"=>"application/example, text/example;charset=utf-8"]);
    }

    public function setAcceptRanges()
    {
        $response = new Response();
        $response->setAcceptRanges(true);
        return (new Arrays($response->toArray()))->assertEquals(["Accept-Ranges"=>"bytes"]);
    }

    public function addAllow()
    {
        $response = new Response();
        $response->addAllow("GET");
        $response->addAllow("POST");
        return (new Arrays($response->toArray()))->assertEquals(["Allow"=>"GET, POST"]);
    }

    public function setCacheControl()
    {
        $response = new Response();
        $cacheControl = $response->setCacheControl();
        $cacheControl->setPublic();
        $cacheControl->setMaxAge(10);
        return (new Arrays($response->toArray()))->assertEquals(["Cache-Control"=>"public, max-age=10"]);
    }

    public function addClearSiteData()
    {
        $response = new Response();
        $response->addClearSiteData("cache");
        $response->addClearSiteData("cookies");
        return (new Arrays($response->toArray()))->assertEquals(["Clear-Site-Data"=>'"cache", "cookies"']);
    }

    public function setContentDisposition()
    {
        $response = new Response();
        $response->setContentDisposition("attachment")->setFileName("test.jpg", true);
        return (new Arrays($response->toArray()))
            ->assertEquals(["Content-Disposition"=>'attachment; filename*="test.jpg"']);
    }

    public function addContentEncoding()
    {
        $response = new Response();
        $response->addContentEncoding("gzip");
        $response->addContentEncoding("compress");
        return (new Arrays($response->toArray()))->assertEquals(["Content-Encoding"=>"gzip, compress"]);
    }

    public function addContentLanguage()
    {
        $response = new Response();
        $response->addContentLanguage("en-US");
        $response->addContentLanguage("de");
        return (new Arrays($response->toArray()))->assertEquals(["Content-Language"=>"en-US, de"]);
    }

    public function setContentLength()
    {
        $response = new Response();
        $response->setContentLength(20);
        return (new Arrays($response->toArray()))->assertEquals(["Content-Length"=>20]);
    }

    public function setContentLocation()
    {
        $response = new Response();
        $response->setContentLocation("https://www.google.com");
        return (new Arrays($response->toArray()))->assertEquals(["Content-Location"=>"https://www.google.com"]);
    }

    public function setContentRange(): array
    {
        $results = [];
        $response = new Response();
        $response->setContentRange("bytes", null, null, 10);
        $results[] = (new Arrays($response->toArray()))->assertEquals(["Content-Range"=>"bytes */10"], "only size given");
        $response->setContentRange("bytes", 0, 200, 500);
        $results[] = (new Arrays($response->toArray()))->assertEquals(["Content-Range"=>"bytes 0-200/500"], "range can start at zero");
        $response->setContentRange("bytes", 100, 200);
        $results[] = (new Arrays($response->toArray()))->assertEquals(["Content-Range"=>"bytes 100-200/*"], "only start and end given");
        $response->setContentRange("bytes", 100, 200, 500);
        $results[] = (new Arrays($response->toArray()))->assertEquals(["Content-Range"=>"bytes 100-200/500"], "all arguments given");
        return $results;
    }

    public function setContentType()
    {
        $response = new Response();
        $response->setContentType("text/html", "UTF-8");
        return (new Arrays($response->toArray()))->assertEquals(["Content-Type"=>"text/html; charset=UTF-8"]);
    }

    public function setCrossOriginResourcePolicy()
    {
        $response = new Response();
        $response->setCrossOriginResourcePolicy("same-site");
        return (new Arrays($response->toArray()))->assertEquals(["Cross-Origin-Resource-Policy"=>"same-site"]);
    }

    public function addDigest()
    {
        $response = new Response();
        $response->addDigest("SHA-256", "X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE=");
        $response->addDigest("UNIXsum", "30637");
        return (new Arrays($response->toArray()))
            ->assertEquals(["Digest"=>"SHA-256=X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE=, UNIXsum=30637"]);
    }

    public function setEtag()
    {
        $response = new Response();
        $response->setEtag("abc");
        return (new Arrays($response->toArray()))->assertEquals(["ETag"=>'"abc"']);
    }

    public function setExpirationTime()
    {
        $time = 1700000010;
        $response = new Response();
        $response->setExpirationTime($time);
        return (new Arrays($response->toArray()))
            ->assertEquals(["Expires"=>gmdate("D, d M Y H:i:s T", $time)]);
    }

    public function setLastModifiedTime()
    {
        $time = 1699999990;
        $response = new Response();
        $response->setLastModifiedTime($time);
        return (new Arrays($response->toArray()))
            ->assertEquals(["Last-Modified"=>gmdate("D, d M Y H:i:s T", $time)]);
    }

    public function setLocation()
    {
        $response = new Response();
        $response->setLocation("https://www.google.com");
        return (new Arrays($response->toArray()))->assertEquals(["Location"=>"https://www.google.com"]);
    }

    public function setReferrerPolicy()
    {
        $response = new Response();
        $response->setReferrerPolicy("no-referrer");
        return (new Arrays($response->toArray()))->assertEquals(["Referrer-Policy"=>"no-referrer"]);
    }

    public function setRetryAfterDate()
    {
        $time = 1700000010;
        $response = new Response();
        $response->setRetryAfterDate($time);
        return (new Arrays($response->toArray()))
            ->assertEquals(["Retry-After"=>gmdate("D, d M Y H:i:s T", $time)]);
    }

    public function setRetryAfterDelay()
    {
        $response = new Response();
        $response->setRetryAfterDelay(10);
        return (new Arrays($response->toArray()))->assertEquals(["Retry-After"=>10]);
    }

    public function setRentryAfterDate()
    {
        $time = 1700000010;
        $response = new Response();
        $response->setRentryAfterDate($time);
        return (new Arrays($response->toArray()))
            ->assertEquals(["Retry-After"=>gmdate("D, d M Y H:i:s T", $time)]);
    }

    public function setRentryAfterDelay()
    {
        $response = new Response();
        $response->setRentryAfterDelay(10);
        return (new Arrays($response->toArray()))->assertEquals(["Retry-After"=>10]);
    }

    public function setRentryAfterZeroDelay()
    {
        $response = new Response();
        $response->setRentryAfterDelay(0);
        return (new Arrays($response->toArray()))->assertEquals(["Retry-After"=>0]);
    }

    public function setSourceMap()
    {
        $response = new Response();
        $response->setSourceMap("https://www.google.com");
        return (new Arrays($response->toArray()))->assertEquals(["Source-Map"=>"https://www.google.com"]);
    }

    public function setStrictTransportSecurity()
    {
        $response = new Response();
        $response->setStrictTransportSecurity();
        return (new Arrays($response->toArray()))
            ->assertEquals(["Strict-Transport-Security"=>"max-age=".Response\StrictTransportSecurity::MAX_AGE]);
    }

    public function addTimingAllowOrigin()
    {
        $response = new Response();
        $response->addTimingAllowOrigin("https://www.google.com");
        return (new Arrays($response->toArray()))->assertEquals(["Timing-Allow-Origin"=>"https://www.google.com"]);
    }

    public function setTk()
    {
        $response = new Response();
        $response->setTk("G");
        return (new Arrays($response->toArray()))->assertEquals(["Tk"=>"G"]);
    }

    public function setTrailer()
    {
        $trailer = 'Expires

7\r\n 
Mozilla\r\n 
9\r\n 
Developer\r\n 
7\r\n 
Network\r\n 
0\r\n 
Expires: Wed, 21 Oct 2015 07:28:00 GMT\r\n
\r\n';
        $response = new Response();
        $response->setTrailer($trailer);
        return (new Arrays($response->toArray()))->assertEquals(["Trailer"=>$trailer]);
    }

    public function addTransferEncoding()
    {
        $response = new Response();
        $response->addTransferEncoding("gzip");
        $response->addTransferEncoding("identity");
        return (new Arrays($response->toArray()))->assertEquals(["Transfer-Encoding"=>"gzip, identity"]);
    }

    public function addVary()
    {
        $response = new Response();
        $response->addVary("User-Agent");
        $response->addVary("Content-Type");
        return (new Arrays($response->toArray()))->assertEquals(["Vary"=>"User-Agent, Content-Type"]);
    }

    public function setWWWAuthenticate()
    {
        $response = new Response();
        $response->setWWWAuthenticate("Bearer", "lucinda");
        return (new Arrays($response->toArray()))
            ->assertEquals(["WWW-Authenticate"=>'Bearer realm="lucinda"']);
    }

    public function setContentTypeOptions()
    {
        $response = new Response();
        $response->setContentTypeOptions();
        return (new Arrays($response->toArray()))->assertEquals(["X-Content-Type-Options"=>"nosniff"]);
    }

    public function setDNSPrefetchControl()
    {
        $response = new Response();
        $response->setDNSPrefetchControl(true);
        return (new Arrays($response->toArray()))->assertEquals(["X-DNS-Prefetch-Control"=>"on"]);
    }

    public function setFrameOptions()
    {
        $response = new Response();
        $response->setFrameOptions("deny");
        return (new Arrays($response->toArray()))->assertEquals(["X-Frame-Options"=>"deny"]);
    }

    public function setCustomHeader()
    {
        $response = new Response();
        $response->setCustomHeader("Test", "me");
        return (new Arrays($response->toArray()))->assertEquals(["Test"=>"me"]);
    }

    public function setAccessControlAllowCredentials()
    {
        $response = new Response();
        $response->setAccessControlAllowCredentials();
        return (new Arrays($response->toArray()))->assertEquals(["Access-Control-Allow-Credentials"=>"true"]);
    }

    public function addAccessControlAllowHeader()
    {
        $response = new Response();
        $response->addAccessControlAllowHeader("X-Custom-Header");
        $response->addAccessControlAllowHeader("Upgrade-Insecure-Requests");
        return (new Arrays($response->toArray()))
            ->assertEquals(["Access-Control-Allow-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests"]);
    }

    public function addAccessControlAllowMethod()
    {
        $response = new Response();
        $response->addAccessControlAllowMethod("GET");
        $response->addAccessControlAllowMethod("POST");
        return (new Arrays($response->toArray()))->assertEquals(["Access-Control-Allow-Methods"=>"GET, POST"]);
    }

    public function setAccessControlAllowOrigin()
    {
        $response = new Response();
        $response->setAccessControlAllowOrigin("https://www.google.com");
        return (new Arrays($response->toArray()))
            ->assertEquals(["Access-Control-Allow-Origin"=>"https://www.google.com"]);
    }

    public function addAccessControlExposeHeader()
    {
        $response = new Response();
        $response->addAccessControlExposeHeader("X-Custom-Header");
        $response->addAccessControlExposeHeader("Upgrade-Insecure-Requests");
        return (new Arrays($response->toArray()))
            ->assertEquals(["Access-Control-Expose-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests"]);
    }

    public function setAccessControlMaxAge()
    {
        $response = new Response();
        $response->setAccessControlMaxAge(10);
        return (new Arrays($response->toArray()))->assertEquals(["Access-Control-Max-Age"=>10]);
    }

    public function toArray()
    {
        $response = new Response();
        $response->addAccessControlExposeHeader("X-Custom-Header");
        $response->setAccessControlMaxAge(10);
        return (new Arrays($response->toArray()))
            ->assertEquals(["Access-Control-Expose-Headers"=>"X-Custom-Header", "Access-Control-Max-Age"=>10]);
    }
}
