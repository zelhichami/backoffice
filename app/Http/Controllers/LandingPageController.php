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
// Define the CSS for the hover effect and edit button
            // --- Start: JavaScript for Hover Effect and Edit Button ---
            $previewEnhancementJs = "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Define styles to be injected
                        const styles = `
                            .xpage-section-wrapper {
                                position: relative;
                                border: 2px dashed transparent;
                                transition: border-color 0.3s ease-in-out;
                            }
                            .xpage-edit-button {
                                position: absolute;
                                top: 15px;
                                right: 15px;
                                z-index: 9999;
                                background-color: #cbcbcb;
                                color: white;
                                padding: 6px 6px;
                                border-radius: 8px;
                                text-decoration: none;
                                opacity: 0;
                                transform: translateY(-10px);
                                transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;

                                cursor: pointer;
                                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
                            }
                            .xpage-section-wrapper:hover .xpage-edit-button {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        `;

                        // Inject the styles into the head
                        const styleSheet = document.createElement('style');
                        styleSheet.type = 'text/css';
                        styleSheet.innerText = styles;
                        document.head.appendChild(styleSheet);

                        // Find all sections by their data attribute
                        const sections = document.querySelectorAll('[data-section-id]');

                        sections.forEach(section => {
                            const sectionId = section.dataset.sectionId;
                            const editUrl = section.dataset.editUrl;

                            // Create the wrapper and move the section inside it
                            const wrapper = document.createElement('div');
                            wrapper.className = 'xpage-section-wrapper';
                            section.parentNode.insertBefore(wrapper, section);
                            wrapper.appendChild(section);

                            // Create and append the edit button
                            const editButton = document.createElement('a');
                            editButton.href = editUrl;
                            editButton.target = '_blank';
                            editButton.className = 'xpage-edit-button';
                            editButton.innerHTML = '<svg viewBox=\"0 0 1024 1024\" class=\"h-5 w-5\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"#000000\"><g id=\"SVGRepo_bgCarrier\" stroke-width=\"0\"></g><g id=\"SVGRepo_tracerCarrier\" stroke-linecap=\"round\" stroke-linejoin=\"round\"></g><g id=\"SVGRepo_iconCarrier\"><path d=\"M705.3 177.1c-13.2-7.6-27.8-11.5-42.7-11.5-7.4 0-14.9 1-22.3 3-21.7 5.8-39.9 19.6-51.4 39 1 0.5 2 1 3 1.6l142.5 82.3c1 0.6 1.9 1.2 2.8 1.8 22.9-40.9 8.7-92.7-31.9-116.2z\" fill=\"#FFBC00\"></path><path d=\"M774.2 221.4c-8-29.8-27.1-54.8-53.9-70.2-26.8-15.4-57.9-19.5-87.8-11.6-29.8 8-54.8 27.1-70.2 53.9-2.2 3.8-4.1 7.6-5.8 11.5-11.1 3.3-20.3 10.6-26.1 20.6L309.6 608.1c-4.8 8.4-6.6 17.6-5.8 26.6-4.9 5.9-7.6 14-7.6 23.4v171.8c0 13.3 5.5 24.1 15 29.7l0.1 0.1s0.1 0 0.1 0.1c4.3 2.4 9.1 3.7 14.1 3.7 6.2 0 12.7-1.9 19.1-5.5l148.8-85.9c8.1-4.7 13.8-11.1 16.5-18.3 8.4-3.9 15.4-10.2 20.1-18.3L750.8 353c6-10.4 7.3-22.1 4.8-32.9 2.5-3.4 4.9-7 7-10.8 15.5-26.9 19.6-58.1 11.6-87.9z m-37 71.8c-0.9-0.6-1.9-1.2-2.8-1.8l-142.5-82.3c-1-0.6-2-1.1-3-1.6 11.5-19.3 29.7-33.1 51.4-39 7.4-2 14.9-3 22.3-3 14.8 0 29.5 3.9 42.7 11.5 40.6 23.6 54.8 75.4 31.9 116.2z m-180.8-52.6c2-3.4 5.2-5.9 9.1-7 1.3-0.4 2.6-0.5 4-0.5 2.6 0 5.1 0.7 7.4 2l142.5 82.3c5 2.9 7.7 8.2 7.5 13.6l-263.4 70.6 92.9-161z m-78 505.4l-148.8 85.9c-1.3 0.8-2.4 1.2-3.1 1.4-0.2-0.7-0.4-1.8-0.4-3.4V658c0-1.6 0.2-2.7 0.4-3.4 0.7 0.2 1.8 0.6 3.1 1.4l148.8 85.9c1.3 0.8 2.2 1.5 2.7 2-0.4 0.6-1.3 1.3-2.7 2.1z m-167-117.8s-0.1 0-0.1 0.1l-0.1 0.1v-0.4l0.2 0.2z m200.3 115.9v-0.2-0.2l0.3 0.2-0.3 0.2z m-7.7-23.8c-0.5 0.9-1.1 1.7-1.7 2.4-2.5-2.5-5.5-4.8-8.9-6.8L446.8 689l89-23.8-31.8 55.1z m53.1-91.8l-147 39.4-62.7-36.2 245.8-65.9-36.1 62.7z m57.4-99.4l-265.7 71.2 36.2-62.7 265.7-71.2-36.2 62.7z m57.3-99.4l-265.7 71.2 36.2-62.7L708 367l-36.2 62.7z\" fill=\"#46287C\"></path><path d=\"M406.1 500.9l265.7-71.2L708 367l-265.7 71.2zM726.9 330.9c0.2-5.4-2.5-10.7-7.5-13.6L576.9 235c-2.3-1.3-4.8-2-7.4-2-1.3 0-2.6 0.2-4 0.5-3.9 1-7.1 3.5-9.1 7l-92.9 160.9 263.4-70.5z m-174.3-53.3c9.1-15.7 29.2-21.1 44.9-12 15.7 9.1 21.1 29.2 12 44.9-9.1 15.7-29.2 21.1-44.9 12-15.7-9.1-21.1-29.2-12-44.9zM348.7 600.3l265.8-71.2 36.1-62.7-265.7 71.2zM493.4 716c3.4 2 6.4 4.2 8.9 6.8 0.6-0.7 1.2-1.5 1.7-2.4l31.8-55.1-89 23.8 46.6 26.9zM410.1 667.8l147-39.3 36.1-62.7-245.7 65.9zM311.3 859.6c-0.1 0-0.1 0 0 0-0.1 0.2 0 0.2 0 0.2 0.1-0.1 0.1-0.1 0-0.2zM311.3 628.3c0-0.1 0-0.1 0 0 0.1-0.1 0.1-0.1 0-0.2-0.1 0-0.1 0-0.1 0.1s0 0.1 0.1 0.1zM355.6 671c-14.3-8.2-26.5-15.3-27.2-15.6-0.7-0.3-2-0.4-2.1 0.1-0.1 0.5-0.2 16-0.2 32.5v111.8c0 16.5 0 30.6 0.1 31.3 0.1 0.7 0.7 1.9 1.2 1.7 0.5-0.2 13.9-7.8 28.2-16.1l96.8-55.9c14.3-8.2 26.5-15.3 27.1-15.7 0.6-0.4 1.3-1.5 0.9-1.9-0.4-0.3-13.7-8.1-28-16.4L355.6 671zM511.7 743.9c0 0.1 0 0.1 0 0 0 0.1 0.1 0.1 0.1 0.1 0.1 0 0.1-0.1 0-0.2-0.1 0-0.1 0-0.1 0.1z\" fill=\"#FFBC00\"></path><path d=\"M564.589235 322.494987a32.9 32.9 0 1 0 32.9-56.984471 32.9 32.9 0 1 0-32.9 56.984471Z\" fill=\"#FFFFFF\"></path></g></svg>';
                            wrapper.appendChild(editButton);
                        });
                    });
                </script>
            ";
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

                $editUrl = route('section.edit', $section);

                // Add data attributes to the root element of the section's HTML
                $htmlWithDataAttr = preg_replace(
                    '/(<\w+)/',
                    '$1 data-section-id="' . $section->id . '" data-edit-url="' . $editUrl . '"',
                    $replacer($htmlContent),
                    1
                );

                $combinedHtml .= $htmlWithDataAttr . "\n";
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

            $finalHtml = $combinedHtml . $previewEnhancementJs;



            $layout_content = str_replace('{{_SECTION_}}', $finalHtml, $layout_content);
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
