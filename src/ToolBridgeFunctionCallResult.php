<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge;

readonly class ToolBridgeFunctionCallResult
{
    public function __construct(
        public ToolBridgeFunctionDefinition $functionDefinition,
        public ToolBridgeFunctionCallResultStatus $status,
        public string $message,
        public array $data,
    ) {
    }
}
