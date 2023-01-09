<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer\Factory;

use GeekCell\KafkaBundle\Contracts\SerializerFactory as SerializerFactoryInterface;

class SerializerFactoryProducer
{
    private array $factories = [];

    public function produce(string $type): SerializerFactoryInterface
    {
        $shortName = (new \ReflectionClass($type))->getShortName();
        $factoryClass = sprintf(
            '%s\\%sSerializerFactory',
            __NAMESPACE__,
            $shortName
        );

        if (
            !class_exists($factoryClass) ||
            !isset($this->factories[$factoryClass])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Factory type "%s" not found',
                    $type
                )
            );
        }

        return $this->factories[$factoryClass];
    }

    public function addFactory(SerializerFactoryInterface $factory): void
    {
        $this->factories[$factory::class] = $factory;
    }
}
