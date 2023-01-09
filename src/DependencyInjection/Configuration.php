<?php

namespace GeekCell\KafkaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('geek_cell_kafka');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('kafka')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('brokers')
                            ->defaultValue('localhost:9092')
                        ->end()
                        ->arrayNode('global')
                            ->variablePrototype()->end()
                        ->end()
                        ->arrayNode('topic')
                            ->variablePrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('avro')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('schema_registry_url')
                            ->defaultValue('http://localhost:8081')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('events')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('lookup')
                            ->info('Dirs/globs where to search for matching events')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return [$v]; })
                            ->end()
                            ->scalarPrototype()->end()
                            ->defaultValue(['src/Event'])
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
