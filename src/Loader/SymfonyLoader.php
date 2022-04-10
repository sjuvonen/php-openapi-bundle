<?php

namespace Juvonet\OpenApiBundle\Loader;

use Juvonet\OpenApi\Context;
use Juvonet\OpenApi\Documentation\OpenApi;
use Juvonet\OpenApi\Documentation\Operation;
use Juvonet\OpenApi\LoaderInterface;
use Symfony\Component\Routing\Route;

final class SymfonyLoader implements LoaderInterface
{
    public function __construct(
        private \Symfony\Component\Routing\RouterInterface $router,
    ) {
    }

    public function load(OpenApi $openApi): void
    {
        // Disabled as we don't want to expose all existing routes in the API docs.
        return;

        $this->loadRoutes($openApi);
    }

    private function loadRoutes(OpenApi $openApi): void
    {
        foreach ($this->router->getRouteCollection() as $route) {
            foreach ($route->getMethods() as $method) {
                [$filename, $className, $methodName] = $this->getControllerFileAndClassAndMethod($route);

                $operation = new Operation($route->getPath(), $method);
                $operation->setContext(new Context(
                    file: $filename,
                    class: $className,
                    method: $methodName,
                ));

                $openApi->paths->addOperation($operation);
            }
        }
    }

    private function getControllerFileAndClassAndMethod(Route $route): array
    {
        $parts = explode('::', $route->getDefault('_controller'). '::__invoke', 2);
        $filename = (new \ReflectionClass($parts[0]))->getFileName();

        return [$filename, ...$parts];
    }
}
