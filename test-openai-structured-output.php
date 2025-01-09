<?php

require_once __DIR__.'/vendor/autoload.php';

use Maestroerror\LarAgent\Drivers\OpenAiDriver;
use Maestroerror\LarAgent\History\InMemoryChatHistory;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use Maestroerror\LarAgent\Messages\ToolResultMessage;
use Maestroerror\LarAgent\Messages\UserMessage;
use Maestroerror\LarAgent\Tool;

$yourApiKey = include 'openai-api-key.php';
$driver = new OpenAiDriver($yourApiKey);
$chatHistory = new InMemoryChatHistory('test-chat-history');

$weatherInfoSchema = [
    'name' => 'weather_info',
    'schema' => [
        'type' => 'object',
        'properties' => [
            'locations' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'city' => ['type' => 'string'],
                        'weather' => ['type' => 'string'],
                    ],
                    'required' => ['city', 'weather'],
                    'additionalProperties' => false,
                ],
            ],
        ],
        'required' => ['locations'],
        'additionalProperties' => false,
    ],
    'strict' => true,
];

$driver->setResponseSchema($weatherInfoSchema);

// Tool calling example
$userMessage = new UserMessage('What\'s the weather like in Boston? I prefer celsius');
$chatHistory->addMessage($userMessage);

// Create tool
$toolName = 'get_current_weather';
$tool = new Tool($toolName, 'Get the current weather in a given location');
$tool->addProperty('location', 'string', 'The city and state, e.g. San Francisco, CA')
    ->addProperty('unit', 'string', 'The unit of temperature', ['celsius', 'fahrenheit'])
    ->setRequired('location')
    ->setMetaData(['sent_at' => '2024-01-01']) // @todo where to use tool's meta data?
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
        ->setCallId($response->getCallId())->setArguments(json_decode($response->getToolArguments(), true));
    $result = $tool->execute();

    $chatHistory->addMessage(new ToolResultMessage($tool, $result));

    $response = $driver->sendMessage($chatHistory->toArray(), [
        'model' => 'gpt-4o-mini',
    ]);

    $chatHistory->addMessage($response);
}

if ($driver->structuredOutputEnabled()) {
    print_r(json_decode($response->getContent(), true));
} else {
    echo $response;
}

function get_current_weather($location, $unit = 'celsius')
{
    // Call the weather API
    return 'The weather in '.$location.' is 72 degrees '.$unit;
}

$chatHistory->writeToMemory();
$chatHistory->clear();
$chatHistory->readFromMemory();
// print_r($chatHistory->toArray());
