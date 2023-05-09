<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge;

readonly class ToolBridgeFunctionCallResult
{
    /**
     * @param mixed[] $data
     */
    public function __construct(
        public ToolBridgeFunctionCallResultStatus $status,
        public string $message,
        public array $data,
    ) {
    }
}
