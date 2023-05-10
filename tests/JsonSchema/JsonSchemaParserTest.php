<?php

declare(strict_types=1);

namespace ManuelKiessling\Test\AiToolBridge\JsonSchema;

use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaInfo;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaInfos;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaParser;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaType;
use PHPUnit\Framework\TestCase;

class JsonSchemaParserTest extends TestCase
{
    private function getJsonSchema(): string
    {
        return <<<'JSON'
{
  "type": "object",
  "properties": {
    "name": {
      "type": "string",
      "minLength": 1,
      "maxLength": 64,
      "pattern": "^[a-zA-Z0-9\\-]+(\\s[a-zA-Z0-9\\-]+)*$"
    },
    "age": {
      "type": "integer",
      "minimum": 18,
      "maximum": 100
    },
    "location": {
      "type": "object",
      "properties": {
        "country": {
          "enum": ["US", "CA", "GB"]
        },
        "address": {
          "type": "string",
          "maxLength": 128
        }
      },
      "required": ["country", "address"],
      "additionalProperties": false
    },
    "interests": {
      "type": "array",
      "minItems": 3,
      "maxItems": 100,
      "uniqueItems": true,
      "items": {
        "type": "string",
        "maxLength": 64
      }
    }
  }
}
JSON;
    }

    public function testJsonSchemaInfos(): void
    {
        $jsonSchemaInfos = new JsonSchemaInfos(...[
            new JsonSchemaInfo('foo', JsonSchemaType::STRING, null),
        ]);

        $this->assertSame($jsonSchemaInfos->current()->path, 'foo');

        $this->assertEquals(
            new JsonSchemaInfo('foo', JsonSchemaType::STRING, null, null),
            $jsonSchemaInfos->current()
        );
    }

    public function testGetJsonSchemaInfosLarge(): void
    {
        $jsonSchema = $this->getJsonSchema();

        $parser = new JsonSchemaParser();

        $results = $parser->getJsonSchemaInfos($jsonSchema);

        $expectedResults = [
            new JsonSchemaInfo('name', JsonSchemaType::STRING, null, null),
            new JsonSchemaInfo('age', JsonSchemaType::INTEGER, null, null),
            new JsonSchemaInfo('location.country', JsonSchemaType::ENUM, null, ['US', 'CA', 'GB']),
            new JsonSchemaInfo('location.address', JsonSchemaType::STRING, null, null),
            new JsonSchemaInfo('interests', JsonSchemaType::ARRAY, JsonSchemaType::STRING, null),
        ];

        foreach ($results as $key => $result) {
            $this->assertEquals($expectedResults[$key], $result);
        }
    }
}
