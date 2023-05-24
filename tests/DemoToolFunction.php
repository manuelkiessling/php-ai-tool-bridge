<?php

declare(strict_types=1);

namespace ManuelKiessling\Test\AiToolBridge;

use ManuelKiessling\AiToolBridge\ToolFunctionCallResult;
use ManuelKiessling\AiToolBridge\ToolFunctionCallResultStatus;
use ManuelKiessling\AiToolBridge\ToolFunction;

class DemoToolFunction implements ToolFunction
{
    public function getName(): string
    {
        return 'createUser';
    }

    public function getDescription(): string
    {
        return 'creates a new user account';
    }

    public function invoke(string $json): ToolFunctionCallResult
    {
        $jsonAsArray = json_decode($json, true);

        return new ToolFunctionCallResult(
            $this,
            ToolFunctionCallResultStatus::SUCCESS,
            'A new user account has been created.',
            ['json' => $jsonAsArray],
        );
    }

    public function getInputJsonSchema(): string
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
}
