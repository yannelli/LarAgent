<?php

namespace Maestroerror\LarAgent\Drivers;

use Maestroerror\LarAgent\Core\Abstractions\LlmDriver;
use Maestroerror\LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;
use Maestroerror\LarAgent\Messages\AssistantMessage;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use OpenAI;

class OpenAiDriver extends LlmDriver implements LlmDriverInterface
{
    protected mixed $client;

    public function __construct(string $apiKey = '')
    {
        $this->client = OpenAI::client($apiKey);
    }

    public function sendMessage(array $messages, array $options = []): AssistantMessage|ToolCallMessage
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

        // @todo Enable parallel tool calls
        if ($finishReason === "tool_calls") {
            $toolName = $this->lastResponse->choices[0]->message->toolCalls[0]->function->name;
            $args = $this->lastResponse->choices[0]->message->toolCalls[0]->function->arguments;
            $callId = $this->lastResponse->choices[0]->message->toolCalls[0]->id;

            return new ToolCallMessage($callId, $toolName, $args, $metaData);
        }

        if ($finishReason === 'stop') {
            $content = $this->lastResponse->choices[0]->message->content;

            return new AssistantMessage($content, $metaData);
        }

        throw new \Exception('Unexpected finish reason: '.$finishReason);
    }
}
