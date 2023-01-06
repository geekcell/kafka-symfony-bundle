<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Dto;

class GenericEventDto
{
    public mixed $subject;
    public string $eventClass;
    public string $subjectClass;
}
