<?php

declare(strict_types=1);

namespace ManuelKiessling\Test\AiToolBridge\JsonSchema;

use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaInfo;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaInfos;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaParser;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaType;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaValue;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaValues;
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
    },
    "hobbies": {
      "type": "array",
      "items": {
        "type": "object",
        "required": [ "name", "active" ],
        "properties": {
          "name": {
            "type": "string",
            "description": "The name of the hobby."
          },
          "active": {
            "type": "boolean",
            "description": "Is this hobby currently pursued?"
          }
        }
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
            new JsonSchemaInfo(
                'name',
                JsonSchemaType::STRING,
                null,
                null,
                null
            ),

            new JsonSchemaInfo(
                'age',
                JsonSchemaType::INTEGER,
                null,
                null,
                null
            ),

            new JsonSchemaInfo(
                'location.country',
                JsonSchemaType::ENUM,
                null,
                ['US', 'CA', 'GB'],
                null
            ),

            new JsonSchemaInfo(
                'location.address',
                JsonSchemaType::STRING,
                null,
                null,
                null
            ),

            new JsonSchemaInfo(
                'interests',
                JsonSchemaType::ARRAY,
                JsonSchemaType::STRING,
                null,
                null
            ),

            new JsonSchemaInfo(
                'hobbies',
                JsonSchemaType::ARRAY,
                JsonSchemaType::OBJECT,
                null,
                [
                    new JsonSchemaInfo(
                        'name',
                        JsonSchemaType::STRING,
                        null,
                        null
                    ),
                    new JsonSchemaInfo(
                        'active',
                        JsonSchemaType::BOOLEAN,
                        null,
                        null
                    ),
                ]
            ),
        ];

        foreach ($results as $key => $result) {
            $this->assertEquals($expectedResults[$key], $result);
        }
    }

    public function testGenerateJsonFromSchema(): void
    {
        $jsonSchemaInfoName = new JsonSchemaInfo(
            'name',
            JsonSchemaType::STRING,
            null,
            null,
            null
        );

        $jsonSchemaInfoAge = new JsonSchemaInfo(
            'age',
            JsonSchemaType::INTEGER,
            null,
            null,
            null
        );

        $jsonSchemaInfoInterests = new JsonSchemaInfo(
            'interests',
            JsonSchemaType::ARRAY,
            JsonSchemaType::STRING,
            null,
            null
        );

        $jsonSchemaInfos = new JsonSchemaInfos(...[
            $jsonSchemaInfoName,
            $jsonSchemaInfoAge,
            $jsonSchemaInfoInterests,
        ]);

        $jsonSchemaValues = new JsonSchemaValues(...[
            new JsonSchemaValue(
                $jsonSchemaInfoName,
                'John Doe'
            ),

            new JsonSchemaValue(
                $jsonSchemaInfoAge,
                '26'
            ),

            new JsonSchemaValue(
                $jsonSchemaInfoInterests,
                'Painting'
            ),

            new JsonSchemaValue(
                $jsonSchemaInfoInterests,
                'Horse Riding'
            ),
        ]);

        $parser = new JsonSchemaParser();

        $this->assertJsonStringEqualsJsonString(
            '{"name":"John Doe","age":26,"interests":["Painting","Horse Riding"]}',
            $parser->generateJsonFromSchema($jsonSchemaInfos, $jsonSchemaValues)
        );
    }
}
