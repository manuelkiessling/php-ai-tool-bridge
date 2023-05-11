<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\Example;

use ManuelKiessling\AiToolBridge\ToolFunctionCallResult;
use ManuelKiessling\AiToolBridge\ToolFunctionCallResultStatus;
use ManuelKiessling\AiToolBridge\ToolFunction;

class MyProductSearchToolFunction implements ToolFunction
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
        $jsonAsArray = json_decode($json, true);

        // use $jsonAsArray['searchterm'] when talking to a DB or an API...

        return new ToolFunctionCallResult(
            $this,
            ToolFunctionCallResultStatus::SUCCESS,
            'Found 2 matching products',
            [
                [
                    'id' => 84,
                    'name' => 'Kawaii Chick Egg Separator',
                    'price' => 14.99,
                    'description' => 'Whether you’re a beginner baker or an experienced cook, the Kawaii Chick Egg Separator is a must-have kitchen tool that will make separating eggs a breeze.'
                ],
                [
                    'id' => 2389,
                    'name' => 'BlendJet 2',
                    'price' => 49.99,
                    'description' => 'Imagine the freedom of being able to go anywhere and blend your favorite smoothies, shakes, margaritas, frappés, or baby food without the limitations of a regular blender.'
                ]
            ]
        );
    }
}
