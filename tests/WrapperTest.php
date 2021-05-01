<?php
namespace Test\Lucinda\Headers;

use Lucinda\Headers\Wrapper;
use Lucinda\UnitTest\Result;

class WrapperTest
{
    private $xml;
    
    public function __construct()
    {
        $this->xml = \simplexml_load_string('
<xml>
    <headers cache_expiration="10" allow_credentials="1" cors_max_age="5" allowed_request_headers="X-Custom-Header, Upgrade-Insecure-Requests" allowed_response_headers="Content-Length, X-Kuma-Revision"/>
    <routes>
        <route id="index" allowed_methods="GET,POST"/>
        <route id="login" no_cache="1"/>
    </routes>
</xml>
');
    }
    

    public function getRequest()
    {
        $wrapper = new Wrapper($this->xml, "index", ["Accept"=>"text/html, application/xml;q=0.9, */*;q=0.8", "Accept-Charset"=>"utf-8, iso-8859-1;q=0.5"]);
        return new Result($wrapper->getRequest()->getAccept()==["text/html", "application/xml"] && $wrapper->getRequest()->getAcceptCharset()==["utf-8", "iso-8859-1"]);
    }
        

    public function validateCache()
    {
        $results = [];
        
        $wrapper = new Wrapper($this->xml, "index", ["Accept"=>"text/html, application/xml;q=0.9, */*;q=0.8", "Accept-Charset"=>"utf-8, iso-8859-1;q=0.5"]);
        $httpStatus = $wrapper->validateCache(new TestCacheable(), "GET");
        $response = $wrapper->getResponse()->toArray();
        $results[] = new Result($httpStatus==200 && $response==[
            "Cache-Control"=>"public, max-age=10",
            "ETag"=>'"qwerty"',
            "Last-Modified"=>gmdate("D, d M Y H:i:s T", time())
        ], "cacheable but no conditionals");
        
        $wrapper = new Wrapper($this->xml, "login", []);
        $httpStatus = $wrapper->validateCache(new TestCacheable(), "GET");
        $response = $wrapper->getResponse()->toArray();
        $results[] = new Result($httpStatus==200 && $response==[], "not cacheable");
        
        $wrapper = new Wrapper($this->xml, "index", ["If-None-Match"=>"asdfg"]);
        $httpStatus = $wrapper->validateCache(new TestCacheable(), "GET");
        $response = $wrapper->getResponse()->toArray();
        $results[] = new Result($httpStatus==200 && $response==[
            "Cache-Control"=>"public, max-age=10",
            "ETag"=>'"qwerty"',
            "Last-Modified"=>gmdate("D, d M Y H:i:s T", time())
        ], "cacheable but conditionals fail");
        
        $wrapper = new Wrapper($this->xml, "index", ["If-None-Match"=>"qwerty"]);
        $httpStatus = $wrapper->validateCache(new TestCacheable(), "GET");
        $response = $wrapper->getResponse()->toArray();
        $results[] = new Result($httpStatus==304 && $response==[
            "Cache-Control"=>"public, max-age=10",
            "ETag"=>'"qwerty"',
            "Last-Modified"=>gmdate("D, d M Y H:i:s T", time())
        ], "cacheable and conditionals match");
        
        return $results;
    }
        

    public function validateCors()
    {
        $wrapper = new Wrapper($this->xml, "index", [
            "Origin"=>"https://www.google.com",
            "Access-Control-Request-Method"=>"POST",
            "Access-Control-Request-Headers"=>"X-Custom-Header, Upgrade-Insecure-Requests"
        ], "OPTIONS");
        $wrapper->validateCors("https://www.google.com");
        return new Result($wrapper->getResponse()->toArray()==[
            'Access-Control-Allow-Credentials' => "true",
            'Access-Control-Allow-Headers' => "X-Custom-Header, Upgrade-Insecure-Requests",
            'Access-Control-Allow-Methods' => "GET, POST",
            'Access-Control-Allow-Origin' => "https://www.google.com",
            'Access-Control-Expose-Headers' => "Content-Length, X-Kuma-Revision",
            'Access-Control-Max-Age' =>5
            ]);
    }
        

    public function getResponse()
    {
        $wrapper = new Wrapper($this->xml, "index", ["If-None-Match"=>"asdfg"]);
        $httpStatus = $wrapper->validateCache(new TestCacheable(), "GET");
        $response = $wrapper->getResponse()->toArray();
        return new Result($httpStatus==200 && $response==[
            "Cache-Control"=>"public, max-age=10",
            "ETag"=>'"qwerty"',
            "Last-Modified"=>gmdate("D, d M Y H:i:s T", time())
        ]);
    }
}
