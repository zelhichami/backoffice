<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertSectionRequest;
use App\Http\Requests\BatchConvertSectionRequest;
use App\Models\Section;
use App\Models\SectionVariable;
use App\Services\ClaudeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SectionConverterController extends Controller
{
    public function __construct(
        private ClaudeService $claudeService
    ) {}

    public function convert(Section $section): JsonResponse
    {
        $html = $section->html_content;
        $type = $section->type;
        $result = $this->claudeService->convertSection($html,$type);


        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Conversion failed',
                'error' => $result['error']
            ], 422);
        }


        // Try to parse the JSON response from Claude
        $convertedData = $this->parseClaudeResponse($result['converted_html']);

        $prompted_html = $convertedData['converted_html'];
        // Get standard path: sections_assets/{id}/index.html
        $htmlPath = $section->getAssetPath('html');

        if (!empty($prompted_html)) {
            // Save/overwrite the HTML file
            if (!Storage::disk('public')->put($htmlPath, $prompted_html)) {
                throw new \Exception("Failed to save HTML file.");
            }
            foreach ( $convertedData['schema']['variables'] as $variableData) {
                SectionVariable::updateOrCreate(
                    [
                        'section_id' => $section->id,
                        'name' => $variableData['name'],
                    ],
                    [
                        'prompt' => $variableData['prompt'],
                        'type' => $variableData['type'],
                    ]
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Section prompted successfully!']);


    }



    private function parseClaudeResponse(string $response): array
    {
        // Try to extract JSON from Claude's response
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return ['converted_html' => $response];
    }
}
