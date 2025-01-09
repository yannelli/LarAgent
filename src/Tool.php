<?php

namespace Maestroerror\LarAgent;

use Maestroerror\LarAgent\Core\Abstractions\Tool as AbstractTool;

class Tool extends AbstractTool
{
    protected mixed $callback = null;

    public function setCallback(?callable $callback): Tool
    {
        $this->callback = $callback;

        return $this;
    }

    public function execute(): mixed
    {
        $input = $this->args;
        if ($this->callback === null) {
            throw new \BadMethodCallException('No callback defined for execution.');
        }

        // Validate required parameters
        foreach ($this->required as $param) {
            if (! array_key_exists($param, $input)) {
                throw new \InvalidArgumentException("Missing required parameter: {$param}");
            }
        }

        // Execute the callback with input
        return call_user_func($this->callback, ...$input);
    }

    public static function create(string $name, string $description): Tool
    {
        return new self($name, $description);
    }
}
