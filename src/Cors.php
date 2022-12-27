<?php
namespace mehmetik\Cors;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class Cors implements HttpKernelInterface
{
    private CorsSupport $corsSupport;

    private array $defaultOptions = [
        'allowedHeaders'         => [],
        'allowedMethods'         => [],
        'allowedOrigins'         => [],
        'allowedOriginsPatterns' => [],
        'exposedHeaders'         => [],
        'maxAge'                 => 0,
        'supportsCredentials'    => false,
    ];

    // HttpKernelInterface implements
    private HttpKernelInterface $httpKernel;

    /**
     * @param  HttpKernelInterface  $httpKernel
     * @param  array  $options
     */
    public function __construct(HttpKernelInterface $httpKernel, array $options = [])
    {
        $this->httpKernel = $httpKernel;
        $this->corsSupport = new CorsSupport(array_merge($this->defaultOptions, $options));
    }

    /**
     * @param  Request  $request
     * @param $type
     * @param $catch
     *
     * @return Response
     * @throws Exception
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true): Response
    {
        if ($this->corsSupport->isPreflightRequest($request)) {
            $response = $this->corsSupport->handlePreflightRequest($request);
            // Add the 'Access-Control-Request-Method' header to the 'Vary' header.
            $response->setVary('Access-Control-Request-Method', false);
            return $response;
        }
        // Application Request (GET, POST, PUT, DELETE, ...)
        $response = $this->httpKernel->handle($request, $type, $catch);

        if ($request->getMethod() === 'OPTIONS') {
            // Add the 'Access-Control-Request-Method' header to the 'Vary' header.
            $this->corsSupport->varyHeader($response, 'Access-Control-Request-Method');
        }

        return $this->corsSupport->addActualRequestHeaders($response, $request);
    }
}
