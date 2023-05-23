<?php

declare(strict_types=1);

namespace ManuelKiessling\Test\AiToolBridge;

use ManuelKiessling\AiToolBridge\AiAssistantMessenger;

class DemoAiAssistantMessenger implements AiAssistantMessenger
{
    public function getResponseForToolFunction(string $userMessage): string
    {
        if ($userMessage === "Value for parameter 'name' (of type STRING):") {
            return 'J. Doe';
        }

        if ($userMessage === "Value for parameter 'age' (of type INTEGER):") {
            return '26';
        }

        if ($userMessage === "Value for entry #0 of array 'interests' (of type STRING - answer with 'AiToolBridgeNone' if all values for this array have been provided):") {
            return 'Painting';
        }

        if ($userMessage === "Value for entry #1 of array 'interests' (of type STRING - answer with 'AiToolBridgeNone' if all values for this array have been provided):") {
            return 'Horse Riding';
        }

        if ($userMessage === "Value for entry #2 of array 'interests' (of type STRING - answer with 'AiToolBridgeNone' if all values for this array have been provided):") {
            return 'AiToolBridgeNone';
        }

        return '';
    }
}
