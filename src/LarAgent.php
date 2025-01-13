<?php

namespace Maestroerror\LarAgent;

use Maestroerror\LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use Maestroerror\LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;
use Maestroerror\LarAgent\Core\Contracts\Message as MessageInterface;
use Maestroerror\LarAgent\Core\Contracts\ToolCall as ToolCallInterface;
use Maestroerror\LarAgent\Core\Traits\Hooks;
use Maestroerror\LarAgent\Messages\ToolCallMessage;
use Maestroerror\LarAgent\Messages\ToolResultMessage;

class LarAgent
{
    use Hooks;

    protected string $model = 'gpt-4o-mini';

    protected int $contextWindowSize = 50000;

    protected int $maxCompletionTokens = 1000;

    protected int $temperature = 1;

    protected int $reinjectInstructionsPer = 0; // 0 Means never

    protected bool $parallelToolCalls = true;

    protected string $instructions;

    protected ?MessageInterface $message;

    protected array $responseSchema;

    protected LlmDriverInterface $driver;

    protected ChatHistoryInterface $chatHistory;

    protected array $tools = [];

    // Config methods

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function useModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getContextWindowSize(): int
    {
        return $this->contextWindowSize;
    }

    public function setContextWindowSize(int $contextWindowSize): self
    {
        $this->contextWindowSize = $contextWindowSize;

        return $this;
    }

    public function getMaxCompletionTokens(): int
    {
        return $this->maxCompletionTokens;
    }

