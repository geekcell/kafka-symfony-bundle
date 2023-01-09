<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Contracts;

use GeekCell\KafkaBundle\Record\Record;

interface Serializer
{
    public function serialize(Record $object): string;

    public function deserialize(string $data, string $type): Record;
}
