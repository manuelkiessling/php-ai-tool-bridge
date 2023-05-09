<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge;

interface GptAssistant
{
    public function getAssistantResponse(string $userMessage): string;
}
