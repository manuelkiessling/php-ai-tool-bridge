<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge;

interface ToolFunction
{
    public function getName(): string;

    public function getDescription(): string;

    public function getInputJsonSchema(): string;

    public function invoke(string $json): ToolFunctionCallResult;
}
