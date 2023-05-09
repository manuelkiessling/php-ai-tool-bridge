<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge\JsonSchema;

use ArrayIterator;
use IteratorIterator;

class JsonSchemaValues extends IteratorIterator
{
    public function __construct(JsonSchemaValue ...$jsonSchemaValues)
    {
        parent::__construct(new ArrayIterator($jsonSchemaValues));
        $this->rewind();
    }

    public function current(): JsonSchemaValue
    {
        return parent::current();
    }
}
