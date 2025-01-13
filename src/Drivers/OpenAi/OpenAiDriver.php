<?php

namespace Maestroerror\LarAgent\Drivers\OpenAi;

use Maestroerror\LarAgent\Core\Abstractions\LlmDriver;
use Maestroerror\LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;
use Maestroerror\LarAgent\Core\Contracts\ToolCall as ToolCallInterface;
use Maestroerror\LarAgent\Messages\AssistantMessage;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use Maestroerror\LarAgent\ToolCall;
use OpenAI;

class OpenAiDriver extends LlmDriver implements LlmDriverInterface
{
    protected mixed $client;

    public function __construct(string $apiKey = '')
    {
        $this->client = OpenAI::client($apiKey);
    }

    public function sendMessage(array $messages, array $options = []): AssistantMessage
    {
        $this->setConfig($options);

        $payload = array_merge($this->config, [
            'messages' => $messages,
        ]);

        // Set the response format if "responseSchema" is provided
        if ($this->structuredOutputEnabled()) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => $this->getResponseSchema(),
            ];
        }

        // Add tools to payload if any are registered
        if (! empty($this->tools)) {
            $tools = $this->getRegisteredTools();
            foreach ($tools as $tool) {
                // Add a default property to bypass schema check of openai-php/client if no properties are defined
                if (empty($tool->getProperties())) {
                    $tool->addProperty('no_properties', ['string', 'null'], 'empty');
                }
                $payload['tools'][] = $tool->toArray();
            }
        }

        // Make an API call to OpenAI ("/chat" endpoint)
        $this->lastResponse = $response = $this->client->chat()->create($payload);

        // Handle the response
        $finishReason = $this->lastResponse->choices[0]->finishReason;
        $metaData = [
            'usage' => $this->lastResponse->usage,
        ];

        if ($finishReason === 'tool_calls') {
            
            // Collect tool calls from the response
            $toolCalls = array_map(function ($toolCall) {
                return new ToolCall($toolCall->id, $toolCall->function->name, $toolCall->function->arguments);
            }, $this->lastResponse->choices[0]->message->toolCalls);

            // Build tool calls message with needed structure
            $message = $this->toolCallsToMessage($toolCalls);
            
            return new ToolCallMessage($toolCalls, $message, $metaData);
        }

        if ($finishReason === 'stop') {
            $content = $this->lastResponse->choices[0]->message->content;

            return new AssistantMessage($content, $metaData);
        }

        throw new \Exception('Unexpected finish reason: '.$finishReason);
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

    // Helper methods

    protected function toolCallToContent(ToolCallInterface $toolCall): array
    {
        $this->validateJson($toolCall->getArguments());

        return [
            'id' => $toolCall->getId(),
            'type' => 'function',
            'function' => [
                'name' => $toolCall->getToolName(),
                'arguments' => $toolCall->getArguments(),
            ],
        ];
    }

    protected function validateJson(string $json): void
    {
        // Validate JSON arguments
        json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \JsonException('Invalid JSON provided for tool call arguments: '.$json);
        }
    }
}
