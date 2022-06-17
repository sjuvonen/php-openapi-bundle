<?php

namespace Juvonet\OpenApiBundle\DependencyInjection;

use Juvonet\OpenApi\Loader\ClassDocumentationLoader;
use Juvonet\OpenApiBundle\Routing\OpenApiRouteLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class JuvonetOpenApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $processed = $this->processConfiguration($configuration, $configs);

        $container->setParameter('juvonet.openapi.class_documentation_paths', $processed['discovery']['class_loader']['paths']);
        $container->setParameter('juvonet.openapi.external_documentation_paths', $processed['discovery']['external_loader']['paths']);
        $container->setParameter('juvonet.openapi.project.title', $processed['project']['title']);
        $container->setParameter('juvonet.openapi.project.description', $processed['project']['description']);
        $container->setParameter('juvonet.openapi.project.version', $processed['project']['version']);
        $container->setParameter('juvonet.openapi.public_schema_namespaces', $processed['discovery']['public_schema']['namespaces']);
        $container->setParameter('juvonet.openapi.public_schema_namespaces', $processed['discovery']['public_schema']['namespaces']);
        $container->setParameter('juvonet.openapi.tags.trim_prefixes', $processed['tags']['trim_prefixes']);

        $this->processRouteLoader($container, $processed);
    }

    private function processRouteLoader(ContainerBuilder $container, array $processed): void
    {
        /**
         * Each route loader needs a dedicated instance of ClassDocumentationLoader
         * as well, since filtering paths is done in there.
         */

        if (empty($processed['routing']['profiles'])) {
            $processed['routing']['profiles']['default'] = [
                'paths' => $processed['discovery']['class_loader']['paths'],
                'exclude' => [],
            ];
        }

        foreach ($processed['routing']['profiles'] as $profile => $config) {
            $routeLoaderId = "juvonet.openapi.routing.route_loader.{$profile}";
            $documentationLoaderId = "juvonet.openapi.routing.class_documentation_loader.{$profile}";

            $container->register($documentationLoaderId, ClassDocumentationLoader::class)
                ->setAutowired(true)
                ->setAutoconfigured(false)
                ->setArgument('$paths', $config['paths'])
                ->setArgument('$exclude', $config['exclude'])
                ;

            if (!$container->has(OpenApiRouteLoader::class)) {
                $routeLoaderDefinition = $container->register(OpenApiRouteLoader::class);
                $container->setAlias($routeLoaderId, OpenApiRouteLoader::class);
            } else {
                $routeLoaderDefinition = $container->register($routeLoaderId, OpenApiRouteLoader::class);
            }

            $routeLoaderDefinition
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setArgument('$loader', new Reference($documentationLoaderId))
                ;
        }
    }
}
