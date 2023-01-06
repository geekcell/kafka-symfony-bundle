<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer\Factory;

use FlixTech\AvroSerializer\Integrations\Symfony\Serializer\AvroSerDeEncoder;
use FlixTech\AvroSerializer\Objects\RecordSerializer as AvroRecordSerializer;
use GeekCell\KafkaBundle\Contracts\Serializer;
use GeekCell\KafkaBundle\Contracts\SerializerFactory;
use GeekCell\KafkaBundle\Serializer\RecordSerializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class RecordSerializerFactory implements SerializerFactory
{
    public function __construct(
        private AvroRecordSerializer $recordSerializer,
    ) {}

    public function create(): Serializer
    {
        $normalizer = new GetSetMethodNormalizer();
        $encoder = new AvroSerDeEncoder($this->recordSerializer);
        $innerSerializer = new SymfonySerializer([$normalizer], [$encoder]);

        return new RecordSerializer($innerSerializer);
    }
}
