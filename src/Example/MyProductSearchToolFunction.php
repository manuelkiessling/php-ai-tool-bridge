<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\Example;

use ManuelKiessling\AiToolBridge\ToolFunctionCallResult;
use ManuelKiessling\AiToolBridge\ToolFunctionCallResultStatus;
use ManuelKiessling\AiToolBridge\ToolFunctionDefinition;

class MyProductSearchToolFunction implements ToolFunctionDefinition
{
    public function getName(): string
    {
        return 'productSearch';
    }

    public function getDescription(): string
    {
        return 'allows to search the product catalogue and retrieve information about products';
    }

    public function getInputJsonSchema(): string
    {
        return <<<'JSON'
{
  "type": "object",
  "properties": {
    "searchterms": {
      "type": "string"
    }
  },
  "required": [
    "searchterms"
  ]
}
JSON;
    }

    public function invoke(string $json): ToolFunctionCallResult
    {
        return new ToolFunctionCallResult(
            $this,
            ToolFunctionCallResultStatus::SUCCESS,
            '',
            []
        );
    }
}