    public function setMaxCompletionTokens(int $maxCompletionTokens): self
    {
        $this->maxCompletionTokens = $maxCompletionTokens;

        return $this;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getReinjectInstuctionsPer(): int
    {
        return $this->reinjectInstructionsPer;
    }

    public function setReinjectInstuctionsPer(int $reinjectInstructionsPer): self
    {
        $this->reinjectInstructionsPer = $reinjectInstructionsPer;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions ?? null;
    }

    public function withInstructions(string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getCurrentMessage(): ?MessageInterface
    {
        return $this->message ?? null;
    }

    public function withMessage(MessageInterface $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getResponseSchema(): ?array
    {
        return $this->responseSchema ?? null;
    }

    public function structured(array $responseSchema): self
    {
        $this->responseSchema = $responseSchema;

        return $this;
    }

    // Main API methods

    public function __construct(LlmDriverInterface $driver, ChatHistoryInterface $chatHistory)
    {
        $this->driver = $driver;
        $this->chatHistory = $chatHistory;
    }

    public static function setup(LlmDriverInterface $driver, ChatHistoryInterface $chatHistory, array $configs = []): self
    {
        $agent = new self($driver, $chatHistory);
        $agent->setConfigs($configs);

        return $agent;
    }

    public function setConfigs(array $configs): void
    {
        $this->contextWindowSize = $configs['contextWindowSize'] ?? $this->contextWindowSize;
        $this->maxCompletionTokens = $configs['maxCompletionTokens'] ?? $this->maxCompletionTokens;
        $this->temperature = $configs['temperature'] ?? $this->temperature;
        $this->reinjectInstructionsPer = $configs['reinjectInstructionsPer'] ?? $this->reinjectInstructionsPer;
        $this->model = $configs['model'] ?? $this->model;
    }

    public function setTools(array $tools): self
    {
        $this->tools = $tools;

        return $this;
    }

    public function registerTool(array $tools): self
    {
        $this->tools[] = $tools;

        return $this;
    }

    public function getParallelToolCalls(): bool
    {
        return $this->parallelToolCalls ?? false;
    }

    public function setParallelToolCalls(bool $parallelToolCalls): self
    {
        $this->parallelToolCalls = $parallelToolCalls;

        return $this;
    }

    // Execution method
    public function run(): MessageInterface|array|null
    {

        // Manage instructions
        $totalMessages = $this->chatHistory->count();

        if ($totalMessages === 0 && $this->getInstructions()) {
            $this->injectInstructions();
        } else {
            // Reinject instructions if ReinjectInstuctionsPer is defined
            $iip = $this->getReinjectInstuctionsPer();
            if ($iip && $iip > 0 && $totalMessages % $iip > 0 && $totalMessages % $iip <= 5) {
                // If any callback returns false, it will stop the process silently
                if ($this->processBeforeReinjectingInstructions() !== false) {
                    $this->injectInstructions();
                }
            }
        }

        // Register tools
        if (! empty($this->tools)) {
            foreach ($this->tools as $tool) {
                $this->driver->registerTool($tool);
            }
        }

        // Set response schema
        if ($this->getResponseSchema()) {
            $this->driver->setResponseSchema($this->responseSchema);
        }

        // Before send (Before adding message in chat history)
        if ($this->processBeforeSend($this->chatHistory, $this->getCurrentMessage()) === false) {
            return null;
        }

        // Send message
        $response = $this->send($this->message);

        // After send (After adding LLM response to Chat history)
        if ($this->processAfterSend($this->chatHistory, $response) === false) {
            return null;
        }

        // if response execution is interrupted by a callback
        if (! $response) {
            return null;
        }

        if ($response instanceof ToolCallMessage) {
            // Process tool
            $this->processTools($response);
            // Set message to null to skip adding it again in chat history
            $this->message = null;

            return $this->run();
        }

        // Before saving chat history
        $this->processBeforeSaveHistory($this->chatHistory);
        // Save chat history to memory
        $this->chatHistory->writeToMemory();

        if ($this->driver->structuredOutputEnabled()) {
            $array = json_decode($response->getContent(), true);
            // Before structured output response
            if ($this->processBeforeStructuredOutput($array) === false) {
                return null;
            }

            return $array;
        } else {
            return $response;
        }
    }

    // Helper methods

    protected function send(?MessageInterface $message): ?MessageInterface
    {
        if ($message) {
            $this->chatHistory->addMessage($message);
        }
        // Before response (Before sending message to LLM)
        // If any callback will return false, it will stop the process silently
        // If you want to rise an exception, you can do it in the callback
        if ($this->processBeforeResponse($this->chatHistory, $message) === false) {
            return null;
        }

        $response = $this->driver->sendMessage($this->chatHistory->toArray(), $this->buildConfig());
        // After response (After receiving message from LLM)
        $this->processAfterResponse($response);
        $this->chatHistory->addMessage($response);

        return $response;
    }

    protected function buildConfig(): array
    {
        $configs = [
            'model' => $this->getModel(),
            'max_completion_tokens' => $this->getMaxCompletionTokens(),
            'temperature' => $this->getTemperature(),
        ];

        if (!empty($this->tools)) {
            $configs['parallel_tool_calls'] = $this->getParallelToolCalls();
            // @todo make tool choice controllable (required & specific tool)
            $configs['tool_choice'] = 'auto';
        }

        return $configs;
    }

    protected function injectInstructions(): void
    {
        $this->chatHistory->addMessage(Message::system($this->getInstructions()));
    }

    protected function processTools(ToolCallMessage $message): void
    {
        foreach ($message->getToolCalls() as $toolCall) {
            $result = $this->processToolCall($toolCall);
            if (! $result) {
                continue;
            }
            $this->chatHistory->addMessage($result);
        }
    }

    protected function processToolCall(ToolCallInterface $toolCall): ?ToolResultMessage
    {
        $tool = $this->driver->getTool($toolCall->getToolName());
        $args = json_decode($toolCall->getArguments(), true);
        // Before tool execution, skip tool if false returned
        if ($this->processBeforeToolExecution($tool) === false) {
            return null;
        }

        $result = $tool->execute($args);

        // After tool execution, skip adding result to chat history if false returned
        if ($this->processAfterToolExecution($tool, $result) === false) {
            return null;
        }

        // Build tool result message content
        $messageArray = $this->driver->toolResultToMessage($toolCall, $result);

        return new ToolResultMessage($messageArray);
    }
}
