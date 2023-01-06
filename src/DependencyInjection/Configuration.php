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
                ->arrayNode('events')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('resources')
                            ->info('Dirs/globs where to search for matching events')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return [$v]; })
                            ->end()
                            ->scalarPrototype()->end()
                            ->defaultValue(['src/Event'])->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
