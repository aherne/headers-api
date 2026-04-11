<?php

namespace Test\Lucinda\Headers;

use Lucinda\Headers\Request;
use Lucinda\UnitTest\Validator\Arrays;
use Lucinda\UnitTest\Validator\Booleans;
use Lucinda\UnitTest\Validator\Objects;
use Lucinda\UnitTest\Validator\Strings;

class RequestTest
{
    public function getAccept()
    {
        $request = new Request(["Accept"=>"text/html, application/xml;q=0.9, */*;q=0.8"]);
        return (new Arrays($request->getAccept()))->assertEquals(["text/html", "application/xml"]);
    }

    public function getAcceptCharset()
    {
        $request = new Request(["Accept-Charset"=>"utf-8, iso-8859-1;q=0.5"]);
        return (new Arrays($request->getAcceptCharset()))->assertEquals(["utf-8", "iso-8859-1"]);
    }

    public function getAcceptEncoding()
    {
        $request = new Request(["Accept-Encoding"=>"deflate, gzip;q=1.0, *;q=0.5"]);
        return (new Arrays($request->getAcceptEncoding()))->assertEquals(["deflate", "gzip"]);
    }

    public function getAcceptLanguage()
    {
        $request = new Request(["Accept-Language"=>"fr-CH, fr;q=0.9, en;q=0.8, *;q=0.5"]);
        return (new Arrays($request->getAcceptLanguage()))->assertEquals(["fr-CH", "fr", "en"]);
    }

    public function getTE()
    {
        $request = new Request(["TE"=>"trailers, deflate;q=0.5"]);
        return (new Arrays($request->getTE()))->assertEquals(["trailers", "deflate"]);
    }

    public function getAuthorization(): array
    {
        $request = new Request(["Authorization"=>"Basic test"]);
        $authorization = $request->getAuthorization();
        return [
            (new Objects($authorization))->assertInstanceOf(\Lucinda\Headers\Request\Authorization::class),
            (new Strings($authorization->getCredentials()))->assertEquals("test")
        ];
    }

    public function getCacheControl(): array
    {
        $request = new Request(["Cache-Control"=>"no-cache, no-store"]);
        $cacheControl = $request->getCacheControl();
        return [
            (new Objects($cacheControl))->assertInstanceOf(\Lucinda\Headers\Request\CacheControl::class),
            (new Booleans($cacheControl->isNoCache()))->assertTrue(),
            (new Booleans($cacheControl->isNoStore()))->assertTrue()
        ];
    }

    public function getDNT()
    {
        $request = new Request(["DNT"=>"1"]);
        return (new Booleans($request->getDNT()))->assertTrue();
    }

    public function getDate()
    {
        $request = new Request(["Date"=>"Wed, 21 Oct 2015 07:28:00 GMT"]);
        return (new Strings(gmdate("Y-m-d H:i:s", (int) $request->getDate())))->assertEquals("2015-10-21 07:28:00");
    }

    public function getExpect()
    {
        $request = new Request(["Expect"=>"100-continue"]);
        return (new Booleans($request->getExpect()))->assertTrue();
    }

    public function getSaveData()
    {
        $request = new Request(["Save-Data"=>"on"]);
        return (new Booleans($request->getSaveData()))->assertTrue();
    }

    public function getForwardedIP()
    {
        $request = new Request(["X-Forwarded-For"=>"2001:db8:85a3:8d3:1319:8a2e:370:7348"]);
        return (new Strings((string) $request->getForwardedIP()))->assertEquals("2001:db8:85a3:8d3:1319:8a2e:370:7348");
    }

    public function getForwardedProxy()
    {
        $request = new Request(["X-Forwarded-For"=>"203.0.113.195, 70.41.3.18"]);
        return (new Strings((string) $request->getForwardedProxy()))->assertEquals("70.41.3.18");
    }

    public function getForwardedHost()
    {
        $request = new Request(["X-Forwarded-Host"=>"id42.example-cdn.com"]);
        return (new Strings((string) $request->getForwardedHost()))->assertEquals("id42.example-cdn.com");
    }

    public function getForwardedProtocol()
    {
        $request = new Request(["X-Forwarded-Proto"=>"https"]);
        return (new Strings((string) $request->getForwardedProtocol()))->assertEquals("https");
    }

