<?php

namespace Maestroerror\LarAgent\History;

use Maestroerror\LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use Maestroerror\LarAgent\Core\Abstractions\ChatHistory;

class InMemoryChatHistory extends ChatHistory implements ChatHistoryInterface {

    protected array $storage = [];

    public function readFromMemory(): void 
    {
        $this->messages = $this->storage[$this->name] ?? [];
        return;
    }

    public function writeToMemory(): void 
    {
        $this->storage[$this->name] = $this->messages;
        return;
    }
}