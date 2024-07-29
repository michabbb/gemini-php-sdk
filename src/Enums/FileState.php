<?php

declare(strict_types=1);

namespace Gemini\Enums;

enum FileState: string
{
    case PROCESSING        = 'PROCESSING';
    case ACTIVE            = 'ACTIVE';
    case FAILED            = 'FAILED';
    case STATE_UNSPECIFIED = 'STATE_UNSPECIFIED';
}