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
                        'number' => JsonSchemaType::NUMBER,
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
                        'number' => JsonSchemaType::NUMBER,
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
        JsonSchemaInfos  $jsonSchemaInfos,
        JsonSchemaValues $jsonSchemaValues,
    ): string {
        $resultArray = [];
        $schemaPaths = [];
        $arrayOfObjectsSchemaPaths = [];

        // Traverse schema to generate a structure in result array and create an array of all schema paths
        foreach ($jsonSchemaInfos as $info) {
            $this->traverseSchemaInfos($info, $schemaPaths, $resultArray, $arrayOfObjectsSchemaPaths);
        }

        // Iterate through each jsonSchemaValue and place it in the corresponding place in the result array
        foreach ($jsonSchemaValues as $jsonSchemaValue) {
            if (!in_array($jsonSchemaValue->jsonSchemaInfo->path, $schemaPaths)) {
                throw new InvalidArgumentException(
                    'No corresponding JsonSchemaInfo found for JsonSchemaValue path: ' . $jsonSchemaValue->jsonSchemaInfo->path
                );
            }

            $pathParts = explode('.', $jsonSchemaValue->jsonSchemaInfo->path);
            $parentPath = implode('.', array_slice($pathParts, 0, -1));

            $currentResultArrayLevel = &$resultArray;
            if (in_array($parentPath, $arrayOfObjectsSchemaPaths)) {
                // This is a jsonSchemaValue for an array of objects
                foreach ($pathParts as $part) {
                    // In case it's not the last part, make sure the part exists in current array and dive into it
                    if ($part !== end($pathParts)) {
                        if (!isset($currentResultArrayLevel[$part])) {
                            $currentResultArrayLevel[$part] = [];
                        }
                        $currentResultArrayLevel = &$currentResultArrayLevel[$part];
                    } else {
                        // At the last path part
                        // Make sure the object id exists and set the jsonSchemaValue in the correct place
                        if (!isset($currentResultArrayLevel[$jsonSchemaValue->objectId])) {
                            $currentResultArrayLevel[$jsonSchemaValue->objectId] = [];
                        }
                        $currentResultArrayLevel[$jsonSchemaValue->objectId][$part] = $this->castValue($jsonSchemaValue->value, $jsonSchemaValue->jsonSchemaInfo->type);
                    }
                }
            } else {
                // This is not a jsonSchemaValue for an array of objects
                foreach ($pathParts as $part) {
                    // If it's not the last part, make sure the part exists in current array and dive into it
                    if (!isset($currentResultArrayLevel[$part])) {
                        $currentResultArrayLevel[$part] = [];
                    }
                    $currentResultArrayLevel = &$currentResultArrayLevel[$part];
                }

                // At the end of the path, set the jsonSchemaValue accordingly based on the type of schema jsonSchemaValue
                if ($jsonSchemaValue->jsonSchemaInfo->type === JsonSchemaType::ARRAY) {
                    $currentResultArrayLevel[] = $this->castValue($jsonSchemaValue->value, $jsonSchemaValue->jsonSchemaInfo->subtype);
                } else {
                    $currentResultArrayLevel = $this->castValue($jsonSchemaValue->value, $jsonSchemaValue->jsonSchemaInfo->type);
                }
            }
        }

        // Clean up the resulting array before returning it
        $resultArray = $this->cleanupResultArray($resultArray, $arrayOfObjectsSchemaPaths);

        return json_encode($resultArray);
    }

    private function cleanupResultArray(array $array, array $arrayOfObjectsSchemaInfos): array
    {
        foreach ($array as $key => $value) {
            if (in_array($key, $arrayOfObjectsSchemaInfos) && is_array($value)) {
                // If it's an array of objects, re-index and remove null values
                $value = array_values(array_filter($value, function ($item) {
                    return $item !== null;
                }));
                $array[$key] = $value;
            } elseif (is_array($value)) {
                // If it's a nested array, recurse
                $array[$key] = $this->cleanupResultArray($value, $arrayOfObjectsSchemaInfos);
            }
        }

        return $array;
    }

    private function traverseSchemaInfos(
        JsonSchemaInfo $info,
        array &$schemaPaths,
        array &$result,
        array &$arrayOfObjectsSchemaInfos
    ): void {
        $schemaPaths[] = $info->path;
        $pathParts = explode('.', $info->path);
        $current = &$result;
        foreach ($pathParts as $part) {
            if (!isset($current[$part])) {
                $current[$part] = $info->type === JsonSchemaType::ARRAY ? [] : null;
            }
            $current = &$current[$part];
        }

        // Store the path of the schema info if it's an array of objects
        if ($info->type === JsonSchemaType::ARRAY && $info->subtype === JsonSchemaType::OBJECT) {
            $arrayOfObjectsSchemaInfos[] = $info->path;
        }

        // Traverse children if the object type is an array with a subtype of 'object'
        if ($info->type === JsonSchemaType::ARRAY && $info->subtype === JsonSchemaType::OBJECT && $info->arrayObjectsJsonSchemaInfo !== null) {
            foreach ($info->arrayObjectsJsonSchemaInfo as $arrayObjectJsonSchemaInfo) {
                $this->traverseSchemaInfos($arrayObjectJsonSchemaInfo, $schemaPaths, $result, $arrayOfObjectsSchemaInfos);
            }
        }
    }

    private function castValue(mixed $value, JsonSchemaType $type): mixed {
        return match ($type) {
            JsonSchemaType::INTEGER => (int)$value,
            JsonSchemaType::NUMBER => (float)$value,
            JsonSchemaType::STRING => trim((string)$value),
            JsonSchemaType::BOOLEAN => trim(mb_strtolower($value)) === 'false' ? false : (bool)$value,
            default => $value
        };
    }
}
