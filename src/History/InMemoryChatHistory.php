<?php

namespace LarAgent\History;

use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class InMemoryChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected array $storage = [];

    public function readFromMemory(): void
    {
        $this->setMessages($this->storage[$this->getIdentifier()] ?? []);
    }

    public function writeToMemory(): void
    {
        $this->storage[$this->getIdentifier()] = $this->getMessages();
    }
}
