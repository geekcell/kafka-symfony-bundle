<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Util;

use FlixTech\AvroSerializer\Objects\Schema;
use FlixTech\AvroSerializer\Objects\Schema\RecordType;

class AvroUtil
{
    public function getNamespace(Schema $schema): ?string
    {
        if (!$schema instanceof RecordType) {
            return null;
        }

        return \AvroUtil::array_value(
            $schema->serialize(),
            \AvroSchema::NAMESPACE_ATTR,
        );
    }

    public function hasNamespace(Schema $schema): bool
    {
        return $this->getNamespace($schema) !== null;
    }

    public function getName(Schema $schema): ?string
    {
        if (!$schema instanceof RecordType) {
            return null;
        }

        return \AvroUtil::array_value(
            $schema->serialize(),
            \AvroSchema::NAME_ATTR,
        );
    }

    public function hasName(Schema $schema): bool
    {
        return $this->getName($schema) !== null;
    }
}
