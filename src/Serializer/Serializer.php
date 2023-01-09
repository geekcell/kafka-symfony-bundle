<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer;

use GeekCell\KafkaBundle\Contracts\Serializer as SerializerInterface;
use GeekCell\KafkaBundle\Record\Record;

abstract class Serializer implements SerializerInterface
{
    public function serialize(Record $object): string
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

    public function deserialize(string $data, string $type): Record
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

    abstract protected function doSerialize(Record $object): string;

    abstract protected function doDeserialize(
        string $data,
        string $type
    ): Record;

    abstract protected function supports(string $type): bool;
}
