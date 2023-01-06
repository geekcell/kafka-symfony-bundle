<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer;

use FlixTech\AvroSerializer\Integrations\Symfony\Serializer\AvroSerDeEncoder;
use GeekCell\KafkaBundle\Contracts\Serializable;
use GeekCell\KafkaBundle\Record\Record;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class RecordSerializer extends Serializer
{
    public function __construct(
        private SymfonySerializer $innerSerializer,
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

        $schemaName = $this->determineSchemaName($object);
        if (!$schemaName) {
            throw new \LogicException(
                sprintf(
                    'Could not determine schema name for %s',
                    $object::class,
                )
            );
        }

        $encodingSubject = sprintf('%s-value', $schemaName);
        $writersSchema = $object::getSchema()->parse();

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

    public function doDeserialize(string $data, string $type): Serializable
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

    protected function determineSchemaName(Record $record): ?string
    {
        $schema = $record::getSchema();
        $avroJson = $schema->serialize();
        $type = \AvroUtil::array_value($avroJson, \AvroSchema::TYPE_ATTR);
        if (!\AvroSchema::is_named_type($type)) {
            return null;
        }

        return \AvroUtil::array_value($avroJson, \AvroSchema::NAME_ATTR);
    }
}
