<?php

return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'api_url' => env('OPENAI_API_URL'),
        'model' => env('OPENAI_MODEL'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'api_url' => env('GEMINI_API_URL'),
        'model' => env('GEMINI_MODEL', 'gemini-pro'),
    ],

    'default_provider' => env('AI_DEFAULT_PROVIDER'),
];