<?php

use Illuminate\Support\Facades\Storage;
use LarAgent\Core\Enums\Role;
use LarAgent\History\CacheChatHistory;
use LarAgent\History\FileChatHistory;
use LarAgent\History\JsonChatHistory;
use LarAgent\History\SessionChatHistory;
use LarAgent\Message;

dataset('messages', [
    Message::create(Role::SYSTEM->value, 'You are helpful assistant', ['test' => 'meta']),
]);

it('can read and write session chat history', function ($message) {
    $history = new SessionChatHistory('test_session');
    $history->addMessage($message);
    $history->writeToMemory();

    $newHistory = new SessionChatHistory('test_session');
    expect($newHistory->getMessages())->toBe([$message]);
})->with('messages');

it('can read and write json chat history', function ($message) {
    $history = new JsonChatHistory('test_json', ['folder' => __DIR__.'/json_storage']);
    $history->clear();

    $history->addMessage($message);
    $history->writeToMemory();

    $newHistory = new JsonChatHistory('test_json', ['folder' => __DIR__.'/json_storage']);

    expect($newHistory->toArray())->toBe([$message->toArray()]);
    expect($newHistory->getLastMessage()->toArray())->toBe($message->toArray());

})->with('messages');

it('can read and write file chat history', function ($message) {
    Storage::fake('local');
    $history = new FileChatHistory('test_file', ['disk' => 'local', 'folder' => 'chat_histories']);
    $history->addMessage($message);
    $history->writeToMemory();

    $newHistory = new FileChatHistory('test_file', ['disk' => 'local', 'folder' => 'chat_histories']);

    expect($newHistory->toArray())->toBe([$message->toArray()]);
    expect($newHistory->getLastMessage()->toArray())->toBe($message->toArray());
})->with('messages');

it('reads and writes chat history using the default cache store', function ($message) {
    $identifier = 'test_cache';

    $history = new CacheChatHistory($identifier);
    $history->addMessage($message);
    $history->writeToMemory();

    $newHistory = new CacheChatHistory($identifier);

    expect($newHistory->toArray())->toBe([$message->toArray()]);
    expect($newHistory->getLastMessage()->toArray())->toBe($message->toArray());
})->with('messages');
