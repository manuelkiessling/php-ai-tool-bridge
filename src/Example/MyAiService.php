<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge\Example;

use ManuelKiessling\AiToolBridge\AiAssistantMessenger;

class MyAiService implements AiAssistantMessenger
{
    public function setSystemPrompt(string $prompt): void
    {
        // Whatever code is needed to set the system prompt of the AI.
    }

    public function getResponseForToolFunction(string $userMessage): string
    {
        // Code that sends a message to the AI assistant and returns its response
        return 'the AI response';
    }
}
