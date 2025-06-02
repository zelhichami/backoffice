<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class CodeEditorController extends Controller
{


    public function index()
    {
        $variables = $this->get_variables();
        $user= Auth::user();
        $query = Section::query();

        switch ($user->role) {
            case User::ROLE_INTEGRATOR :
                // Show only this user's own sections
                $query->where('user_id', $user->id);
                break;

            case User::ROLE_REVIEWER :
                // Show sections that are pending review
                $query->where('status', '!=',Section::STATUS_DRAFT);
                break;

          /*  case User::ROLE_PROMPT_ENGINEER :
                // Show sections that are verified and waiting for prompt work
                $query->where('status', Section::STATUS_VERIFIED);
                break;

            case User::ROLE_ADMIN :
                // Show sections pending admin validation
                $query->where('status', Section::STATUS_PENDING_VALIDATION);
                break;

            case User::ROLE_SUPERADMIN :
                // Superadmin sees all sections, no filter
                break;*/

            default:
                // Unknown role — no access
                $query->whereNull('id'); // Always false condition
        }

        $sections = $query->orderBy('updated_at', 'desc')->paginate(20);

        return view('sections-list', compact('sections','variables'));
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

        // Use model accessors to get content
        $htmlContent = $section->html_content;
        $cssContent = $section->css_content;
        $jsContent = $section->js_content;
        $variables = $this->get_variables();

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

            /*$newHtmlCode = preg_replace_callback(
                '/(src|href)=["\'](?!https?:\/\/|\/\/|data:|#|\{\{)([^"\'}]+)["\']/',
                function ($matches) use ($assetUrl) {
                    $filename = basename($matches[2]); // Get only the filename
                    return $matches[1] . '="' . rtrim($assetUrl, '/') . '/' . $filename . '"';
                },
                $html
            );*/

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

        // Authorization Check
        if ($user->hasRole(User::ROLE_INTEGRATOR) ) {
            if ($section->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            if($section->status != Section::STATUS_DRAFT && $section->status != Section::STATUS_READY && $section->status != Section::STATUS_REJECTED ){
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            $allowedStatuses = [Section::STATUS_DRAFT, Section::STATUS_READY];
        }

        // Authorization Check
        if ($user->hasRole(User::ROLE_REVIEWER) ) {

            if($section->status != Section::STATUS_READY && $section->status != Section::STATUS_UNDER_REVIEW && $section->status != Section::STATUS_VERIFIED && $section->status != Section::STATUS_REJECTED ){
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            $allowedStatuses = [Section::STATUS_VERIFIED, Section::STATUS_REJECTED, Section::STATUS_UNDER_REVIEW];
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
        if ($section->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

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

// Retrieve dynamic content
            $htmlContent     = $section->html_content;
            $cssContent      = $section->css_content;
            $jsContent       = $section->js_content;

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


            // Send the request
            $response = Http::withHeaders([
                'X-Signature' => $signature,
            ])->attach(
                'file', $layout_content, $fileName
            )->post($fullUrl);


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
        // Authorization: Check if the authenticated user owns the section
        if ($section->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
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
        // Authorization: Check if the authenticated user owns the section
        if ($section->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
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
        if ($section->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
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


}
