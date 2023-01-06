<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Contracts;

interface Serializer
{
    public function serialize(Serializable $object): string;

    public function deserialize(string $data, string $type): Serializable;
}
