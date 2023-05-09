<?php

declare(strict_types=1);

namespace ManuelKiessling\Test\GptToolBridge;

use ManuelKiessling\GptToolBridge\GptAssistant;

class DemoGptAssistant implements GptAssistant
{
    public function getAssistantResponse(string $userMessage): string
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
