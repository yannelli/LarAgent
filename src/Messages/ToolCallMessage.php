<?php

namespace Maestroerror\LarAgent\Messages;

use Maestroerror\LarAgent\Core\Abstractions\Message;
use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use Maestroerror\LarAgent\Core\Enums\Role;

class ToolCallMessage extends Message implements MessageInterface
{
    protected array $toolCalls = [];

    public function __construct(string $toolCallId, string $toolName, string $jsonArgs, array $metadata = [])
    {
        // Validate JSON arguments
        json_decode($jsonArgs, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \JsonException('Invalid JSON provided for tool call arguments: '.$jsonArgs);
        }

        parent::__construct(Role::ASSISTANT->value, '', $metadata);
        $this->toolCalls[] = [
            'id' => $toolCallId,
            'type' => 'function',
            'function' => [
                'name' => $toolName,
                'arguments' => $jsonArgs,
            ],
        ];
    }

    public function toArray(): array
    {
        return [
            'role' => $this->getRole(),
            'tool_calls' => $this->toolCalls,
        ];
    }

    public function getCallId(): string
    {
        return $this->toolCalls[0]['id'];
    }

    public function getToolName(): string
    {
        return $this->toolCalls[0]['function']['name'];
    }

    public function getToolArguments(): string
    {
        return $this->toolCalls[0]['function']['arguments'];
    }
}
