<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\EventSubscriber;

use GeekCell\KafkaBundle\Contracts\Event;
use GeekCell\KafkaBundle\Event\GenericEvent;
use GeekCell\KafkaBundle\Serializer\GenericEventSerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class KafkaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GenericEventSerializer $serializer,
    ) {}

    private static $subscribedEvents = [];

    public static function getSubscribedEvents(): array
    {
        return self::$subscribedEvents;
    }

    public static function addSubscribedEvent(string $eventClass): void
    {
        self::$subscribedEvents[$eventClass] = 'handleEvent';
    }

    public function handleEvent(Event $event): void
    {
        $genericEvent = new GenericEvent($event->getSubject());
        $serialized = $this->serializer->serialize($genericEvent);

        // TODO: Produce and send message to Kafka
    }
}
