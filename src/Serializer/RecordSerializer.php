<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer;

use FlixTech\AvroSerializer\Integrations\Symfony\Serializer\AvroSerDeEncoder;
use GeekCell\KafkaBundle\Contracts\Serializable;
use GeekCell\KafkaBundle\Record\Record;
use GeekCell\KafkaBundle\Util\AvroUtil;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class RecordSerializer extends Serializer
{
    public function __construct(
        private SymfonySerializer $innerSerializer,
        private AvroUtil $avroUtil,
    ) {}

    protected function doSerialize(Serializable $object): string
    {
        if (!$object instanceof Record) {
            throw new \InvalidArgumentException(
                sprintf(
                    'This serializer only supports sub types of %s, found %s',
                    Record::class,
                    $object::class,
                )
            );
        }

        $schema = $object->getSchema();
        if (!$this->avroUtil->hasName($schema)) {
            throw new \LogicException(
                sprintf(
                    'Could not determine schema name for %s',
                    $object::class,
                )
            );
        }

        $schemaName = $this->avroUtil->getName($schema);
        $encodingSubject = sprintf('%s-value', $schemaName);
        $writersSchema = $schema->parse();

        $context = [
            AvroSerDeEncoder::CONTEXT_ENCODE_WRITERS_SCHEMA => $writersSchema,
            AvroSerDeEncoder::CONTEXT_ENCODE_SUBJECT => $encodingSubject,
        ];

        return $this->innerSerializer->serialize(
            $object,
            AvroSerDeEncoder::FORMAT_AVRO,
            $context,
        );
    }

    public function doDeserialize(string $data, string $type): Record
    {
        return $this->innerSerializer->deserialize(
            $data,
            $type,
            AvroSerDeEncoder::FORMAT_AVRO,
        );
    }

    protected function supports(string $type): bool
    {
        return is_subclass_of($type, Record::class);
    }
}
