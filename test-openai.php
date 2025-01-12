<?php

require_once __DIR__.'/vendor/autoload.php';

$yourApiKey = include 'openai-api-key.php';
$client = OpenAI::client($yourApiKey);

// Structured output
// $result = $client->chat()->create([
//     'model' => 'gpt-4o-mini',
//     'messages' => [
//         ['role' => 'system', 'content' => 'You are a helpful math tutor. Guide the user through the solution step by step.'],
//         ['role' => 'user', 'content' => 'how can I solve 8x + 7 = -23'],
//     ],
//     "response_format" => [
//         "type" => "json_schema",
//         "json_schema" => [
//           "name" => "math_reasoning",
//           "schema" => [
//             "type" => "object",
//             "properties" => [
//               "steps" => [
//                 "type" => "array",
//                 "items" => [
//                   "type" => "object",
//                   "properties" => [
//                     "explanation" => [ "type" => "string" ],
//                     "output" => [ "type" => "string" ]
//                   ],
//                   "required" => ["explanation", "output"],
//                   "additionalProperties" => false
//                 ]
//               ],
//               "final_answer" => [ "type" => "string" ]
//             ],
//             "required" => ["steps", "final_answer"],
//             "additionalProperties" => false
//           ],
//           "strict" => true
//         ]
//     ]
// ]);

// echo $result->choices[0]->message->content;

// var_dump(json_decode($result->choices[0]->message->content));

// Tool calling example
$messages = [
    ['role' => 'user', 'content' => 'What\'s the weather like in Boston? I prefer celsius'],
];

$response = $client->chat()->create([
    'model' => 'gpt-4o-mini',
    'messages' => $messages,
    'tools' => [
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_current_weather',
                'description' => 'Get the current weather in a given location',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and state, e.g. San Francisco, CA',
                        ],
                        'unit' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit'],
                        ],
                    ],
                    'required' => ['location'],
                ],
            ],
        ],
    ],
]);

foreach ($response->choices as $result) {
    $args = json_decode($result->message->toolCalls[0]->function->arguments, true);
    $unit = isset($args['unit']) ? $args['unit'] : 'celsius';
    $funcResult = call_user_func($result->message->toolCalls[0]->function->name, ...$args);
}

$toolsMessage = $response->choices[0]->message;
$messages[] = [
    'role' => 'assistant',
    'tool_calls' => [
        [
            'id' => $toolsMessage->toolCalls[0]->id,
            'type' => 'function',
            'function' => [
                'name' => 'get_current_weather',
                'arguments' => '{"location":"Boston, MA"}',
            ],
        ],
    ],
];

$messages[] = [
    'role' => 'tool',
    'content' => json_encode([
        ...$args,
        'weather' => $funcResult,
    ]),
    'tool_call_id' => $response->choices[0]->message->toolCalls[0]->id,
];

$response = $client->chat()->create([
    'model' => 'gpt-4o-mini',
    'messages' => $messages,
    'tools' => [
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_current_weather',
                'description' => 'Get the current weather in a given location',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and state, e.g. San Francisco, CA',
                        ],
                        'unit' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit'],
                        ],
                    ],
                    'required' => ['location'],
                ],
            ],
        ],
    ],
]);

function get_current_weather($location, $unit = 'celsius')
{
    // Call the weather API
    return 'The weather in '.$location.' is 72 degrees '.$unit;
}

print_r($response->meta());
echo $response->choices[0]->message->content;
