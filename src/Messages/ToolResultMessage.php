<?php

namespace Maestroerror\LarAgent\Messages;

use Maestroerror\LarAgent\Core\Abstractions\Message;
use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use Maestroerror\LarAgent\Core\Enums\Role;

class ToolResultMessage extends Message implements MessageInterface
{
    // Public properties are returned in the array representation of the object

    public function __construct(array $message, array $metadata = [])
    {
        parent::__construct(Role::TOOL->value, '', $metadata);
        $this->buildFromArray($message);
    }
}
