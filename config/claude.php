<?php

// config/claude.php
return [
    'api_key' => env('CLAUDE_API_KEY'),
    'api_url' => env('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages'),
    'model' => env('CLAUDE_MODEL', 'claude-sonnet-4-20250514'),
    'max_tokens' => env('CLAUDE_MAX_TOKENS', 4000),
    'timeout' => env('CLAUDE_TIMEOUT', 300),
];
