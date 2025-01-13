<?php

namespace Maestroerror\LarAgent;

use Maestroerror\LarAgent\Core\Abstractions\Message as AbstractMessage;
use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use Maestroerror\LarAgent\Messages\AssistantMessage;
use Maestroerror\LarAgent\Messages\SystemMessage;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use Maestroerror\LarAgent\Messages\UserMessage;

// Accessor, Simplified API for messages
class Message extends AbstractMessage
{
    // Create custom message, non-typed
    public static function create(string $role, string|array $content, array $metadata = []): MessageInterface
    {
        self::validateRole($role);

        return new self($role, $content, $metadata);
    }

    /**
     * Create a message from an array
     * !!! Use it with caution, only for internal use, avoiding direct user input
     */
    public static function fromArray(array $data): MessageInterface
    {
        $msg = new self('', '');

        return $msg->buildFromArray($data);
    }

    public static function fromJSON(string $json): MessageInterface
    {
        $msg = new self('', '');

        return $msg->buildFromJson($json);
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
}
