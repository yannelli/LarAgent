<?php

namespace Maestroerror\LarAgent\Core\Abstractions;

use Maestroerror\LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;
use Maestroerror\LarAgent\Core\Contracts\Tool as ToolInterface;

abstract class LlmDriver implements LlmDriverInterface
{
    protected array $config = [];

    protected ?array $responseSchema = null;

    protected mixed $lastResponse = null;

    protected array $tools = [];

    public function registerTool(ToolInterface $tool): self
    {
        $name = $tool->getName();
        $this->tools[$name] = $tool;

        return $this;
    }

    public function getRegisteredTools(): array
    {
        return $this->tools;
    }

    public function getTool(string $name): ToolInterface
    {
        return $this->tools[$name];
    }

    public function setResponseSchema(array $schema): self
    {
        $this->responseSchema = $schema;

        return $this;
    }

    public function getResponseSchema(): ?array
    {
        return $this->responseSchema;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getLastResponse(): ?array
    {
        return $this->lastResponse;
    }

    protected function getRegisteredFunctions(): array
    {
        return array_map(fn (ToolInterface $tool) => $tool->toArray(), $this->tools);
    }

    public function structuredOutputEnabled(): bool
    {
        return ! empty($this->getResponseSchema());
    }
}
