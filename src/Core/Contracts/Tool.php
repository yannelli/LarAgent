<?php

namespace Maestroerror\LarAgent\Core\Contracts;

interface Tool
{
    /**
     * Get the unique name of the tool.
     */
    public function getName(): string;

    /**
     * Get a description of the tool's functionality.
     */
    public function getDescription(): string;

    /**
     * Add a Property with a name, type, description, and optional enum values.
     */
    public function addProperty(string $name, string $type, string $description = "", array $enum = []): self;

    /**
     * Mark a Property as required.
     */
    public function setRequired(string $name): self;

    /**
     * Get the Properties schema.
     */
    public function getProperties(): array;

    /**
     * Generate the complete tool definition as an array.
     */
    public function toArray(): array;

    /**
     * Get the metadata for the tool.
     */
    public function getMetaData(): array;
    public function setMetaData(array $metaData): self;

    /**
     * Get/Set arguments for execution.
     */
    public function setArguments(array $args): self;
    public function getArguments(): array;

    /**
     * Get/Set tool_call_id for execution.
     */
    public function setCallId(string $id): self;
    public function getCallId(): string;

    /**
     * Execute the tool's logic with input parameters.
     *
     * @return mixed
     */
    public function execute(): mixed;
}

