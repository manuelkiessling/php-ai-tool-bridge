<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge;

use Exception;
use ManuelKiessling\GptToolBridge\JsonSchema\JsonSchemaParser;

use function implode;
use function is_null;
use function mb_stristr;
use function sizeof;

class GptToolBridge
{
    /**
     * @var ToolBridgeFunctionDefinition[]
     */
    private array $functionDefinitions = [];

    public function registerFunctionDefinition(ToolBridgeFunctionDefinition $functionDefinition): void
    {
        $this->functionDefinitions[] = $functionDefinition;
    }

    public function containsFunctionCall(string $message): bool
    {
        return mb_stristr($message, '|CallToolBridgeFunction|') !== false;
    }

    public function getFunctionDefinition(string $message): ?ToolBridgeFunctionDefinition
    {
        foreach ($this->functionDefinitions as $toolBridgeDefinition) {
            if (mb_stristr($message, "|CallToolBridgeFunction|{$toolBridgeDefinition->getName()}|") !== false) {
                return $toolBridgeDefinition;
            }
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function queryTool(string $message): ToolBridgeFunctionCallResult
    {
        $definition = $this->getFunctionDefinition($message);
        if (is_null($definition)) {
            throw new Exception();
        }

        return $definition->invoke();
    }

    /**
     * @throws Exception
     */
    public function getToolBridgePrompt(): string
    {
        if (sizeof($this->functionDefinitions) === 0) {
            throw new Exception('Need at least one registered function definition.');
        }

        $jsonSchemaParser = new JsonSchemaParser();

        $prompt = <<<'PROMPT'
When you found out what I want to do and you have gathered all information from me
to put my current intention into action,
you can make me use an external tool called 'GptBackendBridge' for you.
The tool has the following functions:


PROMPT;

        $functionNames = [];
        foreach ($this->functionDefinitions as $functionDefinition) {
            $functionNames[] = "|GptBackendBridge|{$functionDefinition->getName()}|";
            $schemaInfos = $jsonSchemaParser->getJsonSchemaInfos($functionDefinition->getInputJsonSchema());

            $prompt .= "Function '{$functionDefinition->getName()}': {$functionDefinition->getDescription()}.";
            $prompt .= "\n";
            $prompt .= "When using function '{$functionDefinition->getName()}' for you, I will need to call it with the following data:";
            $prompt .= "\n";
            $prompt .= "\n";

            foreach ($schemaInfos as $schemaInfoEntry) {
                $prompt .= "{$schemaInfoEntry->path} (of type {$schemaInfoEntry->type->name})";
                $prompt .= "\n";
            }
        }

        $prompt .= "\n";
        $prompt .= <<<'PROMPT'
Whenever you want to use one of the tool functions,
you need to simply write a single message starting with '|GptBackendBridge|' followed by the function name,
like this:
PROMPT;

        $prompt .= ' ' . implode(' or ', $functionNames);

        $prompt .= <<<'PROMPT'
.
This message must not contain any other text besides the |GptBackendBridge| marker
followed by the exact tool function name and the final | character.

When you write such a tool-function-usage message,
I will follow up with questions regarding the values which you want me to use when using the tool function.
When I ask you to provide a value, you must answer only with the value I ask for,
and nothing else! We will talk about values step-by-step, until I have all the values I need to use the tool for you.

A value question from me might look like this:


PROMPT;

        $schemaInfos = $jsonSchemaParser->getJsonSchemaInfos(
            $this->functionDefinitions[0]->getInputJsonSchema(),
        );

        $schemaInfo = $schemaInfos->current();

        $prompt .= "Value for parameter '{$schemaInfo->path}' (of type {$schemaInfo->type->name}):";
        $prompt .= "\n";
        $prompt .= "\n";

        $prompt .= <<<'PROMPT'
Once I have gathered all parameter values this way,
I will then call the tool function accordingly, and then provide to you the result of running the tool function,
presenting the result data as a JSON object with top-level fields 'success' (of type boolean), 'message' (of type string),
and 'data', with 'success' being true if the tool function call was successful, 'message' providing general information about the
tool function call, and 'data' providing detailed information that you can extract and use in the further course of our dialogue.
Example:


PROMPT;

        $prompt .= "|GptBackendBridge|{$this->functionDefinitions[0]->getName()}|Result|:";
        $prompt .= "\n";

        $prompt .= <<<'PROMPT'
{
  "success": true,
  "message": "Lorem ipsum dolor sit amet, consetetur sadipscing elitr...",
  "data": {
    "foo": "bar",
    "something": true,
    "baz": 1
  }
}

PROMPT;

        return $prompt;
    }
}
