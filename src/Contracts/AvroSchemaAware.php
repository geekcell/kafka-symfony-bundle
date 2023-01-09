<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Contracts;

use FlixTech\AvroSerializer\Objects\Schema;

interface AvroSchemaAware
{
    public function getSchema(): Schema;
}
