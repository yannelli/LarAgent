<?php

namespace Maestroerror\LarAgent;

use Illuminate\Contracts\Auth\Authenticatable;
use Maestroerror\LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;
use Maestroerror\LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use Maestroerror\LarAgent\LarAgent;
use Maestroerror\LarAgent\Message;

class Agent
{
    // Agent properties
    protected string $chatSessionId;

    protected ?string $message;

    protected string $instructions;

    protected array $responseSchema;

    protected array $tools = [];

    // @todo enable chat history by the method too so that we could pass parameters
    // @todo Add session, cache and file history types
    // @todo enable string history types: "redis", "session", "file", "db"
    protected string $history;

    protected string $driver;

    protected string $provider = "default";

    protected string $providerName = "";

    protected LarAgent $agent;

    // Driver configs
    protected string $model = 'gpt-4o-mini';

    protected int $contextWindowSize;

    protected int $maxCompletionTokens;

    protected float $temperature;

    protected int $reinjectInstructionsPer;

    protected bool $parallelToolCalls;

    protected LlmDriverInterface $llmDriver;

    protected ChatHistoryInterface $chatHistory;

    public function __construct(string $key)
    {
        $this->setChatSessionId($key);
        $this->setup();
    }

    // Public API

    public static function forUser(Authenticatable $user): static
    {
        $userId = $user->getAuthIdentifier();
        $instance = new static($userId);

        return $instance;
    }

    public static function for(string $key): static
    {
        $instance = new static($key);

        return $instance;
    }

    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function respond(?string $message): string|array
    {
        if ($message) {
            $this->message($message);
        }

        $message = Message::user($this->prompt($this->message));

        $this->agent
            ->withInstructions($this->instructions())
            ->withMessage($message)
            ->setTools($this->getTools());

        if ($this->structuredOutput()) {
            $this->agent->structured($this->structuredOutput());
        }

        return $this->agent->run();
    }

    public function instructions(): string {
        return $this->instructions;
    }

    public function prompt(string $message): string {
        return $message;
    }

    public function structuredOutput(): ?array {
        return $this->responseSchema ?? null;
    }

    // Public accessors / mutators

    public function setChatSessionId(string $id): static {
        $this->chatSessionId = $this->buildSessionId($id);
        return $this;
    }

    public function getChatSessionId(): string {
        return $this->chatSessionId;
    }

    public function getProviderName(): string {
        return $this->providerName;
    }

    public function getTools(): array {
        // @todo build tools from "methodTool" too
        return array_map(function ($tool) {
            return new $tool();
        }, $this->tools);
    }

    public function initHistory(): void {
        $this->chatHistory = new $this->history($this->getChatSessionId());
    }

    // Helper methods

    protected function buildSessionId(string $id) {
        return sprintf(
            '%s_%s_%s',
            class_basename(static::class),
            $this->model,
            $id
        );
    }

    protected function getProviderData(): ?array {
        return config("laragent.providers.{$this->provider}");
    }
    
    protected function setupDriverConfigs(array $providerData): void {
        if (!isset($this->model) && isset($providerData['model'])) {
            $this->model = $providerData['model'];
        }
        if (!isset($this->maxCompletionTokens) && isset($providerData['default_max_completion_tokens'])) {
            $this->maxCompletionTokens = $providerData['default_max_completion_tokens'];
        }
        if (!isset($this->contextWindowSize) && isset($providerData['default_context_window'])) {
            $this->contextWindowSize = $providerData['default_context_window'];
        }
        if (!isset($this->temperature) && isset($providerData['default_temperature'])) {
            $this->temperature = $providerData['default_temperature'];
        }
        if (!isset($this->parallelToolCalls) && isset($providerData['parallel_tool_calls'])) {
            $this->parallelToolCalls = $providerData['parallel_tool_calls'];
        }
    }

    protected function initDriver($providerData): void {
        $this->llmDriver = new $this->driver([
            "api_key" => $providerData['api_key'],
            'api_url' => $providerData['api_url'] ?? null,
        ]);
    }

    protected function setupProviderData(): void {
        $provider = $this->getProviderData();
        if (!isset($this->driver)) {
            $this->driver = $provider['driver'] ?? config('laragent.default_driver');
        }
        if (!isset($this->history)) {
            $this->history = $provider['chat_history'] ?? config('laragent.default_chat_history');
        }
        $this->providerName = $provider['name'] ?? '';
        $this->setupDriverConfigs($provider);

        $this->initDriver($provider);
    }

    protected function setupAgent(): void {
        $config = [
            'model' => $this->model,
        ];
        if (isset($this->maxCompletionTokens)) {
            $config['max_completion_tokens'] = $this->maxCompletionTokens;
        }
        if (isset($this->contextWindowSize)) {
            $config['context_window_size'] = $this->contextWindowSize;
        }
        if (isset($this->temperature)) {
            $config['temperature'] = $this->temperature;
        }
        if (isset($this->parallelToolCalls)) {
            $config['parallel_tool_calls'] = $this->parallelToolCalls;
        }
        $this->agent = new LarAgent($this->llmDriver, $this->chatHistory, $config);
    }

    protected function setup(): void {
        $this->setupProviderData();
        $this->initHistory();
        $this->setupAgent();
        // @todo setup agent events
    }
}
