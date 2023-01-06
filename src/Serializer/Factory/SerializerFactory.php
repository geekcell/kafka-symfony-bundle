<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer\Factory;

use GeekCell\KafkaBundle\Contracts\SerializerFactory as SerializerFactoryInterface;

abstract class SerializerFactory implements SerializerFactoryInterface
{
    private static array $factories = [];

    public static function get(string $type): SerializerFactoryInterface
    {
        $shortName = (new \ReflectionClass(static::class))->getShortName();
        $factoryClass = sprintf(
            '%s\\%sSerializerFactory',
            __NAMESPACE__,
            $shortName
        );

        if (!class_exists($factoryClass) || !isset(static::$factories[$factoryClass])) {
            throw new \InvalidArgumentException(sprintf('Factory type "%s" not found', $type));
        }

        return static::$factories[$factoryClass];
    }

    public static function addFactory(SerializerFactoryInterface $factory): void
    {
        static::$factories[$factory::class] = $factory;
    }
}
