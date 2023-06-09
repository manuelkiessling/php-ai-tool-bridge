<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge;

use Exception;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaInfo;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaParser;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaType;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaValue;
use ManuelKiessling\AiToolBridge\JsonSchema\JsonSchemaValues;

use function implode;
use function is_null;
use function mb_stristr;
use function sizeof;

readonly class AiToolBridge
{
    /** @param ToolFunction[] $functionDefinitions */
    public function __construct(
        private AiAssistantMessenger $aiAssistant,
        private array                $functionDefinitions,
    ) {
    }

    public function containsFunctionCall(string $message): bool
    {
        return $this->getFunctionDefinition($message) !== null;
    }

    public function handleAssistantMessage(string $message): ?ToolFunctionCallResult
    {
        if (!$this->containsFunctionCall($message)) {
            return null;
        }

        $functionDefinition = $this->getFunctionDefinition($message);

        if (is_null($functionDefinition)) {
            return null;
        }

        $jsonSchemaParser = new JsonSchemaParser();
        $jsonSchemaInfos = $jsonSchemaParser->getJsonSchemaInfos($functionDefinition->getInputJsonSchema());

        $values = [];
        foreach ($jsonSchemaInfos as $jsonSchemaInfo) {

            if ($jsonSchemaInfo->type === JsonSchemaType::ARRAY && $jsonSchemaInfo->subtype !== JsonSchemaType::OBJECT) {
                $index = 0;
                while (true) {

                    $response = $this->aiAssistant->getResponseForToolFunction(
                        "Value for entry #$index of array '{$jsonSchemaInfo->path}' (of type {$jsonSchemaInfo->subtype->name} - answer with 'AiToolBridgeNone' if all values for this array have been provided):",
                    );

                    if (mb_stristr($response, 'AiToolBridgeNone')) {
                        break;
                    }

                    $values[] = new JsonSchemaValue(
                        $jsonSchemaInfo,
                        $response,
                    );

                    $index++;
                }
            } elseif ($jsonSchemaInfo->type === JsonSchemaType::ARRAY && $jsonSchemaInfo->subtype === JsonSchemaType::OBJECT) {
                $index = 0;
                while (true) {

                    /** @var JsonSchemaInfo $arrayObjectSchemaInfo */
                    foreach ($jsonSchemaInfo->arrayObjectsJsonSchemaInfo as $arrayObjectSchemaInfo) {
                        $response = $this->aiAssistant->getResponseForToolFunction(
                            "Value for field '{$arrayObjectSchemaInfo->path}' of entry #$index of array '{$jsonSchemaInfo->path}' (of type {$arrayObjectSchemaInfo->type->name} - answer with 'AiToolBridgeNone' if all values for this array have been provided):",
                        );

                        if (mb_stristr($response, 'AiToolBridgeNone')) {
                            break 2;
                        }

                        $values[] = new JsonSchemaValue(
                            $arrayObjectSchemaInfo,
                            $response,
                            $index
                        );
                    }

                    $index++;
                }
            } else {
                $values[] = new JsonSchemaValue(
                    $jsonSchemaInfo,
                    $this->aiAssistant->getResponseForToolFunction(
                        "Value for parameter '{$jsonSchemaInfo->path}' (of type {$jsonSchemaInfo->type->name}):",
                    ),
                );
            }
        }

        $jsonSchemaValues = new JsonSchemaValues(...$values);

        $json = $jsonSchemaParser->generateJsonFromSchema($jsonSchemaInfos, $jsonSchemaValues);

        return $functionDefinition->invoke($json);
    }

    public function getFunctionDefinition(string $message): ?ToolFunction
    {
        foreach ($this->functionDefinitions as $functionDefinition) {
            if (mb_stristr($message, "|CallToolBridgeFunction|{$functionDefinition->getName()}|") !== false) {
                return $functionDefinition;
            }
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function getPrompt(): string
    {
        if (sizeof($this->functionDefinitions) === 0) {
            throw new Exception('Need at least one registered function definition.');
        }

        $jsonSchemaParser = new JsonSchemaParser();

        $prompt = <<<'PROMPT'
When you found out what I want to do and you have gathered all information from me
to put my current intention into action,
you can make me use an external tool called 'AiToolBridge' for you.
The tool has the following functions:


PROMPT;

        $functionNames = [];
        foreach ($this->functionDefinitions as $functionDefinition) {
            $functionNames[] = "|CallToolBridgeFunction|{$functionDefinition->getName()}|";
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
you need to simply write a single message starting with '|CallToolBridgeFunction|' followed by the function name,
like this:
PROMPT;

        $prompt .= ' ' . implode(' or ', $functionNames);

        $prompt .= <<<'PROMPT'
.
This message must not contain any other text besides the |CallToolBridgeFunction| marker
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

        $prompt .= "|CallToolBridgeFunction|{$this->functionDefinitions[0]->getName()}|Result|:";
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
