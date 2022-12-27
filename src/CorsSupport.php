<?php
namespace mehmetik\Cors;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsSupport
{
    private array $options;

    /**
     * @param  array  $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $this->normalizeOptions($options);
    }

    /**
     * @param  array  $options
     *
     * @return array
     */
    private function normalizeOptions(array $options = []): array
    {
        $options += [
            'allowedOrigins' => [],
            'allowedOriginsPatterns' => [],
            'supportsCredentials' => false,
            'allowedHeaders' => [],
            'exposedHeaders' => [],
            'allowedMethods' => [],
            'maxAge' => 0,
        ];

        if (in_array('*', $options['allowedOrigins'])) {
            $options['allowedOrigins'] = true;
        }
        if (in_array('*', $options['allowedHeaders'])) {
            $options['allowedHeaders'] = true;
        } else {
            $options['allowedHeaders'] = array_map('strtolower', $options['allowedHeaders']);
        }

        if (in_array('*', $options['allowedMethods'])) {
            $options['allowedMethods'] = true;
        } else {
            $options['allowedMethods'] = array_map('strtoupper', $options['allowedMethods']);
        }

        return $options;
    }

    /**
     * @deprecated use isOriginAllowed
     */
    public function isActualRequestAllowed(Request $request): bool
    {
        return $this->isOriginAllowed($request);
    }

    /**
     * @param  Request  $request
     *
     * @return bool
     */
    public function isCorsRequest(Request $request): bool
    {
        return $request->headers->has('Origin');
    }

    /**
     * @param  Request  $request
     *
     * @return bool
     */
    public function isPreflightRequest(Request $request): bool
    {
        return $request->getMethod() === 'OPTIONS' && $request->headers->has('Access-Control-Request-Method');
    }

    /**
     * @param  Request  $request
     *
     * @return Response
     */
    public function handlePreflightRequest(Request $request): Response
    {
        $response = new Response();

        $response->setStatusCode(204);

        return $this->addPreflightResponseHeaders($response, $request);
    }

    /**
     * @param  Response  $response
     * @param  Request  $request
     *
     * @return Response
     */
    public function addPreflightResponseHeaders(Response $response, Request $request): Response
    {
        $this->configureAllowedOrigin($response, $request);

        if ($response->headers->has('Access-Control-Allow-Origin')) {
            $this->configureAllowCredentials($response, $request);

            $this->configureAllowedMethods($response, $request);

            $this->configureAllowedHeaders($response, $request);

            $this->configureMaxAge($response, $request);
        }

        return $response;
    }

    /**
     * @param  Request  $request
     *
     * @return bool
     */
    public function isOriginAllowed(Request $request): bool
    {
        if ($this->options['allowedOrigins'] === true) {
            return true;
        }
        if (!$request->headers->has('Origin')) {
            return false;
        }

        $origin = $request->headers->get('Origin');

        if (in_array($origin, $this->options['allowedOrigins'])) {
            return true;
        }

        foreach ($this->options['allowedOriginsPatterns'] as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Response  $response
     * @param  Request  $request
     *
     * @return void
     */
    private function configureAllowedOrigin(Response $response, Request $request)
    {
        if ($this->options['allowedOrigins'] === true) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } elseif ($this->isOriginAllowed($request)) {
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));
        }
    }

    /**
     * @param  Response  $response
     * @param  Request  $request
     *
     * @return void
     */
    private function configureAllowCredentials(Response $response, Request $request)
    {
        if ($this->options['supportsCredentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
    }

    /**
     * @param  Response  $response
     * @param  Request  $request
     *
     * @return void
     */
    private function configureAllowedMethods(Response $response, Request $request)
    {
        if ($this->options['allowedMethods'] === true) {
            $response->headers->set('Access-Control-Allow-Methods', '*');
        } elseif ($this->isPreflightRequest($request)) {
            $allowedMethods = implode(', ', $this->options['allowedMethods']);
            $response->headers->set('Access-Control-Allow-Methods', $allowedMethods);
        }
    }

    /**
     * @param  Response  $response
     * @param  Request  $request
     *
     * @return void
     */
    private function configureAllowedHeaders(Response $response, Request $request)
    {
        if ($this->options['allowedHeaders'] === true) {
            $response->headers->set('Access-Control-Allow-Headers', '*');
        } elseif ($this->isPreflightRequest($request)) {
            $allowedHeaders = implode(', ', $this->options['allowedHeaders']);
            $response->headers->set('Access-Control-Allow-Headers', $allowedHeaders);
        }
    }

    /**
     * @param  Response  $response
     * @param  Request  $request
     *
     * @return void
     */
    private function configureExposedHeaders(Response $response, Request $request)
    {
        if (!empty($this->options['exposedHeaders'])) {
            $exposedHeaders = implode(', ', $this->options['exposedHeaders']);
            $response->headers->set('Access-Control-Expose-Headers', $exposedHeaders);
        }
    }

    /**
     * @param  Response  $response
     * @param  Request  $request
     *
     * @return void
     */
    private function configureMaxAge(Response $response, Request $request)
    {
        if ($this->options['maxAge'] > 0) {
            $response->headers->set('Access-Control-Max-Age', (string) $this->options['maxAge']);
        }
    }

    /**
     * @param  Response  $response
     * @param  Request  $request
     *
     * @return Response
     */
    public function addActualRequestHeaders(Response $response, Request $request): Response
    {
        $this->configureAllowedOrigin($response, $request);

        if ($response->headers->has('Access-Control-Allow-Origin')) {
            $this->configureAllowCredentials($response, $request);

            $this->configureExposedHeaders($response, $request);
        }

        return $response;
    }

    /**
     * @param  Response  $response
     * @param  string  $header
     *
     * @return void
     */
    public function varyHeader(Response $response, string $header)
    {
        $value = $response->headers->get('Vary', '');
        if (strpos($value, $header) === false) {
            $response->headers->set('Vary', trim("$value, $header"));
        }
    }
}

