<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer;

use FlixTech\AvroSerializer\Integrations\Symfony\Serializer\AvroSerDeEncoder;
use GeekCell\KafkaBundle\Contracts\Serializable;
use GeekCell\KafkaBundle\Dto\GenericEventDto;
use GeekCell\KafkaBundle\Event\GenericEvent;
use GeekCell\KafkaBundle\Record\Record;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class GenericEventSerializer extends Serializer
{
    public function __construct(
        private SymfonySerializer $innerSerializer,
        private RecordSerializer $recordSerializer,
    ) {}

    protected function doSerialize(Serializable $object): string
    {
        if (!$object instanceof GenericEvent) {
            throw new \LogicException(
                sprintf(
                    'This Serializer only supports %s',
                    GenericKafkaEvent::class,
                )
            );
        }

        $encodingSubject = sprintf('%s-value', $object->getSchemaName());
        $writersSchema = $object->getDecoratedSchema()->parse();

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

    protected function doDeserialize(string $data, string $type): Serializable
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
