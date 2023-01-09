<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Support\Resolver;

use GeekCell\KafkaBundle\Contracts\TopicNameResolver;
use GeekCell\KafkaBundle\Record\Record;

use function Symfony\Component\String\u;

class DefaultTopicNameResolver implements TopicNameResolver
{
    public function __construct(
        private string $prefix = '',
        private string $suffix = '',
    ) {
    }

    public function resolveFrom(Record $record): string
    {
        $normalizedName = $record->getNormalizedName();
        return sprintf(
            '%s%s%s',
            $this->prefix,
            u($normalizedName)->snake()->toString(),
            $this->suffix
        );
    }
}
