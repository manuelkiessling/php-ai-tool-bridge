<?php

declare(strict_types=1);

namespace ManuelKiessling\AiToolBridge;

enum ToolFunctionCallResultStatus
{
    case SUCCESS;
    case FAILURE;
}
