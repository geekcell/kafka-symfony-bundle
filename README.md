# Symfony Bundle for Apache Kafka

An opinionated Symfony bundle for a painless integration of [Apache Kafka](https://kafka.apache.org/).

## Requirements

- PHP 8 or higher
- [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) extension installed
- Apache Kafka broker
- Confluent [Schema Registry](https://github.com/confluentinc/schema-registry) for Kafka

## Installation

To use this bundle, require it in Composer

```bash
composer require geekcell/kafka-bundle
```

## Quickstart

Inherit from `Record` to define records you want to send to Kafka.

```php
use GeekCell\KafkaBundle\Record;
use FlixTech\AvroSerializer\Objects\Schema;

class OrderDto extends Record
{
    public int $id;
    public int $productId;
    public int $customerId;
    public int $quantity;
    public float $total;
    public string $status = 'PENDING';

    public function getKey(): ?string
    {
        // Nullable; if provided it will be used as message key
        // to preserve message ordering.
        return sprintf('order_%s', $this->id);
    }

    protected function withFields(RecordType $root): Schema
    {
        // See for examples:
        // https://github.com/flix-tech/avro-serde-php/tree/master/test/Objects/Schema
        $root
            ->field('id', Schema::int())
            ->field('productId', Schema::int())
            ->field('customerId', Schema::int())
            ->field('quantity', Schema::int())
            ->field('total', Schema::float())
            ->field(
                'status', 
                Schema::enum()
                    ->name('OrderStatusEnum')
                    ->symbols(...['PENDING', 'PAID', 'SHIPPED', 'CANCELLED'])
                    ->default('PENDING'),
            );

        return $root;
    }
}
```

Create an event, which implements the `Event` contract and returns the above record as _subject_.

```php
use GeekCell\KafkaBundle\Contracts\Event;

class OrderPlacedEvent implements Event
{
    public function __construct(
        private OrderDto $orderDto,
    ) {
    }

    public function getSubject(): Record
    {
        return $this->orderDto;
    }
}
```

If you dispatch `Event` via the standard Symfony event dispatcher, it will be automatically be serialized into Avro format, registered, send to Kafka based on your configuration.

```php
$this->eventDispatcher->dispatch(new OrderPlacedEvent($orderDto));
```

## Bundle Configuration Example

```yaml
geek_cell_kafka:
    avro:
        schema_registry_url: 'http://schemaregistry:8081'
        schemas:
            defaults:
                namespace: 'com.acme.avro'
    events:
        lookup: # Look up events in the following directories
            - 'src/Event'

    kafka:
        brokers: 'broker:9091,broker:9092'
        global:
            # Global config params for librdkafka (not pre-validated)
            # https://github.com/confluentinc/librdkafka/blob/master/CONFIGURATION.md#global-configuration-properties
        topic:
            # Topic config params for librdkafka (not pre-validated)
            # https://github.com/confluentinc/librdkafka/blob/master/CONFIGURATION.md#topic-configuration-properties
```
