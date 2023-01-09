<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer;

use FlixTech\AvroSerializer\Integrations\Symfony\Serializer\AvroSerDeEncoder;
use GeekCell\KafkaBundle\Dto\GenericEventDto;
use GeekCell\KafkaBundle\Event\GenericEvent;
use GeekCell\KafkaBundle\Record\Record;
use GeekCell\KafkaBundle\Util\AvroUtil;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class GenericEventSerializer extends Serializer
{
    private SymfonySerializer $innerSerializer;

    private RecordSerializer $recordSerializer;

    public function __construct(
        SymfonySerializer $innerSerializer,
        RecordSerializer $recordSerializer,
        AvroUtil $avroUtil,
        array $defaults = [],
    ) {
        parent::__construct($avroUtil, $defaults);

        $this->innerSerializer = $innerSerializer;
        $this->recordSerializer = $recordSerializer;
    }

    protected function doSerialize(Record $object): string
    {
        if (!$object instanceof GenericEvent) {
            throw new \LogicException(
                sprintf(
                    'This Serializer only supports %s',
                    GenericKafkaEvent::class,
                )
            );
        }

        $schema = $this->fixNamespace($object->getSchema());
        if (!$this->avroUtil->hasName($object->getSchema())) {
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
            $object->toDto(),
            AvroSerDeEncoder::FORMAT_AVRO,
            $context,
        );
    }

    protected function doDeserialize(string $data, string $type): Record
    {
        /** @var KafkaDto $dto */
        $dto = $this->innerSerializer->deserialize(
            $data,
            GenericEventDto::class,
            AvroSerDeEncoder::FORMAT_AVRO,
        );

        if (
            !class_exists($dto->subjectClass) ||
            !is_subclass_of($dto->subjectClass, Record::class)) {
            throw new \LogicException(
                sprintf(
                    'The event subject must be a subclass of %s, found %s',
                    Record::class,
                    $dto->subjectClass,
                ),
            );
        }

        /** @var Record $subject */
        $subject = $this->recordSerializer
            ->deserialize($dto->subject, $dto->subjectClass);

        return new GenericEvent($subject);
    }

    protected function supports(string $type): bool
    {
        return $type === GenericEvent::class;
    }
}
