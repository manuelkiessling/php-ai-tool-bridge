<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge;

interface ToolBridgeFunctionDefinition
{
    public function getName(): string;

    public function getDescription(): string;

    public function getInputJsonSchema(): string;

    public function getOutputJsonSchema(): string;

    public function invoke(string $json): ToolBridgeFunctionCallResult;
}