    public function getFrom()
    {
        $request = new Request(["From"=>"webmaster@example.org"]);
        return (new Strings((string) $request->getFrom()))->assertEquals("webmaster@example.org");
    }

    public function getHost()
    {
        $request = new Request(["Host"=>"developer.cdn.mozilla.net"]);
        return (new Strings((string) $request->getHost()))->assertEquals("developer.cdn.mozilla.net");
    }

    public function getIfRangeDate()
    {
        $request = new Request(["If-Range"=>"21 Oct 2015 07:28:00 GMT"]);
        return (new Strings(gmdate("Y-m-d H:i:s", (int) $request->getIfRangeDate())))->assertEquals("2015-10-21 07:28:00");
    }

    public function getIfRangeEtag()
    {
        $request = new Request(["If-Range"=>"\"abc\""]);
        return (new Strings((string) $request->getIfRangeEtag()))->assertEquals("abc");
    }

    public function getRange(): array
    {
        $request = new Request(["Range"=>"bytes=200-1000, 19000-"]);
        $conditions = $request->getRange()->getConditions();
        return [
            (new Arrays($conditions))->assertSize(2),
            (new Strings($request->getRange()->getUnit()))->assertEquals("bytes"),
            (new Strings((string) $conditions[0]->getStart()))->assertEquals("200"),
            (new Strings((string) $conditions[0]->getEnd()))->assertEquals("1000"),
            (new Strings((string) $conditions[1]->getStart()))->assertEquals("19000"),
            (new Booleans($conditions[1]->getEnd() === null))->assertTrue()
        ];
    }

    public function getReferer()
    {
        $request = new Request(["Referer"=>"https://developer.mozilla.org/en-US/docs/Web/JavaScript"]);
        return (new Strings((string) $request->getReferer()))
            ->assertEquals("https://developer.mozilla.org/en-US/docs/Web/JavaScript");
    }

    public function getUserAgent()
    {
        $request = new Request(["User-Agent"=>"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0"]);
        return (new Strings((string) $request->getUserAgent()))
            ->assertEquals("Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0");
    }

    public function getWantDigest()
    {
        $request = new Request(["Want-Digest"=>"SHA-512, md5;q=0"]);
        return (new Arrays($request->getWantDigest()))->assertEquals(["SHA-512", "md5"]);
    }

    public function getIfMatch()
    {
        $request = new Request(["If-Match"=>"\"abc\""]);
        return (new Strings((string) $request->getIfMatch()))->assertEquals("abc");
    }

    public function getIfModifiedSince()
    {
        $request = new Request(["If-Modified-Since"=>"21 Oct 2015 07:28:00 GMT"]);
        return (new Strings(gmdate("Y-m-d H:i:s", (int) $request->getIfModifiedSince())))->assertEquals("2015-10-21 07:28:00");
    }

    public function getIfNoneMatch()
    {
        $request = new Request(["If-None-Match"=>"\"abc\""]);
        return (new Strings((string) $request->getIfNoneMatch()))->assertEquals("abc");
    }

    public function getIfUnmodifiedSince()
    {
        $request = new Request(["If-Unmodified-Since"=>"21 Oct 2015 07:28:00 GMT"]);
        return (new Strings(gmdate("Y-m-d H:i:s", (int) $request->getIfUnmodifiedSince())))->assertEquals("2015-10-21 07:28:00");
    }

    public function getAccessControlRequestHeaders()
    {
        $request = new Request(["Access-Control-Request-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests"]);
        return (new Arrays($request->getAccessControlRequestHeaders()))
            ->assertEquals(["X-Custom-Header", "Upgrade-Insecure-Requests"]);
    }

    public function getAccessControlRequestMethod()
    {
        $request = new Request(["Access-Control-Request-Method"=>"POST"]);
        return (new Strings((string) $request->getAccessControlRequestMethod()))->assertEquals("POST");
    }

    public function getOrigin()
    {
        $request = new Request(["Origin"=>"https://developer.mozilla.org"]);
        return (new Strings((string) $request->getOrigin()))->assertEquals("https://developer.mozilla.org");
    }

    public function getCustomHeaders(): array
    {
        $request = new Request(["Ping"=>"pong"]);
        $customHeaders = $request->getCustomHeaders();
        return [
            (new Arrays($customHeaders))->assertContainsKey("Ping"),
            (new Strings($customHeaders["Ping"]))->assertEquals("pong")
        ];
    }
}
