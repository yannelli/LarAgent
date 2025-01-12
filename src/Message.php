<?php

namespace Maestroerror\LarAgent;

use Maestroerror\LarAgent\Core\Abstractions\Message as AbstractMessage;
use Maestroerror\LarAgent\Messages\SystemMessage;

use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use Maestroerror\LarAgent\Core\Contracts\Tool as ToolInterface;
use Maestroerror\LarAgent\Messages\AssistantMessage;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use Maestroerror\LarAgent\Messages\ToolResultMessage;
use Maestroerror\LarAgent\Messages\UserMessage;

// Accessor, Simplified API for messages
class Message extends AbstractMessage
{
    // Create custom message, non-typed
    public static function create(string $role, string|array $content, array $metadata = []): MessageInterface
    {
        if (empty($role)) {
            throw new \InvalidArgumentException('Role cannot be empty.');
        }

        return new self($role, $content, $metadata);
    }

    public static function assistant(string $content, array $metadata = []): MessageInterface
    {
        return new AssistantMessage($content, $metadata);
    }

    public static function user(string $content, array $metadata = []): MessageInterface
    {
        return new UserMessage($content, $metadata);
    }

    public static function system(string $content, array $metadata = []): MessageInterface
    {
        return new SystemMessage($content, $metadata);
    }

    public static function toolCall(string $toolCallId, string $toolName, string $jsonArgs, array $metadata = []): MessageInterface
    {
        return new ToolCallMessage($toolCallId, $toolName, $jsonArgs, $metadata);
    }

    public static function toolResult(ToolInterface $tool, string $result, array $metadata = []): MessageInterface
    {
        return new ToolResultMessage($tool, $result, $metadata);
    }
}
