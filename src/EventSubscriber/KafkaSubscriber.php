<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\EventSubscriber;

use GeekCell\KafkaBundle\Contracts\Event;
use GeekCell\KafkaBundle\Event\GenericEvent;
use GeekCell\KafkaBundle\Kafka\Context as KafkaContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class KafkaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private KafkaContext $context,
    ) {}

    private static $subscribedEvents = [];

    public static function getSubscribedEvents(): array
    {
        return self::$subscribedEvents;
    }

    public static function addSubscribedEvent(string $eventClass): void
    {
        self::$subscribedEvents[$eventClass] = 'onEvent';
    }

    public function onEvent(Event $event): void
    {
        if (!($event instanceof GenericEvent)) {
            $event = new GenericEvent($event->getSubject(), $event::class);
        }

        $this->context->produce($event);
    }
}
