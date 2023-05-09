<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge\JsonSchema;

use JsonException;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class JsonSchemaParser
{
    /**
     * @throws JsonException
     */
    public function getJsonSchemaInfos(string $jsonSchema): JsonSchemaInfos
    {
        $schema = json_decode($jsonSchema, true, 512, JSON_THROW_ON_ERROR);

        $result = [];
        $this->processSchema($schema['properties'], '', $result);

        return new JsonSchemaInfos(...$result);
    }

    private function processSchema(array $schema, string $pathPrefix, array &$result): void
    {
        foreach ($schema as $key => $value) {
            $currentPath = $pathPrefix ? "{$pathPrefix}.{$key}" : $key;

            if (is_array($value) && (isset($value['type']) || isset($value['enum']))) {
                if (isset($value['type'])) {
                    $type = match ($value['type']) {
                        'string' => JsonSchemaType::STRING,
                        'integer' => JsonSchemaType::INTEGER,
                        'float' => JsonSchemaType::FLOAT,
                        'array' => JsonSchemaType::ARRAY,
                        'boolean' => JsonSchemaType::BOOLEAN,
                        'object' => JsonSchemaType::OBJECT
                    };
                } else {
                    $type = JsonSchemaType::ENUM;
                }

                if ($type === JsonSchemaType::ARRAY && isset($value['items']['type'])) {
                    $subtype = match ($value['items']['type']) {
                        'string' => JsonSchemaType::STRING,
                        'integer' => JsonSchemaType::INTEGER,
                        'float' => JsonSchemaType::FLOAT,
                        'array' => JsonSchemaType::ARRAY,
                        'boolean' => JsonSchemaType::BOOLEAN,
                        'object' => JsonSchemaType::OBJECT
                    };
                } else {
                    $subtype = null;
                }

                if ($type === JsonSchemaType::OBJECT && isset($value['properties'])) {
                    $this->processSchema($value['properties'], $currentPath, $result);
                } else {
                    $result[] = new JsonSchemaInfo(
                        path: $currentPath,
                        type: $type,
                        subtype: $subtype,
                        enumValues: $value['enum'] ?? null,
                    );
                }
            }
        }
    }
}
