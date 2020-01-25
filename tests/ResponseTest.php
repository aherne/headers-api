<?php
namespace Test\Lucinda\Headers;

use Lucinda\Headers\Response;
use Lucinda\UnitTest\Result;

class ResponseTest
{
    public function addAcceptPatch()
    {
        $response = new Response();
        $response->addAcceptPatch("application/example");
        $response->addAcceptPatch("text/example", "utf-8");
        return new Result($response->toArray()==["Accept-Patch"=>"application/example, text/example;charset=utf-8"]);
    }
        

    public function setAcceptRanges()
    {
        $response = new Response();
        $response->setAcceptRanges("bytes");
        return new Result($response->toArray()==["Accept-Ranges"=>"bytes"]);
    }
        

    public function addAllow()
    {
        $response = new Response();
        $response->addAllow("GET");
        $response->addAllow("POST");
        return new Result($response->toArray()==["Allow"=>"GET, POST"]);
    }
        

    public function setCacheControl()
    {
        $response = new Response();
        $cacheControl = $response->setCacheControl();
        $cacheControl->setPublic();
        $cacheControl->setMaxAge(10);
        return new Result($response->toArray()==["Cache-Control"=>"public, max-age=10"]);
    }
        

    public function addClearSiteData()
    {
        $response = new Response();
        $response->addClearSiteData("cache");
        $response->addClearSiteData("cookies");
        return new Result($response->toArray()==["Clear-Site-Data"=>'"cache", "cookies"']);
    }
        

    public function setContentDisposition()
    {
        $response = new Response();
        $response->setContentDisposition("attachment")->setFileName("test.jpg", true);
        return new Result($response->toArray()==["Content-Disposition"=>'attachment; filename*="test.jpg"']);
    }
        

    public function addContentEncoding()
    {
        $response = new Response();
        $response->addContentEncoding("gzip");
        $response->addContentEncoding("compress");
        return new Result($response->toArray()==["Content-Encoding"=>'gzip, compress']);
    }
        

    public function addContentLanguage()
    {
        $response = new Response();
        $response->addContentLanguage("en-US");
        $response->addContentLanguage("de");
        return new Result($response->toArray()==["Content-Language"=>'en-US, de']);
    }
        

    public function setContentLength()
    {
        $response = new Response();
        $response->setContentLength(20);
        return new Result($response->toArray()==["Content-Length"=>'20']);
    }
        

    public function setContentLocation()
    {
        $response = new Response();
        $response->setContentLocation("https://www.google.com");
        return new Result($response->toArray()==["Content-Location"=>'https://www.google.com']);
    }
        

    public function setContentRange()
    {
        $results = [];
        $response = new Response();
        $response->setContentRange("bytes", null, null, 10);
        $results[] = new Result($response->toArray()==["Content-Range"=>'bytes */10'], "only size given");
        $response->setContentRange("bytes", 100, 200);
        $results[] = new Result($response->toArray()==["Content-Range"=>'bytes 100-200/*'], "only start and end given");
        $response->setContentRange("bytes", 100, 200, 500);
        $results[] = new Result($response->toArray()==["Content-Range"=>'bytes 100-200/500'], "all arguments given");
        return $results;
    }
        

    public function setContentType()
    {
        $response = new Response();
        $response->setContentType("text/html", "UTF-8");
        return new Result($response->toArray()==["Content-Type"=>'text/html; charset=UTF-8']);
    }
        

    public function setCrossOriginResourcePolicy()
    {
        $response = new Response();
        $response->setCrossOriginResourcePolicy("same-site");
        return new Result($response->toArray()==["Cross-Origin-Resource-Policy"=>'same-site']);
    }
        

    public function addDigest()
    {
        $response = new Response();
        $response->addDigest("SHA-256", "X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE=");
        $response->addDigest("UNIXsum", "30637");
        return new Result($response->toArray()==["Digest"=>'SHA-256=X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE=, UNIXsum=30637']);
    }
        

    public function setEtag()
    {
        $response = new Response();
        $response->setEtag("abc");
        return new Result($response->toArray()==["ETag"=>'"abc"']);
    }
        

    public function setExpirationTime()
    {
        $response = new Response();
        $response->setExpirationTime(time()+10);
        return new Result($response->toArray()==["Expires"=>gmdate("D, d M Y H:i:s T", time()+10)]);
    }
        

    public function setLastModifiedTime()
    {
        $response = new Response();
        $response->setLastModifiedTime(time()-10);
        return new Result($response->toArray()==["Last-Modified"=>gmdate("D, d M Y H:i:s T", time()-10)]);
    }
        

    public function setLocation()
    {
        $response = new Response();
        $response->setLocation("https://www.google.com");
        return new Result($response->toArray()==["Location"=>'https://www.google.com']);
    }
        

    public function setReferrerPolicy()
    {
        $response = new Response();
        $response->setReferrerPolicy("no-referrer");
        return new Result($response->toArray()==["Referrer-Policy"=>'no-referrer']);
    }
        

    public function setRentryAfterDate()
    {
        $response = new Response();
        $response->setRentryAfterDate(time()+10);
        return new Result($response->toArray()==["Rentry-After"=>gmdate("D, d M Y H:i:s T", time()+10)]);
    }
        

