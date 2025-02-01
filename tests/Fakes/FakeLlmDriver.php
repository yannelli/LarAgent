<?php

namespace LarAgent\Tests\Fakes;

use LarAgent\Core\Abstractions\LlmDriver;
use LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;
use LarAgent\Core\Contracts\ToolCall as ToolCallInterface;
use LarAgent\Messages\AssistantMessage;
use LarAgent\Messages\ToolCallMessage;
use LarAgent\ToolCall;

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
            $toolCallId = '12345';
            $toolCalls[] = new ToolCall($toolCallId, $responseData['toolName'], $responseData['arguments']);

            return new ToolCallMessage(
                $toolCalls,
                $this->toolCallsToMessage($toolCalls),
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

    public function toolCallsToMessage(array $toolCalls): array
    {
        $toolCallsArray = [];
        foreach ($toolCalls as $tc) {
            $toolCallsArray[] = $this->toolCallToContent($tc);
        }

        return [
            'role' => 'assistant',
            'tool_calls' => $toolCallsArray,
        ];
    }

    public function toolResultToMessage(ToolCallInterface $toolCall, mixed $result): array
    {
        // Build toolCall message content from toolCall
        $content = json_decode($toolCall->getArguments(), true);
        $content[$toolCall->getToolName()] = $result;

        return [
            'role' => 'tool',
            'content' => json_encode($content),
            'tool_call_id' => $toolCall->getId(),
        ];
    }

    // Helper methods

    protected function toolCallToContent(ToolCallInterface $toolCall): array
    {
        return [
            'id' => $toolCall->getId(),
            'type' => 'function',
            'function' => [
                'name' => $toolCall->getToolName(),
                'arguments' => $toolCall->getArguments(),
            ],
        ];
    }
}
