<?php

namespace Maestroerror\LarAgent\Core\Abstractions;

use Maestroerror\LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use ArrayAccess;

abstract class ChatHistory implements ChatHistoryInterface, ArrayAccess
{
    protected array $messages = []; // Store messages as an array of MessageInterface
    protected int $contextWindow;   // Maximum allowed tokens in the context
    protected int $reservedForCompletion = 1000; // Reserved tokens for completion
    protected string $name; // History identifier

    public function __construct(string $name, int $contextWindow = 60000)
    {
        $this->name = $name;
        $this->readFromMemory();
        $this->contextWindow = $contextWindow;
    }

    public function addMessage(MessageInterface $message): void
    {
        $this->messages[] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getIdentifier(): string
    {
        return $this->name;
    }

    public function getLastMessage(): ?MessageInterface
    {
        return end($this->messages) ?: null;
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public function clear(): void
    {
        $this->messages = [];
    }

    public function toArray(): array
    {
        return array_map(fn(MessageInterface $message) => $message->toArray(), $this->messages);
    }

    // ArrayAccess implementation
    public function offsetExists($offset): bool
    {
        return isset($this->messages[$offset]);
    }

    public function offsetGet($offset): ?MessageInterface
    {
        return $this->messages[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof MessageInterface) {
            throw new \InvalidArgumentException('Only instances of MessageInterface can be added to ChatHistory.');
        }

        if (is_null($offset)) {
            $this->messages[] = $value;
        } else {
            $this->messages[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->messages[$offset]);
    }

    // Abstract methods for memory handling
    abstract public function readFromMemory(): void;

    abstract public function writeToMemory(): void;

    // Token management methods
    public function setContextWindow(int $tokens): void
    {
        $this->contextWindow = $tokens;
    }

    // You can get $tokens for comparison from usage->promptTokens
    public function exceedsContextWindow(int $tokens): bool
    {
        return $tokens > ($this->contextWindow - $this->reservedForCompletion);
    }

    public function truncateOldMessages(int $messagesCount): void {
        array_splice($this->messages, 0, $messagesCount);
    }
}
