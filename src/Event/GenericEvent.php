<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Event;

use FlixTech\AvroSerializer\Objects\Schema;
use GeekCell\KafkaBundle\Contracts\Event;
use GeekCell\KafkaBundle\Contracts\Serializable;
use GeekCell\KafkaBundle\Dto\GenericEventDto;
use GeekCell\KafkaBundle\Record\Record;

use function Symfony\Component\String\u;

final class GenericEvent implements Serializable, Event
{
    public function __construct(
        protected Record $record,
    ) {}

    public function getSubject(): Record
    {
        return $this->record;
    }

    public function getDecoratedSchema(): Schema
    {
        return Schema::record()
            ->name($this->getSchemaName())
            ->field('eventClass', Schema::string())
            ->field('subjectClass', Schema::string())
            ->field('subject', $this->getSubject()->getSchema());
    }

    public function toDto(): GenericEventDto
    {
        $subject = $this->getSubject();

        $dto = new GenericEventDto();
        $dto->eventClass = static::class;
        $dto->subjectClass = $subject::class;
        $dto->subject = $subject;

        return $dto;
    }

    public function getSchemaName(): string
    {
        $shortName = (new \ReflectionClass(static::class))->getShortName();
        return u($shortName)->snake()->toString();
    }
}
