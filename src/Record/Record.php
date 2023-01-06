<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Record;

use GeekCell\KafkaBundle\Contracts\AvroSchemaAware;
use GeekCell\KafkaBundle\Contracts\Keyable;
use Serializable;

abstract class Record implements  AvroSchemaAware, Keyable, Serializable
{
}
