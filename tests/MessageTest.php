<?php

use Maestroerror\LarAgent\Message;
use Maestroerror\LarAgent\Messages\AssistantMessage;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use Maestroerror\LarAgent\Messages\ToolResultMessage;
use Maestroerror\LarAgent\Messages\UserMessage;
use Maestroerror\LarAgent\Tool;

it('creates a custom message', function () {
    $message = Message::create('custom_role', 'Custom content', ['key' => 'value']);

    expect($message->getRole())->toBe('custom_role')
        ->and($message->getContent())->toBe('Custom content')
        ->and($message->getMetadata())->toHaveKey('key', 'value');
});

it('creates an assistant message', function () {
    $message = Message::assistant('This is an assistant message', ['usage' => 'test']);

    expect($message)->toBeInstanceOf(AssistantMessage::class)
        ->and($message->getRole())->toBe('assistant')
        ->and($message->getContent())->toBe('This is an assistant message')
        ->and($message->getMetadata())->toHaveKey('usage', 'test');
});

it('creates a user message', function () {
    $message = Message::user('This is a user message', ['timestamp' => '2025-01-01']);

    expect($message)->toBeInstanceOf(UserMessage::class)
        ->and($message->getRole())->toBe('user')
        ->and($message->getContent())->toBe('This is a user message')
        ->and($message->getMetadata())->toHaveKey('timestamp', '2025-01-01');
});

it('creates a tool call message', function () {
    $toolCallId = '12345';
    $toolName = 'get_weather';
    $jsonArgs = '{"location": "Boston", "unit": "celsius"}';
    $message = Message::toolCall($toolCallId, $toolName, $jsonArgs, ['status' => 'pending']);

    expect($message)->toBeInstanceOf(ToolCallMessage::class)
        ->and($message->getRole())->toBe('assistant')
        ->and($message->getToolArguments())->toBe($jsonArgs)
        ->and($message->getMetadata())->toHaveKey('status', 'pending');
});

it('creates a tool result message', function () {
    $tool = Tool::create('get_weather', 'Get the weather in a location')
        ->setCallback(function ($location, $unit = 'celsius') {
            return 'The weather in '.$location.' is 72 degrees '.$unit;
        })
        ->setCallId('12345');

    $result = '{"temperature": "20°C"}';
    $message = Message::toolResult($tool, $result, ['status' => 'completed']);

    expect($message)->toBeInstanceOf(ToolResultMessage::class)
        ->and($message->getRole())->toBe('tool')
        ->and(json_decode($message->getContent()))->toHaveKey($tool->getName())
        ->and($message->getMetadata())->toHaveKey('status', 'completed');
});

// Edge cases

it('creates a custom message with invalid role', function () {
    $message = Message::create('', 'Content');

    expect($message->getRole())->toBe('');
})->throws(\InvalidArgumentException::class, 'Role cannot be empty'); // Add this validation in your class if not already there.

it('throws an exception for invalid JSON in tool call message', function () {
    $toolCallId = '12345';
    $toolName = 'get_weather';
    $invalidJsonArgs = '{"location": "Boston", "unit":'; // Invalid JSON

    Message::toolCall($toolCallId, $toolName, $invalidJsonArgs);
})->throws(\JsonException::class);

it('handles empty content for user message', function () {
    $message = Message::user('', []);

    expect($message->getContent())->toBe('');
});
