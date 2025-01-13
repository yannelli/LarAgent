<?php

namespace Maestroerror\LarAgent\Messages;

use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use Maestroerror\LarAgent\Core\Enums\Role;
use Maestroerror\LarAgent\Messages\AssistantMessage;

class ToolCallMessage extends AssistantMessage implements MessageInterface
{
    protected array $toolCalls = [];
    protected mixed $toMessageCallback;

    public function __construct(array $toolCalls, array $message, array $metadata = [])
    {
        parent::__construct('', $metadata);
        $this->buildFromArray($message);
        $this->toolCalls = $toolCalls;
    }

    public function getToolCalls(): array
    {
        return $this->toolCalls;
    }
}
