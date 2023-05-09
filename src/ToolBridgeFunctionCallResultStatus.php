<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge;

enum ToolBridgeFunctionCallResultStatus
{
    case SUCCESS;
    case FAILURE;
}
