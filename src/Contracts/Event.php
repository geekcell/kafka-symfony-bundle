<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Contracts;

use GeekCell\KafkaBundle\Record\Record;

interface Event
{
    public function getSubject(): Record;
}
