<?php

namespace Maestroerror\LarAgent\Messages;

use Maestroerror\LarAgent\Core\Abstractions\Message;
use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use Maestroerror\LarAgent\Core\Contracts\Tool as ToolInterface;
use Maestroerror\LarAgent\Core\Enums\Role;

class ToolResultMessage extends Message implements MessageInterface
{
    protected string $toolCallId;

    public function __construct(ToolInterface $tool, mixed $result, array $metadata = [])
    {
        $args = $tool->getArguments();
        $content[$tool->getName()] = $result;

        $content = json_encode([
            ...$args,
            ...$content,
        ]);

        $this->toolCallId = $tool->getCallId();

        parent::__construct(Role::TOOL->value, $content, $metadata);
    }

    public function toArray(): array
    {
        return [
            'role' => $this->getRole(),
            'content' => $this->getContent(),
            'tool_call_id' => $this->toolCallId,
        ];
    }
}
