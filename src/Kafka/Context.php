<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Kafka;

use GeekCell\KafkaBundle\Contracts\TopicNameResolver;
use GeekCell\KafkaBundle\Record\Record;
use GeekCell\KafkaBundle\Serializer\Factory\SerializerFactoryProducer;
use RdKafka\Producer as RdKafkaProducer;

class Context
{
    public function __construct(
        private readonly RdKafkaProducer $producer,
        private readonly SerializerFactoryProducer $factoryProducer,
        private readonly TopicNameResolver $topicNameResolver,
    ) {}

    public function produce(Record $record): void
    {
        $serializer = $this->factoryProducer
            ->produce($record::class)
            ->create();

        $topicName = $this->topicNameResolver->resolveFrom($record);

        $topic = $this->producer->newTopic($topicName);
        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            $serializer->serialize($record),
            $record->getKey(),
        );

        $this->producer->flush(100);
    }
}
