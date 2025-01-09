<?php

namespace Maestroerror\LarAgent\Core\Enums;

enum Role: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case TOOL = 'tool';
}
