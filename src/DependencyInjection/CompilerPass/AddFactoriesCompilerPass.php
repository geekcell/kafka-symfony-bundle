<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\DependencyInjection\CompilerPass;

use GeekCell\KafkaBundle\Serializer\Factory\SerializerFactoryProducer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddFactoriesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Add serializer factories to factory producer
        $serializerFactoryProducerDefinition = $container
            ->findDefinition(SerializerFactoryProducer::class);
        $taggedServices = $container
            ->findTaggedServiceIds('geek_cell_kafka.serializer_factory');

        foreach ($taggedServices as $id => $tags) {
            $serializerFactoryProducerDefinition
                ->addMethodCall('addFactory', [$container->getDefinition($id)]);
        }
    }
}
