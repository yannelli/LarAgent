<?php

require_once __DIR__.'/vendor/autoload.php';

use Maestroerror\LarAgent\Drivers\OpenAiDriver;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use Maestroerror\LarAgent\Messages\ToolResultMessage;
use Maestroerror\LarAgent\Messages\UserMessage;
use Maestroerror\LarAgent\Tool;

$yourApiKey = include 'openai-api-key.php';
$driver = new OpenAiDriver($yourApiKey);

// Tool calling example
$userMessage = new UserMessage('What\'s the weather like in Boston? I prefer celsius');
$messages = [
    $userMessage->toArray(),
];

$messageObjects = [
    $userMessage,
];

// Create tool
$toolName = 'get_current_weather';
$tool = new Tool($toolName, 'Get the current weather in a user location');
$location = 'Boston';
$unit = 'Farenheit';
$tool->setCallback(function () use ($location, $unit) {
    // "Call the weather API"
    return 'The weather in '.$location.' is 72 degrees '.$unit;
});

// Register tool
$driver->registerTool($tool);

$response = $driver->sendMessage($messages, [
    'model' => 'gpt-4o-mini',
]);

$messages[] = $response->toArray();
$messageObjects[] = $response;

if ($response instanceof ToolCallMessage) {
    $tool = $driver->getTool($response->getToolName())
        ->setCallId($response->getCallId())->setArguments(json_decode($response->getToolArguments(), true));
    $result = $tool->execute();
    $messages[] = (new ToolResultMessage($tool, $result))->toArray();

    $response = $driver->sendMessage($messages, [
        'model' => 'gpt-4o-mini',
    ]);
}

$messageObjects[] = $response;

echo $response;

function get_current_weather($location, $unit = 'celsius')
{
    // Call the weather API
    return 'The weather in '.$location.' is 72 degrees '.$unit;
}