    public function setRentryAfterDelay()
    {
        $response = new Response();
        $response->setRentryAfterDelay(10);
        return new Result($response->toArray()==["Rentry-After"=>'10']);
    }
        

    public function setSourceMap()
    {
        $response = new Response();
        $response->setSourceMap("https://www.google.com");
        return new Result($response->toArray()==["Source-Map"=>'https://www.google.com']);
    }
        

    public function setStrictTransportSecurity()
    {
        $response = new Response();
        $response->setStrictTransportSecurity();
        return new Result($response->toArray()==["Strict-Transport-Security"=>'max-age: 31536000']);
    }
        

    public function addTimingAllowOrigin()
    {
        $response = new Response();
        $response->addTimingAllowOrigin("https://www.google.com");
        return new Result($response->toArray()==["Timing-Allow-Origin"=>'https://www.google.com']);
    }
        

    public function setTk()
    {
        $response = new Response();
        $response->setTk("G");
        return new Result($response->toArray()==["Tk"=>'G']);
    }
        

    public function setTrailer()
    {
        $response = new Response();
        $response->setTrailer('Expires

7\r\n 
Mozilla\r\n 
9\r\n 
Developer\r\n 
7\r\n 
Network\r\n 
0\r\n 
Expires: Wed, 21 Oct 2015 07:28:00 GMT\r\n
\r\n');
        return new Result($response->toArray()==["Trailer"=>'Expires

7\r\n 
Mozilla\r\n 
9\r\n 
Developer\r\n 
7\r\n 
Network\r\n 
0\r\n 
Expires: Wed, 21 Oct 2015 07:28:00 GMT\r\n
\r\n']);
    }
        

    public function addTransferEncoding()
    {
        $response = new Response();
        $response->addTransferEncoding("gzip");
        $response->addTransferEncoding("identity");
        return new Result($response->toArray()==["Transfer-Encoding"=>'gzip, identity']);
    }
        

    public function addVary()
    {
        $response = new Response();
        $response->addVary("User-Agent");
        $response->addVary("Content-Type");
        return new Result($response->toArray()==["Vary"=>'User-Agent, Content-Type']);
    }
        

    public function setWWWAuthenticate()
    {
        $response = new Response();
        $response->setWWWAuthenticate("Bearer", "lucinda");
        return new Result($response->toArray()==["WWW-Authenticate"=>'Bearer realm="lucinda"']);
    }
        

    public function setContentTypeOptions()
    {
        $response = new Response();
        $response->setContentTypeOptions();
        return new Result($response->toArray()==["X-Content-Type-Options"=>'nosniff']);
    }
        

    public function setDNSPrefetchControl()
    {
        $response = new Response();
        $response->setDNSPrefetchControl(true);
        return new Result($response->toArray()==["X-DNS-Prefetch-Control"=>'on']);
    }
        

    public function setFrameOptions()
    {
        $response = new Response();
        $response->setFrameOptions("deny");
        return new Result($response->toArray()==["X-Frame-Options"=>'deny']);
    }
        

    public function setCustomHeader()
    {
        $response = new Response();
        $response->setCustomHeader("Test", "me");
        return new Result($response->toArray()==["Test"=>'me']);
    }
        

    public function setAccessControlAllowCredentials()
    {
        $response = new Response();
        $response->setAccessControlAllowCredentials();
        return new Result($response->toArray()==["Access-Control-Allow-Credentials"=>'true']);
    }
        

    public function addAccessControlAllowHeaders()
    {
        $response = new Response();
        $response->addAccessControlAllowHeaders("X-Custom-Header");
        $response->addAccessControlAllowHeaders("Upgrade-Insecure-Requests");
        return new Result($response->toArray()==["Access-Control-Allow-Headers"=>'X-Custom-Header, Upgrade-Insecure-Requests']);
    }
        

    public function addAccessControlAllowMethod()
    {
        $response = new Response();
        $response->addAccessControlAllowMethod("GET");
        $response->addAccessControlAllowMethod("POST");
        return new Result($response->toArray()==["Access-Control-Allow-Methods"=>'GET, POST']);
    }
        

    public function setAccessControlAllowOrigin()
    {
        $response = new Response();
        $response->setAccessControlAllowOrigin("https://www.google.com");
        return new Result($response->toArray()==["Access-Control-Allow-Origin"=>'https://www.google.com']);
    }
        

    public function addAccessControlExposeHeaders()
    {
        $response = new Response();
        $response->addAccessControlExposeHeaders("X-Custom-Header");
        $response->addAccessControlExposeHeaders("Upgrade-Insecure-Requests");
        return new Result($response->toArray()==["Access-Control-Expose-Headers"=>'X-Custom-Header, Upgrade-Insecure-Requests']);
    }
        

    public function setAccessControlMaxAge()
    {
        $response = new Response();
        $response->setAccessControlMaxAge(10);
        return new Result($response->toArray()==["Access-Control-Max-Age"=>'10']);
    }
        

    public function toArray()
    {
        $response = new Response();
        $response->addAccessControlExposeHeaders("X-Custom-Header");
        $response->setAccessControlMaxAge(10);
        return new Result($response->toArray()==["Access-Control-Expose-Headers"=>'X-Custom-Header',"Access-Control-Max-Age"=>'10']);
    }
}
