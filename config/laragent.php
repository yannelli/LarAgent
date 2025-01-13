<?php

// config for Maestroerror/LarAgent
return [
    'default_driver' => \Maestroerror\LarAgent\Drivers\OpenAi\OpenAiDriver::class,
    'default_chat_history' => \Maestroerror\LarAgent\ChatHistory\InMemoryChatHistory::class,

    'providers' => [
        'default' => [
            'name' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'default_context_window' => 50000,
            'default_max_completion_tokens' => 100,
            'default_temperature' => 1,
        ],
        // Example custom provider
        'custom_provider' => [
            'name' => 'mini',
            'model' => 'gpt-3.5-turbo',
            'api_key' => env('CUSTOM_API_KEY'),
            'driver' => \Maestroerror\LarAgent\Drivers\OpenAi\OpenAiDriver::class,
            'chat_history' => \Maestroerror\LarAgent\ChatHistory\InMemoryChatHistory::class,
            'default_context_window' => 15000,
        ],
    ],
];
