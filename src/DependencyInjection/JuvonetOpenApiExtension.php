<?php

namespace Juvonet\OpenApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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
        $container->setParameter('juvonet.openapi.project.title', $processed['project']['title']);
        $container->setParameter('juvonet.openapi.project.description', $processed['project']['description']);
        $container->setParameter('juvonet.openapi.project.version', $processed['project']['version']);
        $container->setParameter('juvonet.openapi.public_schema_namespaces', $processed['discovery']['public_schema']['namespaces']);
        $container->setParameter('juvonet.openapi.public_schema_namespaces', $processed['discovery']['public_schema']['namespaces']);
        $container->setParameter('juvonet.openapi.tags.trim_prefixes', $processed['tags']['trim_prefixes']);
    }
}
