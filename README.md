# LarAgent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maestroerror/laragent.svg?style=flat-square)](https://packagist.org/packages/maestroerror/laragent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/maestroerror/laragent/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/maestroerror/laragent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/maestroerror/laragent/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/maestroerror/laragent/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/maestroerror/laragent.svg?style=flat-square)](https://packagist.org/packages/maestroerror/laragent)

The **easiest** way to **create** and **maintain** AI agents in your Laravel projects.

## Introduction

LarAgent brings the power of AI agents to your Laravel projects with an elegant syntax. Create, extend, and manage AI agents with ease while maintaining Laravel's fluent API design patterns.

_Need to use LarAgent outside of Laravel? Check out this [Docs](https://github.com/MaestroError/LarAgent/blob/main/LARAGENT.md)._

What if you can create AI agents just like you create any other Eloquent model?

Why not?! 👇

```bash
php artisan make:agent YourAgentName
```

And it looks familiar, isn't it?

```php
namespace App\AiAgents;

use LarAgent\Agent;

class YourAgentName extends Agent
{
    protected $model = 'gpt-4';

    protected $history = 'in_memory';

    protected $provider = 'default';

    protected $tools = [];

    public function instructions()
    {
        return "Define your agent's instructions here.";
    }

    public function prompt($message)
    {
        return $message;
    }
}

```

And you can tweak the configs, like `history`

```php
// ...
protected $history = \LarAgent\History\CacheChatHistory::class;
// ...
```

Or add `temperature`:
 
```php
// ...
protected $temperature = 0.5;
// ...
```
Even disable parallel tool calls:
 
```php
// ...
protected $parallelToolCalls = false;
// ...
```

Oh, and add a new tool as well:

```php
// ...
#[Tool('Get the current weather in a given location')]
public function exampleWeatherTool($location, $unit = 'celsius')
{
    return 'The weather in '.$location.' is '.'20'.' degrees '.$unit;
}
// ...
```

And run it, per user:

```php
Use App\AiAgents\YourAgentName;
// ...
YourAgentName::forUser(auth()->user())->respond($message);
```

Or use a custom name for the chat history:

```php
Use App\AiAgents\YourAgentName;
// ...
YourAgentName::for("custom_history_name")->respond($message);
```

Let's find out more with [documentation](#table-of-contents) below 👍

## Features

- Eloquent-like syntax for creating and managing AI agents
- Laravel-style artisan command for agent generation (`make:agent`)
- Flexible agent configuration (model, temperature, context window, etc.)
- Structured output handling
- Image input support
- Easily extendable, including chat histories and LLM drivers
- Multiple built-in chat history storage options (in-memory, cache, json)
    - Per-user chat history management
    - Custom chat history naming support
- Custom tool creation with attribute-based configuration
    - Tools via classes
    - Tools via methods of AI agent class (Auto)
    - `Tool` facade for shortened tool creation
    - Parallel tool execution capability (can be disabled)
- Extensive Event system for agent interactions (Nearly everything is hookable)
- Multiple provider support (Can be set per model)
- Support for both Laravel and standalone usage

## Planned

Here's what's coming next to make LarAgent even more powerful:

### Developer Experience 🛠️
- **Artisan Commands for Rapid Development**
  - `make:agent:tool` - Generate tool classes with ready-to-use stubs
  - `make:agent:chat-history` - Scaffold custom chat history implementations
  - `make:llm-driver` - Create custom LLM driver integrations

### Enhanced AI Capabilities 🧠
- **Prism Package Integration** - Additional LLM providers support
- **Streaming Support** - Out-of-the-box support for streaming responses
- **RAG & Knowledge Base** 
  - Built-in vector storage providers
  - Seamless document embeddings integration
  - Smart context management
- **Ready-to-use Tools** - Built-in tools as traits

### Security & Storage 🔒
- **Enhanced Chat History Security** - Optional encryption for sensitive conversations

### Advanced Integrations 🔌
- **Provider Fallback System** - Automatic fallback to alternative providers
- **Laravel Actions Integration** - Use your existing Actions as agent tools
- **Voice Chat Support** - Out of the box support for voice interactions with your agents

Stay tuned! We're constantly working on making LarAgent the most versatile AI agent framework for Laravel.

## Table of Contents

- [Introduction](#introduction)
- [Getting Started](#getting-started)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Configuration](#configuration)
- [Core Concepts](#core-concepts)
  - [Agents](#agents)
  - [Tools](#tools)
  - [Chat History](#chat-history)
  - [Structured Output](#structured-output)
  - [Usage without Laravel](#usage-in-and-outside-of-laravel)
- [Basic Usage](#basic-usage)
  - [Creating an Agent](#creating-an-agent)
  - [Using Tools](#using-tools)
  - [Managing Chat History](#managing-chat-history)
  - [Using Events](#using-events)
- [Commands](#commands)
  - [Creating an Agent](#creating-an-agent-1)
  - [Interactive Chat](#interactive-chat)
- [Advanced Usage](#advanced-usage)
  - [Custom Agents](#custom-agents)
  - [Custom Tools](#custom-tools)
  - [Providers and Models](#providers-and-models)
  - [Advanced Configuration](#advanced-configuration)
- [Examples](#examples)
  - [Weather Agent Example](#weather-agent-example)
  - [Common Use Cases](#common-use-cases)
- [Contributing](#contributing)
- [Testing](#testing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)
- [Roadmap](#roadmap)

## Getting Started

### Requirements

*   Laravel 10.x or higher
*   PHP 8.3 or higher

### Installation

You can install the package via composer:

```bash
composer require maestroerror/laragent
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laragent-config"
```

This is the contents of the published config file:

```php
return [
    'default_driver' => \LarAgent\Drivers\OpenAi\OpenAiDriver::class,
    'default_chat_history' => \LarAgent\History\InMemoryChatHistory::class,

    'providers' => [

        'default' => [
            'name' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'default_context_window' => 50000,
            'default_max_completion_tokens' => 100,
            'default_temperature' => 1,
        ],
    ],
];

```

### Configuration

You can configure the package by editing the `config/laragent.php` file. Here is an example of custom provider with all possible configurations you can apply:

```php
    // Example custom provider with all possible configurations
    'custom_provider' => [
        // Just name for reference, changes nothing
        'name' => 'mini',
        'model' => 'gpt-3.5-turbo',
        'api_key' => env('CUSTOM_API_KEY'),
        'api_url' => env('CUSTOM_API_URL'),
        // Default driver and chat history
        'driver' => \LarAgent\Drivers\OpenAi\OpenAiDriver::class,
        'chat_history' => \LarAgent\History\InMemoryChatHistory::class,
        'default_context_window' => 15000,
        'default_max_completion_tokens' => 100,
        'default_temperature' => 1,
        // Enable/disable parallel tool calls
        'parallel_tool_calls' => true,
        // Store metadata with messages
        'store_meta' => true,
    ],
```

Provider just gives you the defaults. Every config can be overridden per agent in agent class.

## Core Concepts

### Agents

@todo Table of contents for Agents section

Agents are the core of LarAgent. They represent a conversational AI model that can be used to interact with users, systems, or any other source of input.

#### Creating Agent
@todo agent creation happens by extending `LarAgent\Agent` class
@todo you can make it faster using make agent command
#### Configuring agent
@todo you are configuring it by adding tools, chat history, model, provider, etc
@todo list of all possible configurations of agents and their short descriptions


    /** @var string */
    protected $instructions;

    /** @var string */
    protected $history;

    /** @var string */
    protected $driver;

    /** @var string */
    protected $provider = 'default';

    /** @var string */
    protected $model = 'gpt-4o-mini';

    /** @var int */
    protected $maxCompletionTokens;

    /** @var float */
    protected $temperature;

/** @var string|null */
    protected $message;

@todo few overridable methods with different purposes (add short descriptions)
/**
     * Get the instructions for the agent
     *
     * @return string The agent's instructions
     */
    public function instructions()
    {
        return $this->instructions;
    }

    /**
     * Process a message before sending to the agent
     *
     * @param  string  $message  The message to process
     * @return string The processed message
     */
    public function prompt(string $message)
    {
        return $message;
    }


#### Using agent
@todo usage examples for short and chainable versions
echo WeatherAgent::for('test_chat')->respond('Where am I now?');
echo WeatherAgent::for('test_chat')->message('Where am I now?')->respond();


### Tools

@todo Table of contents for Tools section

Tools are used to extend the functionality of agents. They can be used to perform tasks such as sending messages, running jobs, making API calls, or executing shell commands.

// @todo small example and link to Using Tools section


    /** @var bool */
    protected $parallelToolCalls;

    /** @var array */
    protected $tools = [];

    /**
     * Register additional tools for the agent
     *
     * Override this method in child classes to register custom tools.
     * Tools should be instances of LarAgent\Tool class.
     *
     * Example:
     * ```php
     * public function registerTools() {
     *     return [
     *         Tool::create("user_location", "Returns user's current location")
     *              ->setCallback(function () use ($user) {
     *                   return $user->location()->city;
     *              }),
     *         Tool::create("get_current_weather", "Returns the current weather in a given location")
     *              ->addProperty("location", "string", "The city and state, e.g. San Francisco, CA")
     *              ->setCallback("getWeather"),
     *     ];
     * }
     * ```
     *
     * @return array Array of Tool instances
     */
    public function registerTools()
    {
        return [];
    }

@todo detailed description of tool creation by agent class methods. examples:
    // Example of a tool defined as a method with optional and required parameters
    #[Tool('Get the current weather in a given location')]
    public function weatherTool($location, $unit = 'celsius')
    {
        return 'The weather in '.$location.' is '.'20'.' degrees '.$unit;
    }

    // @todo implement metadata support for tool attribute

    // Example of using static method as tool and all it's features
    // Tool Description, property descriptions, enums, required properties
    #[Tool('Get the current weather in a given location', ['unit' => 'Unit of temperature'])]
    public static function weatherToolForNewYork(Unit $unit)
    {
        return 'The weather in New York is '.'50'.' degrees '.$unit->value;
    }

### Chat History

Chat history is used to store the conversation history between the user and the agent.

// @todo What types of chat histories are supported now? In Laravel and outside?
// @todo how can be used?


    /** @var int */
    protected $reinjectInstructionsPer;

    /** @var int */
    protected $contextWindowSize;

    /**
     * Store message metadata with messages in chat history
     *
     * @var bool
     */
    protected $storeMeta;

    /**
     * Create a new chat history instance
     *
     * @param  string  $sessionId  The session ID for the chat history
     * @return ChatHistoryInterface The created chat history instance
     */
    public function createChatHistory(string $sessionId)
    {
        $historyClass = $this->builtInHistories[$this->history] ?? $this->history;

        return new $historyClass($sessionId, [
            'context_window' => $this->contextWindowSize,
            'store_meta' => $this->storeMeta,
        ]);
    }

### Structured Output

Structured output is used to define the format (JSON Schema) of the output generated by the agent.

// @todo how can be used in laravel?

    /** @var array */
    protected $responseSchema = [];

    /**
     * Get the structured output schema if any
     *
     * @return array|null The response schema or null if none set
     */
    public function structuredOutput()
    {
        return $this->responseSchema ?? null;
    }


### Usage in and outside of Laravel

Agent classes is powered by LarAgent's main class `LarAgent\LarAgent`, which often refered as "LarAgent engine".       
Laragent engine is standalone part which holds all abstractions and doesn't depend on Laravel. It is used to create and manage agents, tools, chat histories, structured output and etc.

So you can use LarAgent's engine outside of Laravel as well. Usage is a bit different than inside Laravel, but the principles are the same.

Check out the [Docs](https://github.com/MaestroError/LarAgent/blob/main/LARAGENT.md) for more information.

## Basic Usage

@todo usage examples for short and chainable versions
@todo image input example

### Creating an Agent

You can create an agent by extending the `LarAgent\Agent` class.

// @todo add agent creation command here

Here is an example of bery basic agent created by extending `LarAgent\Agent`:

```php

namespace App\AiAgents;

use LarAgent\Agent;
use App\AiTools\WeatherTool; // Example tool

class WeatherAgent extends Agent
{
    protected $model = "gpt-4o-mini";

    // Tool by classes
    protected $tools = [
        WeatherTool::class
    ];

    // Built in chat histories: "in_memory", "session", "cache", "file", "json"
    protected $history = "in_memory";

    public function instructions() {
        return "You are weather agent holding info about weather in any city.";
    }

    public function prompt($message) {
        return $message . ". Always check if I have other questions.";
    }
}
```

### Using Tools

You can use tools to extend the functionality of agents.

// @todo add examples of all types of tools creation and registration here

### Managing Chat History

You can manage chat history by using agent class per key or user.

// @todo add chat history management methods

### Using Events

@todo Descriptions for driver and agent specific events

## Commands

### Creating an Agent

You can quickly create a new agent using the `make:agent` command:

```bash
php artisan make:agent WeatherAgent
```

This will create a new agent class in your `app/AiAgents` directory with the basic structure and methods needed to get started.

### Interactive Chat

You can start an interactive chat session with any of your agents using the `agent:chat` command:

```bash
# Start a chat with default history name
php artisan agent:chat WeatherAgent

# Start a chat with a specific history name
php artisan agent:chat WeatherAgent --history=weather_chat_1
```

The chat session allows you to:
- Send messages to your agent
- Get responses in real-time
- Use any tools configured for the agent
- Type 'exit' to end the chat session

## Advanced Usage

### Ai agents as Tools

You can create tools which calls another agent and bind the result to the agent to create a chain or complex workflow.

// @todo add example


### Providers and chat histories

You can use custom providers and models to extend the functionality of agents.

// @todo add example


## Examples

### Weather Agent Example

You can use the `WeatherAgent` class to create a weather agent.

```php
use LarAgent\Attributes\Tool;
use LarAgent\Core\Contracts\ChatHistory;

class WeatherAgent extends LarAgent\Agent
{
    protected $model = "gpt-4o-mini";

    // Tool by classes
    protected $tools = [
        WeatherTool::class
    ];

    // Built in chat histories: "in_memory", "session", "cache", "file", "json"
    protected $history = "in_memory";

    // Or Define history with custom options or using custom history class
    // Note that defining createChatHistory method overrides the property-defined history
    public function createChatHistory($name) {
        return new LarAgent\History\JsonChatHistory($name, ['folder' => __DIR__.'/json_History']);
    }

    // Define instructions with external info
    public function instructions() {
        $user = auth()->user();
        return 
            "You are weather agent holding info about weather in any city.
            Always use User's name while responding.
            User info: " . json_encode($user->toArray());
    }

    // Define prompt using blade
    public function prompt($message) {
        return view('ai.prompts.weather', ['message' => $message])->render();
    }

    // Register quickly tools using \LarAgent\Tool
    public function registerTools() {
        $user = auth()->user();
        return [
            // Tool without properties
            \LarAgent\Tool::create("user_location", "Returns user's current location")
                 ->setCallback(function () use ($user) {
                      return $user->location;
                 }),
        ];
    }


    // Example of a tool defined as a method with optional and required parameters
    #[Tool("Get the current weather in a given location")]
    public function weatherTool($location, $unit = 'celsius') {
        return 'The weather in '.$location.' is ' . "20" . ' degrees '.$unit;
    }


    // Example of using static method as tool and all it's features
    // Tool Description, property descriptions, enums, required properties
    #[Tool("Get the current weather in a given location", ['unit' => "Unit of temperature"])]
    public static function weatherToolForNewYork(Unit $unit) {
        return 'The weather in New York is ' . "50" . ' degrees '. $unit->value;
    }
}
```

### Common Use Cases

You can use LarAgent to create conversational AI models for various use cases such as customer support, language translation, and more.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Testing

```bash
composer test
```

## Security

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [maestroerror](https://github.com/maestroerror)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Roadmap

Please see [ROADMAP](ROADMAP.md) for more information on the future development of LarAgent.
