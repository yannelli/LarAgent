<?php

namespace Maestroerror\LarAgent\History;

use Maestroerror\LarAgent\Core\Abstractions\ChatHistory;
use Maestroerror\LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class InMemoryChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected array $storage = [];

    public function readFromMemory(): void
    {
        $this->messages = $this->storage[$this->name] ?? [];

    }

    public function writeToMemory(): void
    {
        $this->storage[$this->name] = $this->messages;

    }
}
