# Power of AI Agents in your Laravel project

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maestroerror/laragent.svg?style=flat-square)](https://packagist.org/packages/maestroerror/laragent)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/maestroerror/laragent/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/maestroerror/laragent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/maestroerror/laragent/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/maestroerror/laragent/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/maestroerror/laragent.svg?style=flat-square)](https://packagist.org/packages/maestroerror/laragent)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## To Do

-   Fck The LLPhant, start with only OpenAI https://github.com/openai-php/client (& Anthropic https://github.com/mozex/anthropic-php ???), pincone, redis, file and in memory vector stores
-   Decisions: https://chatgpt.com/c/67621643-c474-8006-8fec-5a2df2844fd4
-   Main concerns: https://chatgpt.com/c/67620ef2-6ef0-8006-b752-5d7a7036d83b

## Goal

Laravel model-lika AI agent classes, extendable, easily maintainable. Fluent & Eloquent API. Easy setup. Easy access via facade + model name or model class

-   Chat memory
-   Tools
-   Structured output

**Setup goal:**

```php
<?php

namespace App\AiAgents;

use Maestroerror\LarAgent\Agent;
use Maestroerror\LarAgent\Tool;
use Maestroerror\LarAgent\ChatHistory\RedisChatHistory;
use App\AiTools\WeatherTool;

class WeatherAgent extends Agent {

    protected $provider = "default";
    protected $model = "gpt-4o-mini";

    // Should support also string values like "redis", "session", "file", "db"
    protected $history = RedisChatHistory::class;

    // Or use defaults
    protected $contextWindowSize = 20000;
    protected $maxCompletionTokens = 1000;
    protected $temperature = 1;
    protected $injectInstructionsPer = 50;

    // Tool by classes
    protected $tools = [
        WeatherTool::class
    ];

    public function instructions() {
        return "You are weather agent holding info about weather in any city.";
    }

    public function prompt(string $message) {
        return view("prompts.weather_prompt", ['user_message' => $message]);
    }

    // Tool by method
    public function locationTool() {
        $user = auth()->user();
        return Tool::create("user_location", "Returns user's current location")
            ->setCallback(function () use ($user) {
                return $user->location()->city;
            });
    }

    public function structuredOutput() {
        // Should support also json and separate php files
        return [
            "name" => "weather_info",
            "schema" => [
                "type" => "object",
                "properties" => [
                    "locations" => [
                        "type" => "array",
                        "items" => [
                            "type" => "object",
                            "properties" => [
                                "city" => [ "type" => "string" ],
                                "weather" => [ "type" => "string" ]
                            ],
                            "required" => ["city", "weather"],
                            "additionalProperties" => false
                        ]
                    ],
                ],
                "required" => ["locations"],
                "additionalProperties" => false
            ],
            "strict" => true
        ];
    }
}

```

**Usage goal:**

```php
WeatherAgent::for("test_chat")->respond("What is a weather in Tbilisi today?");
WeatherAgent::forUser($user)->respond("What is a weather in Tbilisi today?");
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/LarAgent.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/LarAgent)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

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
];
```

## Usage

```php
$larAgent = Maestroerror\LarAgent::setup([]);
echo $larAgent->respond('Hello, Maestroerror!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [maestroerror](https://github.com/maestroerror)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
