<?php

declare(strict_types=1);

namespace ManuelKiessling\Test\GptToolBridge;

use ManuelKiessling\GptToolBridge\ToolBridgeFunctionCallResult;
use ManuelKiessling\GptToolBridge\ToolBridgeFunctionCallResultStatus;
use ManuelKiessling\GptToolBridge\ToolBridgeFunctionDefinition;

class DemoToolBridgeFunctionDefinition implements ToolBridgeFunctionDefinition
{
    public function getName(): string
    {
        return 'createUser';
    }

    public function getDescription(): string
    {
        return 'creates a new user account';
    }

    public function invoke(string $json): ToolBridgeFunctionCallResult
    {
        $jsonAsArray = json_decode($json, true);

        return new ToolBridgeFunctionCallResult(
            $this,
            ToolBridgeFunctionCallResultStatus::SUCCESS,
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
    }
  }
}
JSON;
    }

    public function getOutputJsonSchema(): string
    {
        return <<<'JSON'
{
  "type": "object",
  "properties": {
    "userId": {
      "type": "string"
    }
  },
  "required": [
    "userId"
  ]
}
JSON;
    }
}
