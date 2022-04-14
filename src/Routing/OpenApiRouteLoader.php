<?php

namespace Juvonet\OpenApiBundle\Routing;

use Juvonet\OpenApi\Documentation\OpenApi;
use Juvonet\OpenApi\Documentation\Operation;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Generates Symfony routes from discovered OpenApi operations.
 */
class OpenApiRouteLoader implements RouteLoaderInterface
{
    private $consumed = false;

    public function __construct(
        private \Juvonet\OpenApi\LoaderInterface $loader,
    ) {
    }

    public function __invoke(): RouteCollection
    {
        if ($this->consumed) {
            throw new \RuntimeException('Trying to load OpenAPI routes for the second time.');
        }

        $routes = new RouteCollection();
        $openApi = new OpenApi();

        $this->loader->load($openApi);

        foreach ($openApi->paths as $pathItem) {
            foreach ($pathItem as $operation) {
                if (!($operation instanceof Operation)) {
                    continue;
                }

                $routeName = $this->routeName($operation);
                $routes->add($routeName, $this->toRoute($operation));
            }
        }

        return $routes;
    }

    private function toRoute(Operation $operation): Route
    {
        $requirements = $this->extract($operation, 'require');
        $options = $this->extract($operation, 'option');
        $defaults = $this->extract($operation, 'default');

        if (!$operation->context->method || $operation->context->method === '__invoke') {
            $defaults['_controller'] = $operation->context->class;
        } else {
            $defaults['_controller'] = "{$operation->context->class}::{$operation->context->method}";
        }

        /**
         * FIXME: Extract entity bundle metadata. Should move it to EntityBundle
         * then...
         */
        foreach ($this->extractRaw($operation, 'entity') as $key => $value) {
            $defaults["_{$key}"] = $value;
        }

        $route = new Route($operation->path, $defaults, $requirements, $options);
        $route->setMethods([strtoupper($operation->method)]);

        return $route;
    }

    private function extractRaw(Operation $operation, string $prefix): array
    {
        if (!is_array($operation->x)) {
            return [];
        }

        $values = [];

        foreach ($operation->x as $key => $value) {
            if (strpos($key, "{$prefix}-") === 0) {
                $key = strtr($key, ['-' => '_']);
                $values[$key] = $value;
            }
        }

        return $values;
    }

    private function extract(Operation $operation, string $prefix): array
    {
        $values = [];

        foreach ($this->extractRaw($operation, $prefix) as $key => $value) {
            $key = substr($key, strlen($prefix) + 1);
            $values[$key] = $value;
        }

        return $values;
    }

    private function routeName(Operation $operation): string
    {
        if ($operation->operationId) {
            return strtr($operation->operationId, ['-' => '_']);
        }

        $signature = "{$operation->context->class}_{$operation->context->method}";
        $signature = strtr($signature, ['\\' => '_']);
        $signature = trim($signature, '_');
        $signature = strtolower($signature);

        return $signature;
    }
}
