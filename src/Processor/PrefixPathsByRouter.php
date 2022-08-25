<?php

namespace Juvonet\OpenApiBundle\Processor;

use Juvonet\OpenApi\Documentation\OpenApi;
use Juvonet\OpenApi\Documentation\Operation;

/**
 * Will fix paths to include the prefix configured in Symfony router.
 */
class PrefixPathsByRouter
{
    public function __construct(
        private \Symfony\Component\Routing\RouterInterface $router
    ) {
    }

    public function __invoke(OpenApi $openApi): void
    {
        $map = [];

        foreach ($this->router->getRouteCollection() as $name => $route) {
            $map[$name] = $route->getPath();
        }

        foreach ($openApi->paths as $pathItem) {
            foreach ($pathItem as $operation) {
                if (!($operation instanceof Operation)) {
                    continue;
                }

                $oid = strtr($operation->operationId, ['-' => '_']);

                /**
                 * Prefix paths by looking for matches by Operation ID.
                 *
                 * Might not be 100 % reliable but so far we are satisfied.
                 */
                if (isset($map[$oid])) {
                    $openApi->paths->remove($pathItem->path);

                    $pathItem->path = $map[$oid];
                    $operation->path = $map[$oid];

                    $openApi->paths->add($pathItem);
                }
            }
        }
    }
}
