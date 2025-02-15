<?php

use Illuminate\Contracts\Auth\Authenticatable;
use LarAgent\Agent;
use LarAgent\Tests\Fakes\FakeLlmDriver;
use LarAgent\Tool;

class TestAgent extends Agent
{
    protected $model = 'gpt-4o-mini';

    protected $history = 'in_memory';

    protected $driver = FakeLlmDriver::class;

    public $saveToolResult = null;

    public function instructions()
    {
        return 'You are a test agent.';
    }

    public function prompt($message)
    {
        return $message.' Please respond appropriately.';
    }

    public function registerTools()
    {
        return [
            Tool::create('test_tool', 'A tool for testing')
                ->addProperty('input', 'string', 'Input for the tool')
                ->setRequired('input')
                ->setCallback(function ($input) {
                    return 'Processed '.$input;
                }),
        ];
    }

    protected function onInitialize()
    {
        $this->llmDriver->addMockResponse('tool_calls', [
            'toolName' => 'test_tool',
            'arguments' => json_encode(['input' => 'test input']),
        ]);

        $this->llmDriver->addMockResponse('stop', [
            'content' => 'Processed test input',
        ]);
    }

    protected function afterResponse($message)
    {
        $message->setContent($message.'. Edited via event');
    }

    protected function afterToolExecution($tool, &$result)
    {
        $this->saveToolResult = $result;
    }
}

it('can create an agent for a user', function () {
    $user = Mockery::mock(Authenticatable::class);
    $user->shouldReceive('getAuthIdentifier')->andReturn('user_123');

    $agent = TestAgent::forUser($user);

    expect($agent)->toBeInstanceOf(Agent::class);
    expect($agent->getChatSessionId())->toContain('user_123');
});

it('can create an agent with a specific key', function () {
    $agent = TestAgent::for('test_key');

    expect($agent)->toBeInstanceOf(Agent::class);
    expect($agent->getChatSessionId())->toContain('test_key');
});

it('can set and get message', function () {
    $agent = TestAgent::for('test_key');
    $message = 'Hello, Agent!';
    $agent->respond($message);

    expect($agent->currentMessage())->toBe($message);
});

it('can use tools and respond', function () {
    $agent = TestAgent::for('test_key');

    $response = $agent->respond('Use the test tool with input "test input".');

    expect($response)->toBe('Processed test input. Edited via event');
    expect($agent->saveToolResult)->toBe('Processed test input');
});

it('can handle events', function () {
    $agent = TestAgent::for('test_key');
    $agent->respond('test');
    $message = $agent->lastMessage();

    // Check if "afterResponse" event worked
    expect((string) $message)->toContain('Edited via event');
});

it('can handle image urls in response', function () {
    $agent = new TestAgent('test_session');
    $agent->withImages([
        'http://example.com/image1.jpg',
        'http://example.com/image2.jpg',
    ]);

    $message = $agent->message('Test message')->respond();

    expect($message)->toBe('Processed test input. Edited via event');

    // Get the last message from chat history to verify images
    $messages = $agent->chatHistory()->getMessages();
    $firstUserMessage = $messages[1];

    expect($firstUserMessage->getContent())->toBeArray()
        ->and($firstUserMessage->getContent())->toHaveCount(3) // text + 2 images
        ->and($firstUserMessage->getContent()[0])->toMatchArray([
            'type' => 'text',
            'text' => 'Test message Please respond appropriately.',
        ])
        ->and($firstUserMessage->getContent()[1])->toMatchArray([
            'type' => 'image_url',
            'image_url' => ['url' => 'http://example.com/image1.jpg'],
        ])
        ->and($firstUserMessage->getContent()[2])->toMatchArray([
            'type' => 'image_url',
            'image_url' => ['url' => 'http://example.com/image2.jpg'],
        ]);
});
