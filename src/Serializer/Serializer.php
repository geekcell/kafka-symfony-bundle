<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer;

use FlixTech\AvroSerializer\Objects\Schema;
use FlixTech\AvroSerializer\Objects\Schema\RecordType;
use GeekCell\KafkaBundle\Contracts\Serializer as SerializerInterface;
use GeekCell\KafkaBundle\Record\Record;
use GeekCell\KafkaBundle\Util\AvroUtil;

abstract class Serializer implements SerializerInterface
{
    public function __construct(
        protected AvroUtil $avroUtil,
        protected array $defaults = [],
    ) {
    }

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

    protected function fixNamespace(Schema $schema): Schema
    {
        if ($this->avroUtil->hasNamespace($schema)) {
            return $schema;
        }

        $defaultNamespace = $this->defaults['namespace'] ?? null;
        if (null == $defaultNamespace) {
            return $schema;
        }

        /** @var RecordType $schema */
        $schema = $schema->namespace($defaultNamespace);

        return $schema;
    }

    abstract protected function doSerialize(Record $object): string;

    abstract protected function doDeserialize(
        string $data,
        string $type
    ): Record;

    abstract protected function supports(string $type): bool;
}
