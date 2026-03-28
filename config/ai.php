<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active AI Provider
    |--------------------------------------------------------------------------
    | 1 = Claude (Anthropic)
    | 2 = Gemini (Google)
    */
    'provider' => (int) env('AI_PROVIDER', 1),

    'providers' => [

        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'api_url' => 'https://api.anthropic.com/v1/messages',
            'model'   => env('CLAUDE_MODEL', 'claude-haiku-4-5-20251001'),
            'version' => '2023-06-01',
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'model'   => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        ],

    ],

];
