<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge;

readonly class ToolFunctionCallResult
{
    public function __construct(
        public ToolFunctionDefinition       $functionDefinition,
        public ToolFunctionCallResultStatus $status,
        public string                       $message,
        public array                        $data,
    ) {
    }
}
