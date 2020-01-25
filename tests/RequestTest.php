<?php
namespace Test\Lucinda\Headers;

use Lucinda\Headers\Request;
use Lucinda\UnitTest\Result;

class RequestTest
{
    public function getAccept()
    {
        $request = new Request(["Accept"=>"text/html, application/xml;q=0.9, */*;q=0.8"]);
        return new Result($request->getAccept()==["text/html", "application/xml"]);
    }
        

    public function getAcceptCharset()
    {
        $request = new Request(["Accept-Charset"=>"utf-8, iso-8859-1;q=0.5"]);
        return new Result($request->getAcceptCharset()==["utf-8", "iso-8859-1"]);
    }
        

    public function getAcceptEncoding()
    {
        $request = new Request(["Accept-Encoding"=>"deflate, gzip;q=1.0, *;q=0.5"]);
        return new Result($request->getAcceptEncoding()==["deflate", "gzip"]);
    }
        

    public function getAcceptLanguage()
    {
        $request = new Request(["Accept-Language"=>"fr-CH, fr;q=0.9, en;q=0.8, *;q=0.5"]);
        return new Result($request->getAcceptLanguage()==["fr-CH", "fr", "en"]);
    }
        

    public function getTE()
    {
        $request = new Request(["TE"=>"trailers, deflate;q=0.5"]);
        return new Result($request->getTE()==["trailers", "deflate"]);
    }
        

    public function getAuthorization()
    {
        $request = new Request(["Authorization"=>"Basic test"]);
        return new Result($request->getAuthorization()->getCredentials()=="test");
    }
        

    public function getCacheControl()
    {
        $request = new Request(["Cache-Control"=>"no-cache, no-store"]);
        return new Result($request->getCacheControl()->isNoCache() && $request->getCacheControl()->isNoStore());
    }
        

    public function getDNT()
    {
        $request = new Request(["DNT"=>"1"]);
        return new Result($request->getDNT());
    }
        

    public function getDate()
    {
        $request = new Request(["Date"=>"Wed, 21 Oct 2015 07:28:00 GMT"]);
        return new Result(gmdate("Y-m-d H:i:s", $request->getDate())=="2015-10-21 07:28:00");
    }
        

    public function getExpect()
    {
        $request = new Request(["Expect"=>"100-continue"]);
        return new Result($request->getExpect());
    }
        

    public function getSaveData()
    {
        $request = new Request(["Save-Data"=>"on"]);
        return new Result($request->getSaveData());
    }
        

    public function getForwardedIP()
    {
        $request = new Request(["X-Forwarded-For"=>"2001:db8:85a3:8d3:1319:8a2e:370:7348"]);
        return new Result($request->getForwardedIP()=="2001:db8:85a3:8d3:1319:8a2e:370:7348");
    }
        

    public function getForwardedProxy()
    {
        $request = new Request(["X-Forwarded-For"=>"203.0.113.195, 70.41.3.18"]);
        return new Result($request->getForwardedProxy()=="70.41.3.18");
    }
        

    public function getForwardedHost()
    {
        $request = new Request(["X-Forwarded-Host"=>"id42.example-cdn.com"]);
        return new Result($request->getForwardedHost()=="id42.example-cdn.com");
    }
        

    public function getForwardedProtocol()
    {
        $request = new Request(["X-Forwarded-Proto"=>"https"]);
        return new Result($request->getForwardedProtocol()=="https");
    }
        

    public function getFrom()
    {
        $request = new Request(["From"=>"webmaster@example.org"]);
        return new Result($request->getFrom()=="webmaster@example.org");
    }
        

    public function getHost()
    {
        $request = new Request(["Host"=>"developer.cdn.mozilla.net"]);
        return new Result($request->getHost()=="developer.cdn.mozilla.net");
    }
        

    public function getIfRangeDate()
    {
        $request = new Request(["If-Range"=>"21 Oct 2015 07:28:00 GMT"]);
        return new Result(gmdate("Y-m-d H:i:s", $request->getIfRangeDate())=="2015-10-21 07:28:00");
    }
        

    public function getIfRangeEtag()
    {
        $request = new Request(["If-Range"=>"\"abc\""]);
        return new Result($request->getIfRangeEtag()=="abc");
    }
        

    public function getRange()
    {
        $request = new Request(["Range"=>"bytes=200-1000, 19000-"]);
        $conditions = $request->getRange()->getConditions();
        return new Result($conditions[0]->getStart()==200 && $conditions[0]->getEnd()==1000 && $conditions[1]->getStart()==19000 && $conditions[1]->getEnd()==null);
    }
        

    public function getReferer()
    {
        $request = new Request(["Referer"=>"https://developer.mozilla.org/en-US/docs/Web/JavaScript"]);
        return new Result($request->getReferer()=="https://developer.mozilla.org/en-US/docs/Web/JavaScript");
    }
        

    public function getUserAgent()
    {
        $request = new Request(["User-Agent"=>"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0"]);
        return new Result($request->getUserAgent()=="Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0");
    }
        

    public function getWantDigest()
    {
        $request = new Request(["Want-Digest"=>"SHA-512, md5;q=0"]);
        return new Result($request->getWantDigest()==["SHA-512", "md5"]);
    }
        

    public function getIfMatch()
    {
        $request = new Request(["If-Match"=>"\"abc\""]);
        return new Result($request->getIfMatch()=="abc");
    }
        

    public function getIfModifiedSince()
    {
        $request = new Request(["If-Modified-Since"=>"21 Oct 2015 07:28:00 GMT"]);
        return new Result(gmdate("Y-m-d H:i:s", $request->getIfModifiedSince())=="2015-10-21 07:28:00");
    }
        

    public function getIfNoneMatch()
    {
        $request = new Request(["If-None-Match"=>"\"abc\""]);
        return new Result($request->getIfNoneMatch()=="abc");
    }
        

    public function getIfUnmodifiedSince()
    {
        $request = new Request(["If-Unmodified-Since"=>"21 Oct 2015 07:28:00 GMT"]);
        return new Result(gmdate("Y-m-d H:i:s", $request->getIfUnmodifiedSince())=="2015-10-21 07:28:00");
    }
        

    public function getAccessControlRequestHeaders()
    {
        $request = new Request(["Access-Control-Request-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests"]);
        return new Result($request->getAccessControlRequestHeaders()==["X-Custom-Header", "Upgrade-Insecure-Requests"]);
    }
        

    public function getAccessControlRequestMethod()
    {
        $request = new Request(["Access-Control-Request-Method"=>"POST"]);
        return new Result($request->getAccessControlRequestMethod()=="POST");
    }
        

    public function getOrigin()
    {
        $request = new Request(["Origin"=>"https://developer.mozilla.org"]);
        return new Result($request->getOrigin()=="https://developer.mozilla.org");
    }
        

    public function getCustomHeaders()
    {
        $request = new Request(["Ping"=>"pong"]);
        return new Result($request->getCustomHeaders()["Ping"]=="pong");
    }
}
