# LarAgent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maestroerror/laragent.svg?style=flat-square)](https://packagist.org/packages/maestroerror/laragent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/maestroerror/laragent/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/maestroerror/laragent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/maestroerror/laragent/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/maestroerror/laragent/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/maestroerror/laragent.svg?style=flat-square)](https://packagist.org/packages/maestroerror/laragent)

The **easiest** way to **create** and **maintain** AI agents in your Laravel projects.

Jump to [Table of Contents](#table-of-contents)

_Need to use LarAgent outside of Laravel? Check out this [Docs](https://github.com/MaestroError/LarAgent/blob/main/LARAGENT.md)._

## Introduction

LarAgent brings the power of AI agents to your Laravel projects with an elegant syntax. Create, extend, and manage AI agents with ease while maintaining Laravel's fluent API design patterns.

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
- Laravel-style artisan commands
- Flexible agent configuration (model, temperature, context window, etc.)
- Structured output handling
- Image input support
- Easily extendable, including chat histories and LLM drivers
- Multiple built-in chat history storage options (in-memory, cache, json, etc.)
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
- **Structured Output at runtime** - Allow defining the response JSON Schema at runtime.

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
- [Events](#events)
  - [Using Events](#using-events)
- [Commands](#commands)
  - [Creating an Agent](#creating-an-agent-1)
  - [Interactive Chat](#interactive-chat)
- [Advanced Usage](#advanced-usage)
  - [AI Agents as Tools](#ai-agents-as-tools)
  - [Creating Custom Providers](#creating-custom-providers)
  - [Creating Custom Chat Histories](#creating-custom-chat-histories)
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

You can create a new agent by extending the `LarAgent\Agent` class. This is the foundation for building your custom AI agent with specific capabilities and behaviors.

```php
namespace App\AiAgents;

use LarAgent\Agent;

class MyAgent extends Agent
{
    // Your agent implementation
}
```

For rapid development, you can use the artisan command to generate a new agent with a basic structure:

```bash
php artisan make:agent MyAgent
```

This will create a new agent class in the `App\AiAgents` directory with all the necessary boilerplate code.

#### Configuring agent

Agents can be configured through various properties and methods to customize their behavior. Here are the core configuration options:

```php
/** @var string - Define the agent's behavior and role */
protected $instructions;

/** @var string - Create your or Choose from built-in chat history: "in_memory", "session", "cache", "file", or "json" */
protected $history;

/** @var string - Specify which LLM driver to use */
protected $driver;

/** @var string - Select the AI provider configuration from your config file */
protected $provider = 'default';

/** @var string - Choose which language model to use */
protected $model = 'gpt-4o-mini';

/** @var int - Set the maximum number of tokens in the completion */
protected $maxCompletionTokens;

/** @var float - Control response creativity (0.0 for focused, 1.0 for creative) */
protected $temperature;

/** @var string|null - Current message being processed */
protected $message;
```

The agent also provides two core methods that you can override:

```php
/**
 * Define the agent's system instructions
 * This sets the behavior, role, and capabilities of your agent
 * For simple textual instructions, use the `instructions` property
 * For more complex instructions or dynamic behavior, use the `instructions` method
 */
public function instructions()
{
    return "Define your agent's instructions here.";
}

/**
 * Customize how messages are processed before sending to the AI
 * Useful for formatting, adding context (RAG), or standardizing input
 */
public function prompt(string $message)
{
    return $message;
}
```

Example:
```php
class WeatherAgent extends Agent
{
    protected $model = 'gpt-4';
    protected $history = 'cache';
    protected $temperature = 0.7;
    
    public function instructions()
    {
        return "You are a weather expert assistant. Provide accurate weather information.";
    }
    
    public function prompt(string $message)
    {
        return "Weather query: " . $message;
    }
}
```

#### Using agent

There are two ways to interact with your agent: direct response or chainable methods.

##### Direct Response
The simplest way is to use the `for()` method to specify a chat history name and get an immediate response:

```php
// Using a specific chat history name
echo WeatherAgent::for('test_chat')->respond('What is the weather like?');
```

##### Chainable Methods
For more control over the interaction, you can use the chainable syntax:

```php
$response = WeatherAgent::for('test_chat')
    ->message('What is the weather like?')  // Set the message
    ->temperature(0.7)                      // Optional: Override temperature
    ->respond();                            // Get the response
```

The `for()` and `forUser()` method allows you to maintain separate conversation histories for different contexts or users:

```php
// Different histories for different users
echo WeatherAgent::for('user_1_chat')->respond('What is the weather like?');
echo WeatherAgent::for('user_2_chat')->respond('How about tomorrow?');
echo WeatherAgent::forUser(auth()->user())->respond('How about tomorrow?');
```

Here are some chainable methods to modify the agents behavior on the fly:

```php
/**
 * Set the message for the agent to process
 */
public function message(string $message);

/**
 * Add images to the agent's input (mesasge)
 * @param array $imageUrls Array of image URLs
 */
public function withImages(array $imageUrls);

/**
 * Clear the chat history 
 * This removes all messages from the chat history
 */
public function clear();

/**
 * Set other chat history instance
 */
public function setChatHistory(ChatHistoryInterface $chatHistory);

/**
 * Add tool to the agent's registered tools
 */
public function withTool(ToolInterface $tool);

/**
 * Remove tool for this specific call
 */
public function removeTool(string $name);

/**
 * Override the temperature for this specific call
 */
public function temperature(float $temp);
```


##### Agent accessors

You can access the agent's properties using these methods on an instance of the agent:

```php
/**
 * Get the current chat session ID
 * String like "[AGENT_NAME]_[MODEL_NAME]_[CHAT_NAME]"
 * CHAT_NAME is defined by "for" method
 * Example: WeatherAgent_gtp-4o-mini_test-chat
 */
public function getChatSessionId(): string;
/**
 * Returns the provider name
 */
public function getProviderName(): string;
/**
 * Returns an array of registered tools
 */
public function getTools(): array;
/**
 * Returns current chat history instance
 */
public function chatHistory(): ChatHistoryInterface;
/**
 * Returns the current message
 */
public function currentMessage(): ?string;
/**
 * Returns the last message
 */
public function lastMessage(): ?MessageInterface;
```

### Tools

Tools are used to extend the functionality of agents. They can be used to perform tasks such as sending messages, running jobs, making API calls, or executing shell commands.

Here's a quick example of creating a tool using the `#[Tool]` attribute:

```php
use LarAgent\Attributes\Tool;
// ...
#[Tool('Get the current weather')]
public function getWeather(string $city)
{
    return WeatherService::getWeather($city);
}
```



Tools in LarAgent can be configured using these properties:

```php
/** @var bool - Controls whether tools can be executed in parallel */
protected $parallelToolCalls;

/** @var array - List of tool classes to be registered with the agent */
protected $tools = [];
```

There are three ways to create and register tools in your agent:

1. **Using the registerTools Method**
This method allows you to programmatically create and register tools:

```php
use LarAgent\Tool;
// ...
public function registerTools() 
{
    return [
        Tool::create("user_location", "Returns user's current location")
             ->setCallback(function () use ($user) {
                  return $user->location()->city;
             }),
        Tool::create("get_current_weather", "Returns the current weather in a given location")
             ->addProperty("location", "string", "The city and state, e.g. San Francisco, CA")
             ->setCallback("getWeather"),
    ];
}
```

2. **Using the #[Tool] Attribute**
The `#[Tool]` attribute provides a simple way to create tools from class methods:

```php
use LarAgent\Attributes\Tool;
// Basic tool with parameters
#[Tool('Get the current weather in a given location')]
public function weatherTool($location, $unit = 'celsius')
{
    return 'The weather in '.$location.' is '.'20'.' degrees '.$unit;
}
```
Agent will automatically register tool with given description as `Tool` attribute's first argument and other method info,
such as method name, required and optional parameters.

`Tool` attribute also accepts a second argument, which is an array mapping parameter names to their descriptions for more precise control. Also, it can be used with Static methods and parameters with Enum as type, where you can specify the values for the Agent to choose from.

**Enum**
```php
namespace App\Enums;

enum Unit: string
{
    case CELSIUS = 'celsius';
    case FAHRENHEIT = 'fahrenheit';
}
```

**Agent class**
```php
use LarAgent\Attributes\Tool;
use App\Enums\Unit;
// ...
#[Tool(
    'Get the current weather in a given location',
    ['unit' => 'Unit of temperature', 'location' => 'The city and state, e.g. San Francisco, CA']
)]
public static function weatherToolForNewYork(Unit $unit, $location = 'New York')
{
    return WeatherService::getWeather($location, $unit->value);
}
```

So the tool registered for your LLM will define `$unit` as enum of "celsius" and "fahrenheit" and required parameter, but `$location` will be optional, of course with coresponding descriptions from `Tool` attribute's second argument.

_Recommended to use `#[Tool]` attribute with static methods if there is no need for agent instance ($this)_

3. **Using Tool Classes**
You can create separate tool classes and add them to the `$tools` property:

```php
protected $tools = [
    WeatherTool::class,
    LocationTool::class
];
```

It's recommended to use tool classes with any complex workflows as they provide more control over the tool's behavior, maintainability and reusability (can be used in different agents).

_Tool creation command coming soon_

Tool class example:
```php
class WeatherTool extends LarAgent\Tool
{
    protected string $name = 'get_current_weather';

    protected string $description = 'Get the current weather in a given location';

    protected array $properties = [
        'location' => [
            'type' => 'string',
            'description' => 'The city and state, e.g. San Francisco, CA',
        ],
        'unit' => [
            'type' => 'string',
            'description' => 'The unit of temperature',
            'enum' => ['celsius', 'fahrenheit'],
        ],
    ];

    protected array $required = ['location'];

    protected array $metaData = ['sent_at' => '2024-01-01'];

    public function execute(array $input): mixed
    {
        // Call the weather API
        return 'The weather in '.$input['location'].' is '.rand(10, 60).' degrees '.$input['unit'];
    }
}
```

### Chat History

Chat history is used to store the conversation history between the user and the agent. LarAgent provides several built-in chat history implementations and allows for custom implementations.

#### Built-in Chat Histories

In Laravel:
```php
protected $history = 'in_memory';  // Stores chat history temporarily in memory (lost after request)
protected $history = 'session';    // Uses Laravel's session storage
protected $history = 'cache';      // Uses Laravel's cache system
protected $history = 'file';       // Stores in files (storage/app/chat-histories)
protected $history = 'json';       // Stores in JSON files (storage/app/chat-histories)
```

Outside Laravel:
```php
LarAgent\History\InMemoryChatHistory::class  // Stores chat history in memory
LarAgent\History\JsonChatHistory::class      // Stores in JSON files
```

#### Chat History Configuration

Chat histories can be configured using these properties.

```php
/** @var int - Number of messages after which to reinject the agent's instructions */
protected $reinjectInstructionsPer;
```
Instructions are always injected at the beginning of the chat history, `$reinjectInstructionsPer` defined when to reinject the instructions. By default it is set to `0` (disabled).

```php
/** @var int - Maximum number of tokens to keep in context window */
protected $contextWindowSize;
```
After the context window is exceeded, the oldest messages are removed until the context window is satisfied or the limit is reached. You can implement custom logic for the context window management using events and chat history instance inside your agent.

```php
/** @var bool - Whether to store additional metadata with messages */
protected $storeMeta;
```
Some LLM drivers such as OpenAI provide additional data with the response, such as token usage, completion time, etc. By default it is set to `false` (disabled).

#### Creating Custom Chat History

You can create your own chat history implementation by implementing the `ChatHistoryInterface` and extending the `LarAgent\Core\Abstractions\ChatHistory` abstract class.

There are two ways to register your chat history into an agent. If you use standard construction only with `$name` parameter, you can define it in by class in `$history` property or provider configuration:

**Agent Class**
```php
protected $history = \App\ChatHistories\CustomChatHistory::class;
```
**Provider Configuration (config/laragent.php)**
```php
'chat_history' => \App\ChatHistories\CustomChatHistory::class,
```

If you need any other configuration other than `$name`, you can override `createChatHistory()` method:

```php
public function createChatHistory($name)
{
    return new \App\ChatHistories\CustomChatHistory($name, ['folder' => __DIR__.'/history']);
}
```

#### Using Chat History

Chat histories are automatically managed based on the chat session ID. You can use the `for()` or `forUser()` methods to specify different chat sessions:

```php
// Using specific chat history name
$agent = WeatherAgent::for('weather-chat');

// Using user-specific chat history
$agent = WeatherAgent::forUser(auth()->user());

// Clear chat history
$agent->clear();

// Get last message
$lastMessage = $agent->lastMessage();
```
You can access chat history instance with `chatHistory()` method from the agent instance:

```php
// Access chat history instance
$history = $agent->chatHistory();
```

Here are several methods you can use with Chat History:
```php
public function addMessage(MessageInterface $message): void;
public function getMessages(): array;
public function getIdentifier(): string;
public function getLastMessage(): ?MessageInterface;
public function count(): int;
public function clear(): void;
public function toArray(): array;
public function toArrayWithMeta(): array;
public function setContextWindow(int $tokens): void;
public function exceedsContextWindow(int $tokens): bool;
```

The chat history is created with the following configuration:
```php
$historyInstance = new $historyClass($sessionId, [
    'context_window' => $this->contextWindowSize,  // Control token limit
    'store_meta' => $this->storeMeta,             // Store additional message metadata
]);
```

### Structured Output

Structured output allows you to define the exact format of the agent's response using JSON Schema. When structured output is enabled, the `respond()` method will return an array instead of a string, formatted according to your schema.

#### Defining Schema

You can define the response schema in your agent class using the `$responseSchema` property:

```php
protected $responseSchema = [
    'type' => 'object',
    'properties' => [
        'temperature' => [
            'type' => 'number',
            'description' => 'Temperature in degrees'
        ],
    ],
    'required' => ['temperature']
];
```

For defining more complex schemas you can add the `structuredOutput` method in you agent class:

```php
public function structuredOutput()
{
    return [
        'type' => 'object',
        'properties' => [
            'temperature' => [
                'type' => 'number',
                'description' => 'Temperature in degrees'
            ],
            'conditions' => [
                'type' => 'string',
                'description' => 'Weather conditions (e.g., sunny, rainy)'
            ],
            'forecast' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'day' => ['type' => 'string'],
                        'temp' => ['type' => 'number']
                    ]
                ],
                'description' => '5-day forecast'
            ]
        ],
        'required' => ['temperature', 'conditions']
    ];
}
```

The schema follows the JSON Schema specification and supports all its features including:
- Basic types (string, number, boolean, array, object)
- Required properties
- Nested objects and arrays
- Property descriptions
- Enums and patterns


#### Using Structured Output

When structured output is defined, the agent's response will be automatically formatted and returned as an array according to the schema:

```php
// Returns:
[
    'temperature' => 25.5,
    'conditions' => 'sunny',
    'forecast' => [
        ['day' => 'tomorrow', 'temp' => 28],
        ['day' => 'Wednesday', 'temp' => 24]
    ]
]
```

The schema can be accessed or modified using the `structuredOutput()` method at runtime:

```php
// Get current schema
$schema = $agent->structuredOutput();

// Check if structured output is enabled
if ($agent->structuredOutput()) {
    // Handle structured response
}
```

### Usage in and outside of Laravel

Agent classes is powered by LarAgent's main class `LarAgent\LarAgent`, which often refered as "LarAgent engine".       
Laragent engine is standalone part which holds all abstractions and doesn't depend on Laravel. It is used to create and manage agents, tools, chat histories, structured output and etc.

So you can use LarAgent's engine outside of Laravel as well. Usage is a bit different than inside Laravel, but the principles are the same.

Check out the [Docs](https://github.com/MaestroError/LarAgent/blob/main/LARAGENT.md) for more information.

## Events

@todo list of event

### Using Events

@todo Descriptions for driver and agent specific events

@todo usage examples

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


### Creating Custom Providers

// @todo add example


### Creating Custom chat histories

// @todo add example



## Contributing

We welcome contributions to LarAgent! Whether it's improving documentation, fixing bugs, or adding new features, your help is appreciated. Here's how you can contribute:

### Development Setup

1. Fork the repository
2. Clone your fork:
```bash
git clone https://github.com/YOUR_USERNAME/LarAgent.git
cd LarAgent
```
3. Install dependencies:
```bash
composer install
```
4. Create a new branch:
```bash
git checkout -b feature/your-feature-name
```

### Guidelines

1. **Code Style**
   - Use type hints and return types where possible
   - Add PHPDoc blocks for classes and methods
   - Keep methods focused and concise

2. **Testing**
   - Add tests for new features
   - Ensure all tests pass before submitting:
   ```bash
   composer test
   ```
   - Maintain or improve code coverage

3. **Documentation**
   - Update README.md for significant changes
   - Add PHPDoc blocks for new classes and methods
   - Include examples for new features

4. **Commits**
   - Use clear, descriptive commit messages
   - Reference issues and pull requests
   - Keep commits focused and atomic

### Pull Request Process

1. Update your fork with the latest changes from main:
```bash
git remote add upstream https://github.com/MaestroError/LarAgent.git
git fetch upstream
git rebase upstream/main
```

2. Push your changes:
```bash
git push origin feature/your-feature-name
```

3. Create a Pull Request with:
   - Clear title and description
   - List of changes and impact
   - Any breaking changes highlighted
   - Screenshots/examples if relevant

### Getting Help

- Open an issue for bugs or feature requests
- Join discussions in existing issues (@todo add discord channel invite link)
- Reach out to maintainers for guidance

We aim to review all pull requests within a 2 weeks. Thank you for contributing to LarAgent!

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

Please see [Planned](#planned) for more information on the future development of LarAgent.
