<?php

require_once __DIR__.'/vendor/autoload.php';

use Maestroerror\LarAgent\Drivers\OpenAi\OpenAiDriver;
use Maestroerror\LarAgent\History\InMemoryChatHistory;
use Maestroerror\LarAgent\History\JsonChatHistory;
use Maestroerror\LarAgent\Message;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use Maestroerror\LarAgent\Tool;

$yourApiKey = include 'openai-api-key.php';
$driver = new OpenAiDriver(["api_key" => $yourApiKey]);
$chatHistory = new InMemoryChatHistory('test-chat-history', [
    'context_window' => 60000,
]);

var_dump(new JsonChatHistory('test-chat-history', [
    'context_window' => 60000,
]));

// Tool calling example
$userMessage = Message::user('What\'s the weather like in Boston? I prefer celsius');
$chatHistory->addMessage($userMessage);

// Create tool
$toolName = 'get_current_weather';
$tool = Tool::create($toolName, 'Get the current weather in a given location');
$tool->addProperty('location', 'string', 'The city and state, e.g. San Francisco, CA')
    ->addProperty('unit', 'string', 'The unit of temperature', ['celsius', 'fahrenheit'])
    ->setRequired('location')
    ->setMetaData(['sent_at' => '2024-01-01'])
    // ->setCallback('get_current_weather')
    ->setCallback(function ($location, $unit = 'celsius') {
        // "Call the weather API"
        return 'The weather in '.$location.' is 72 degrees '.$unit;
    });

// Register tool
$driver->registerTool($tool);

$response = $driver->sendMessage($chatHistory->toArray(), [
    'model' => 'gpt-4o-mini',
]);

$chatHistory->addMessage($response);

if ($response instanceof ToolCallMessage) {
    $tool = $driver->getTool($response->getToolName())
        ->setCallId($response->getCallId());
    $result = $tool->execute(json_decode($response->getToolArguments(), true));

    $chatHistory->addMessage(Message::toolResult($tool, $result));

    $response = $driver->sendMessage($chatHistory->toArray(), [
        'model' => 'gpt-4o-mini',
    ]);

    $chatHistory->addMessage($response);
}

echo $response;

function get_current_weather($location, $unit = 'celsius')
{
    // Call the weather API
    return 'The weather in '.$location.' is 72 degrees '.$unit;
}

$chatHistory->writeToMemory();
$chatHistory->clear();
$chatHistory->readFromMemory();
print_r($chatHistory->toArray());
