<?php

namespace Maestroerror\LarAgent;

use Maestroerror\LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use Maestroerror\LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;

class LarAgent {

    public function __construct(LlmDriverInterface $driver, ChatHistoryInterface $chatHistory) {
        // Constructor body
    }

    public static function setup(LlmDriverInterface $driver, ChatHistoryInterface $chatHistory, array $configs = []): self {
        // @todo define properties and read them from $configs or set default values
        return new self($driver, $chatHistory);
    }
}
