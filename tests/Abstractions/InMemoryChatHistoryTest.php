<?php

use Maestroerror\LarAgent\History\InMemoryChatHistory;
use Maestroerror\LarAgent\Messages\UserMessage;

it('can add and retrieve messages', function () {
    $chatHistory = new InMemoryChatHistory('test_history');
    $message = new UserMessage('What\'s the weather like in Boston? I prefer celsius');

    $chatHistory->addMessage($message);

    expect($chatHistory->getMessages())
        ->toHaveCount(1)
        ->and($chatHistory->getLastMessage()->getContent())
        ->toBe('What\'s the weather like in Boston? I prefer celsius');
});

it('can clear messages', function () {
    $chatHistory = new InMemoryChatHistory('test_history');
    $chatHistory->addMessage(new UserMessage('Message 1'));
    $chatHistory->addMessage(new UserMessage('Message 2'));

    $chatHistory->clear();

    expect($chatHistory->getMessages())->toBeEmpty();
});

it('supports array access for messages', function () {
    $chatHistory = new InMemoryChatHistory('test_history');
    $message = new UserMessage('This is an array-accessible message');

    $chatHistory[] = $message;

    expect($chatHistory[0])
        ->toBeInstanceOf(UserMessage::class)
        ->and($chatHistory[0]->getContent())
        ->toBe('This is an array-accessible message');

    unset($chatHistory[0]);

    expect(isset($chatHistory[0]))->toBeFalse();
});

it('can write and read messages to and from memory', function () {
    $chatHistory = new InMemoryChatHistory('test_memory_history');
    $message = new UserMessage('Remember this message in memory');

    $chatHistory->addMessage($message);
    $chatHistory->writeToMemory();

    // Clear and reload from memory
    $chatHistory->clear();
    $chatHistory->readFromMemory();

    expect($chatHistory->getMessages())
        ->toHaveCount(1)
        ->and($chatHistory->getMessages()[0]->getContent())
        ->toBe('Remember this message in memory');
});

it('handles empty memory gracefully', function () {
    $chatHistory = new InMemoryChatHistory('empty_memory_history');

    // Ensure no errors occur when reading from empty memory
    $chatHistory->readFromMemory();

    expect($chatHistory->getMessages())->toBeEmpty();
});

it('can truncate old messages when exceeding context window', function () {
    $chatHistory = new InMemoryChatHistory('truncate_test');
    $chatHistory->setContextWindow(5000);

    $chatHistory->addMessage(new UserMessage('Message 1'));
    $chatHistory->addMessage(new UserMessage('Message 2'));
    $chatHistory->addMessage(new UserMessage('Message 3'));
    $chatHistory->addMessage(new UserMessage('Message 4'));

    if ($chatHistory->exceedsContextWindow(5300)) {
        $chatHistory->truncateOldMessages(3);
    }

    expect($chatHistory->getMessages())
        ->toHaveCount(1)
        ->and($chatHistory->getMessages()[0]->getContent())
        ->toBe('Message 4');
});

it('can determine if a token count exceeds context window', function () {
    $chatHistory = new InMemoryChatHistory('context_test');
    $chatHistory->setContextWindow(5000);

    expect($chatHistory->exceedsContextWindow(6000))->toBeTrue();
    expect($chatHistory->exceedsContextWindow(3000))->toBeFalse();
});

it('Takes in account the reservedForCompletion property', function () {
    $chatHistory = new InMemoryChatHistory('context_test');
    // Default reservedForCompletion is 1000
    $chatHistory->setContextWindow(5000);

    expect($chatHistory->exceedsContextWindow(4500))->toBeTrue();
});
