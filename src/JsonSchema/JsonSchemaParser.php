<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\JsonSchema;

use InvalidArgumentException;
use JsonException;

use function explode;
use function in_array;
use function is_array;
use function json_decode;
use function json_encode;

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

                $arrayObjectsJsonSchemaInfo = null;

                if ($type === JsonSchemaType::ARRAY && $subtype === JsonSchemaType::OBJECT && isset($value['items']['properties'])) {
                    $arrayObjectsJsonSchemaInfo = [];
                    $this->processSchema($value['items']['properties'], $key, $arrayObjectsJsonSchemaInfo);
                }

                if ($type === JsonSchemaType::OBJECT && isset($value['properties'])) {
                    $this->processSchema($value['properties'], $currentPath, $result);
                } else {
                    $result[] = new JsonSchemaInfo(
                        path: $currentPath,
                        type: $type,
                        subtype: $subtype,
                        enumValues: $value['enum'] ?? null,
                        arrayObjectsJsonSchemaInfo: $arrayObjectsJsonSchemaInfo
                    );
                }
            }
        }
    }

    public function generateJsonFromSchema(
        JsonSchemaInfos $schemaInfos,
        JsonSchemaValues $values,
    ): string {
        $result = [];
        $schemaPaths = [];

        // Create an array of all schema paths
        foreach ($schemaInfos as $info) {
            $schemaPaths[] = $info->path;

            $pathParts = explode('.', $info->path);
            $current = &$result;
            foreach ($pathParts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = $info->type === JsonSchemaType::ARRAY ? [] : null;
                }
                $current = &$current[$part];
            }
        }

        foreach ($values as $value) {
            if (!in_array($value->jsonSchemaInfo->path, $schemaPaths)) {
                throw new InvalidArgumentException(
                    'No corresponding JsonSchemaInfo found for JsonSchemaValue path: ' . $value->jsonSchemaInfo->path
                );
            }

            $pathParts = explode('.', $value->jsonSchemaInfo->path);

            $current = &$result;
            foreach ($pathParts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }

            if ($value->jsonSchemaInfo->type === JsonSchemaType::ARRAY) {
                $current[] = $this->castValue($value->value, $value->jsonSchemaInfo->subtype);
            } else {
                $current = $this->castValue($value->value, $value->jsonSchemaInfo->type);
            }
        }

        return json_encode($result);
    }

    private function castValue(mixed $value, JsonSchemaType $type): mixed {
        return match ($type) {
            JsonSchemaType::INTEGER => (int) $value,
            JsonSchemaType::FLOAT => (float) $value,
            JsonSchemaType::STRING => (string) $value,
            JsonSchemaType::BOOLEAN => (bool) $value,
            default => $value
        };
    }
}
