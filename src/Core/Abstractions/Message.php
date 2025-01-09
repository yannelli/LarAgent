<?php

namespace Maestroerror\LarAgent\Core\Abstractions;

use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use ArrayAccess;
use Maestroerror\LarAgent\Core\Enums\Role;

abstract class Message implements MessageInterface, ArrayAccess
{
    protected string $role;  // Represents the sender or role (e.g., "user", "agent")
    protected string|array $content;  // The actual message content
    protected array $metadata;  // Additional data about the message

    public function __construct(string $role, string|array $content, array $metadata = [])
    {
        $this->role = $role;
        $this->content = $content;
        $this->metadata = $metadata;
    }

    // Implementation of MessageInterface methods
    public function getRole(): string
    {
        return $this->role;
    }

    public function getContent(): string|array
    {
        return $this->content;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $data): void
    {
        $this->metadata = $data;
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }

    // Implementation of ArrayAccess
    public function offsetExists($offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->toArray()[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException('Message is immutable.');
    }

    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException('Message is immutable.');
    }

    // Additional
    public function __toString(): string
    {
        if (is_string($this->getContent())) {
            return $this->getContent();
        } else {
            return $this->getContent()[0]['text'];
        }
    }
}
