{{-- resources/views/section-edit.blade.php --}}
@extends('layouts.app')

@push('styles')
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Tab Styling */
        .tab-button { color: #757575;padding: 0.5rem 1rem; border: none; cursor: pointer; background-color: #f0f0f0; border-bottom: 2px solid transparent; transition: all 0.2s ease-in-out; font-weight: 300;font-size: 0.9rem; }
        .tab-button:hover { background-color: #fff; }
        .tab-button.active { background-color: white; color: #000; border-bottom: 2px solid #000; font-weight: 600; }
        .tab-bar { display: flex; border-bottom: 1px solid #e2e8f0; background-color: #f8fafc; border-top-left-radius: 0.375rem; border-top-right-radius: 0.375rem; flex-shrink: 0; }
        /* Editor Wrapper */
        .editor-wrapper { border: 1px solid #e2e8f0; border-radius: 0.375rem; overflow: hidden; height: 50rem; } /* Height from user's last version */
        .editor-instance-container { height: calc(100%); width: 100%; overflow: hidden; }
        /* Variable Tag styles */
        .copy-button { opacity: 0; transition: opacity 0.3s ease-in-out;}
        .variable-item-tag:hover .copy-button { opacity: 1; }
        .variable-item-tag  svg  { margin-bottom: -3px; margin-left: 4px;}
        /* Modal */
        .modal { transition: opacity 0.3s ease; }
        .modal-content { max-height: 85vh; display: flex; flex-direction: column; }
        #variable-list-container { flex-grow: 1; }
        /* Button Loading */
        .button-loading > * { visibility: hidden; }
        .button-loading::after { content: 'Saving...'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); white-space: nowrap; font-size: inherit; font-weight: inherit; color: inherit; }
        .button-loading { position: relative; }
        /* Variable Modal Specific */
        .variable-group-title { font-size: 1.125rem; font-weight: 600; color: #1e293b; margin-top: 1.5rem; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; text-transform: capitalize; }
        .variable-item { background-color: #f9fafb; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 0.75rem; border: 1px solid #f3f4f6; }
        .variable-item-name { font-family: monospace; font-size: 1rem ; margin-top: 0.5rem; }
        .variable-item-desc { font-size: 0.875rem; color: #4b5563; margin-top: 0.25rem; }
        .variable-item-example { font-family: monospace; font-size: 0.8rem; color: #374151; background-color: #e5e7eb; padding: 0.25rem 0.5rem; border-radius: 0.25rem; margin-top: 0.5rem; display: inline-block; white-space: pre; }

        .editor-readonly-overlay { position: absolute; inset: 0; background-color: rgba(200, 200, 200, 0.1); z-index: 10; cursor: not-allowed; }
        /* Asset Upload Area Styling */
        .asset-drop-zone { border: 2px dashed #cbd5e1; border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; background-color: #f8fafc; transition: background-color 0.2s ease-in-out; }
        .asset-drop-zone:hover { background-color: #f1f5f9; border-color: #94a3b8; }
        .asset-drop-zone.dragover { background-color: #e0f2fe; border-color: #38bdf8; }
        .asset-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 1rem; }
        .asset-preview-item { position: relative; border: 1px solid #e2e8f0; border-radius: 0.375rem; overflow: hidden; }
        .asset-preview-item img, .asset-preview-item video { display: block; width: 100%; height: 100px; object-fit: cover; }
        .asset-preview-item .asset-delete-btn { position: absolute; top: 4px; right: 4px; background-color: rgba(0, 0, 0, 0.6); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; line-height: 18px; text-align: center; cursor: pointer; opacity: 0; transition: opacity 0.2s ease-in-out; }
        .asset-preview-item:hover .asset-delete-btn { opacity: 1; }
        .form-input { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;}
        .form-error { color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; }


    </style>
@endpush

@section('content')
    <div class="p-4 flex flex-col flex-1 overflow-hidden">
        {{-- Status Messages etc. --}}
        <div id="status-messages" class="flex-shrink-0">
            @if ($errors->any()) <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded"> <ul> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul> </div> @endif
            <div id="ajax-status-message" class="mb-4 hidden"></div>
        </div>

        {{-- Header Section --}}
        <div class="bg-white p-4 shadow-md rounded-md mb-4 flex justify-between items-center flex-shrink-0">
            {{-- Left Side: Title and ID & Status--}}
            <div class="flex items-center space-x-4"> {{-- Removed justify-between --}}
                <h2 class="text-xl font-semibold text-gray-800">{{ $section->name }}
                    <span class="text-sm text-gray-500"> ({{ $section->id }}) </span>
                </h2>
                <div class="relative">
                    <button id="status-badge-button" data-section-id="{{ $section->id }}" type="button" class=" py-2 px-4 :opacity-50 modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit rounded-lg border border-border shadow-sm inline-flex items-center"> <span id="status-badge-indicator" class="inline-block w-2 h-2 rounded-full mr-1.5"></span> <span id="status-badge-text"></span> <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg> </button>
                    <div id="status-dropdown" class="hidden absolute left-0 mt-2 w-40 origin-top-left bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-20"> <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                            @php
                                $userRole = auth()->user()->role;
                                $statuses = [];
                                if ($userRole === \App\Models\User::ROLE_INTEGRATOR) {
                                    $statuses = ['draft', 'ready'];
                                } elseif ($userRole === \App\Models\User::ROLE_REVIEWER) {
                                    $statuses = ['under_review', 'rejected', 'verified'];
                                } elseif ($userRole === \App\Models\User::ROLE_PROMPT_ENGINEER) {
                                    $statuses = ['pending_prompt', 'prompted'];
                                }
                            @endphp
                            @foreach($statuses as $statusOption)
                                <button type="button" data-status="{{ $statusOption }}" class="status-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem"> {{ ucfirst(str_replace('_', ' ', $statusOption)) }} </button>
                            @endforeach </div> </div>
                </div>
            </div>
            {{-- Right Side: Buttons --}}
            <div class="flex items-center space-x-4">
                <button id="preview-section-btn" type="button" data-section-id="{{ $section->id }}" class="py-2 px-4 :opacity-50 modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm inline-flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2"> <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /> <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /> </svg> <span class="button-text">Preview</span> </button>
                <button id="submit-button" type="button" data-section-id="{{ $section->id }}" class=" py-2 px-4 :opacity-50 modal-cancel-btn !text-sm !py-1.5 !px-3 !w-fit bg-gray-700 text-white rounded-lg border border-border hover:bg-gray-600 shadow-sm inline-flex items-center"> <svg fill="#000000" viewBox="0 0 24 24" id="plus" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path id="primary" d="M5,12H19M12,5V19" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg> <span class="button-text">Save</span> </button>
            </div>
        </div>

        {{-- Main Content Area - Editor Only --}}
        <div class="flex flex-1 min-h-0"> {{-- Removed md:flex-row md:space-x-4 --}}

            {{-- Editor Area - Make this flex column grow --}}
            <div class="flex-1 mb-4 md:mb-0 flex flex-col min-h-0">

                {{-- *** NEW: Wrapper for Tabs and Variables Button *** --}}
                <div class="flex justify-between items-center border-b border-gray-200 bg-gray-50 rounded-t-md flex-shrink-0">
                    {{-- Tab Bar --}}
                    <div id="editor-tab-bar" class="tab-bar border-b-0"> {{-- Removed border-bottom --}}
                        <button type="button" data-target="html-editor-container" class="tab-btn tab-button active">HTML</button>
                        <button type="button" data-target="css-editor-container" class="tab-btn tab-button">CSS</button>
                        <button type="button" data-target="js-editor-container" class="tab-btn tab-button">JavaScript</button>
                        <button type="button" data-target="assets-container" class="tab-btn tab-button">Assets</button>
                        @if(!auth()->user()->hasRole(\App\Models\User::ROLE_INTEGRATOR))
                            <button type="button" data-target="ai-variables-container" class="tab-btn tab-button">AI Variables</button>
                        @endif
                    </div>
                    @if ($userRole === \App\Models\User::ROLE_INTEGRATOR)
                        {{-- Show Variables Button (Moved Here) --}}
                        <button id="show-variables-btn" type="button" class="mr-2 my-1 py-1 px-3 text-xs text-[#006575] bg-[#ddecee] rounded-md border border-[#006575] hover:bg-[#f1f4f4] shadow-sm inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /> </svg>
                            Variables
                        </button>
                    @endif
                </div>
                {{-- *** END: Wrapper *** --}}

                {{-- Editor Panes Wrapper - Make this grow --}}
                <div class="editor-wrapper bg-white flex-grow min-h-0 relative">
                    <div id="html-editor-container" class="editor-pane editor-instance-container"></div>
                    <div id="css-editor-container" class="editor-pane editor-instance-container hidden"></div>
                    <div id="js-editor-container" class="editor-pane editor-instance-container hidden"></div>
                    <div id="assets-container" class="editor-pane p-6 h-full overflow-y-auto hidden">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Section Assets</h3>
                        <div class="mb-6">
                            <h4 class="text-md font-medium mb-2 text-gray-600">Current Assets</h4>
                            <div id="current-assets-list" class="asset-preview-grid">
                                <p class="text-sm text-gray-500 italic col-span-full">No assets uploaded yet.</p>
                            </div>
                        </div>
                        <hr class="my-6">
                        <div>
                            <h4 class="text-md font-medium mb-3 text-gray-600">Upload New Assets <span class="font-xs text-gray-400"> (Maximum {{ ini_get('max_file_uploads') }} files can be uploaded via a single request)</span>  </h4>
                            <form id="asset-upload-form" enctype="multipart/form-data">
                                <input type="hidden" name="section_id" value="{{ $section->id }}">
                                <div id="asset-drop-zone" class="asset-drop-zone mb-4">
                                    <input type="file" id="asset-file-input" name="assets[]" multiple class="hidden">
                                    <label for="asset-file-input" class="cursor-pointer">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"> <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /> </svg>
                                        <p class="mt-1 text-sm text-gray-600"> <span class="font-semibold">Click to upload</span> or drag and drop </p>
                                        <p class="text-xs text-gray-500">Images, Videos, PDFs etc.</p>
                                    </label>
                                </div>
                                <div id="asset-upload-preview" class="mb-4 text-sm text-gray-600"></div>
                                <button id="upload-assets-btn" type="submit" class="py-2 px-4 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md shadow-sm disabled:opacity-50" disabled> Upload Selected Files </button>
                            </form>
                        </div>
                    </div>

                    {{-- START: AI Variables Pane --}}
                    @if(!auth()->user()->hasRole(\App\Models\User::ROLE_INTEGRATOR))
                        <div id="ai-variables-container" class="editor-pane p-6 h-full overflow-y-auto hidden">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-700">Section AI Variables</h3>
                                <button id="create-variable-btn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Create Variable
                                </button>
                            </div>
                            <div id="variables-list-display" class="space-y-3">
                                @if($sectionVariables->isEmpty())
                                    <p id="no-variables-message" class="text-center text-gray-500 py-4">No AI variables created yet.</p>
                                @else
                                    @foreach($sectionVariables as $variable)
                                        <div class="variable-list-item flex justify-between items-center p-3 border rounded-md bg-gray-50" data-variable-id="{{ $variable->id }}">
                                            <div>
                                                <p class="font-mono font-semibold text-gray-800">{{ $variable->name }}</p>
                                                <span class="text-xs px-2 py-0.5 rounded-full {{ $variable->type === 'text' ? 'bg-indigo-100 text-indigo-800' : 'bg-pink-100 text-pink-800' }}">{{ $variable->type }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button type="button" class="edit-variable-btn p-1 text-gray-500 hover:text-blue-600" title="Edit Variable">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <button type="button" class="delete-variable-btn p-1 text-gray-500 hover:text-red-600" title="Delete Variable">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif
                    {{-- END: AI Variables Pane --}}

                    {{-- Read-only overlay --}}
                    <div id="editor-readonly-overlay" class="editor-readonly-overlay hidden absolute inset-0 bg-gray-200 bg-opacity-30 z-10 cursor-not-allowed"></div>
                </div>
            </div>
            {{-- Modals --}}
            @include('modals.section-edit._modal_product_select')
            @include('modals.section-edit._modal_preview_display')
            @include('modals.section-edit._modal_variables')
            @include('modals.section-edit._modal_status_confirm')
            @include('modals.section-edit._modal_style_settings')
            @include('modals.section-edit._modal_ai_variables')


        </div>
        @endsection

        @push('scripts')
            <script>
                // --- Monaco Editor Initialization ---
                let htmlEditor, cssEditor, jsEditor;

                // START: Snippet Definitions
                const customSnippets = {
                    "__.setQuantity": { "prefix": "__.setQuantity", "body": ["__.setQuantity(\"@{{ product.id }}\", ${1:quantity})$0;"], "description": "Sets the quantity for a given category and notifies listeners." },
                    "__.setVariantId": { "prefix": "__.setVariantId", "body": ["__.setVariantId(\"@{{ product.id }}\", ${1:variantId})$0;"], "description": "Sets the variant ID for a given category and notifies listeners." },
                    "__.addToCart": { "prefix": "__.addToCart", "body": ["__.addToCart(\"@{{ product.id }}\", ${1:checkout})$0;"], "description": "Adds an item to the cart, optionally proceeding to checkout." },
                    "__.removeFromCart": { "prefix": "__.removeFromCart", "body": ["__.removeFromCart(${1:variantId})$0;"], "description": "Removes an item from the cart." },
                    "__.getQuantityUpdateHandler": { "prefix": "__.getQuantityUpdateHandler", "body": ["__.getQuantityUpdateHandler(\"@{{ product.id }}\"})$0;"], "description": "Returns a handler function for quantity input changes." },
                    "__.getVariantUpdateHandler": { "prefix": "__.getVariantUpdateHandler", "body": ["__.getVariantUpdateHandler(\"@{{ product.id }}\"})$0;"], "description": "Returns a handler function for variant select changes." },
                    "__.bindQuantityInput": { "prefix": "__.bindQuantityInput", "body": ["__.bindQuantityInput(\"@{{ product.id }}\"})$0;"], "description": "Binds a quantity input element to the render state." },
                    "__.bindVariantSelect": { "prefix": "__.bindVariantSelect", "body": ["__.bindVariantSelect(\"@{{ product.id }}\"})$0;"], "description": "Binds a variant select element to the render state." },
                    "__.getQuantity": { "prefix": "__.getQuantity", "body": ["__.getQuantity(\"@{{ product.id }}\"})$0;"], "description": "Gets the current quantity for a category." },
                    "__.getVariantId": { "prefix": "__.getVariantId", "body": ["__.getVariantId(\"@{{ product.id }}\"})$0;"], "description": "Gets the current variant ID for a category." },
                    "__.getCart": { "prefix": "__.getCart", "body": ["__.getCart()$0;"], "description": "Gets a deep clone of the current cart." },
                    "__.registerChangeListener": { "prefix": "__.registerChangeListener", "body": ["__.registerChangeListener('${1:property}', ${2:callback})$0;"], "description": "Registers a callback function to listen for state changes." },
                    "__.removeChangeListener": { "prefix": "__.removeChangeListener", "body": ["__.removeChangeListener('${1:property}', ${2:callback})$0;"], "description": "Removes a previously registered change listener." },
                    "__.pushError": { "prefix": "__.pushError", "body": ["__.pushError('${1:message}', ${2:timeout})$0;"], "description": "Pushes an error message to registered error handlers." },
                    "__.defineErrorHandler": { "prefix": "__.defineErrorHandler", "body": ["__.defineErrorHandler(${1:callback})$0;"], "description": "Defines a callback function to handle errors." },
                    "__.removeErrorHandler": { "prefix": "__.removeErrorHandler", "body": ["__.removeErrorHandler(${1:callback})$0;"], "description": "Removes a previously defined error handler." }
                };
                // END: Snippet Definitions

                require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@latest/min/vs' }});
                require(['vs/editor/editor.main'], function() {
                    const commonEditorOptions = { theme: 'vs-dark', automaticLayout: true, minimap: { enabled: true }, wordWrap: 'on', fontSize: 14, scrollBeyondLastLine: false, padding: { top: 20, bottom: 10 } };
                    htmlEditor = monaco.editor.create(document.getElementById('html-editor-container'), { ...commonEditorOptions, value: {!! json_encode(old('html_code', $htmlContent ?? '')) !!}, language: 'html' });
                    cssEditor = monaco.editor.create(document.getElementById('css-editor-container'), { ...commonEditorOptions, value:{!! json_encode(old('css_code', $cssContent ?? '')) !!}, language: 'css' });
                    jsEditor = monaco.editor.create(document.getElementById('js-editor-container'), { ...commonEditorOptions, value: {!! json_encode(old('js_code', $jsContent ?? '')) !!}, language: 'javascript' });

                    // Function to create completion items from snippet definitions
                    function createCompletionItems(snippets) {
                        let suggestions = [];
                        for (const key in snippets) {
                            if (snippets.hasOwnProperty(key)) {
                                const snippet = snippets[key];
                                suggestions.push({
                                    label: snippet.prefix,
                                    kind: monaco.languages.CompletionItemKind.Snippet,
                                    documentation: snippet.description,
                                    insertText: snippet.body.join('\n'),
                                    insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                                    // Range will be set by the provider based on current word
                                });
                            }
                        }
                        return suggestions;
                    }

                    const sharedCompletionProvider = {
                        provideCompletionItems: function(model, position) {
                            const word = model.getWordUntilPosition(position);
                            const range = {
                                startLineNumber: position.lineNumber,
                                endLineNumber: position.lineNumber,
                                startColumn: word.startColumn,
                                endColumn: word.endColumn
                            };
                            const items = createCompletionItems(customSnippets);
                            // Update range for all suggestions
                            const suggestionsWithRange = items.map(item => ({...item, range: range }));
                            return { suggestions: suggestionsWithRange };
                        }
                    };

                    // Register Completion Provider for JavaScript
                    monaco.languages.registerCompletionItemProvider('javascript', sharedCompletionProvider);

                    // START: Register Completion Provider for HTML
                    monaco.languages.registerCompletionItemProvider('html', sharedCompletionProvider);
                    // END: Register Completion Provider for HTML

                    setTimeout(() => { htmlEditor?.layout(); cssEditor?.layout(); jsEditor?.layout(); }, 100); // Ensure all editors layout
                });

                document.addEventListener('DOMContentLoaded', () => {
                    // --- Element References (Consolidated) ---
                    const statusBadgeButton = document.getElementById('status-badge-button');
                    const statusDropdown = document.getElementById('status-dropdown');
                    const statusBadgeText = document.getElementById('status-badge-text');
                    const statusBadgeIndicator = document.getElementById('status-badge-indicator');
                    const tabContainer = document.getElementById('editor-tab-bar');
                    const editorPanes = document.querySelectorAll('.editor-pane');
                    const tabButtons = document.querySelectorAll('.tab-btn');
                    const submitButton = document.getElementById('submit-button');
                    const previewBtn = document.getElementById('preview-section-btn');
                    const productModal = document.getElementById('product-select-modal');
                    const productListContainer = document.getElementById('product-list-container');
                    const productPaginationContainer = document.getElementById('product-pagination-container');
                    const previewModal = document.getElementById('preview-display-modal');
                    const previewIframe = document.getElementById('preview-iframe');
                    const previewLoading = document.getElementById('preview-loading-state');
                    const previewError = document.getElementById('preview-error-state');
                    const previewErrorText = previewError?.querySelector('p');
                    const clearDefaultBtn = document.getElementById('clear-default-product-btn');
                    const showVariablesBtn = document.getElementById('show-variables-btn');
                    const variablesModal = document.getElementById('variables-modal');
                    const variableListContainer = document.getElementById('variable-list-container');
                    const editorReadOnlyOverlay = document.getElementById('editor-readonly-overlay');
                    const statusConfirmModal = document.getElementById('status-confirm-modal');
                    const statusConfirmRequiredIdEl = document.getElementById('status-confirm-required-id');
                    const statusConfirmNewStatus = document.querySelectorAll('.new-section-status');
                    const statusConfirmNewSectionValue = document.getElementById('section_new_status_value');
                    const statusConfirmInput = document.getElementById('status-confirm-input');
                    const statusConfirmButton = document.getElementById('status-confirm-button');
                    const statusConfirmError = document.getElementById('status-confirm-error');
                    const assetDropZone = document.getElementById('asset-drop-zone');
                    const assetFileInput = document.getElementById('asset-file-input');
                    const assetUploadPreview = document.getElementById('asset-upload-preview');
                    const uploadAssetsBtn = document.getElementById('upload-assets-btn');
                    const currentAssetsList = document.getElementById('current-assets-list');
                    const reloadPreviewBtn = document.getElementById('reload-preview-btn');
                    const styleSettingsBtn = document.getElementById('style-settings-btn');
                    const styleSettingsModal = document.getElementById('style-settings-modal');
                    const styleSettingsTabBar = document.getElementById('style-settings-tab-bar');
                    const styleSettingsTabButtons = styleSettingsTabBar?.querySelectorAll('.style-tab-button');
                    const styleSettingsTabPanes = styleSettingsModal?.querySelectorAll('.style-tab-pane');
                    const previewHeaderProductImage = document.getElementById('preview-header-product-image');
                    const paletteGrid = document.getElementById('ssp-palette-grid');

                    // --- Initial State & Data ---
                    const initialStatus = '{{ $section->status }}';
                    const sectionId = '{{ $section->id }}';
                    const currentUserRole = '{{ auth()->user()->role }}';
                    let currentStatus = initialStatus;
                    let isStatusLoading = false;
                    let currentlyOpenDropdown = null;
                    const editors = { 'html-editor-container': () => htmlEditor, 'css-editor-container': () => cssEditor, 'js-editor-container': () => jsEditor };
                    const PREVIEW_PRODUCT_STORAGE_KEY = `preview_default_product_${sectionId}`;
                    const PREVIEW_PRODUCT_IMAGE_URL_STORAGE_KEY = `preview_default_product_image_url_${sectionId}`;
                    const placeholderProductImageUrl = 'https://placehold.co/200x200/e2e8f0/475569?text=No+Image';
                    const availableVariables = @json($variables ?? []);

                    // MODIFIED: stylesToLoad - Added border-radius variables
                    const stylesToLoad = [

                        { key: 'color-primary',        inputId: 'ssp-color-primary',        cssVar: '--color-primary',        default: '#3B82F6' },
                        { key: 'color-primary-fg',     inputId: 'ssp-color-primary-fg',     cssVar: '--color-primary-fg',     default: '#ffffff' },
                        { key: 'color-secondary',      inputId: 'ssp-color-secondary',      cssVar: '--color-secondary',      default: '#F59E0B' },
                        { key: 'color-secondary-fg',   inputId: 'ssp-color-secondary-fg',   cssVar: '--color-secondary-fg',   default: '#000000' },
                        { key: 'color-accent',         inputId: 'ssp-color-accent',         cssVar: '--color-accent',         default: '#10B981' },
                        { key: 'color-accent-fg',      inputId: 'ssp-color-accent-fg',      cssVar: '--color-accent-fg',      default: '#ffffff' },

                        { key: 'bg-section-primary',   inputId: 'ssp-bg-section-primary',   cssVar: '--bg-section-primary',   default: '#E0F2FE' },
                        { key: 'fg-section-primary',   inputId: 'ssp-fg-section-primary',   cssVar: '--fg-section-primary',   default: '#0369A1' },
                        { key: 'bg-section-secondary', inputId: 'ssp-bg-section-secondary', cssVar: '--bg-section-secondary', default: '#FEF3C7' },
                        { key: 'fg-section-secondary', inputId: 'ssp-fg-section-secondary', cssVar: '--fg-section-secondary', default: '#92400E' },
                        { key: 'bg-section-accent',    inputId: 'ssp-bg-section-accent',    cssVar: '--bg-section-accent',    default: '#ECFDF5' },
                        { key: 'fg-section-accent',    inputId: 'ssp-fg-section-accent',    cssVar: '--fg-section-accent',    default: '#065F46' },

                        { key: 'text-muted',           inputId: 'ssp-text-muted',           cssVar: '--text-muted',           default: '#6B7280' },
                        { key: 'bg-muted',             inputId: 'ssp-bg-muted',             cssVar: '--bg-muted',             default: '#F3F4F6' },
                        { key: 'muted-fg',             inputId: 'ssp-muted-fg',             cssVar: '--muted-fg',             default: '#1F2937' },


                        { key: 'page-bg',              inputId: 'ssp-page-bg',              cssVar: '--background',     default: '#ffffff' },
                        { key: 'page-fg',              inputId: 'ssp-page-fg',              cssVar: '--fg',     default: '#000000' },

                        { key: 'style-heading-font',   inputId: 'heading-font-select',      cssVar: '--font-header',       default: 'Inter, sans-serif' },
                        { key: 'style-body-font',      inputId: 'body-font-select',         cssVar: '--font-body',         default: 'Inter, sans-serif' },

                        // NEWLY ADDED for border-radius
                        { key: 'button-border-radius', inputId: 'ssp-button-radius',        cssVar: '--button-border-radius',   default: '0.5', unit: 'rem' },
                        { key: 'card-border-radius',   inputId: 'ssp-card-radius',          cssVar: '--card-border-radius',     default: '0.5', unit: 'rem' }
                    ];

                    const predefinedPalettes = {
                        "fashion_apparel": {
                            "--background": "#EFEFEF",
                            "--fg": "#000000",
                            "--bg-section-primary": "#F4E4C1",
                            "--fg-section-primary": "#000000",
                            "--bg-section-secondary": "#F9F3E9",
                            "--fg-section-secondary": "#000000",
                            "--bg-section-accent": "#E8D5B7",
                            "--fg-section-accent": "#000000",
                            "--color-primary": "#8B4513",
                            "--color-primary-fg": "#FFFFFF",
                            "--color-secondary": "#A0522D",
                            "--color-secondary-fg": "#FFFFFF",
                            "--color-accent": "#D2B48C",
                            "--color-accent-fg": "#000000",
                            "--text-muted": "#8B7765",
                            "--bg-muted": "#F0E6D6",
                            "--muted-fg": "#000000",
                            "--font-body": "'Poppins', sans-serif",
                            "--font-header": "'Poppins', sans-serif",
                            "--button-border-radius": "0.5rem",
                            "--card-border-radius": "0.5rem"

                        },
                        "beauty_makeup": {
                            "--background": "#FDF8F6",
                            "--fg": "#000000",
                            "--bg-section-primary": "#FFE4E1",
                            "--fg-section-primary": "#000000",
                            "--bg-section-secondary": "#F8E8E5",
                            "--fg-section-secondary": "#000000",
                            "--bg-section-accent": "#FFCCCB",
                            "--fg-section-accent": "#000000",
                            "--color-primary": "#E91E63",
                            "--color-primary-fg": "#FFFFFF",
                            "--color-secondary": "#D81B60",
                            "--color-secondary-fg": "#FFFFFF",
                            "--color-accent": "#FF6B9D",
                            "--color-accent-fg": "#FFFFFF",
                            "--text-muted": "#8D6E6E",
                            "--bg-muted": "#F5E6E8",
                            "--muted-fg": "#000000",
                            "--font-body": "'Poppins', sans-serif",
                            "--font-header": "'Poppins', sans-serif",
                            "--button-border-radius": "0.5rem",
                            "--card-border-radius": "0.5rem"

                        }
                    };


                    // --- Helper: Show/Hide Modal ---
                    const showModal = (modal) => modal?.classList.remove('hidden');
                    const hideModal = (modal) => modal?.classList.add('hidden');



                    // START: AI VARIABLE MANAGEMENT SCRIPT
                    const aiVariablesTab = document.querySelector('button[data-target="ai-variables-container"]');
                    if (aiVariablesTab) {
                        const createVariableBtn = document.getElementById('create-variable-btn');
                        const variableModal = document.getElementById('variable-edit-modal');
                        const variableForm = document.getElementById('variable-form');
                        const variableModalTitle = document.getElementById('variable-modal-title');
                        const variableFormMethod = document.getElementById('variable-form-method');
                        const variableFormId = document.getElementById('variable-form-id');
                        const variableNameInput = document.getElementById('variable-name');
                        const variableTypeSelect = document.getElementById('variable-type');
                        const variablePromptTextarea = document.getElementById('variable-prompt');
                        const defaultTextContainer = document.getElementById('default-text-container');
                        const defaultImageContainer = document.getElementById('default-image-container');
                        const defaultTextInput = document.getElementById('variable-default-text');
                        const defaultImageInput = document.getElementById('variable-default-image');
                        const currentImagePreview = document.getElementById('current-default-image-preview');
                        const variableListDisplay = document.getElementById('variables-list-display');
                        const sectionId = '{{ $section->id }}';

                        const clearFormErrors = () => {
                            variableForm.querySelectorAll('.form-error').forEach(el => el.textContent = '');
                        };

                        const toggleDefaultFields = () => {
                            if (variableTypeSelect.value === 'text') {
                                defaultTextContainer.classList.remove('hidden');
                                defaultImageContainer.classList.add('hidden');
                                defaultImageInput.value = '';
                            } else {
                                defaultTextContainer.classList.add('hidden');
                                defaultImageContainer.classList.remove('hidden');
                                defaultTextInput.value = '';
                            }
                        };

                        const resetAndShowModalForCreate = () => {
                            variableModalTitle.textContent = 'Create New AI Variable';
                            variableForm.reset();
                            clearFormErrors();
                            variableFormMethod.value = 'POST';
                            variableFormId.value = '';
                            variableForm.action = `/section/${sectionId}/variables`;
                            currentImagePreview.innerHTML = '';
                            toggleDefaultFields();
                            showModal(variableModal);
                        };

                        const populateAndShowModalForEdit = (variable) => {
                            variableModalTitle.textContent = 'Edit AI Variable';
                            variableForm.reset();
                            clearFormErrors();
                            variableFormMethod.value = 'PUT';
                            variableForm.action = `/section/variables/${variable.id}`;

                            variableNameInput.value = variable.name;
                            variableTypeSelect.value = variable.type;
                            variablePromptTextarea.value = variable.prompt || '';
                            defaultTextInput.value = variable.default_text_value || '';

                            toggleDefaultFields();

                            if(variable.type === 'image' && variable.default_image_url) {
                                currentImagePreview.innerHTML = `<p class="text-xs text-gray-600 mb-1">Current Image:</p><img src="${variable.default_image_url}" class="h-16 w-16 object-cover border rounded">`;
                            } else {
                                currentImagePreview.innerHTML = '';
                            }

                            showModal(variableModal);
                        };

                        const renderVariableListItem = (variable) => {
                            const typeClass = variable.type === 'text' ? 'bg-indigo-100 text-indigo-800' : 'bg-pink-100 text-pink-800';
                            return `
                                <div class="variable-list-item flex justify-between items-center p-3 border rounded-md bg-gray-50" data-variable-id="${variable.id}">
                                    <div>
                                        <p class="font-mono font-semibold text-gray-800">${variable.name}</p>
                                        <span class="text-xs px-2 py-0.5 rounded-full ${typeClass}">${variable.type}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" class="edit-variable-btn p-1 text-gray-500 hover:text-blue-600" title="Edit Variable">
                                            <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button type="button" class="delete-variable-btn p-1 text-gray-500 hover:text-red-600" title="Delete Variable">
                                            <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            `;
                        };

                        const addOrUpdateVariableInList = (variable) => {
                            const existingItem = variableListDisplay.querySelector(`.variable-list-item[data-variable-id="${variable.id}"]`);
                            const newItemHTML = renderVariableListItem(variable);
                            if (existingItem) {
                                existingItem.outerHTML = newItemHTML;
                            } else {
                                const noVarsMessage = document.getElementById('no-variables-message');
                                if(noVarsMessage) noVarsMessage.remove();
                                variableListDisplay.insertAdjacentHTML('beforeend', newItemHTML);
                            }
                        };

                        if(createVariableBtn) createVariableBtn.addEventListener('click', resetAndShowModalForCreate);
                        if(variableModal) {
                            variableModal.querySelectorAll('.modal-close-btn, .modal-cancel-btn').forEach(btn => btn.addEventListener('click', () => hideModal(variableModal)));
                            variableModal.addEventListener('click', (e) => { if (e.target === variableModal) hideModal(variableModal); });
                        }
                        if(variableTypeSelect) variableTypeSelect.addEventListener('change', toggleDefaultFields);

                        if (variableNameInput) {
                            variableNameInput.addEventListener('input', function() {
                                this.value = this.value.toUpperCase();
                            });
                        }

                        if(variableForm) {
                            variableForm.addEventListener('submit', async (e) => {
                                e.preventDefault();
                                const submitBtn = document.getElementById('variable-form-submit-btn');
                                submitBtn.disabled = true;
                                submitBtn.textContent = 'Saving...';
                                clearFormErrors();

                                const formData = new FormData(variableForm);
                                const url = variableForm.action;
                                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                                try {
                                    const response = await fetch(url, {
                                        method: 'POST',
                                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                        body: formData,
                                    });
                                    const data = await response.json();
                                    if (!response.ok) {
                                        if (response.status === 422) {
                                            Object.keys(data.errors).forEach(key => {
                                                const errorKey = key.split('.')[0]; // Handle array inputs
                                                const errorElement = document.getElementById(`variable-${errorKey.replace(/_/g, '-')}-error`);
                                                if (errorElement) errorElement.textContent = data.errors[key][0];
                                            });
                                        }
                                        throw new Error(data.message || 'Validation failed.');
                                    }
                                    showToast(data.message || 'Variable saved successfully!', 'success');
                                    hideModal(variableModal);
                                    addOrUpdateVariableInList(data);
                                } catch (error) {
                                    showToast(error.message, 'error');
                                } finally {
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = 'Save Variable';
                                }
                            });
                        }

                        if(variableListDisplay) {
                            variableListDisplay.addEventListener('click', async (e) => {
                                const editBtn = e.target.closest('.edit-variable-btn');
                                const deleteBtn = e.target.closest('.delete-variable-btn');

                                if (editBtn) {
                                    const listItem = editBtn.closest('.variable-list-item');
                                    const variableId = listItem.dataset.variableId;
                                    try {
                                        const response = await fetch(`/section/variables/${variableId}`);
                                        if (!response.ok) throw new Error('Failed to fetch variable data.');
                                        const variableData = await response.json();
                                        populateAndShowModalForEdit(variableData);
                                    } catch (error) {
                                        showToast(error.message, 'error');
                                    }
                                }

                                if (deleteBtn) {
                                    if (!confirm('Are you sure? This will delete the variable permanently.')) return;
                                    const listItem = deleteBtn.closest('.variable-list-item');
                                    const variableId = listItem.dataset.variableId;
                                    const url = `/section/variables/${variableId}`;
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                                    try {
                                        const response = await fetch(url, {
                                            method: 'DELETE',
                                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                                        });
                                        const data = await response.json();
                                        if (!response.ok) throw new Error(data.message);
                                        showToast(data.message, 'success');
                                        listItem.remove();
                                        if (variableListDisplay.children.length === 0) {
                                            variableListDisplay.innerHTML = `<p id="no-variables-message" class="text-center text-gray-500 py-4">No AI variables created yet.</p>`;
                                        }
                                    } catch (error) {
                                        showToast(error.message, 'error');
                                    }
                                }
                            });
                        }
                    }

                    const updatePreviewHeaderProductImage = () => { /* ... existing code ... */
                        const imageUrl = localStorage.getItem(PREVIEW_PRODUCT_IMAGE_URL_STORAGE_KEY);
                        if (previewHeaderProductImage) {
                            if (imageUrl && imageUrl !== placeholderProductImageUrl) {
                                previewHeaderProductImage.src = imageUrl;
                                previewHeaderProductImage.classList.remove('hidden');
                            } else {
                                previewHeaderProductImage.src = 'https://placehold.co/200x200/e2e8f0/475569?text=No+Image';
                                //previewHeaderProductImage.classList.add('hidden');
                            }
                        }
                    };


                    const toggleEditorInteractivity = (isReady) => { /* ... existing code ... */
                        const readOnly = isReady;
                        htmlEditor?.updateOptions({ readOnly: readOnly });
                        cssEditor?.updateOptions({ readOnly: readOnly });
                        jsEditor?.updateOptions({ readOnly: readOnly });
                        if(editorReadOnlyOverlay) editorReadOnlyOverlay.classList.toggle('hidden', !readOnly);
                        if(submitButton) submitButton.disabled = readOnly;
                    };


                    const updateBadgeStyle = (status) => {
                        if (!statusBadgeButton || !statusBadgeText || !statusBadgeIndicator) return;
                        statusBadgeText.textContent = (status || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        const baseButtonClasses = ['py-2 px-4 :opacity-50 modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit rounded-lg border border-border shadow-sm inline-flex items-center'];
                        const statusClasses = {
                            'draft': { button: 'bg-gray-100 text-gray-800', indicator: 'bg-gray-400' },
                            'ready': { button: 'bg-sky-100 text-sky-800 hover:bg-sky-200', indicator: 'bg-sky-500' },
                            'rejected': { button: 'bg-red-100 text-red-800 hover:bg-red-200', indicator: 'bg-red-500' },
                            'under_review': { button: 'bg-orange-100 text-orange-800 hover:bg-orange-200', indicator: 'bg-orange-500' },
                            'verified': { button: 'bg-green-100 text-green-800', indicator: 'bg-green-500' },
                            'pending_prompt': { button: 'bg-purple-100 text-purple-800 hover:bg-purple-200', indicator: 'bg-purple-500' },
                            'prompted': { button: 'bg-teal-100 text-teal-800 hover:bg-teal-200', indicator: 'bg-teal-500' }
                        };
                        const currentClasses = statusClasses[status] || { button: 'bg-gray-100 text-gray-800', indicator: 'bg-gray-400' };
                        statusBadgeButton.className = [...baseButtonClasses, currentClasses.button].join(' ');
                        statusBadgeIndicator.className = 'inline-block w-2 h-2 rounded-full mr-1.5 ' + currentClasses.indicator;

                        let isReadOnly = true;
                        if (currentUserRole === '{{ \App\Models\User::ROLE_INTEGRATOR }}' && status === 'draft') {
                            isReadOnly = false;
                        } else if (currentUserRole === '{{ \App\Models\User::ROLE_PROMPT_ENGINEER }}' && ['pending_prompt'].includes(status)) {
                            isReadOnly = false;
                        }
                        setTimeout(() => toggleEditorInteractivity(isReadOnly), 200);
                    };

                    if(statusBadgeButton) { updateBadgeStyle(initialStatus); }

                    const closeAllDropdowns = (exceptButton = null) => {
                        if (currentlyOpenDropdown && (!exceptButton || exceptButton !== currentlyOpenDropdown.previousElementSibling)) {
                            currentlyOpenDropdown.classList.add('hidden');
                            currentlyOpenDropdown = null;
                        }
                    };

                    if (statusBadgeButton && statusDropdown) {
                        statusBadgeButton.addEventListener('click', (event) => {
                            event.stopPropagation();
                            const isHidden = statusDropdown.classList.contains('hidden');
                            closeAllDropdowns(statusBadgeButton); // Pass button to avoid self-closing
                            if (isHidden) {
                                statusDropdown.classList.remove('hidden');
                                currentlyOpenDropdown = statusDropdown;
                            } else {
                                // already handled by closeAllDropdowns if it was this one
                                currentlyOpenDropdown = null;
                            }
                        });
                        statusDropdown.addEventListener('click', (event) => { /* ... existing status option logic ... */
                            const targetButton = event.target.closest('.status-option');
                            if (targetButton && !isStatusLoading) {
                                const newStatus = targetButton.dataset.status;
                                statusDropdown.classList.add('hidden');
                                currentlyOpenDropdown = null;
                                if (newStatus && newStatus !== currentStatus) {
                                    if (newStatus === 'ready' && currentStatus === 'draft') {
                                        if (statusConfirmModal && statusConfirmRequiredIdEl && statusConfirmInput && statusConfirmButton) {
                                            statusConfirmRequiredIdEl.textContent = sectionId;
                                            statusConfirmNewStatus.forEach(text => { text.textContent = (newStatus || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())});
                                            statusConfirmNewSectionValue.value= newStatus;
                                            statusConfirmInput.value = '';
                                            statusConfirmError?.classList.add('hidden');
                                            statusConfirmButton.disabled = true;
                                            showModal(statusConfirmModal);
                                        } else { console.error("Status confirmation modal elements not found."); }
                                    }
                                    if (newStatus === 'prompted' && currentStatus === 'pending_prompt') {
                                        if (statusConfirmModal && statusConfirmRequiredIdEl && statusConfirmInput && statusConfirmButton) {
                                            statusConfirmRequiredIdEl.textContent = sectionId;
                                            statusConfirmNewStatus.forEach(text => { text.textContent = (newStatus || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())});
                                            statusConfirmNewSectionValue.value= newStatus;
                                            statusConfirmInput.value = '';
                                            statusConfirmError?.classList.add('hidden');
                                            statusConfirmButton.disabled = true;
                                            showModal(statusConfirmModal);
                                        } else { console.error("Status confirmation modal elements not found."); }
                                    }
                                    else { updateSectionStatus(sectionId, newStatus); }
                                }
                            }
                        });
                    }
                    document.addEventListener('click', (event) => {
                        if (currentlyOpenDropdown) {
                            const isClickInsideToggle = statusBadgeButton?.contains(event.target);
                            const isClickInsideDropdown = statusDropdown?.contains(event.target);
                            if (!isClickInsideToggle && !isClickInsideDropdown) {
                                closeAllDropdowns();
                            }
                        }
                    });

                    const updateSectionStatus = (id, newStatus) => { /* ... existing code ... */
                        if (isStatusLoading || !id) return;
                        isStatusLoading = true;
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (!csrfToken) { console.error('CSRF token missing!'); isStatusLoading = false; return; }
                        const apiUrl = `/section/status/${id}`;
                        fetch(apiUrl, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ status: newStatus }) })
                            .then(response => response.json().then(data => ({ statusVal: response.status, ok: response.ok, body: data }))) // Renamed 'status' to 'statusVal' to avoid conflict
                            .then(({ statusVal, ok, body }) => {
                                if (!ok) { throw new Error(body.message || `HTTP error! status: ${statusVal}`); }
                                currentStatus = body.newStatus;
                                updateBadgeStyle(currentStatus);
                                showToast(body.message || 'Status updated!', 'success');
                            })
                            .catch(error => {
                                console.error('Status Update Error:', error);
                                showToast(`Error updating status: ${error.message}`, 'error');
                            })
                            .finally(() => { isStatusLoading = false; });
                    };

                    if (statusConfirmInput && statusConfirmButton && statusConfirmRequiredIdEl) { /* ... existing code ... */
                        statusConfirmInput.addEventListener('input', () => {
                            statusConfirmButton.disabled = (statusConfirmInput.value.trim() !== statusConfirmRequiredIdEl.textContent);
                            statusConfirmError?.classList.add('hidden');
                        });
                        statusConfirmButton.addEventListener('click', () => {
                            if (statusConfirmInput.value.trim() === statusConfirmRequiredIdEl.textContent) {
                                hideModal(statusConfirmModal);
                                updateSectionStatus(sectionId, statusConfirmNewSectionValue.value);
                            } else {
                                statusConfirmError?.classList.remove('hidden');
                                statusConfirmButton.disabled = true;
                            }
                        });
                    }

                    if (tabContainer) { /* ... existing code ... */
                        tabContainer.addEventListener('click', (event) => {
                            const targetButton = event.target.closest('.tab-btn');
                            if (!targetButton || targetButton.classList.contains('active')) return;
                            const targetPaneId = targetButton.dataset.target;
                            if (!targetPaneId) return;
                            tabButtons.forEach(btn => btn.classList.remove('active'));
                            targetButton.classList.add('active');
                            let newlyVisibleEditor = null;
                            editorPanes.forEach(pane => {
                                const isTarget = pane.id === targetPaneId;
                                pane.classList.toggle('hidden', !isTarget);
                                if (isTarget && editors[pane.id]) {
                                    newlyVisibleEditor = editors[pane.id]();
                                }
                            });
                            if (newlyVisibleEditor) {
                                setTimeout(() => { newlyVisibleEditor.layout(); }, 50);
                            } else if (targetPaneId === 'assets-container') {
                                fetchAndDisplayCurrentAssets(); // Refresh assets when tab is clicked
                            }
                        });
                    }

                    const handlePreviewClick = () => { /* ... existing code ... */
                        if (!sectionId) { showToast('Cannot preview: Section ID is missing.', 'error'); return; }
                        const savedProductId = localStorage.getItem(PREVIEW_PRODUCT_STORAGE_KEY);
                        updatePreviewHeaderProductImage();
                        if (savedProductId) {
                            fetchPreview(sectionId, savedProductId);
                        } else {
                            productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8 product-list-state">Loading products...</p>';
                            productPaginationContainer.innerHTML = '';
                            fetchProducts(1);
                            showModal(productModal);
                        }
                    };

                    document.querySelectorAll('.modal').forEach(modal => { /* ... existing code ... */
                        modal.addEventListener('click', (event) => { if (event.target === modal) hideModal(modal); });
                        modal.querySelectorAll('.modal-close-btn, .modal-cancel-btn').forEach(btn => {
                            btn.addEventListener('click', () => hideModal(modal));
                        });
                    });

                    const fetchProducts = async (page = 1) => { /* ... existing code ... */
                        const productsUrl = `{{ route('section.products') }}?page=${page}`;
                        productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8 product-list-state">Loading products...</p>';
                        productPaginationContainer.innerHTML = '';
                        try {
                            const response = await fetch(productsUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
                            if (!response.ok) { const errorData = await response.json().catch(() => ({ message: `HTTP error! status: ${response.status}` })); throw new Error(errorData.message || `HTTP error! status: ${response.status}`); }
                            const data = await response.json(); displayProducts(data);
                        } catch (error) { console.error('Error fetching products:', error); productListContainer.innerHTML = `<p class="text-center text-red-500 py-8 product-list-state">Error loading products: ${error.message}</p>`; }
                    };

                    const displayProducts = (data) => { /* ... existing code ... */
                        const products = data.products || []; const paginator = data.paginator || {};
                        if (!products || products.length === 0) { productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8 product-list-state">No products found.</p>'; productPaginationContainer.innerHTML = ''; return; }
                        let productHtml = '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">';
                        products.forEach(product => {
                            const productId = product.id; const productName = product.title || 'Unnamed Product';
                            const productImageUrl = product.image?.url || placeholderProductImageUrl;
                            productHtml += `<div class="border rounded-lg overflow-hidden cursor-pointer hover:shadow-md product-item group" data-product-id="${productId}" data-product-image-url="${productImageUrl}" title="${productName}"><img src="${productImageUrl}" alt="${productName}" loading="lazy" class="w-full h-32 object-cover transition-transform duration-200 group-hover:scale-105"><p class="text-sm p-2 truncate">${productName}</p></div>`;
                        });
                        productHtml += '</div>'; productListContainer.innerHTML = productHtml;
                        let paginationHtml = ''; const currentPage = paginator.currentPage || 1; const lastPage = paginator.lastPage || 1;
                        if (lastPage > 1) { paginationHtml += ` <button type="button" data-page="${currentPage - 1}" class="!text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''} product-page-btn" ${currentPage === 1 ? 'disabled' : ''}>« Prev</button> `; paginationHtml += `<span class="px-3 py-1 text-sm text-gray-600">Page ${currentPage} of ${lastPage}</span>`; paginationHtml += ` <button type="button" data-page="${currentPage + 1}" class="!text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm ${currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : ''} product-page-btn" ${currentPage === lastPage ? 'disabled' : ''}>Next »</button> `; }
                        productPaginationContainer.innerHTML = paginationHtml;
                    };

                    if (productListContainer) { /* ... existing code ... */
                        productListContainer.addEventListener('click', (event) => {
                            const productItem = event.target.closest('.product-item');
                            if (productItem && productItem.dataset.productId) {
                                const productId = productItem.dataset.productId; const productImageUrl = productItem.dataset.productImageUrl;
                                localStorage.setItem(PREVIEW_PRODUCT_STORAGE_KEY, productId);
                                if (productImageUrl && productImageUrl !== placeholderProductImageUrl) { localStorage.setItem(PREVIEW_PRODUCT_IMAGE_URL_STORAGE_KEY, productImageUrl); }
                                else { localStorage.removeItem(PREVIEW_PRODUCT_IMAGE_URL_STORAGE_KEY); }
                                updatePreviewHeaderProductImage(); hideModal(productModal); fetchPreview(sectionId, productId);
                            }
                        });
                    }
                    if (productPaginationContainer) { /* ... existing code ... */
                        productPaginationContainer.addEventListener('click', (event) => {
                            const pageButton = event.target.closest('.product-page-btn');
                            if (pageButton && pageButton.dataset.page && !pageButton.disabled) { fetchProducts(pageButton.dataset.page); }
                        });
                    }

                    const fetchPreview = async (secId, prodId) => { /* ... existing code ... */ // Renamed productId to prodId for clarity in this scope
                        if (!secId || !prodId) { showToast('Cannot generate preview: Missing information.', 'error'); return; }
                        const previewUrl = `/section/preview/${secId}`; const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (!csrfToken) { showToast('Cannot generate preview: Missing security token.', 'error'); return; }
                        updatePreviewHeaderProductImage(); showModal(previewModal);
                        previewLoading?.classList.remove('hidden'); previewError?.classList.add('hidden');
                        if(previewIframe) previewIframe.srcdoc = '';
                        try {
                            const response = await fetch(previewUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ productId: prodId }) });
                            const data = await response.json(); if (!response.ok) { throw new Error(data.message || `HTTP error! status: ${response.status}`); }
                            if(previewIframe) { previewIframe.srcdoc = data.previewContent || '<p class="p-4 text-center text-gray-500">No preview content received.</p>'; previewIframe.onload = () => { applyStoredStylesToIframe(); previewIframe.onload = null; }; }
                        } catch (error) { console.error('Error fetching preview:', error); if(previewErrorText) previewErrorText.textContent = `Failed to load preview: ${error.message}`; previewError?.classList.remove('hidden'); if(previewIframe) previewIframe.srcdoc = ''; }
                        finally { previewLoading?.classList.add('hidden'); }
                    };

                    if (clearDefaultBtn) { /* ... existing code ... */
                        clearDefaultBtn.addEventListener('click', () => {
                            localStorage.removeItem(PREVIEW_PRODUCT_STORAGE_KEY); localStorage.removeItem(PREVIEW_PRODUCT_IMAGE_URL_STORAGE_KEY);
                            if (previewHeaderProductImage) {
                                previewHeaderProductImage.src = 'https://placehold.co/200x200/e2e8f0/475569?text=No+Image';
                                //previewHeaderProductImage.classList.add('hidden');
                            }
                            productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8 product-list-state">Loading products...</p>';
                            productPaginationContainer.innerHTML = ''; fetchProducts(1); showModal(productModal);
                        });
                    }

                    const submitEditorContent = async (buttonElement, onSuccessCallback = null) => { /* ... existing code ... */
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (!htmlEditor || !cssEditor || !jsEditor || !buttonElement || !sectionId || !csrfToken) { showToast('Cannot save: Missing required elements or token.', 'error'); return false; }
                        let htmlCode, cssCode, jsCode; try { htmlCode = htmlEditor.getValue(); cssCode = cssEditor.getValue(); jsCode = jsEditor.getValue(); } catch (e) { showToast('Cannot save: Editor content unavailable.', 'error'); return false;}
                        const apiUrl = `/section/edit/${sectionId}`; const buttonTextElement = buttonElement.querySelector('.button-text'); const icon = buttonElement.querySelector('svg');
                        buttonElement.disabled = true; buttonElement.classList.add('button-loading'); if(buttonTextElement) buttonTextElement.style.visibility = 'hidden'; if(icon) icon.style.visibility = 'hidden';
                        try {
                            const response = await fetch(apiUrl, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ html_code: htmlCode, css_code: cssCode, js_code: jsCode }) });
                            const data = await response.json(); if (!response.ok) { throw new Error(data.message || `HTTP error! status: ${response.status}`); }
                            showToast(data.message || 'Section content saved successfully!', 'success');
                            if (typeof onSuccessCallback === 'function') { onSuccessCallback(); } return true;
                        } catch (error) { console.error('Update Error:', error); showToast(`Error saving section: ${error.message}`, 'error'); return false; }
                        finally { buttonElement.disabled = false; buttonElement.classList.remove('button-loading'); if(buttonTextElement) buttonTextElement.style.visibility = 'visible'; if(icon) icon.style.visibility = 'visible';}
                    };

                    if (submitButton) submitButton.addEventListener('click', (e) => submitEditorContent(e.currentTarget));
                    if (previewBtn) previewBtn.addEventListener('click', async (e) => { await submitEditorContent(e.currentTarget, handlePreviewClick); });

                    if (showVariablesBtn && variablesModal && variableListContainer) { /* ... existing code ... */
                        displayVariables(availableVariables);
                        showVariablesBtn.addEventListener('click', () => showModal(variablesModal));
                    }

                    // --- Style Settings Modal Logic ---
                    const applyAndSaveStyle = (cssVarName, value, storageKey) => { /* ... existing code ... */
                        if (!previewIframe || !previewIframe.contentWindow || !cssVarName) return;
                        try {
                            previewIframe.contentWindow.document.documentElement.style.setProperty(cssVarName, value);
                            if(storageKey) localStorage.setItem(storageKey, value);
                        } catch (e) { console.error("Error setting style in iframe:", e); showToast('Could not apply style to preview.', 'error');}
                    };

                    function applyStoredStylesToIframe() { /* ... existing code ... using the globally defined stylesToLoad ... */
                        const iframeDoc = previewIframe?.contentWindow?.document; if (!iframeDoc) return;
                        stylesToLoad.forEach(style => { // Uses the global stylesToLoad
                            if(style.cssVar) { // Only apply if it's meant to be a CSS var
                                const storedValue = localStorage.getItem(style.key);
                                const valueToApply = storedValue !== null ? storedValue : style.default;
                                // For font sizes from number inputs, ensure unit is appended if not already part of default/stored value
                                let finalValueToApply = valueToApply;
                                if (style.unit && !String(valueToApply).endsWith(style.unit) && !isNaN(parseFloat(valueToApply))) {
                                    finalValueToApply = `${parseFloat(valueToApply)}${style.unit}`;
                                }
                                try { iframeDoc.documentElement.style.setProperty(style.cssVar, finalValueToApply); }
                                catch (e) { console.error(`Error applying ${style.cssVar}:`, e); }
                            }
                        });
                    }

                    function loadAndSetStyleControls() { // Now uses the global stylesToLoad
                        if (!stylesToLoad || !Array.isArray(stylesToLoad)) { console.error("stylesToLoad is not available for loadAndSetStyleControls"); return; }
                        stylesToLoad.forEach(style => {
                            const control = document.getElementById(style.inputId);
                            if (control) {
                                const storedValue = localStorage.getItem(style.key);
                                let valueToSet = storedValue !== null ? storedValue : style.default;
                                if (control.type === 'number' && style.unit) {
                                    control.value = parseFloat(valueToSet) || parseFloat(style.default);
                                } else {
                                    control.value = valueToSet;
                                }
                            } else { /* console.warn(`Control with ID ${style.inputId} not found for loading.`); */ }
                        });
                    }

                    function renderPaletteGrid() { /* ... existing code ... */
                        const gridContainer = document.getElementById('ssp-palette-grid'); if (!gridContainer) return; gridContainer.innerHTML = '';
                        Object.keys(predefinedPalettes).forEach(paletteKey => {
                            const palette = predefinedPalettes[paletteKey]; const paletteItem = document.createElement('button');
                            paletteItem.type = 'button'; paletteItem.className = 'p-2.5 border border-gray-300 rounded-lg hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-150 flex flex-col items-center space-y-1.5';
                            paletteItem.dataset.paletteKey = paletteKey; paletteItem.setAttribute('aria-label', `Apply ${paletteKey.replace(/_/g, ' ')} palette`);
                            const name = document.createElement('div'); name.className = 'text-xs font-semibold text-gray-700'; name.textContent = paletteKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()); paletteItem.appendChild(name);
                            const swatchesContainer = document.createElement('div'); swatchesContainer.className = 'flex space-x-1';
                            // MODIFIED: Use new variable names for palette preview swatches
                            const keyColorsForPreview = [
                                palette['--bg-section-primary'],
                                palette['--fg-section-primary'],
                                palette['--color-primary'],
                                palette['--color-accent'],
                                palette['--bg-muted']
                            ].filter(Boolean).slice(0, 5);
                            keyColorsForPreview.forEach(color => { const swatch = document.createElement('div'); swatch.className = 'w-4 h-4 rounded-full border border-gray-200 shadow-sm'; swatch.style.backgroundColor = color; swatchesContainer.appendChild(swatch); });
                            paletteItem.appendChild(swatchesContainer); gridContainer.appendChild(paletteItem);
                        });
                    }


                    if (styleSettingsBtn && styleSettingsModal) {
                        styleSettingsBtn.addEventListener('click', () => { loadAndSetStyleControls(); showModal(styleSettingsModal); });
                        if (styleSettingsTabBar && styleSettingsTabButtons && styleSettingsTabPanes) { /* ... existing tab logic ... */
                            styleSettingsTabBar.addEventListener('click', (event) => {
                                const targetButton = event.target.closest('.style-tab-button'); if (!targetButton || targetButton.classList.contains('active')) return;
                                const targetPaneId = targetButton.dataset.target; if (!targetPaneId) return;
                                styleSettingsTabButtons.forEach(btn => btn.classList.remove('active')); targetButton.classList.add('active');
                                styleSettingsTabPanes.forEach(pane => { pane.classList.toggle('hidden', pane.id !== targetPaneId); pane.classList.toggle('active', pane.id === targetPaneId);});
                            });
                        }

                        styleSettingsModal.querySelectorAll('input[type="color"][data-css-var][data-storage-key]').forEach(input => { /* ... existing event listener ... */
                            input.addEventListener('input', (event) => {
                                applyAndSaveStyle(event.target.dataset.cssVar, event.target.value, event.target.dataset.storageKey);
                            });
                        });
                        styleSettingsModal.querySelectorAll('input[type="number"][data-css-var][data-storage-key]').forEach(input => { /* ... existing event listener, ensures unit handling ... */
                            input.addEventListener('change', (event) => {
                                const cssVar = event.target.dataset.cssVar; const storageKey = event.target.dataset.storageKey;
                                let value = event.target.value; const targetUnit = event.target.dataset.unit || '';
                                if (value && !isNaN(value) && cssVar && storageKey) {
                                    let finalValue = value; if (targetUnit) { finalValue = `${value}${targetUnit}`; }
                                    applyAndSaveStyle(cssVar, finalValue, storageKey);
                                }
                            });
                        });
                        styleSettingsModal.querySelectorAll('select[data-css-var][data-storage-key]').forEach(select => { /* ... existing event listener ... */
                            select.addEventListener('change', (event) => {
                                applyAndSaveStyle(event.target.dataset.cssVar, event.target.value, event.target.dataset.storageKey);
                            });
                        });

                        if (paletteGrid && typeof predefinedPalettes !== 'undefined' && typeof stylesToLoad !== 'undefined') { // Ensure stylesToLoad is available
                            renderPaletteGrid();
                            paletteGrid.addEventListener('click', (event) => {
                                const paletteButton = event.target.closest('button[data-palette-key]'); if (!paletteButton) return;
                                const selectedPaletteKey = paletteButton.dataset.paletteKey; if (!selectedPaletteKey || !predefinedPalettes[selectedPaletteKey]) return;
                                paletteGrid.querySelectorAll('button[data-palette-key]').forEach(btn => { btn.classList.remove('ring-2', 'ring-indigo-600', 'border-indigo-600', 'shadow-xl'); btn.classList.add('border-gray-300'); });
                                paletteButton.classList.add('ring-2', 'ring-indigo-600', 'border-indigo-600', 'shadow-xl'); paletteButton.classList.remove('border-gray-300');
                                const palette = predefinedPalettes[selectedPaletteKey];
                                stylesToLoad.forEach(styleControl => {
                                    // MODIFIED: Use styleControl.cssVar to match keys in palette
                                    if (palette[styleControl.cssVar] && styleControl.inputId) {
                                        const newValue = palette[styleControl.cssVar];
                                        const inputElement = document.getElementById(styleControl.inputId);
                                        const cssVarName = styleControl.cssVar;
                                        if (inputElement && cssVarName) {
                                            // Handle different input types
                                            if (inputElement.type === 'color' || inputElement.tagName.toLowerCase() === 'select') {
                                                inputElement.value = newValue;
                                            } else if (inputElement.type === 'number') {
                                                inputElement.value = parseFloat(newValue) || 0;
                                            }
                                            applyAndSaveStyle(cssVarName, newValue, styleControl.key); // Use styleControl.key for localStorage
                                        }
                                    }
                                });
                            });
                        }
                    } // End Style Settings Logic

                    // --- Asset Management Logic ---
                    async function fetchAndDisplayCurrentAssets() { /* ... existing code ... */
                        const assetsUrl = `/section/assets/${sectionId}`; if (!currentAssetsList) return;
                        currentAssetsList.innerHTML = '<p class="text-sm text-gray-500 italic col-span-full asset-list-state">Loading assets...</p>';
                        try {
                            const response = await fetch(assetsUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
                            if (!response.ok) { const errorData = await response.json().catch(() => null); throw new Error(errorData?.message || `Failed to fetch assets (Status: ${response.status})`);}
                            const assets = await response.json();
                            if (assets && Array.isArray(assets) && assets.length > 0) {
                                let assetsHtml = '';
                                assets.forEach(asset => {
                                    const assetUrl = asset.url || '#'; const assetName = asset.name || 'Unnamed Asset';
                                    assetsHtml += `<div class="asset-preview-item group truncate" title="${assetName}"><img src="${assetUrl}" alt="${assetName}" loading="lazy" onerror="this.style.display='none'; this.parentElement.innerHTML += '<span class=\\'text-xs text-gray-500 p-1\\'>Cannot preview</span><span class=\\'asset-filename \\'>${assetName}</span>';"><span class="asset-filename text-xs text-gray-500">${assetName}</span><button class="asset-delete-btn" data-asset-name="${assetName}" title="Delete">&times;</button></div>`;
                                });
                                currentAssetsList.innerHTML = assetsHtml;
                            } else { currentAssetsList.innerHTML = '<p class="text-sm text-gray-500 italic col-span-full asset-list-state">No assets uploaded yet.</p>';}
                        } catch (error) { console.error('Error fetching or displaying current assets:', error); currentAssetsList.innerHTML = '<p class="text-sm text-red-500 italic col-span-full asset-list-state">Could not load assets.</p>'; showToast('Error loading current assets.', 'error');}
                    }

                    async function deleteAsset(assetName) { /* ... existing code ... */
                        if (!assetName) { showToast('Cannot delete asset: Name missing.', 'error'); return; }
                        const deleteUrl = `/section/assets/${sectionId}/${encodeURIComponent(assetName)}`; const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        try {
                            const response = await fetch(deleteUrl, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }});
                            const result = await response.json(); if (!response.ok) { throw new Error(result.message || `HTTP error! status: ${response.status}`);}
                            showToast(result.message || 'Asset deleted successfully!', 'success'); fetchAndDisplayCurrentAssets();
                        } catch (error) { console.error('Asset Deletion Error:', error); showToast(`Error deleting asset: ${error.message}`, 'error');}
                    }

                    if (assetDropZone && assetFileInput && assetUploadPreview && uploadAssetsBtn) { /* ... existing asset upload setup ... */
                        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => { assetDropZone.addEventListener(eventName, (e) => { e.preventDefault(); e.stopPropagation(); }, false); });
                        ['dragenter', 'dragover'].forEach(eventName => { assetDropZone.addEventListener(eventName, () => assetDropZone.classList.add('dragover'), false); });
                        ['dragleave', 'drop'].forEach(eventName => { assetDropZone.addEventListener(eventName, () => assetDropZone.classList.remove('dragover'), false); });
                        assetDropZone.addEventListener('drop', (e) => { let dt = e.dataTransfer; assetFileInput.files = dt.files; handleFiles(dt.files); }, false);
                        assetFileInput.addEventListener('change', (e) => { handleFiles(e.target.files); });
                        function handleFiles(files) { assetUploadPreview.innerHTML = ''; if (files.length > 0) { let fileNames = Array.from(files).map(file => `<span class="block p-1 bg-gray-100 rounded text-xs mb-1">${file.name} (${(file.size / 1024).toFixed(1)} KB)</span>`).join(''); assetUploadPreview.innerHTML = `Selected files:<div class="mt-2">${fileNames}</div>`; uploadAssetsBtn.disabled = false; } else { assetUploadPreview.innerHTML = ''; uploadAssetsBtn.disabled = true; } }
                        uploadAssetsBtn.addEventListener('click', async (event) => { /* ... existing upload logic ... */
                            event.preventDefault(); const files = assetFileInput.files; if (!files || files.length === 0) { showToast('No files selected for upload.', 'error'); return; }
                            const formData = new FormData(); for (let i = 0; i < files.length; i++) { formData.append('assets[]', files[i]); }
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); if (csrfToken) formData.append('_token', csrfToken);
                            const uploadUrl = `/section/assets/${sectionId}`; const originalButtonText = uploadAssetsBtn.textContent;
                            uploadAssetsBtn.disabled = true; uploadAssetsBtn.textContent = 'Uploading...';
                            try {
                                const response = await fetch(uploadUrl, { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }});
                                const result = await response.json(); if (!response.ok) { throw new Error(result.message || `HTTP error! status: ${response.status}`);}
                                showToast(result.message || 'Assets uploaded successfully!', 'success'); assetFileInput.value = ''; assetUploadPreview.innerHTML = '';
                            } catch (error) { console.error('Asset Upload Error:', error); showToast(`Error uploading assets: ${error.message}`, 'error');}
                            finally { uploadAssetsBtn.disabled = false; uploadAssetsBtn.textContent = originalButtonText; fetchAndDisplayCurrentAssets(); }
                        });
                    }
                    if (currentAssetsList) { /* ... existing delete listener ... */
                        currentAssetsList.addEventListener('click', async (event) => {
                            if (event.target.classList.contains('asset-delete-btn')) {
                                const assetName = event.target.dataset.assetName;
                                if (assetName && confirm(`Are you sure you want to delete the asset "${assetName}"?`)) { // Added confirm
                                    await deleteAsset(assetName);
                                }
                            }
                        });
                    }
                    fetchAndDisplayCurrentAssets(); // Initial fetch

                    if (reloadPreviewBtn) reloadPreviewBtn.addEventListener('click', () => { /* ... existing code ... */
                        const savedProductId = localStorage.getItem(PREVIEW_PRODUCT_STORAGE_KEY);
                        if (savedProductId) fetchPreview(sectionId, savedProductId); else showToast('No default product set to reload preview.', 'error');
                    });

                    function copyToClipboard(text, buttonElement) { /* ... existing code ... */
                        if (navigator && navigator.clipboard) { navigator.clipboard.writeText(text).then(() => { const originalHTML = buttonElement.innerHTML; buttonElement.innerHTML = '<svg viewBox="0 0 24 24" class="h-4 w-4 text-green-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"></path></svg> Copied!'; setTimeout(() => { buttonElement.innerHTML = originalHTML; }, 2000); }).catch(err => {showToast('Failed to copy.', 'error');}); }
                        else { const tempInput = document.createElement('textarea'); tempInput.value = text; document.body.appendChild(tempInput); tempInput.select(); tempInput.focus(); try { document.execCommand('copy'); showToast('Copied to clipboard!', 'success');} catch (e) {showToast('Failed to copy.', 'error');} finally {document.body.removeChild(tempInput);}}
                    }

                    function displayVariables(variablesData) { /* ... existing code ... */
                        const container = document.getElementById('variable-list-container'); if (!container || typeof variablesData !== 'object' || variablesData === null) { container.innerHTML = '<p class="text-center text-gray-500 py-8">No variables defined.</p>'; return;}
                        let html = ''; for (const groupName in variablesData) { if (variablesData.hasOwnProperty(groupName) && Array.isArray(variablesData[groupName])) { html += `<h3 class="variable-group-title">${groupName}<span class="bg-white text-sm text-gray-500 border-gray-300 p-1">(object)</span></h3>`; const variables = variablesData[groupName]; if (variables.length > 0) { variables.forEach(variable => { const safeName = variable.name ? variable.name.replace(/</g, "&lt;").replace(/>/g, "&gt;") : ''; const safeDesc = variable.description ? variable.description.replace(/</g, "&lt;").replace(/>/g, "&gt;") : ''; const safeExample = variable.example ? variable.example.replace(/</g, "&lt;").replace(/>/g, "&gt;") : ''; html += `<div class="variable-item"><div class="flex justify-between items-center"><span onclick="copyToClipboard('${groupName}.${safeName}', this.querySelector('button'))" title="Copy to clipboard" class="variable-item-tag cursor-pointer hover:text-blue-500 transition"> <code class="variable-item-name">${groupName}.${safeName}</code> <button type="button" class="copy-button"><svg class="icon line-color w-4 h-4" fill="#000000" viewBox="0 0 24 24"><path d="M15,5h3a1,1,0,0,1,1,1V20a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V6A1,1,0,0,1,6,5H9"></path><path d="M15,4a1,1,0,0,0-1-1H10A1,1,0,0,0,9,4V7h6ZM9,17h6M9,13h6" style="fill:none;stroke:#2ca9bc;stroke-linecap:round;stroke-linejoin:round;stroke-width:2;"></path></svg></button></span></div> ${safeDesc ? `<p class="variable-item-desc">${safeDesc}</p>` : ''} ${safeExample ? `<pre><span onclick="copyToClipboard('${safeExample}', this.querySelector('button'))" title="Copy to clipboard" class="variable-item-tag cursor-pointer hover:text-blue-500 transition"><code class="variable-item-example hover:bg-blue-100">${safeExample}</code><button type="button" class="copy-button"><svg class="icon line-color w-4 h-4"><use xlink:href="#clipboard-icon-svg-path-here"></use></svg></button></span></pre>` : ''}</div>`; }); } else { html += `<p class="text-sm text-gray-500 italic">No variables defined for ${groupName}.</p>`; } } } container.innerHTML = html || '<p class="text-center text-gray-500 py-8">No variable groups found.</p>';
                    }


                    document.querySelectorAll('.device-btn').forEach(button => { /* ... existing code ... */
                        button.addEventListener('click', () => {
                            const width = button.getAttribute('data-width'); const wrapper = document.getElementById('preview-wrapper');
                            if (wrapper) wrapper.style.width = width === '100%' ? '100%' : `${width}px`;
                        });
                    });

                    if (localStorage.getItem(PREVIEW_PRODUCT_STORAGE_KEY)) {
                        updatePreviewHeaderProductImage();
                    }
                });


                const modal = document.getElementById("style-settings-modal");
                const header = document.getElementById("style-modal-header");

                let isDragging = false;
                let offsetX, offsetY;

                header.addEventListener("mousedown", (e) => {
                    isDragging = true;
                    offsetX = e.clientX - modal.offsetLeft;
                    offsetY = e.clientY - modal.offsetTop;
                    document.body.style.userSelect = 'none'; // Prevent text selection while dragging
                });

                document.addEventListener("mousemove", (e) => {
                    if (isDragging) {
                        modal.style.left = (e.clientX - offsetX) + "px";
                        modal.style.top = (e.clientY - offsetY) + "px";
                    }
                });

                document.addEventListener("mouseup", () => {
                    isDragging = false;
                    document.body.style.userSelect = ''; // Re-enable text selection
                });



            </script>
    @endpush
