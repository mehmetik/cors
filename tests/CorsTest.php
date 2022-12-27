<?php

use mehmetik\Cors\Cors;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CorsTest extends TestCase
{
    public function testItCanHandlePreflightRequests()
    {
        $mockApp = $this->createMock(HttpKernelInterface::class);
        $mockApp->method('handle')->willReturn(new Response());

        $options = [
            'allowedOrigins' => ['*'],
            'allowedMethods' => ['POST', 'GET', 'PUT', 'DELETE'],
            'allowedHeaders' => ['Content-Type', 'Authorization'],
            'maxAge'         => 3600
        ];
        $cors = new Cors($mockApp, $options);

        $request = new Request([], [], [], [], [], [
            'HTTP_ORIGIN'          => 'http://localhost:3000',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, Authorization'
        ]);
        $response = $cors->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testItCanHandleActualRequests()
    {
        $mockApp = $this->createMock(HttpKernelInterface::class);
        $mockApp->method('handle')->willReturn(new Response());

        $options = [
            'allowedOrigins' => ['*'],
            'allowedMethods' => ['POST', 'GET', 'PUT', 'DELETE'],
            'allowedHeaders' => ['Content-Type', 'Authorization'],
            'maxAge'         => 3600
        ];
        $cors = new Cors($mockApp, $options);

        $request = new Request([], [], [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:3000',
        ]);

        $response = $cors->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testItCanHandleActualRequestsWhenOriginIsNotAllowed()
    {
        $mockApp = $this->createMock(HttpKernelInterface::class);
        $mockApp->method('handle')->willReturn(new Response());

        $options = [
            'allowedOrigins' => ['http://localhost:3000'],
            'allowedMethods' => ['POST', 'GET', 'PUT', 'DELETE'],
            'allowedHeaders' => ['Content-Type', 'Authorization'],
            'maxAge'         => 3600
        ];

        $cors = new Cors($mockApp, $options);

        $request = new Request([], [], [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:3001',
        ]);

        $response = $cors->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(null, $response->headers->get('Access-Control-Allow-Origin'));
    }
}
