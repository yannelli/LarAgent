<?php

namespace Maestroerror\LarAgent\Core\Abstractions;

use Maestroerror\LarAgent\Core\Contracts\Tool as ToolInterface;

abstract class Tool implements ToolInterface
{
    protected string $name;

    protected string $description;

    protected array $properties = [];

    protected array $required = [];

    protected array $metaData = [];

    protected array $args = [];

    protected string $toolCallId;

    public function __construct(string $name, string $description, $metaData = [])
    {
        $this->name = $name;
        $this->description = $description;
        $this->metaData = $metaData;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function addProperty(string $name, string|array $type, string $description = '', array $enum = []): self
    {
        $property = [
            'type' => $type,
        ];
        if ($description) {
            $property['description'] = $description;
        }
        if ($enum) {
            $property['enum'] = $enum;
        }
        $this->properties[$name] = $property;

        return $this;
    }

    public function setRequired(string $name): self
    {
        if (! array_key_exists($name, $this->properties)) {
            throw new \InvalidArgumentException("Property '{$name}' does not exist.");
        }

        $this->required[] = $name;

        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setMetaData(array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function setArguments(array $args): self
    {
        $this->args = $args;

        return $this;
    }

    public function getArguments(): array
    {
        return $this->args;
    }

    public function setCallId(string $id): self
    {
        $this->toolCallId = $id;

        return $this;
    }

    public function getCallId(): string
    {
        return $this->toolCallId;
    }

    public function toArray(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => [
                    'type' => 'object',
                    'properties' => $this->getProperties(),
                    'required' => $this->required,
                ],
            ],
        ];
    }

    

    abstract public function execute(array $input): mixed;
}
