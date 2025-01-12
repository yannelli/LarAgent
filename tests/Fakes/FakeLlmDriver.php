<?php

namespace Maestroerror\LarAgent\Tests\Fakes;

use Maestroerror\LarAgent\Core\Abstractions\LlmDriver;
use Maestroerror\LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;
use Maestroerror\LarAgent\Messages\AssistantMessage;
use Maestroerror\LarAgent\Messages\ToolCallMessage;

class FakeLlmDriver extends LlmDriver implements LlmDriverInterface
{
    protected array $mockResponses = [];

    public function addMockResponse(string $finishReason, array $responseData): void
    {
        $this->mockResponses[] = [
            'finishReason' => $finishReason,
            'responseData' => $responseData,
        ];
    }

    public function sendMessage(array $messages, array $options = []): AssistantMessage|ToolCallMessage
    {
        $this->setConfig($options);

        if (empty($this->mockResponses)) {
            throw new \Exception('No mock responses are defined.');
        }

        $mockResponse = array_shift($this->mockResponses);

        $finishReason = $mockResponse['finishReason'];
        $responseData = $mockResponse['responseData'];

        // Handle different finish reasons
        if ($finishReason === 'tool_calls') {
            return new ToolCallMessage(
                $responseData['callId'] ?? uniqid('tool_call_'),
                $responseData['toolName'],
                $responseData['arguments'],
                $responseData['metaData'] ?? []
            );
        }

        if ($finishReason === 'stop') {
            return new AssistantMessage(
                $responseData['content'],
                $responseData['metaData'] ?? []
            );
        }

        throw new \Exception('Unexpected finish reason: '.$finishReason);
    }
}
