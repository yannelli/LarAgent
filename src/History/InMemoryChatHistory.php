<?php

namespace LarAgent\History;

use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class InMemoryChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected array $storage = [];
    protected array $keyStorage = [];

    public function readFromMemory(): void
    {
        $this->setMessages($this->storage[$this->getIdentifier()] ?? []);
    }

    public function writeToMemory(): void
    {
        $this->storage[$this->getIdentifier()] = $this->getMessages();
    }

    public function saveKeyToMemory(): void
    {
        $key = $this->getIdentifier();
        if (!in_array($key, $this->keyStorage)) {
            $this->keyStorage[] = $key;
        }
    }

    public function loadKeysFromMemory(): array
    {
        return $this->keyStorage;
    }
}
