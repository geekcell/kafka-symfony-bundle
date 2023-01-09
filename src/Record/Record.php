<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Record;

use FlixTech\AvroSerializer\Objects\Schema;
use FlixTech\AvroSerializer\Objects\Schema\RecordType;
use GeekCell\KafkaBundle\Contracts\AvroSchemaAware;
use GeekCell\KafkaBundle\Contracts\Keyable;
use GeekCell\KafkaBundle\Contracts\Serializable;

use function Symfony\Component\String\u;

abstract class Record implements AvroSchemaAware, Keyable, Serializable
{
    public function getSchema(): Schema
    {
        $root = Schema::record()
            ->name($this->getNormalizedName());

        return $this->withFields($root);
    }

    public function getNormalizedName(): string
    {
        $shortName = (new \ReflectionClass($this))->getShortName();
        return u($shortName)->camel()->title()->toString();
    }

    abstract protected function withFields(RecordType $root): Schema;
}
