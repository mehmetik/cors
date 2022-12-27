# mehmetik/cors

This package provides a library and stack middleware for handling Cross-Origin Resource Sharing (CORS) in a [Stack](https://github.com/stackphp/stack) application.

## Installation
To install the package, require it through Composer.

```bash
composer require mehmetik/cors
```

## Usage

This package can be used as a library or as [stack middleware](http://stackphp.com/).


### Options

| Option                 | Description                                                | Type        | Default value |
|------------------------|------------------------------------------------------------|-------------|---------------|
| allowedMethods         | Matches the request method.                                | `string[]`  | `[]`          |
| allowedOrigins         | Matches the request origin.                                | `string[]`  | `[]`          |
| allowedOriginsPatterns | Matches the request origin with `preg_match`.              | `string[]`  | `[]`          |
| allowedHeaders         | Sets the Access-Control-Allow-Headers response header.     | `string[]`  | `[]`          |
| exposedHeaders         | Sets the Access-Control-Expose-Headers response header.    | `string[]`  | `[]`          |
| maxAge                 | Sets the Access-Control-Max-Age response header.           | `int`       | `false`       |
| supportsCredentials    | Sets the Access-Control-Allow-Credentials header.          | `bool`      | `false`       |


The _allowedMethods_ and _allowedHeaders_ options are case-insensitive.

You don't need to provide both _allowedOrigins_ and _allowedOriginsPatterns_. If one of the strings passed matches, it is considered a valid origin.

If `['*']` is provided to _allowedMethods_, _allowedOrigins_ or _allowedHeaders_ all methods / origins / headers are allowed.

### Example: using the library

```php
<?php
use mehmetik\Cors\CorsSupport;

$cors = new CorsSupport([
    'allowedHeaders'         => ['x-allowed-header', 'x-other-allowed-header'],
    'allowedMethods'         => ['DELETE', 'GET', 'POST', 'PUT'],
    'allowedOrigins'         => ['http://localhost'],
    'allowedOriginsPatterns' => ['/localhost:\d/'],
    'exposedHeaders'         => false,
    'maxAge'                 => false,
    'supportsCredentials'    => false,
]);

$cors->addActualRequestHeaders(Response $response, $origin);
$cors->handlePreflightRequest(Request $request);
$cors->isActualRequestAllowed(Request $request);
$cors->isCorsRequest(Request $request);
$cors->isPreflightRequest(Request $request);
```

### Example: using the stack middleware

```php
<?php
use mehmetik\Cors\Cors;

$app = new Stack\Builder();
$app->push(new Cors($app, [
    'allowedHeaders'         => ['x-allowed-header', 'x-other-allowed-header'],
    'allowedMethods'         => ['DELETE', 'GET', 'POST', 'PUT'],
    'allowedOrigins'         => ['http://localhost'],
    'allowedOriginsPatterns' => ['/localhost:\d/'],
    'exposedHeaders'         => false,
    'maxAge'                 => false,
    'supportsCredentials'    => false,
]));
```

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
