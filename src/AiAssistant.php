<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge;

interface AiAssistant
{
    public function getAssistantResponse(string $userMessage): string;
}
