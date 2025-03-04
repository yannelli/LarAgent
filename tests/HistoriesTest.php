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

it('can save and load keys in session chat history', function () {
    $firstHistory = new SessionChatHistory('test_session_1');
    $secondHistory = new SessionChatHistory('test_session_2');

    // Save keys
    $firstHistory->saveKeyToMemory();
    $secondHistory->saveKeyToMemory();

    // Load and verify keys
    $keys = $firstHistory->loadKeysFromMemory();
    expect($keys)->toBeArray()
        ->toContain('test_session_1')
        ->toContain('test_session_2')
        ->toHaveCount(2);

    // Verify no duplicates when saving same key again
    $firstHistory->saveKeyToMemory();
    expect($firstHistory->loadKeysFromMemory())->toHaveCount(2);
});

it('can save and load keys in json chat history', function () {
    $firstHistory = new JsonChatHistory('test_json_1', ['folder' => __DIR__.'/json_storage']);
    $secondHistory = new JsonChatHistory('test_json_2', ['folder' => __DIR__.'/json_storage']);

    // Save keys
    $firstHistory->saveKeyToMemory();
    $secondHistory->saveKeyToMemory();

    // Load and verify keys
    $keys = $firstHistory->loadKeysFromMemory();
    expect($keys)->toBeArray()
        ->toContain('test_json_1')
        ->toContain('test_json_2')
        ->toHaveCount(2);

    // Verify no duplicates when saving same key again
    $firstHistory->saveKeyToMemory();
    expect($firstHistory->loadKeysFromMemory())->toHaveCount(2);

    // Cleanup
    $firstHistory->clear();
    $secondHistory->clear();
});

it('can save and load keys in file chat history', function () {
    Storage::fake('local');
    $firstHistory = new FileChatHistory('test_file_1', ['disk' => 'local', 'folder' => 'chat_histories']);
    $secondHistory = new FileChatHistory('test_file_2', ['disk' => 'local', 'folder' => 'chat_histories']);

    // Save keys
    $firstHistory->saveKeyToMemory();
    $secondHistory->saveKeyToMemory();

    // Load and verify keys
    $keys = $firstHistory->loadKeysFromMemory();
    expect($keys)->toBeArray()
        ->toContain('test_file_1')
        ->toContain('test_file_2')
        ->toHaveCount(2);

    // Verify no duplicates when saving same key again
    $firstHistory->saveKeyToMemory();
    expect($firstHistory->loadKeysFromMemory())->toHaveCount(2);
});

it('can save and load keys in cache chat history', function () {
    $firstHistory = new CacheChatHistory('test_cache_1');
    $secondHistory = new CacheChatHistory('test_cache_2');

    // Save keys
    $firstHistory->saveKeyToMemory();
    $secondHistory->saveKeyToMemory();

    // Load and verify keys
    $keys = $firstHistory->loadKeysFromMemory();
    expect($keys)->toBeArray()
        ->toContain('test_cache_1')
        ->toContain('test_cache_2')
        ->toHaveCount(2);

    // Verify no duplicates when saving same key again
    $firstHistory->saveKeyToMemory();
    expect($firstHistory->loadKeysFromMemory())->toHaveCount(2);
});
