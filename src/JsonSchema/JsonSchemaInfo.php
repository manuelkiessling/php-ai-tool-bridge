<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\JsonSchema;

readonly class JsonSchemaInfo
{
    public function __construct(
        public string          $path,
        public JsonSchemaType  $type,
        public ?JsonSchemaType $subtype = null,
        public ?array          $enumValues = null,
    ) {
    }
}
