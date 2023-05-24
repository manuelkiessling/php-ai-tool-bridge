<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\JsonSchema;

readonly class JsonSchemaValue
{
    public function __construct(
        public JsonSchemaInfo $jsonSchemaInfo,
        public mixed $value,
        public ?int $objectId = null
    ) {
    }
}
