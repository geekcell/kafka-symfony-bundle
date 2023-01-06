<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Contracts;

interface Keyable
{
    public function getKey(): ?string;
}
