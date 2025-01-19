<?php

require_once __DIR__.'/vendor/autoload.php';


function config(string $key): mixed {
    $yourApiKey = include 'openai-api-key.php';
    return [
        'laragent.default_driver' => Maestroerror\LarAgent\Drivers\OpenAi\OpenAiDriver::class,
        'laragent.default_chat_history' => Maestroerror\LarAgent\History\InMemoryChatHistory::class,
        'laragent.providers.default' => [
            'name' => 'openai',
            'api_key' => $yourApiKey,
            'default_context_window' => 50000,
            'default_max_completion_tokens' => 100,
            'default_temperature' => 1,
        ],
    ][$key];
}

class WeatherAgent extends Maestroerror\LarAgent\Agent
{
    protected string $model = "gpt-4o";

    // Tool by classes
    protected array $tools = [
        WeatherTool::class
    ];

    protected string $history = Maestroerror\LarAgent\History\JsonChatHistory::class;

    public function instructions(): string {
        return "You are weather agent holding info about weather in any city.";
    }

    public function prompt(string $message): string {
        return $message . " Specify the check date in answer. Checked at 2024-01-01.";
    }
}

class WeatherTool extends Maestroerror\LarAgent\Tool
{
    protected string $name = "get_current_weather";

    protected string $description = "Get the current weather in a given location";

    protected array $properties = [
        'location' => [
            'type' => 'string',
            'description' => 'The city and state, e.g. San Francisco, CA'
        ],
        'unit' => [
            'type' => 'string',
            'description' => 'The unit of temperature',
            'enum' => ['celsius', 'fahrenheit']
        ]
    ];

    protected array $required = ['location'];

    protected array $metaData = ['sent_at' => '2024-01-01'];

    public function execute(array $input): mixed {
        // Call the weather API
        return 'The weather in '.$input['location'].' is ' . rand(10, 60) . ' degrees '.$input['unit'];
    }
}

echo WeatherAgent::for("test_chat")->respond('What\'s the weather like in Boston and Los Angeles? I prefer fahrenheit');
echo "\n";
echo WeatherAgent::for("test_chat")->respond('Thanks for the info. What about New York?');
