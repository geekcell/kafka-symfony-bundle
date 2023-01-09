<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\Util;

use FlixTech\AvroSerializer\Objects\Schema;

class AvroUtil
{
    public function isRecord(Schema $schema): bool
    {
        $type = \AvroUtil::array_value(
            $schema->serialize(),
            \AvroSchema::TYPE_ATTR,
        );

        return $type === \AvroSchema::RECORD_SCHEMA;
    }

    public function hasNamespace(Schema $schema): bool
    {
        if (!$this->isRecord($schema)) {
            return false;
        }

        $namespace = \AvroUtil::array_value(
            $schema->serialize(),
            \AvroSchema::NAMESPACE_ATTR,
        );

        return $namespace !== null;
    }

    public function hasName(Schema $schema): bool
    {
        if (!$this->isRecord($schema)) {
            return false;
        }

        $name = \AvroUtil::array_value(
            $schema->serialize(),
            \AvroSchema::NAME_ATTR,
        );

        return $name !== null;
    }

    public function getNamespace(Schema $schema): ?string
    {
        if (!$this->isRecord($schema)) {
            return null;
        }

        return \AvroUtil::array_value(
            $schema->serialize(),
            \AvroSchema::NAMESPACE_ATTR,
        );
    }

    public function getName(Schema $schema): ?string
    {
        if (!$this->isRecord($schema)) {
            return null;
        }

        return \AvroUtil::array_value(
            $schema->serialize(),
            \AvroSchema::NAME_ATTR,
        );
    }
}
