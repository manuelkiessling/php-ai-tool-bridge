<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\JsonSchema;

use ArrayIterator;
use IteratorIterator;

class JsonSchemaInfos extends IteratorIterator
{
    public function __construct(JsonSchemaInfo ...$jsonSchemaInfos)
    {
        parent::__construct(new ArrayIterator($jsonSchemaInfos));
        $this->rewind();
    }

    public function current(): JsonSchemaInfo
    {
        return parent::current();
    }
}
