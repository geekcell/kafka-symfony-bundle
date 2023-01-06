<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Contracts;

interface SerializerFactory
{
    public function create(): Serializer;
}
