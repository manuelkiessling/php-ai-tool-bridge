<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge;

interface AiAssistantMessenger
{
    public function getResponseForToolFunction(string $userMessage): string;
}
