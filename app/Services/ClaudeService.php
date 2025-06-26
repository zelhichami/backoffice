<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ClaudeService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private int $maxTokens;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('claude.api_key');
        $this->apiUrl = config('claude.api_url');
        $this->model = config('claude.model');
        $this->maxTokens = config('claude.max_tokens');
        $this->timeout = config('claude.timeout');

        if (empty($this->apiKey)) {
            throw new Exception('Claude API key not configured');
        }
    }

    public function convertSection(string $htmlCode, string $sectionType = 'HERO'): array
    {
        $systemPrompt = $this->getSystemPrompt();

        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'system' => $systemPrompt,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Convert this {$sectionType} section:\n\n{$htmlCode}"
                ]
            ]
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01'
                ])
                ->post($this->apiUrl, $payload);

            if ($response->failed()) {
                throw new Exception("API request failed: " . $response->body());
            }

            $data = $response->json();

            return [
                'success' => true,
                'converted_html' => $data['content'][0]['text'] ?? '',
                'usage' => $data['usage'] ?? null,
                'section_type' => $sectionType
            ];

        } catch (Exception $e) {
            Log::error('Claude API Error', [
                'error' => $e->getMessage(),
                'section_type' => $sectionType,
                'html_length' => strlen($htmlCode)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'section_type' => $sectionType
            ];
        }
    }

    public function batchConvertSections(array $sections): array
    {
        $results = [];

        foreach ($sections as $index => $section) {
            if (!isset($section['html']) || !isset($section['type'])) {
                $results[] = [
                    'success' => false,
                    'error' => 'Missing html or type in section data',
                    'index' => $index
                ];
                continue;
            }

            $result = $this->convertSection($section['html'], $section['type']);
            $result['index'] = $index;
            $results[] = $result;

            // Add small delay to respect rate limits
            usleep(100000); // 0.1 second
        }

        return $results;
    }

    private function getSystemPrompt(): string
    {
        return <<<PROMPT
You are a section converter that transforms static HTML/Liquid code into configurable templates.

YOUR ONLY JOB:
1. Take the provided HTML/Liquid code exactly as-is
2. Identify static content (text, images, links)
3. Replace static content with template variables
4. Generate a JSON schema for the variables
5. DO NOT change any styling, classes, structure, or functionality

CONVERSION RULES:
- Text content: {% xpage-text variable: "descriptiveName" %}
- Images: src="{{ variableName }}" {% xpage-image variable: "variableName" %}
- Alt text: alt="{{ variableNameAlt }}"
- Links: href="{{ variableNameUrl }}"
- Keep ALL existing classes, IDs, and structure unchanged
- Keep ALL JavaScript functionality unchanged
- Keep ALL CSS custom properties unchanged

JSON SCHEMA FORMAT:
{
    "type": "SECTION_NAME",
    "description": "Brief description of what this section does",
    "variables": [
        {
            "name": "variableName",
            "prompt": "Human-readable description for content creators",
            "type": "TEXT|IMAGE"
        }
    ]
}

SECTION NAMES:
HERO, BENEFITS_ICONS, TESTIMONIALS, FAQ, FEATURED_BRANDS, HEADER, NAVBAR, PRODUCT_OFFER, UGC_VIDEOS, HOW_TO_USE, COMPARISON_TABLE, BEFORE_AFTER, REVIEWS, GUARANTEE_SECTION, FOOTER, BENEFITS_BLOCKS_IMAGES, BENEFITS_LONG_DESCRIPTION

VARIABLE NAMING:
- Use descriptive, camelCase names
- For images, add "Alt" suffix for alt text variables
- For links, add "Url" suffix for href variables
- Group related variables logically (feature1Title, feature1Description, feature1Icon)

IMPORTANT: Never modify the styling, layout, or functionality. Only replace static content with variables.
IMPORTANT: Default values that are context related should never be provided, the sections should be compatible with any product, additionally when default value is provided the prompt won't work, so for context related value, the default value should be null, and for static things that are not dependent on the context you choose if you want them to be generated (prompt only) or static (default value only)

When you replace links, set the default_value as the old link value and keep the prompt empty, if the link is an image src or background image url, generally and image replace with this placeholder API https://placehold.co/600x400 and change the size based on the context.

Return the response in this exact JSON format:
{
    "converted_html": "...",
    "schema": {...}
}
PROMPT;
    }
}
