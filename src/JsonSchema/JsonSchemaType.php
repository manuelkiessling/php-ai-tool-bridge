<?php

declare(strict_types=1);

namespace ManuelKiessling\GptToolBridge\JsonSchema;

enum JsonSchemaType
{
    case STRING;
    case INTEGER;
    case FLOAT;
    case ARRAY;
    case ENUM;
    case BOOLEAN;
    case OBJECT;
}
