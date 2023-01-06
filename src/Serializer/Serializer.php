<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer;

use GeekCell\KafkaBundle\Contracts\Serializable;
use GeekCell\KafkaBundle\Contracts\Serializer as SerializerInterface;

abstract class Serializer implements SerializerInterface
{
    public function serialize(Serializable $object): string
    {
        if (!$this->supports($object::class)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Serializer %s does not support %s',
                    static::class,
                    $object::class,
                )
            );
        }

        return $this->doSerialize($object);
    }

    public function deserialize(string $data, string $type): Serializable
    {
        if (!$this->supports($type)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Serializer %s does not support %s',
                    static::class,
                    $type,
                )
            );
        }

        return $this->doDeserialize($data, $type);
    }

    abstract protected function doSerialize(Serializable $object): string;

    abstract protected function doDeserialize(
        string $data,
        string $type
    ): Serializable;

    abstract protected function supports(string $type): bool;
}
