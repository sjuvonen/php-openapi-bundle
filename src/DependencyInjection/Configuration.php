<?php

namespace Juvonet\OpenApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('juvonet_open_api');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('project')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('title')
                            ->defaultValue('My Project')
                        ->end()
                        ->scalarNode('description')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('version')
                            ->defaultValue('0.0.0')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('discovery')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('class_loader')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('paths')
                                    ->info('These paths are searched for annotated schema and operation classes.')
                                    ->isRequired()
                                    ->scalarPrototype()->end()
                                    ->defaultValue(['%kernel.project_dir%/src'])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('external_loader')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('paths')
                                    ->info('Paths are searched for documentation files.')
                                    ->scalarPrototype()->end()
                                    ->defaultValue([])
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('public_schema')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('namespaces')
                                    ->info('Namespaces are used to whitelist automatically discovered schemas.')
                                    ->isRequired()
                                    ->scalarPrototype()->end()
                                    ->defaultValue([])
                                ->end()
                                ->booleanNode('fallback')
                                    ->info('Fallback to public schema if no properties are discovered.')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('tags')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('automatic_tags')
                            ->info('Generate tags from operation paths.')
                            ->defaultValue(true)
                        ->end()
                        ->arrayNode('trim_prefixes')
                            ->info('Generate tags from operation paths.')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end()
            ;

        return $treeBuilder;
    }
}
