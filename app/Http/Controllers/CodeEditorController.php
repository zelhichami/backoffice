<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\SectionVariable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use App\Services\ClaudeService;
use OpenAI\Laravel\Facades\OpenAI;

class CodeEditorController extends Controller
{



    public function index(Request $request)
    {

        $variables = $this->get_variables();
        $user = Auth::user();
        $query = Section::query();

        // Search
        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhere('name', 'like', "%{$searchTerm}%");
            });
        }

        // Filters
        if ($request->has('status') && $request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('type') && $request->input('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('user') && $request->input('user')) {
            $query->where('user_id', $request->input('user'));
        }

        switch ($user->role) {
            case User::ROLE_INTEGRATOR:
                // Show only this user's own sections
                $query->where('user_id', $user->id);
                break;

            case User::ROLE_REVIEWER:
                // Show sections that are pending review
                $query->where('status', '!=', Section::STATUS_DRAFT);
                break;

            // --- START: NEW LOGIC FOR PROMPT ENGINEER ---
            case User::ROLE_PROMPT_ENGINEER:
                // Show sections that are ready for prompting or have been prompted
                $query->whereIn('status', [
                    Section::STATUS_VERIFIED, // This status needs to be added
                    Section::STATUS_PENDING_PROMPT, // This status needs to be added
                    Section::STATUS_PROMPTED,       // This status needs to be added
                    Section::STATUS_PENDING_VALIDATION,
                    Section::STATUS_APPROVED,
                    Section::STATUS_PUBLISHED
                ]);
                break;
            // --- END: NEW LOGIC FOR PROMPT ENGINEER ---

            default:
                // For other roles like admin or if role is unknown, show all for now.
                // Or apply specific logic, e.g., superadmin sees all.
                // For this example, we'll let other roles see everything.
                // if (!$user->is_superadmin) { $query->whereNull('id'); } // Example for locking down
                break;
        }

        $sections = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('sections-list', compact('sections', 'variables'));
    }


    /**
     * Initialize a new section (name, optional screenshot) from the popup.
     * Redirects to the edit page for the new section.
     */
    public function initialize(Request $request): RedirectResponse
    {
        // 1. Validate name and optional screenshot
        $validated = $request->validate([
            'section_name' => 'required|string|max:255',
            'section_screenshot' => [
                'nullable', // Make screenshot optional
                \Illuminate\Validation\Rules\File::image() // Validate it's an image
                ->max(4 * 1024) // Max size 2MB (adjust as needed)
                // ->dimensions(Rule::dimensions()->maxWidth(1000)->maxHeight(1000)), // Optional dimensions
            ],
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return back()->with('error', 'User not authenticated.'); // Should be caught by middleware
        }
        $section_id = generateUniqueUuid(Section::class,'id');
        $storageDisk = 'public'; // Or 'public' if you linked storage
        $basePath = "sections_assets/$section_id"; // Store in its own folder
        try {
            // 2. Handle Screenshot Upload if present
            if ($request->hasFile('section_screenshot') && $request->file('section_screenshot')->isValid()) {
                $file = $request->file('section_screenshot');
                // Use store() which generates a unique name automatically
                $screenshotPath = $file->storeAs($basePath,'screenshot.'.$file->getClientOriginalExtension(), $storageDisk);

                if (!$screenshotPath) {
                    throw new \Exception("Failed to store screenshot.");
                }
            }else{
                $screenshotPath= '';
            }


            // 3. Create the initial Section record
            $newSection = Section::create([
                'id' => $section_id,
                'user_id' => $userId,
                'name' => $validated['section_name'], // Use validated name
                'screenshot_path' => $screenshotPath, // Store path or null
                'status' => 'draft', // Default status
            ]);

            if ($newSection) {
                // 4. Redirect to the edit page for the newly created section
                return redirect()->route('section.edit', ['section' => $section_id])
                    ->with('success', 'Section created! Start adding your code.');
            }else{
                throw new \Exception("Failed to create section.");
            }
        } catch (\Exception $e) {
            Log::error("Error initializing section for user {$userId}: " . $e->getMessage());

            // Clean up screenshot if saved before error
            if (isset($screenshotPath) && $screenshotPath && Storage::disk($storageDisk)->exists($screenshotPath)) {
                Storage::disk($storageDisk)->delete($screenshotPath);
            }

            return back()->with('error', 'Failed to create section: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the code editor view for editing an existing section.
     */
    public function edit(Section $section) {
        // Optional: Add authorization check if needed (e.g., user can only edit their own sections)
        // Authorization Check
        $user = Auth::user();
        if ($user->hasRole(User::ROLE_INTEGRATOR) ) {
            if ($section->user_id !== $user->id ) {
                abort(403, 'Unauthorized action.');
            }
        }
        if ($user->hasRole(User::ROLE_REVIEWER) ) {
            if ($section->status == Section::STATUS_DRAFT ) {
                return redirect('/sections');
            }
            if ($section->status == Section::STATUS_READY ) {
                // Update the status field on the model to under review
                $section->status = Section::STATUS_UNDER_REVIEW;
                $section->save();
            }
        }
        if ($user->hasRole(User::ROLE_PROMPT_ENGINEER) ) {

            if ($section->status == Section::STATUS_VERIFIED ) {
                $htmlPath = $section->getAssetPath('html');
                $backupHtmlPath = "sections_assets/{$section->id}/index.html.bak";
                $storageDisk = 'public';
                if (Storage::disk($storageDisk)->exists($htmlPath)) {
                    Storage::disk($storageDisk)->copy($htmlPath, $backupHtmlPath);
                }
                // Update the status field on the model to under review
                $section->status = Section::STATUS_PENDING_PROMPT;
                $section->save();
            }

            if ($section->status != Section::STATUS_PENDING_PROMPT AND $section->status != Section::STATUS_PROMPTED ) {
                return redirect('/sections');
            }

        }

        // Use model accessors to get content
        $htmlContent = $section->html_content;
        $cssContent = $section->css_content;
        $jsContent = $section->js_content;
        $variables = $this->get_variables();
        $sectionVariables = $section->variables()->orderBy('created_at','desc')->get(); // Assumes a 'variables' relationship on Section model
        return view('section-edit', compact('section', 'htmlContent', 'cssContent', 'jsContent', 'variables', 'sectionVariables'));

        return view('section-edit', compact('section', 'htmlContent', 'cssContent', 'jsContent', 'variables'));

    }

    /**
     * Update the HTML, CSS, and JS files for the specified section.
     * Handles submission from the main code editor page.
     *
     * @param Request $request
     * @param Section $section Route model binding
     * @return JsonResponse
     */
    public function update(Request $request, Section $section): JsonResponse
    {
        $user = Auth::user();

        // Authorization Check
        if ($user->hasRole(User::ROLE_INTEGRATOR) ) {
            if ($section->user_id !== $user->id or $section->status != Section::STATUS_DRAFT ) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
        }


        // 1. Validate incoming code content
        $validated = $request->validate([
            'html_code' => 'nullable|string',
            'css_code' => 'nullable|string',
            'js_code' => 'nullable|string',
        ]);

        $storageDisk = 'public';
        // No $updateData needed for DB paths

        try {
            $baseUrl = config('app.url');
            $media_path= Storage::url("sections_assets/{$section->id}/media/");
            $assetUrl = $baseUrl.$media_path;


            $newHtmlCode = $validated['html_code'] ?? null;

             // Get standard path: sections_assets/{id}/index.html
            $htmlPath = $section->getAssetPath('html');

            if (!empty($newHtmlCode)) {
                // Save/overwrite the HTML file
                if (!Storage::disk($storageDisk)->put($htmlPath, $newHtmlCode)) {
                    throw new \Exception("Failed to save HTML file.");
                }
            } else { // Submitted code is empty, delete file if it exists
                if ($htmlPath && Storage::disk($storageDisk)->exists($htmlPath)) {
                    Storage::disk($storageDisk)->delete($htmlPath);
                }
            }

            $newCssCode = $validated['css_code'] ?? null;

            /*$newCssCode = preg_replace_callback(
                '/url\(["\']?(?!https?:\/\/|\/\/|data:|#|\{\{)([^"\')]+)["\']?\)/',
                function ($matches) use ($assetUrl) {
                    $filename = basename($matches[1]); // Get only the filename
                    return 'url("' . rtrim($assetUrl, '/') . '/' . $filename . '")';
                },
                $css
            );*/


            // 3. Handle CSS File Update
            $cssPath = $section->getAssetPath('css'); // Get standard path: sections_assets/{id}/style.css


            if (!empty($newCssCode)) {
                // Save/overwrite the CSS file
                if (!Storage::disk($storageDisk)->put($cssPath, $newCssCode)) {
                    throw new \Exception("Failed to save CSS file.");
                }
            } else { // Submitted code is empty, delete file if it exists
                if ($cssPath && Storage::disk($storageDisk)->exists($cssPath)) {
                    Storage::disk($storageDisk)->delete($cssPath);
                }
            }

            // 4. Handle JS File Update
            $newJsCode = $validated['js_code'] ?? null;
            $jsPath = $section->getAssetPath('js'); // Get standard path: sections_assets/{id}/script.js

            if (!empty($newJsCode)) {
                // Save/overwrite the JS file
                if (!Storage::disk($storageDisk)->put($jsPath, $newJsCode)) {
                    throw new \Exception("Failed to save JS file.");
                }
            } else { // Submitted code is empty, delete file if it exists
                if ($jsPath && Storage::disk($storageDisk)->exists($jsPath)) {
                    Storage::disk($storageDisk)->delete($jsPath);
                }
            }

            // 5. Update the section's timestamp
            $section->touch(); // Updates the updated_at timestamp

            // 6. Return JSON success response
            return response()->json(['message' => 'Section content updated successfully!']);

        } catch (\Exception $e) {
            Log::error("Error updating content for section {$section->id}: " . $e->getMessage());
            // Return JSON error response
            return response()->json(['message' => 'Failed to update section content: ' . $e->getMessage()], 500);
        }
    }

    public function get_variables(){
        $jsonFilePath = public_path('variables.json'); // Path to variables.json
        $variables = [];
        if (File::exists($jsonFilePath)) {
            $jsonData = File::get($jsonFilePath);
            $variables = json_decode($jsonData, true); // Decode JSON data into an associative array
        }
        return $variables;
    }



    /**
     * Update the name and screenshot details of a specific section.
     * Handles submission from the 'Edit Details' modal.
     *
     * @param Request $request
     * @param Section $section Route model binding
     * @return RedirectResponse
     */
    public function updateDetails(Request $request, Section $section): RedirectResponse {
        // Optional: Authorization check - Ensure user owns the section
         if ($section->user_id !== Auth::id() OR !Section::where('id','=', $section->id )->where('user_id','=',Auth::id())->exists()) {
           return redirect()->route('editor.index')->with('error', 'Unauthorized action.');
        }

        $validated = Validator::make($request->all(), [
            'section_name' => 'required|string|max:255',
            'section_screenshot' => [
                'nullable',
                \Illuminate\Validation\Rules\File::image()->max(2 * 1024),
            ],
            'form_type' => 'required|in:edit_details',
        ]);
        if ($validated->fails()) {
            // Optional: Customize error messages or logic here
            return redirect()->back()
                ->withErrors($validated)
                ->withInput();
        }


        $storageDisk = 'public'; // Or 'public' if you linked storage
        $basePath = "sections_assets/$section->id"; // Store in its own folder
        $updateData = ['name' => $request['section_name']]; // Start with name update
        try {
            // 2. Handle Screenshot Upload if present
            if ($request->hasFile('section_screenshot') && $request->file('section_screenshot')->isValid()) {
                $file = $request->file('section_screenshot');

                // Delete the old screenshot *after* successfully storing the new one
                $oldScreenshotPath = $section->screenshot_path;
                if ($oldScreenshotPath && Storage::disk($storageDisk)->exists($oldScreenshotPath)) {
                    Storage::disk($storageDisk)->delete($oldScreenshotPath);
                }

                $screenshotName= 'screenshot.'.$file->getClientOriginalExtension();
                $screenshotPath = $file->storeAs($basePath,$screenshotName, $storageDisk);

                if (!$screenshotPath) {
                    throw new \Exception("Failed to store screenshot.");
                }

                $updateData['screenshot_path'] = $screenshotPath;


            }

            // 3. Update the Section record in the database
            $section->update($updateData);

            // Redirect back to the list with a success message (toast will display it)
            return redirect()->route('sections.index')->with('success', 'Section details updated successfully!');

        } catch (\Exception $e) {
            Log::error("Error updating details for section {$section->id}: " . $e->getMessage());
            // Redirect back with error message and input (including hidden fields)
            return back()->with('error', 'Failed to update section details: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified section from storage and database.
     */
    public function destroy(Section $section): RedirectResponse {

        if ($section->user_id !== Auth::id()) {
            return redirect()->route('sections.index')->with('error', 'Unauthorized action.');
        }

        $storageDisk = 'public'; // Or 'public' - must match where files are stored

        try {
            // Delete the section record from the database
            $section_id=$section->id;
            $section->delete();
            Storage::disk($storageDisk)->deleteDirectory('sections_assets/'.$section_id);
            return redirect()->route('sections.index')->with('success', 'Section deleted successfully!');

        } catch (\Exception $e) {
            Log::error("Error deleting section {$section->id}: " . $e->getMessage());
            return redirect()->route('sections.index')->with('error', 'Failed to delete section.');
        }
    }


    /**
     * Update the status of the specified section.
     * Handles AJAX request from the status dropdown on the edit page.
     *
     * @param Request $request
     * @param Section $section Route model binding provides the section
     * @return JsonResponse
     */
    public function updateStatus(Request $request, Section $section): JsonResponse
    {
        // Authorization Check: Ensure the logged-in user owns this section
        $user = Auth::user();
        $section_status= $section->status;

        // Authorization Check
        if ($user->hasRole(User::ROLE_INTEGRATOR) ) {
            if ($section->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            if($section_status != Section::STATUS_DRAFT && $section_status != Section::STATUS_READY && $section_status != Section::STATUS_REJECTED ){
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            $allowedStatuses = [Section::STATUS_DRAFT, Section::STATUS_READY];
        }

        // Authorization Check
        if ($user->hasRole(User::ROLE_REVIEWER) ) {

            if($section_status != Section::STATUS_READY && $section_status != Section::STATUS_UNDER_REVIEW && $section_status != Section::STATUS_VERIFIED && $section_status != Section::STATUS_REJECTED ){
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            $allowedStatuses = [Section::STATUS_VERIFIED, Section::STATUS_REJECTED, Section::STATUS_UNDER_REVIEW];
        }

        // Authorization Check
        if ($user->hasRole(User::ROLE_PROMPT_ENGINEER) ) {

            if($section_status != Section::STATUS_PROMPTED && $section_status != Section::STATUS_PENDING_PROMPT && $section_status != Section::STATUS_PROMPT_REJECTED ){
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            $allowedStatuses = [Section::STATUS_PENDING_PROMPT, Section::STATUS_PROMPTED];
        }


        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in($allowedStatuses)],
        ]);

        try {
            // Update the status field on the model
            $section->status = $validated['status'];

            // Save the changes to the database
            $section->save();

            // Return a success response with the new status
            return response()->json([
                'message' => 'Status updated successfully!',
                'newStatus' => $section->status // Send back the updated status for UI confirmation
            ]);

        } catch (\Exception $e) {
            // Log any errors during the save process
            Log::error("Error updating status for section {$section->id}: " . $e->getMessage());

            // Return a generic error response
            return response()->json(['message' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }




    /**
     * Fetch products from XPage API for the current user's hosting ID.
     * Handles pagination via query parameter.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProducts(Request $request): JsonResponse
    {
        $user = Auth::user();
        // Ensure user is loaded, although middleware should handle this
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }
        $hostingId = $user->hosting_id; // Assumes hosting_id is on User model

        if (!$hostingId) {
            return response()->json(['message' => 'Hosting ID not configured for user.'], 400);
        }

        $page = $request->query('page', 1); // Get page number, default to 1
        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }


        try {

            $secret = config('services.xpage.secret');
            $basePath = "api/v1/hosting/{$hostingId}/product";
            $apiUrlBase = rtrim(config('services.xpage.base_uri', ''), '');
            $fullUrl = $apiUrlBase. $basePath."?page=$page";

            $signature = hash_hmac('sha256', '', $secret);

            Log::info("Fetching products from XPage: {$fullUrl}"); // Log the attempt

            $response = Http::withHeaders([
                'X-Signature' => $signature,
                'Accept' => 'application/json',
            ])->timeout(15)->get($fullUrl);

            if ($response->failed()) {
                // Log detailed error from XPage if possible
                Log::error("XPage getProducts API error for hosting {$hostingId} (Status: {$response->status()}): " . $response->body());
                $errorMessage = $response->json('message', 'Failed to fetch products from XPage.'); // Try to get specific message
                return response()->json(['message' => $errorMessage], $response->status());
            }

            // Return the JSON response from the XPage API directly
            // Assuming it includes product data and Laravel-compatible pagination info
            return response()->json($response->json());

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Connection Exception fetching products for hosting {$hostingId}: " . $e->getMessage());
            return response()->json(['message' => 'Could not connect to the product service.'], 504); // Gateway Timeout
        } catch (\Exception $e) {
            Log::error("Exception fetching products for hosting {$hostingId}: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching products.'], 500);
        }
    }

    public function generatePreview(Request $request, Section $section): JsonResponse
    {
        // Authorization
       /* if ($section->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }*/

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $hostingId = $user->hosting_id;
        if (!$hostingId) {
            return response()->json(['message' => 'Hosting ID not configured for user.'], 400);
        }

        // Validate incoming product ID
        $validated = $request->validate([
            'productId' => 'required',
        ]);
        $productId = $validated['productId'];

        try {
            // Retrieve the section's HTML, CSS, and JS content
            $htmlContent = $section->html_content ?? '';
            $cssContent = $section->css_content ?? '';
            $jsContent = $section->js_content ?? '';

            // --- New Single-Call AI Variable Processing ---

            $contextVariables = [];
            foreach ($section->variables as $variable) {
                $contextVariables[] = [
                    'name' => $variable->name,
                    'type' => $variable->type,
                    'prompt' => $variable->prompt
                ];
            }



            // --- End of AI Variable Processing ---

// Load the layout template
            $layout_content = getAssetContent('layout_section');

// Replace placeholders in layout
            $layout_content = str_replace('{{_SECTION_}}', $htmlContent, $layout_content);
            $layout_content = str_replace('{{_CSS_}}', $cssContent, $layout_content);
            $layout_content = str_replace('{{_JS_}}', $jsContent, $layout_content);

// Get asset base URL
            $baseUrl     = rtrim(config('app.url'), '/');
            $mediaPath   = Storage::url("sections_assets/{$section->id}/media/");
            $assetUrl    = $baseUrl . rtrim($mediaPath, '/');

// Fix local src/href URLs (non-external)
            $layout_content = preg_replace_callback(
                '/\b(src|href)=["\'](?!https?:\/\/|\/\/|data:|#|\{\{)([^"\'}]+)["\']/i',
                function ($matches) use ($assetUrl) {
                    $attr = $matches[1];
                    $path = ltrim($matches[2], '/'); // Remove leading slash if exists
                    $filename = basename($path);     // Optional: use full path if folder structure is needed
                    return sprintf('%s="%s/%s"', $attr, $assetUrl, $filename);
                },
                $layout_content
            );
            $layout_content = preg_replace_callback(
                '/url\((?![\'"]?(?:https?:\/\/|\/\/|data:|#|\{\{))([\'"]?)([^\'"\)]+)\1\)/i',
                function ($matches) use ($assetUrl) {
                    $quote = $matches[1];
                    $path = ltrim($matches[2], '/'); // remove leading slash
                    $filename = basename($path);
                    return 'url(' . $quote . rtrim($assetUrl, '/') . '/' . $filename . $quote . ')';
                },
                $layout_content
            );


            $secret = config('services.xpage.secret');
            if (!$secret) {
                throw new \Exception('API signing secret is not configured.');
            }
            $fileName = "section_$section->id.liquid";

            // Signature is usually calculated on raw content
            $signature = hash_hmac('sha256', "", $secret);

            // 3. Call XPage Preview API
            $apiUrlBase = rtrim(config('services.xpage.global_uri', ''), '/');
            $path = "/{$hostingId}/product/{$productId}/preview";
            $fullUrl = $apiUrlBase . $path;

            $response = Http::withHeaders([
                'X-Signature' => $signature,
            ])->attach(
                'file', $layout_content, $fileName
            )->post($fullUrl, ['context' => json_encode($contextVariables)]);


            if ($response->failed()) {
                Log::error("XPage preview API error for section {$section->id}, product {$productId} (Status: {$response->status()}): " . $response->body());
                return response()->json([
                    'message' => 'Preview generated successfully.',
                    'previewContent' => $response->body(),
                ],200);
            }

            $previewHtml = $response->json('previewContent', $response->body());

            return response()->json([
                'message' => 'Preview generated successfully.',
                'previewContent' => $previewHtml,
            ]);

        } catch (\Exception $e) {
            Log::error("Exception generating preview for section {$section->id}: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 200);
        }
    }


    /**
     * Display the page listing available variables from variables.json.
     *
     * @return View
     */
    public function showVariablesPage(): View
    {
        $variables = $this->get_variables(); // Use existing helper

        return view('variables', compact('variables'));
    }

    /**
     * NEW: Display the RenderState class documentation page.
     *
     * @return View
     */
    public function showRenderStateDocsPage(): View
    {
        return view('render-state-documentation');
    }


    // --- NEW METHOD for Asset Upload ---
    /**
     * Handle asset file uploads for a section.
     *
     * @param Request $request
     * @param Section $section Route model binding
     * @return JsonResponse
     */
    public function uploadAssets(Request $request, Section $section): JsonResponse
    {
        // Authorization: Check authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // Validation: Check for 'assets' array and validate each file
        $validator = Validator::make($request->all(), [
            'assets'   => 'required|array|max:100', // Limit max number of files per upload (e.g., 10)
            'assets.*' => [
                'required',
                'file',
                // Example validation: Allow common images, videos, pdf up to 10MB
                'mimes:jpg,jpeg,png,gif,svg,webp,mp4,mov,avi,pdf,zip,avif', // Adjust allowed types
                'max:102400' // Max 100MB per file (adjust as needed)
            ],
        ]);

        if ($validator->fails()) {
            // Return validation errors if any file fails
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $uploadedFilesInfo = []; // To store info about successfully uploaded files
        $storageDisk = 'public'; // Use the public disk configured in filesystems.php
        // Define a sub-directory for media assets within the section's folder
        $assetDirectory = "sections_assets/{$section->id}/media";

        try {
            if ($request->hasFile('assets')) {
                foreach ($request->file('assets') as $file) {
                    // Double-check validity just in case
                    if ($file->isValid()) {
                        // Sanitize the filename to prevent security issues
                        $originalName = $file->getClientOriginalName();
                        $sanitizedFilename = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "_", $originalName);
                        // Optional: Add a unique prefix if exact original names aren't required or might conflict
                        // $uniqueFilename = Str::random(4) . '_' . $sanitizedFilename;

                        // Store the file using the sanitized name in the specific asset directory
                        $path = $file->storeAs($assetDirectory, $sanitizedFilename, $storageDisk);

                        if ($path) {
                            Log::info("Stored asset for section {$section->id}: {$path}");
                            // Collect info to potentially return to the frontend
                            $uploadedFilesInfo[] = [
                                'name' => $sanitizedFilename,
                                'url' => Storage::url($path), // Get the public URL
                                'size' => $file->getSize(),
                                'mime_type' => $file->getMimeType()
                            ];
                        } else {
                            // Log error if a specific file failed to store
                            Log::error("Failed to store asset '{$originalName}' for section {$section->id}");
                            // Optionally, add specific file errors to a separate array to return
                        }
                    }
                }
            }

            // Check if any files were actually processed successfully
            if (empty($uploadedFilesInfo)) {
                return response()->json(['message' => 'No valid files were uploaded or saved.'], 400);
            }

            // Return a success response
            return response()->json([
                'message' => count($uploadedFilesInfo) . ' asset(s) uploaded successfully!',
                'uploaded_files' => $uploadedFilesInfo // Optionally return info about uploaded files
            ]);

        } catch (\Exception $e) {
            // Log any unexpected exceptions during the process
            Log::error("Exception uploading assets for section {$section->id}: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred during file upload.'], 500);
        }
    }

    // --- NEW METHOD to Get Assets ---
    /**
     * Get a list of assets for a specific section.
     *
     * @param Section $section Route model binding
     * @return JsonResponse
     */
    public function getAssets(Section $section): JsonResponse
    {
        // Authorization: Check authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $storageDisk = 'public'; // Use the public disk configured in filesystems.php
        // Define the sub-directory for media assets within the section's folder
        $assetDirectory = "sections_assets/{$section->id}/media";
        $assets = []; // Initialize an empty array to hold asset information

        try {
            // Check if the asset directory exists
            if (!Storage::disk($storageDisk)->exists($assetDirectory)) {
                // If the directory doesn't exist, return an empty array (no assets)
                return response()->json([]);
            }

            // Get all files within the asset directory
            $files = Storage::disk($storageDisk)->files($assetDirectory);

            foreach ($files as $file) {
                // Skip hidden files (like .DS_Store on macOS)
                if (strpos(basename($file), '.') === 0) {
                    continue;
                }

                // Add information about each asset to the array
                $assets[] = [
                    'name' => basename($file), // Get just the filename
                    'url' => Storage::url($file), // Get the publicly accessible URL
                    'size' => Storage::disk($storageDisk)->size($file), // Get file size in bytes
                    'lastModified' => Storage::disk($storageDisk)->lastModified($file), // Get last modified timestamp
                    'type' => Storage::disk($storageDisk)->mimeType($file) // Get the MIME type (e.g., 'image/jpeg')
                ];
            }

            // Optional: Sort assets, for example, alphabetically by name
            // usort($assets, fn($a, $b) => strcmp($a['name'], $b['name']));

            // Return the list of assets as a JSON response
            return response()->json($assets);

        } catch (\Exception $e) {
            // Log any errors that occur during the process
            Log::error("Error listing assets for section {$section->id}: " . $e->getMessage());
            // Return a generic error response
            return response()->json(['message' => 'Could not retrieve asset list.'], 500);
        }
    }
    // --- NEW METHOD to Delete an Asset ---
    /**
     * Delete a specific asset file for a section.
     *
     * @param Section $section Route model binding
     * @param string $assetName The name of the file to delete
     * @return JsonResponse
     */
    public function deleteAsset(Section $section, string $assetName): JsonResponse
    {
        // Authorization
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // Basic validation/sanitization for the filename (prevent directory traversal)
        if (empty($assetName) || strpos($assetName, '/') !== false || strpos($assetName, '..') !== false) {
            return response()->json(['message' => 'Invalid asset name.'], 400);
        }

        $storageDisk = 'public';
        $assetDirectory = "sections_assets/{$section->id}/media";
        $filePath = $assetDirectory . '/' . $assetName;

        try {
            // Check if the file exists
            if (!Storage::disk($storageDisk)->exists($filePath)) {
                return response()->json(['message' => 'Asset not found.'], 404);
            }

            // Attempt to delete the file
            if (Storage::disk($storageDisk)->delete($filePath)) {
                Log::info("Deleted asset '{$assetName}' for section {$section->id}");
                return response()->json(['message' => "Asset '{$assetName}' deleted successfully."]);
            } else {
                Log::error("Failed to delete asset '{$assetName}' for section {$section->id}. File might still exist.");
                return response()->json(['message' => 'Failed to delete the asset.'], 500);
            }

        } catch (\Exception $e) {
            Log::error("Exception deleting asset '{$assetName}' for section {$section->id}: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting the asset.'], 500);
        }
    }


    /**
     * Show a single section variable as JSON.
     */
    public function showVariable(SectionVariable $variable): JsonResponse
    {
        // Authorization: Ensure the user can access the section this variable belongs to.
        $user = Auth::user();
        $section = $variable->section;

        if ($user->hasRole(User::ROLE_INTEGRATOR) && $section->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Allow prompt engineers and reviewers to see it.
        if (in_array($user->role, [User::ROLE_REVIEWER, User::ROLE_PROMPT_ENGINEER])) {
            // No additional ownership check needed for these roles.
        } else if ($user->role !== User::ROLE_INTEGRATOR) {
            return response()->json(['message' => 'Unauthorized role.'], 403);
        }


        // Append the public URL for the image if it exists
        if ($variable->type === 'image' && $variable->default_image_path) {
            $variable->default_image_url = Storage::url($variable->default_image_path);
        }

        return response()->json($variable);
    }

    /**
     * Store a new AI variable for a section.
     */
    public function storeVariable(Request $request, Section $section): JsonResponse
    {
        // Authorization: Ensure the prompt engineer can edit this section
        if (!Auth::user()->hasRole(User::ROLE_PROMPT_ENGINEER) || !in_array($section->status, ['pending_prompt'])) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_]+$/', Rule::unique('section_variables')->where('section_id', $section->id)],
            'type' => ['required', Rule::in(['text', 'image'])],
            'prompt' => ['required', 'string'],
            'default_text_value' => ['nullable', 'string'],
            'default_image' => ['nullable', 'image', 'max:2048'], // For default image uploads
        ]);

        $variableData = $validated;

        if ($request->hasFile('default_image') && $validated['type'] === 'image') {
            $path = $request->file('default_image')->store("sections_assets/{$section->id}/variable_defaults", 'public');
            $variableData['default_image_path'] = $path;
        }

        $variable = $section->variables()->create($variableData);

        return response()->json($variable, 201);
    }

    /**
     * Update an existing AI variable.
     */
    public function updateVariable(Request $request, SectionVariable $variable): JsonResponse
    {
        // Authorization: Check if the user has access to the variable's section
        $section = $variable->section;
        if (!Auth::user()->hasRole(User::ROLE_PROMPT_ENGINEER) || !in_array($section->status, ['pending_prompt', 'prompted'])) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_]+$/', Rule::unique('section_variables')->where('section_id', $variable->section_id)->ignore($variable->id)],
            'type' => ['required', Rule::in(['text', 'image'])],
            'prompt' => ['required', 'string'],
            'default_text_value' => ['nullable', 'string'],
            'default_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $variableData = $validated;

        if ($request->hasFile('default_image') && $validated['type'] === 'image') {
            // Delete old image if it exists
            if ($variable->default_image_path) {
                Storage::disk('public')->delete($variable->default_image_path);
            }
            $path = $request->file('default_image')->store("sections_assets/{$section->id}/variable_defaults", 'public');
            $variableData['default_image_path'] = $path;
        }

        $variable->update($variableData);

        return response()->json($variable);
    }

    /**
     * Delete an AI variable.
     */
    public function destroyVariable(SectionVariable $variable): JsonResponse
    {
        // Authorization
        $section = $variable->section;
        if (!Auth::user()->hasRole(User::ROLE_PROMPT_ENGINEER) || !in_array($section->status, ['pending_prompt', 'prompted'])) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        // Delete the associated default image if it exists
        if ($variable->type === 'image' && $variable->default_image_path) {
            Storage::disk('public')->delete($variable->default_image_path);
        }

        $variable->delete();

        return response()->json(['message' => 'Variable deleted successfully.']);
    }

    public function saveDataset(Request $request, Section $section): JsonResponse
    {
        // Authorization: Check if the user is allowed to edit this section's dataset.
        $user = Auth::user();

        // Allow the owner to edit if they are an integrator.
        if ($user->hasRole(User::ROLE_INTEGRATOR)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        // Allow prompt engineers to edit only if the section is in the correct state.
        if ($user->hasRole(User::ROLE_PROMPT_ENGINEER) && !in_array($section->status, [Section::STATUS_PENDING_PROMPT, Section::STATUS_PROMPTED])) {
            return response()->json(['message' => 'You can only edit the dataset for sections that are pending prompt or have been prompted.'], 403);
        }

        // Validate the incoming data.
        $validated = $request->validate([
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|string|max:255',
            'position' => 'nullable|integer',
        ]);

        try {
            // Update the section with the validated data.
            $section->update([
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'] ?? null,
                'position' => $validated['position'] ?? 0,
            ]);

            // Return a success response.
            return response()->json(['message' => 'Section dataset saved successfully!']);

        } catch (\Exception $e) {
            // Log any errors and return an error response.
            Log::error("Error saving dataset for section {$section->id}: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred while saving the dataset.'], 500);
        }
    }



    /**
     * Rollback the HTML content from the backup file.
     *
     * @param Section $section
     * @return JsonResponse
     */
    public function rollback(Section $section): JsonResponse
    {
        $user = Auth::user();

        // Authorization Check
        if (!$user->hasRole(User::ROLE_PROMPT_ENGINEER)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $htmlPath = $section->getAssetPath('html');
        $backupHtmlPath = "sections_assets/{$section->id}/index.html.bak";
        $storageDisk = 'public';

        if (!Storage::disk($storageDisk)->exists($backupHtmlPath)) {
            return response()->json(['message' => 'No backup found to restore.'], 404);
        }

        try {
            Storage::disk($storageDisk)->copy($backupHtmlPath, $htmlPath);
            return response()->json(['message' => 'HTML content has been rolled back successfully!']);
        } catch (\Exception $e) {
            Log::error("Error rolling back HTML for section {$section->id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to rollback HTML content.'], 500);
        }
    }

    public function generatePalette(Request $request): JsonResponse
    {
        $request->validate([
            'image_url' => 'required|url',
        ]);

        try {
            $imageUrl = $request->input('image_url');

            // Download the image content
            $imageContent = Http::get($imageUrl)->body();
            if (!$imageContent) {
                return response()->json(['message' => 'Failed to fetch image from URL.'], 400);
            }

            $base64Image = 'data:image/jpeg;base64,' . base64_encode($imageContent);
            //$prompt = "You are a top-level design assistant generating a complete color token set for a landing page based solely on a provided product image. Your job is to extract harmonious, accessible, and brand-aligned colors that follow the design logic and token structure outlined below.\n\n🎯 OBJECTIVE:\nExtract and generate a design system color palette from the product image that follows strict branding logic, contrast rules, and token structure. Output must be used directly in a CSS theme or design system.\n\n📥 INPUT:\nA product image (PNG or JPG)\n\nLight mode (default)\n\nUse your best design judgment to select Primary and Accent colors based on the product packaging, contents, and theme.\n\n\n🔁 LOGIC RULES:\n🎨 COLOR DEFINITIONS\nPrimary Color:\n\nMain brand color from the product\n\nUsed for: CTA background, header, icons, price (if dark), headline (on light bg)\n\nAccent Color:\n\nHarmonizes with primary color\n\nUsed in background sections and secondary UI visuals\n\n🎨 SECTION BACKGROUND COLORS\n--bg-section-primary: Very light tint of --color-primary\n\n--bg-section-accent: Very light tint of --color-accent\n\nLight mode → tints toward white\n\nDark mode → shades toward black\n\n( --text-muted) Text muted → must be like fg but more lighter in the light mode, and more darker in the dark mode\n( --bg-muted) bg muted → must be like bg but more lighter in the dark mode, and more darker in the light mode \n\n--fg-section-* must ensure WCAG AA contrast with their bg.\n\n🎯 FOREGROUND PAIRING RULES\nIf --bg-section-primary is used → --fg-section-primary = color-primary but it should be more darker in the light mode and more lighter in the dark mode\n\nIf --bg-section-accent is used → --fg-section-accent = color-accent but it should be more darker in the light mode and more lighter in the dark mode\n\nIf --color-primary or --color-accent used as full backgrounds → foreground must be white or high-contrast light version\n\n\n🧠 ACCESSIBILITY RULES\n\nIf the accent color is not matching with the primary color ( and you as Color pallet expert ) ignore that accent color and bring or propose another color that will match with the primary color\n\nThe section primary fg\n\nAll text colors must meet 5:1 contrast minimum with their backgrounds\n\nNever use low-contrast primary/accent pairings\n\nFallback to #000000 or #FFFFFF where needed for clarity\n\n🔧 COMPONENT TOKENS\n--color-primary-soft: Optional, use for icon backgrounds (20–30% opacity tint)\n\nCTA, stars, icons = always use --color-primary\n\nSecondary use of --color-accent = visual variety (never overuse)\n\n📤 OUTPUT FORMAT:\nReturn the result strictly in this JSON structure:\n\n{\n  \"--color-primary\": \"#HEX\",\n  \"--color-primary-fg\": \"#HEX\",\n  \"--color-accent\": \"#HEX\",\n  \"--color-accent-fg\": \"#HEX\",\n  \"--bg-section-primary\": \"#HEX\",\n  \"--fg-section-primary\": \"#HEX\",\n  \"--bg-section-accent\": \"#HEX\",\n  \"--fg-section-accent\": \"#HEX\",\n  \"--background\": \"#HEX\",\n  \"--fg\": \"#HEX\",\n  \"--text-muted\": \"#HEX\",\n  \"--bg-muted\": \"#HEX\",\n  \"--font-body\": \"'Poppins', sans-serif\",\n  \"--font-header\": \"'Poppins', sans-serif\",\n  \"--button-border-radius\": \"0.5rem\",\n  \"--card-border-radius\": \"0.5rem\"\n}";

            $prompt_system = "You are a top-level AI design assistant. Your job is to extract a clean, accessible, and brand-aligned color token set from a provided product image. You must strictly follow the token structure, contrast rules, and output format specified by the user. Always prioritize visual harmony, WCAG accessibility, and CSS-compatibility. Output only the JSON color tokens, nothing else.";
            $prompt_user = <<<EOT
🧠 Prompt to Generate AI-Compatible Color Tokens for a Landing Page

You are a top-level design assistant generating a complete color token set for a landing page based solely on a provided product image. Your job is to extract harmonious, accessible, and brand-aligned colors that follow the design logic and token structure outlined below.

🎯 OBJECTIVE:
Extract and generate a design system color palette from the product image that follows strict branding logic, contrast rules, and token structure. Output must be used directly in a CSS theme or design system.

📥 INPUT:
A product image (PNG or JPG)

Light mode (default)

Use your best design judgment to select Primary and Accent colors based on the product packaging, contents, and theme.


🔁 LOGIC RULES:
🎨 COLOR DEFINITIONS
Primary Color:

Main brand color from the product

Used for: CTA background, header, icons, price (if dark), headline (on light bg)

Accent Color:

Harmonizes with primary color

Used in background sections and secondary UI visuals

🎨 SECTION BACKGROUND COLORS
--bg-section-primary: Very light tint of --color-primary

--bg-section-accent: Very light tint of --color-accent

Light mode → tints toward white

Dark mode → shades toward black

( --text-muted) Text muted → must be like fg but more lighter in the light mode, and more darker in the dark mode
( --bg-muted) bg muted → must be like bg but more lighter in the dark mode, and more darker in the light mode

--fg-section-* must ensure WCAG AA contrast with their bg.

🎯 FOREGROUND PAIRING RULES
If --bg-section-primary is used → --fg-section-primary = color-primary but it should be more darker in the light mode and more lighter in the dark mode

If --bg-section-accent is used → --fg-section-accent = color-accent but it should be more darker in the light mode and more lighter in the dark mode

If --color-primary or --color-accent used as full backgrounds → foreground must be white or high-contrast light version

🧠 ACCESSIBILITY RULES

If the accent color is not matching with the primary color ( and you as Color pallet expert ) ignore that accent color and bring or propose another color that will match with the primary color

The section primary fg

All text colors must meet 5:1 contrast minimum with their backgrounds

Never use low-contrast primary/accent pairings

Fallback to #000000 or #FFFFFF where needed for clarity

🔧 COMPONENT TOKENS
--color-primary-soft: Optional, use for icon backgrounds (20–30% opacity tint)

CTA, stars, icons = always use --color-primary

Secondary use of --color-accent = visual variety (never overuse)

📤 OUTPUT FORMAT:
Return the result strictly in this JSON structure:

{
  "--color-primary": "#HEX",
  "--color-primary-fg": "#HEX",
  "--color-accent": "#HEX",
  "--color-accent-fg": "#HEX",
  "--bg-section-primary": "#HEX",
  "--fg-section-primary": "#HEX",
  "--bg-section-accent": "#HEX",
  "--fg-section-accent": "#HEX",
  "--background": "#HEX",
  "--fg": "#HEX",
  "--text-muted": "#HEX",
  "--bg-muted": "#HEX",
  "--font-body": "'Poppins', sans-serif",
  "--font-header": "'Poppins', sans-serif",
  "--button-border-radius": "0.5rem",
  "--card-border-radius": "0.5rem"
}
EOT;

            $prompt_user_new = <<<EOT
Generate a complete, accessible, and harmonious color token set for a landing page based on the uploaded product image. Use your best design judgment to identify the primary brand color and a complementary accent color based on the product packaging, content, or theme.

Apply the following rules:

OBJECTIVE:
Extract a color palette that is brand-aligned and visually balanced, suitable for light mode UI. Follow accessibility standards, especially WCAG contrast ratios. Your output will be used directly in a CSS theme or design system.

COLOR RULES:
--bg-primary: The main brand color.
--bg-accent: A complementary color that harmonizes with --bg-primary.
--bg-section-primary: A very light tint of --bg-primary (for section backgrounds).
--bg-section-accent: A very light tint of --bg-accent (for secondary sections).
--fg-section-primary: A darker version of --bg-primary (for text on --bg-section-primary).
--fg-section-accent: A darker version of --bg-accent (for text on --bg-section-accent).
--bg-muted: A variant of --bg that is slightly darker for light mode.
--fg-muted: A variant of --fg that is slightly lighter for light mode.

Ensure that:
- All text meets at least 5:1 contrast with its background.
- If the chosen accent color does not match the primary, replace it with a better fit.
- Use white or high-contrast light text on full --bg-primary or --bg-accent sections.

OUTPUT FORMAT:
Return the result strictly in this JSON format:

{
  "--bg": "#HEX",
  "--fg": "#HEX",
  "--bg-primary": "#HEX",
  "--fg-primary": "#HEX",
  "--bg-accent": "#HEX",
  "--fg-accent": "#HEX",
  "--bg-section-primary": "#HEX",
  "--fg-section-primary": "#HEX",
  "--bg-section-accent": "#HEX",
  "--fg-section-accent": "#HEX",
  "--bg-muted": "#HEX",
  "--fg-muted": "#HEX",
  "--font-body": "'Poppins', sans-serif",
  "--font-header": "'Poppins', sans-serif",
  "--button-border-radius": "0.5rem",
  "--card-border-radius": "0.5rem"
}
EOT;
            $promptMessages = [
                [
                    'role' => 'system',
                    'content' => $prompt_system
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt_user
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => ['url' => $base64Image]
                        ],
                    ]
                ]
            ];
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => $promptMessages,
                'response_format' => ['type' => 'json_object'], // Enable JSON mode
                'max_tokens' => 1000,
            ]);
            $palette = json_decode($result->choices[0]->message->content, true);



            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['message' => 'Failed to parse AI response.', 'raw_response' => $result->choices[0]->message->content], 500);
            }

            return response()->json($palette);

        } catch (\Exception $e) {
            Log::error('OpenAI Palette Generation Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to generate palette.'], 500);
        }
    }


}
