<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Serializer\Factory;

use FlixTech\AvroSerializer\Integrations\Symfony\Serializer\AvroSerDeEncoder;
use FlixTech\AvroSerializer\Objects\RecordSerializer as AvroRecordSerializer;
use GeekCell\KafkaBundle\Contracts\Serializer;
use GeekCell\KafkaBundle\Contracts\SerializerFactory;
use GeekCell\KafkaBundle\Serializer\RecordSerializer;
use GeekCell\KafkaBundle\Util\AvroUtil;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class RecordSerializerFactory implements SerializerFactory
{
    public function __construct(
        private AvroRecordSerializer $recordSerializer,
        private AvroUtil $avroUtil,
        private array $defaults = [],
    ) {}

    public function create(): Serializer
    {
        $normalizer = new PropertyNormalizer();
        $encoder = new AvroSerDeEncoder($this->recordSerializer);
        $innerSerializer = new SymfonySerializer([$normalizer], [$encoder]);

        return new RecordSerializer(
            $innerSerializer,
            $this->avroUtil,
            $this->defaults,
        );
    }
}
