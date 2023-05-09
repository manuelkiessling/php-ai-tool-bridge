<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge\JsonSchema;

readonly class JsonSchemaValue
{
    public function __construct(
        public JsonSchemaInfo $jsonSchemaInfo,
        public mixed $value,
    ) {
    }
}
