<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    /**
     * Display the landing page builder interface.
     */
    public function index(): View
    {
        $sections = Section::whereNotIn('status', [Section::STATUS_DRAFT, Section::STATUS_REJECTED])
            ->orderBy('name', 'asc')
            ->get();

        return view('landing-page-builder', compact('sections'));
    }

    /**
     * Generate a preview for multiple sections, handling variables and asset paths.
     */
    public function preview(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $hostingId = $user->hosting_id;
        if (!$hostingId) {
            return response()->json(['message' => 'Hosting ID not configured for user.'], 400);
        }

        $validated = $request->validate([
            'section_ids' => 'required|array|min:2',
            'section_ids.*' => 'required|string|exists:sections,id',
            'product_id' => 'required|string',
        ]);

        try {
            $sectionIds = $validated['section_ids'];
            $productId = $validated['product_id'];

            // Eager load variables to prevent N+1 queries
            $placeholders = implode(',', array_fill(0, count($sectionIds), '?'));
            $orderedSections = Section::with('variables')
                ->whereIn('id', $sectionIds)
                ->orderByRaw("FIELD(id, $placeholders)", $sectionIds)
                ->get();

            $combinedHtml = '';
            $combinedCss = '';
            $combinedJs = '';
            $combinedContextVariables = [];
            $baseUrl = rtrim(config('app.url'), '/');

            foreach ($orderedSections as $section) {
                // --- Start: Asset Path and Variable Handling for each section ---
                $htmlContent = $section->html_content ?? '';
                $cssContent = $section->css_content ?? '';
                $jsContent = $section->js_content ?? '';

                $mediaPath = Storage::url("sections_assets/{$section->id}/media/");
                $assetUrl = $baseUrl . rtrim($mediaPath, '/');

                // Fix local src/href URLs (non-external)
                $replacer = function ($content) use ($assetUrl) {
                    $content = preg_replace_callback(
                        '/\b(src|href)=["\'](?!https?:\/\/|\/\/|data:|#|\{\{)([^"\'}]+)["\']/i',
                        function ($matches) use ($assetUrl) {
                            $attr = $matches[1];
                            $path = ltrim($matches[2], '/');
                            $filename = basename($path);
                            return sprintf('%s="%s/%s"', $attr, $assetUrl, $filename);
                        },
                        $content
                    );

                    // Fix CSS url() paths
                    $content = preg_replace_callback(
                        '/url\((?![\'"]?(?:https?:\/\/|\/\/|data:|#|\{\{))([\'"]?)([^\'"\)]+)\1\)/i',
                        function ($matches) use ($assetUrl) {
                            $quote = $matches[1];
                            $path = ltrim($matches[2], '/');
                            $filename = basename($path);
                            return 'url(' . $quote . rtrim($assetUrl, '/') . '/' . $filename . $quote . ')';
                        },
                        $content
                    );
                    return $content;
                };

                $combinedHtml .= $replacer($htmlContent) . "\n";
                $combinedCss .= $replacer($cssContent) . "\n";
                $combinedJs .= $replacer($jsContent) . "\n";

                // Aggregate context variables from each section
                foreach ($section->variables as $variable) {
                    $combinedContextVariables[] = [
                        'name' => $variable->name,
                        'type' => $variable->type,
                        'prompt' => $variable->prompt
                    ];
                }
                // --- End: Asset Path and Variable Handling ---
            }

            $layout_content = getAssetContent('layout_section');
            if (!$layout_content) {
                throw new \Exception('The file `layout_section.html` could not be found.');
            }

            $layout_content = str_replace('{{_SECTION_}}', $combinedHtml, $layout_content);
            $layout_content = str_replace('{{_CSS_}}', $combinedCss, $layout_content);
            $layout_content = str_replace('{{_JS_}}', $combinedJs, $layout_content);

            $secret = config('services.xpage.secret');
            $apiUrlBase = rtrim(config('services.xpage.global_uri', ''), '/');
            $path = "/{$hostingId}/product/{$productId}/preview";
            $fullUrl = $apiUrlBase . $path;
            $signature = hash_hmac('sha256', "", $secret);

            $response = Http::withHeaders(['X-Signature' => $signature])
                ->attach('file', $layout_content, 'landing_page_preview.liquid')
                ->post($fullUrl, [
                    'context' => json_encode($combinedContextVariables) // Send combined variables
                ]);

            if ($response->failed()) {
                Log::error("XPage multi-section preview API error for product {$productId} (Status: {$response->status()}): " . $response->body());
                return response()->json(['message' => 'The preview service returned an error.', 'previewContent' => $response->body()], 200);
            }

            $previewHtml = $response->json('previewContent', $response->body());

            return response()->json([
                'message' => 'Preview generated successfully.',
                'previewContent' => $previewHtml,
            ]);

        } catch (\Exception $e) {
            Log::error("Exception generating multi-section preview: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred while generating the preview: ' . $e->getMessage()], 500);
        }
    }
}
