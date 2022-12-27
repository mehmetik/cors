<?php

use mehmetik\Cors\CorsSupport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsSupportTest extends TestCase
{
    public function testIsCorsRequest()
    {
        $cors = new CorsSupport();

        $request = new Request();
        $this->assertFalse($cors->isCorsRequest($request));

        $request->headers->set('Origin', 'http://example.com');
        $this->assertTrue($cors->isCorsRequest($request));
    }
    public function testIsPreflightRequest()
    {
        $cors = new CorsSupport();

        $request = new Request();
        $this->assertFalse($cors->isPreflightRequest($request));

        $request->headers->set('Access-Control-Request-Method', 'POST');
        $request->setMethod('OPTIONS');
        $this->assertTrue($cors->isPreflightRequest($request));
    }
    public function testIsOriginAllowed()
    {
        $cors = new CorsSupport(['allowedOrigins' => ['http://example.com']]);

        $request = new Request();
        $request->headers->set('Origin', 'http://example.com');
        $this->assertTrue($cors->isOriginAllowed($request));

        $request->headers->set('Origin', 'http://other.com');
        $this->assertFalse($cors->isOriginAllowed($request));
    }
    public function testIsOriginAllowedWithAllowedOriginsPatterns()
    {
        $cors = new CorsSupport([
            'allowedOrigins'         => ['http://example.com'],
            'allowedOriginsPatterns' => ['#^http://[a-z]+.com$#'],
        ]);

        $request = new Request();
        $request->headers->set('Origin', 'http://test.com');
        $this->assertTrue($cors->isOriginAllowed($request));

        $request->headers->set('Origin', 'http://other.net');
        $this->assertFalse($cors->isOriginAllowed($request));
    }
    public function testHandlePreflightRequest()
    {
        $cors = new CorsSupport(['allowedOrigins' => ['http://example.com']]);

        $request = new Request();
        $request->headers->set('Access-Control-Request-Method', 'POST');
        $request->headers->set('Origin', 'http://example.com');
        $request->setMethod('OPTIONS');

        $response = $cors->handlePreflightRequest($request);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('http://example.com', $response->headers->get('Access-Control-Allow-Origin'));
    }
    public function testAddPreflightResponseHeaders()
    {
        $cors = new CorsSupport(['allowedOrigins' => ['http://example.com']]);

        $request = new Request();
        $request->headers->set('Access-Control-Request-Method', 'POST');
        $request->headers->set('Origin', 'http://example.com');
        $request->setMethod('OPTIONS');

        $response = new Response();
        $response = $cors->addPreflightResponseHeaders($response, $request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('http://example.com', $response->headers->get('Access-Control-Allow-Origin'));
    }
    public function testAddActualRequestHeaders()
    {
        $cors = new CorsSupport(['allowedOrigins' => ['http://example.com']]);

        $request = new Request();
        $request->headers->set('Origin', 'http://example.com');

        $response = new Response();
        $response = $cors->addActualRequestHeaders($response, $request);
        $this->assertEquals('http://example.com', $response->headers->get('Access-Control-Allow-Origin'));
    }
    public function testAddPreflightResponseHeadersWithAllowedHeaders()
    {
        $cors = new CorsSupport([
            'allowedOrigins' => ['http://example.com'],
            'allowedHeaders' => ['X-Test'],
        ]);

        $request = new Request();
        $request->headers->set('Access-Control-Request-Method', 'POST');
        $request->headers->set('Origin', 'http://example.com');
        $request->setMethod('OPTIONS');

        $response = new Response();
        $response = $cors->addPreflightResponseHeaders($response, $request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('http://example.com', $response->headers->get('Access-Control-Allow-Origin'));
    }
}
