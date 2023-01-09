<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Event;

use FlixTech\AvroSerializer\Objects\Schema;
use FlixTech\AvroSerializer\Objects\Schema\RecordType;
use GeekCell\KafkaBundle\Contracts\Event;
use GeekCell\KafkaBundle\Dto\GenericEventDto;
use GeekCell\KafkaBundle\Record\Record;

use function Symfony\Component\String\u;

final class GenericEvent extends Record implements Event
{
    public function __construct(
        protected Record $record,
    ) {}

    public function getKey(): ?string
    {
        return $this->getSubject()->getKey();
    }

    public function getSubject(): Record
    {
        return $this->record;
    }

    public function getNormalizedName(): string
    {
        $shortName = (new \ReflectionClass($this->record))->getShortName();
        return 'event_' . u($shortName)->snake()->toString();
    }

    protected function withFields(RecordType $root): Schema
    {
        return $root
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
}
