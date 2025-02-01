<?php

namespace LarAgent\History;

use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use Illuminate\Support\Facades\Cache;

class CacheChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected ?string $store;

    public function __construct(string $name, array $options = [])
    {
        $this->store = $options['store'] ?? null;
        parent::__construct($name, $options);
    }

    public function readFromMemory(): void
    {
        $messages = $this->store ? Cache::store($this->store)->get($this->getIdentifier(), []) : Cache::get($this->getIdentifier(), []);
        $this->setMessages($messages);
    }

    public function writeToMemory(): void
    {
        $messages = $this->getMessages();
        if ($this->store) {
            Cache::store($this->store)->put($this->getIdentifier(), $messages);
        } else {
            Cache::put($this->getIdentifier(), $messages);
        }
    }
}
