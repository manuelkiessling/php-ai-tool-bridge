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

        return '';
    }
}