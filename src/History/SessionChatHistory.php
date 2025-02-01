<?php

namespace LarAgent\History;

use Illuminate\Support\Facades\Session;
use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class SessionChatHistory extends ChatHistory implements ChatHistoryInterface
{
    public function readFromMemory(): void
    {
        $messages = Session::get($this->getIdentifier(), []);
        if (! is_array($messages)) {
            $messages = [];
        }
        $this->setMessages($messages);
    }

    public function writeToMemory(): void
    {
        $messages = $this->getMessages();
        Session::put($this->getIdentifier(), $messages);
    }
}
