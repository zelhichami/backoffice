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
        .variable-item-name-wrapper { display: flex; justify-content: space-between; align-items: center; cursor: pointer; } /* Added wrapper */
        .variable-item-name { font-family: monospace; font-size: 0.9rem ; margin-right: 0.5rem; color: #111827; } /* Adjusted size */
        .variable-item-desc { font-size: 0.875rem; color: #4b5563; margin-top: 0.25rem; }
        .variable-item-example { font-family: monospace; font-size: 0.8rem; color: #374151; background-color: #e5e7eb; padding: 0.25rem 0.5rem; border-radius: 0.25rem; margin-top: 0.5rem; display: inline-block; white-space: pre; }
        .copy-feedback { font-size: 0.75rem; color: #16a34a; margin-left: 0.5rem; opacity: 0; transition: opacity 0.3s ease-in-out; display: inline-block; vertical-align: middle;}
        .copy-feedback.visible { opacity: 1; }
        /* Read-only editor overlay */
        .editor-readonly-overlay { position: absolute; inset: 0; background-color: rgba(200, 200, 200, 0.1); z-index: 10; cursor: not-allowed; }
        /* Asset Upload Area Styling */
        .asset-drop-zone { border: 2px dashed #cbd5e1; border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; background-color: #f8fafc; transition: background-color 0.2s ease-in-out; }
        .asset-drop-zone:hover { background-color: #f1f5f9; border-color: #94a3b8; }
        .asset-drop-zone.dragover { background-color: #e0f2fe; border-color: #38bdf8; }
        .asset-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 1rem; }
        .asset-preview-item { position: relative; border: 1px solid #e2e8f0; border-radius: 0.375rem; overflow: hidden; }
        .asset-preview-item img, .asset-preview-item video { display: block; width: 100%; height: 100px; object-fit: cover; }
        .asset-preview-item .asset-delete-btn { position: absolute; top: 4px; right: 4px; background-color: rgba(0, 0, 0, 0.6); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; line-height: 18px; text-align: center; cursor: pointer; opacity: 0; transition: opacity 0.2s ease-in-out; }
        .asset-preview-item:hover .asset-delete-btn { opacity: 1; }

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
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-semibold text-gray-800">{{ $section->name }}
                    <span class="text-sm text-gray-500"> ({{ $section->id }}) </span>
                </h2>
                <div class="relative">
                    <button id="status-badge-button" data-section-id="{{ $section->id }}" type="button" class=" py-2 px-4 :opacity-50 modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit rounded-lg border border-border shadow-sm inline-flex items-center"> <span id="status-badge-indicator" class="inline-block w-2 h-2 rounded-full mr-1.5"></span> <span id="status-badge-text"></span> <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg> </button>
                    <div id="status-dropdown" class="hidden absolute left-0 mt-2 w-40 origin-top-left bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-20"> <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu"> @php $statuses = ['draft', 'ready']; @endphp @foreach($statuses as $statusOption) <button type="button" data-status="{{ $statusOption }}" class="status-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem"> {{ ucfirst($statusOption) }} </button> @endforeach </div> </div>
                </div>
            </div>
            {{-- Right Side: Buttons --}}
            <div class="flex items-center space-x-4">
                <button id="show-variables-btn" type="button" class="py-2 px-4 :opacity-50 modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit bg-purple-100 text-purple-700 rounded-lg border border-purple-200 hover:bg-purple-200 shadow-sm inline-flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /> </svg> Variables </button>
                <button id="preview-section-btn" type="button" data-section-id="{{ $section->id }}" class="py-2 px-4 :opacity-50 modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm inline-flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2"> <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /> <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /> </svg> <span class="button-text">Save & Preview</span> </button>
                <button id="submit-button" type="button" data-section-id="{{ $section->id }}" class=" py-2 px-4 :opacity-50 modal-cancel-btn !text-sm !py-1.5 !px-3 !w-fit bg-gray-700 text-white rounded-lg border border-border hover:bg-gray-600 shadow-sm inline-flex items-center"> <svg fill="#000000" viewBox="0 0 24 24" id="plus" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path id="primary" d="M5,12H19M12,5V19" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg> <span class="button-text">Save</span> </button>
            </div>
        </div>

        {{-- Main Content Area - Editor Only --}}
        <div class="flex flex-1 min-h-0">

            {{-- Editor Area - Make this flex column grow --}}
            <div class="flex-1 mb-4 md:mb-0 flex flex-col min-h-0 relative">

                {{-- Wrapper for Tabs and Variables Button --}}
                <div class="flex justify-between items-center border-b border-gray-200 bg-gray-50 rounded-t-md flex-shrink-0">
                    {{-- Tab Bar --}}
                    <div id="editor-tab-bar" class="tab-bar border-b-0">
                        <button type="button" data-target="html-editor-container" class="tab-btn tab-button active">HTML</button>
                        <button type="button" data-target="css-editor-container" class="tab-btn tab-button">CSS</button>
                        <button type="button" data-target="js-editor-container" class="tab-btn tab-button">JavaScript</button>
                        <button type="button" data-target="assets-container" class="tab-btn tab-button">Assets</button>
                    </div>
                    {{-- Show Variables Button --}}
                    <button id="show-variables-btn" type="button" class="mr-2 my-1 py-1 px-3 text-xs text-[#006575] bg-[#ddecee] rounded-md border border-[#006575] hover:bg-[#f1f4f4] shadow-sm inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /> </svg>
                        Variables
                    </button>
                </div>
                {{-- End Wrapper --}}

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
                            <h4 class="text-md font-medium mb-3 text-gray-600">Upload New Assets</h4>
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
                </div>
                {{-- Read-only overlay --}}
                <div id="editor-readonly-overlay" class="editor-readonly-overlay hidden absolute inset-0 bg-gray-200 bg-opacity-30 z-10 cursor-not-allowed"></div>
            </div>
            {{-- Variables Sidebar Removed --}}
        </div>

        {{-- Modals --}}
        <div id="product-select-modal" class="modal fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center hidden p-4"> <div class="modal-content relative mx-auto p-6 border w-full max-w-3xl shadow-lg rounded-md bg-white"> <div class="flex justify-between items-center border-b pb-3 mb-4"> <h3 class="text-md font-semibold">Select Product for Preview</h3> <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button> </div> <div id="product-list-container" class="max-h-[60vh] overflow-y-auto mb-4"> <p class="text-center text-gray-500 py-8 product-list-state">Loading products...</p> </div> <div class="border-t flex justify-between items-center mt-4 pt-4"> <div id="product-pagination-container" class=" flex justify-center items-center space-x-1"> {{-- Pagination buttons --}} </div> <button type="button" class="modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm"> Cancel </button> </div> </div> </div>

        {{-- Preview Modals --}}

        <div id="preview-display-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-90 overflow-hidden h-full w-full z-[60] flex items-center justify-center hidden ">
            <div class="modal-content relative w-full h-full max-w-full max-h-full bg-white rounded-lg shadow-xl flex flex-col">
                <div class="flex justify-between items-center p-3 sm:p-4 border-b flex-shrink-0 bg-black">
                    <h3 class="text-sm font-semibold text-white">Section Preview</h3>
                    <div class="flex items-center space-x-8">
                        <button id="clear-default-product-btn" type="button" class="text-xs text-gray-400 hover:text-white underline"> Clear Default Product </button>
                        <button type="button" class="modal-close-btn text-gray-400 hover:text-white text-3xl leading-none">&times;</button>
                    </div>
                </div>
                <div class="flex-grow overflow-auto relative">
                    <div id="preview-loading-state" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden z-10">
                        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg> <p class="text-gray-600 text-lg ml-3">Generating preview...</p>
                    </div>
                    <div id="preview-error-state" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden z-10 p-8">
                        <p class="text-red-600 text-lg text-center"></p>
                    </div>
                    <iframe id="preview-iframe" src="about:blank" class="w-full h-full border-0"></iframe>
                </div>
            </div>
        </div>


        <div id="variables-modal" class="modal fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center hidden p-4"> <div class="modal-content relative mx-auto p-6 border w-full max-w-2xl shadow-lg rounded-md bg-white"> <div class="flex justify-between items-center border-b pb-3 mb-4"> <h3 class="text-xl font-semibold text-gray-700"> Object Properties <a href="/variables" target="_blank" class="inline-flex items-center gap-1 bg-white text-xs border-gray-300 p-1 underline text-blue-700 hover:text-blue-500"> More Details <svg fill="none" class="iconify iconify--solar w-4 h-4 rounded-md text-blue-700 hover:text-blue-500" width="1em" height="1em" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 6H6C4.89543 6 4 6.89543 4 8V18C4 19.1046 4.89543 20 6 20H16C17.1046 20 18 19.1046 18 18V14M14 4H20M20 4V10M20 4L10 14" stroke="#4A5568" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg> </a> </h3> <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button> </div> <div id="variable-list-container" class="overflow-y-auto pr-2"> <p class="text-center text-gray-500 py-8 variable-list-state">Loading variables...</p> </div> <div class="flex justify-end mt-4 pt-4 border-t flex-shrink-0"> <button type="button" class="modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm"> Close </button> </div> </div> </div>
        {{-- Status Confirmation Modal --}}
        <div id="status-confirm-modal" class="modal fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full z-[70] flex items-center justify-center hidden p-4"> <div class="modal-content relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white"> <div class="flex justify-between items-center border-b pb-3 mb-4"> <h3 class="text-lg font-semibold text-gray-800">Confirm Status Change to 'Ready'</h3> <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button> </div> <div class="mb-4"> <p class="text-sm text-gray-600 mb-3">Setting the status to 'Ready' will make the editor read-only. To confirm this change, please type the section ID below:</p> <p class="text-center font-mono text-lg font-bold text-indigo-600 bg-indigo-50 p-2 rounded" id="status-confirm-required-id"></p> </div> <div class="mb-4"> <label for="status-confirm-input" class="block text-sm font-medium text-gray-700 mb-1">Enter Section ID</label> <input type="text" id="status-confirm-input" name="status_confirm_input" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" autocomplete="off"> <p id="status-confirm-error" class="text-red-600 text-xs mt-1 hidden">IDs do not match.</p> </div> <div class="flex justify-end items-center mt-4 pt-4 border-t space-x-3"> <button type="button" class="modal-cancel-btn px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button> <button id="status-confirm-button" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled> Confirm & Set Ready </button> </div> </div> </div>

    </div> {{-- End p-4 --}}
@endsection

@push('scripts')
    {{-- All JavaScript remains the same --}}
    <script>
        // --- Monaco Editor Initialization ---
        let htmlEditor, cssEditor, jsEditor;
        const isReadOnly = '{{ $section->status }}' === 'ready';

        require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@latest/min/vs' }});
        require(['vs/editor/editor.main'], function() {
            const commonEditorOptions = { theme: 'vs-dark', automaticLayout: true, minimap: { enabled: true }, wordWrap: 'on', fontSize: 14, scrollBeyondLastLine: false, padding: { top: 20, bottom: 10 }, readOnly: isReadOnly };
            htmlEditor = monaco.editor.create(document.getElementById('html-editor-container'), { ...commonEditorOptions, value: @json(old('html_code', $htmlContent ?? '')), language: 'html' });
            cssEditor = monaco.editor.create(document.getElementById('css-editor-container'), { ...commonEditorOptions, value:@json(old('css_code', $cssContent ?? '')), language: 'css' });
            jsEditor = monaco.editor.create(document.getElementById('js-editor-container'), { ...commonEditorOptions, value: @json(old('js_code', $jsContent ?? '')), language: 'javascript' });
            setTimeout(() => { htmlEditor?.layout(); }, 100);
        });

        // --- Vanilla JS for Status Dropdown and Tabs ---
        document.addEventListener('DOMContentLoaded', () => {
            // --- Element References ---
            const statusBadgeButton = document.getElementById('status-badge-button');
            const statusDropdown = document.getElementById('status-dropdown');
            const statusBadgeText = document.getElementById('status-badge-text');
            const statusBadgeIndicator = document.getElementById('status-badge-indicator');
            const tabContainer = document.getElementById('editor-tab-bar');
            const editorPanes = document.querySelectorAll('.editor-pane'); // Includes assets container now
            const tabButtons = document.querySelectorAll('.tab-btn');
            const submitButton = document.getElementById('submit-button'); // Save Button
            const previewBtn = document.getElementById('preview-section-btn'); // Save & Preview Button
            const productModal = document.getElementById('product-select-modal');
            const productListContainer = document.getElementById('product-list-container');
            const productPaginationContainer = document.getElementById('product-pagination-container');
            const previewModal = document.getElementById('preview-display-modal');
            const previewIframe = document.getElementById('preview-iframe');
            const previewLoading = document.getElementById('preview-loading-state');
            const previewError = document.getElementById('preview-error-state');
            const previewErrorText = previewError ? previewError.querySelector('p') : null;
            const clearDefaultBtn = document.getElementById('clear-default-product-btn');
            const showVariablesBtn = document.getElementById('show-variables-btn');
            const variablesModal = document.getElementById('variables-modal');
            const variableListContainer = document.getElementById('variable-list-container');
            const editorReadOnlyOverlay = document.getElementById('editor-readonly-overlay');
            const statusConfirmModal = document.getElementById('status-confirm-modal');
            const statusConfirmRequiredIdEl = document.getElementById('status-confirm-required-id');
            const statusConfirmInput = document.getElementById('status-confirm-input');
            const statusConfirmButton = document.getElementById('status-confirm-button');
            const statusConfirmError = document.getElementById('status-confirm-error');
            // *** NEW Asset Elements ***
            const assetsContainer = document.getElementById('assets-container'); // The new pane
            const assetDropZone = document.getElementById('asset-drop-zone');
            const assetFileInput = document.getElementById('asset-file-input');
            const assetUploadPreview = document.getElementById('asset-upload-preview');
            const uploadAssetsBtn = document.getElementById('upload-assets-btn');
            const currentAssetsList = document.getElementById('current-assets-list');

            // --- Initial State & Data ---
            const initialStatus = '{{ $section->status }}';
            const sectionId = '{{ $section->id }}';
            let currentStatus = initialStatus;
            let isStatusLoading = false;
            let currentlyOpenDropdown = null;
            const editors = { 'html-editor-container': () => htmlEditor, 'css-editor-container': () => cssEditor, 'js-editor-container': () => jsEditor };
            const PREVIEW_PRODUCT_STORAGE_KEY = `preview_default_product_${sectionId}`;
            const availableVariables = @json($variables ?? []);

            // --- Helper: Show/Hide Modal ---
            const showModal = (modal) => modal?.classList.remove('hidden');
            const hideModal = (modal) => modal?.classList.add('hidden');

            // --- Toggle Editor/Button Interactivity ---
            const toggleEditorInteractivity = (isReady) => { /* ... Keep existing ... */ const readOnly = isReady; htmlEditor?.updateOptions({ readOnly: readOnly }); cssEditor?.updateOptions({ readOnly: readOnly }); jsEditor?.updateOptions({ readOnly: readOnly }); if(editorReadOnlyOverlay) { editorReadOnlyOverlay.classList.toggle('hidden', !readOnly); } if(submitButton) submitButton.disabled = readOnly; if(previewBtn) previewBtn.disabled = readOnly; };

            // --- Status Badge Styling ---
            const updateBadgeStyle = (status) => { /* ... Keep existing ... */ if (!statusBadgeButton || !statusBadgeText || !statusBadgeIndicator) return; statusBadgeText.textContent = status.charAt(0).toUpperCase() + status.slice(1); const baseButtonClasses = ['py-2 px-4 :opacity-50 modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit rounded-lg border border-border shadow-sm inline-flex items-center']; const statusButtonClasses = { 'draft': 'bg-gray-100 text-gray-800', 'ready': 'bg-sky-100 text-sky-800 hover:bg-sky-200' }; const statusIndicatorClasses = { 'draft': 'bg-gray-400 hover:bg-gray-200', 'ready': 'bg-sky-500 hover:bg-sky-200' }; statusBadgeButton.className = baseButtonClasses.join(' ') + ' ' + (statusButtonClasses[status] || 'bg-gray-100 text-gray-800 hover:bg-sky-200'); statusBadgeIndicator.className = 'inline-block w-2 h-2 rounded-full mr-1.5 ' + (statusIndicatorClasses[status] || 'bg-gray-400'); toggleEditorInteractivity(status === 'ready'); };
            if(statusBadgeButton) { updateBadgeStyle(initialStatus); }

            // --- Status Dropdown Logic ---
            if (statusBadgeButton && statusDropdown) { /* ... Keep existing ... */ statusBadgeButton.addEventListener('click', (event) => { event.stopPropagation(); const isHidden = statusDropdown.classList.contains('hidden'); closeAllDropdowns(statusBadgeButton); if (isHidden) { statusDropdown.classList.remove('hidden'); currentlyOpenDropdown = statusDropdown; } else { currentlyOpenDropdown = null; } }); statusDropdown.addEventListener('click', (event) => { const targetButton = event.target.closest('.status-option'); if (targetButton && !isStatusLoading) { const newStatus = targetButton.dataset.status; statusDropdown.classList.add('hidden'); currentlyOpenDropdown = null; if (newStatus && newStatus !== currentStatus) { if (newStatus === 'ready' && currentStatus === 'draft') { if (statusConfirmModal && statusConfirmRequiredIdEl && statusConfirmInput && statusConfirmButton) { statusConfirmRequiredIdEl.textContent = sectionId; statusConfirmInput.value = ''; statusConfirmError?.classList.add('hidden'); statusConfirmButton.disabled = true; showModal(statusConfirmModal); } else { console.error("Status confirmation modal elements not found."); } } else { updateSectionStatus(sectionId, newStatus); } } } }); }
            const closeAllDropdowns = (exceptButton = null) => { /* ... Keep existing ... */ if (currentlyOpenDropdown) { if (!exceptButton || exceptButton !== currentlyOpenDropdown.previousElementSibling) { currentlyOpenDropdown.classList.add('hidden'); currentlyOpenDropdown = null; } } };
            document.addEventListener('click', (event) => { /* ... Keep existing ... */ if (currentlyOpenDropdown) { const toggle = event.target.closest('#status-badge-button'); const menu = event.target.closest('#status-dropdown'); if (!toggle && !menu) { closeAllDropdowns(); } } });

            // --- Status Update AJAX Function ---
            const updateSectionStatus = (id, newStatus) => { /* ... Keep existing ... */ if (isStatusLoading || !id) return; isStatusLoading = true; const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); if (!csrfToken) { console.error('CSRF token missing!'); isStatusLoading = false; return; } const apiUrl = `/section/status/${id}`; console.log(`Updating status to: ${newStatus} for section ${id}`); fetch(apiUrl, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'PATCH' }, body: JSON.stringify({ status: newStatus }) }) .then(response => response.json().then(data => ({ status: response.status, ok: response.ok, body: data }))) .then(({ status, ok, body }) => { if (!ok) { throw new Error(body.message || `HTTP error! status: ${status}`); } console.log('Status Update Success:', body); currentStatus = body.newStatus; updateBadgeStyle(currentStatus); if (typeof showToast === 'function') { showToast(body.message || 'Status updated!', 'success'); } else { alert(body.message || 'Status updated!'); } }) .catch(error => { console.error('Status Update Error:', error); if (typeof showToast === 'function') { showToast(`Error updating status: ${error.message}`, 'error'); } else { alert(`Error updating status: ${error.message}`); } }) .finally(() => { isStatusLoading = false; }); };

            // --- Status Confirmation Modal Logic ---
            if (statusConfirmInput && statusConfirmButton && statusConfirmRequiredIdEl) { /* ... Keep existing ... */ statusConfirmInput.addEventListener('input', () => { const requiredId = statusConfirmRequiredIdEl.textContent; const enteredId = statusConfirmInput.value.trim(); statusConfirmButton.disabled = (enteredId !== requiredId); statusConfirmError?.classList.add('hidden'); }); statusConfirmButton.addEventListener('click', () => { const requiredId = statusConfirmRequiredIdEl.textContent; const enteredId = statusConfirmInput.value.trim(); if (enteredId === requiredId) { hideModal(statusConfirmModal); updateSectionStatus(sectionId, 'ready'); } else { statusConfirmError?.classList.remove('hidden'); statusConfirmButton.disabled = true; } }); }

            // --- Editor Tab Switching Logic (UPDATED) ---
            if (tabContainer) {
                tabContainer.addEventListener('click', (event) => {
                    const targetButton = event.target.closest('.tab-btn');
                    if (!targetButton || targetButton.classList.contains('active')) return;

                    const targetPaneId = targetButton.dataset.target;
                    if (!targetPaneId) return;

                    // Update button active states
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    targetButton.classList.add('active');

                    // Update pane visibility and trigger layout for editors
                    let newlyVisibleEditor = null;
                    editorPanes.forEach(pane => {
                        if (pane.id === targetPaneId) {
                            pane.classList.remove('hidden');
                            // Check if the target is an editor instance
                            if (editors[pane.id]) {
                                newlyVisibleEditor = editors[pane.id]();
                            }
                        } else {
                            pane.classList.add('hidden');
                        }
                    });
                    // Also hide/show the assets container
                    if (assetsContainer) {
                        assetsContainer.classList.toggle('hidden', targetPaneId !== 'assets-container');
                    }

                    // Trigger layout for the newly visible editor
                    if (newlyVisibleEditor) {
                        setTimeout(() => {
                            console.log(`Calling layout for editor in #${targetPaneId}`);
                            newlyVisibleEditor.layout();
                        }, 50);
                    }
                });
            }


            // --- Preview Functionality ---
            const handlePreviewClick = () => { /* ... Keep existing ... */ if (!sectionId) { console.error('Section ID missing for preview.'); showToast('Cannot preview: Section ID is missing.', 'error'); return; } const savedProductId = localStorage.getItem(PREVIEW_PRODUCT_STORAGE_KEY); if (savedProductId) { console.log(`Using saved product ID: ${savedProductId} for section ${sectionId}`); fetchPreview(sectionId, savedProductId); } else { console.log(`No saved product ID found for section ${sectionId}, opening modal.`); productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8 product-list-state">Loading products...</p>'; productPaginationContainer.innerHTML = ''; fetchProducts(1); showModal(productModal); } };
            // Generic Modal Close/Cancel Logic
            document.querySelectorAll('.modal').forEach(modal => { modal.addEventListener('click', (event) => { if (event.target === modal) hideModal(modal); }); modal.querySelectorAll('.modal-close-btn, .modal-cancel-btn').forEach(btn => { btn.addEventListener('click', () => hideModal(modal)); }); });
            // Fetch Products Function
            const fetchProducts = async (page = 1) => { /* ... Keep existing ... */ const productsUrl = `{{ route('section.products') }}?page=${page}`; productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8 product-list-state">Loading products...</p>'; productPaginationContainer.innerHTML = ''; try { const response = await fetch(productsUrl, { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }); if (!response.ok) { const errorData = await response.json().catch(() => ({ message: `HTTP error! status: ${response.status}` })); throw new Error(errorData.message || `HTTP error! status: ${response.status}`); } const data = await response.json(); displayProducts(data); } catch (error) { console.error('Error fetching products:', error); productListContainer.innerHTML = `<p class="text-center text-red-500 py-8 product-list-state">Error loading products: ${error.message}</p>`; } };
            // Display Products Function
            const displayProducts = (data) => { /* ... Keep existing (using data.products and data.paginator) ... */ const products = data.products || []; const paginator = data.paginator || {}; if (!products || products.length === 0) { productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8 product-list-state">No products found.</p>'; productPaginationContainer.innerHTML = ''; return; } let productHtml = '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">'; products.forEach(product => { const productId = product.id; const productName = product.title || 'Unnamed Product'; const productImageUrl = product.image?.url || 'https://placehold.co/200x200/e2e8f0/475569?text=No+Image'; productHtml += ` <div class="border rounded-lg overflow-hidden cursor-pointer hover:shadow-md product-item group" data-product-id="${productId}" title="${productName}"> <img src="${productImageUrl}" alt="${productName}" loading="lazy" class="w-full h-32 object-cover transition-transform duration-200 group-hover:scale-105"> <p class="text-sm p-2 truncate">${productName}</p> </div> `; }); productHtml += '</div>'; productListContainer.innerHTML = productHtml; let paginationHtml = ''; const currentPage = paginator.currentPage || 1; const lastPage = paginator.lastPage || 1; if (lastPage > 1) { paginationHtml += ` <button type="button" data-page="${currentPage - 1}" class="!text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'text-blue-600'} product-page-btn" ${currentPage === 1 ? 'disabled' : ''}> « Prev </button> `; paginationHtml += `<span class="px-3 py-1 text-sm text-gray-600">Page ${currentPage} of ${lastPage}</span>`; paginationHtml += ` <button type="button" data-page="${currentPage + 1}" class="!text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm ${currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : 'text-blue-600'} product-page-btn" ${currentPage === lastPage ? 'disabled' : ''}> Next » </button> `; } productPaginationContainer.innerHTML = paginationHtml; };
            // Event listener for product selection (delegated)
            if (productListContainer) { productListContainer.addEventListener('click', (event) => { /* ... Keep existing: Saves to localStorage ... */ const productItem = event.target.closest('.product-item'); if (productItem && productItem.dataset.productId) { const productId = productItem.dataset.productId; console.log(`Product selected: ${productId}`); localStorage.setItem(PREVIEW_PRODUCT_STORAGE_KEY, productId); console.log(`Saved product ID ${productId} to localStorage for key ${PREVIEW_PRODUCT_STORAGE_KEY}`); hideModal(productModal); fetchPreview(sectionId, productId); /* Use sectionId */ } }); }
            // Event listener for pagination buttons (delegated)
            if (productPaginationContainer) { productPaginationContainer.addEventListener('click', (event) => { /* ... Keep existing ... */ const pageButton = event.target.closest('.product-page-btn'); if (pageButton && pageButton.dataset.page && !pageButton.disabled) { fetchProducts(pageButton.dataset.page); } }); }
            // Fetch Preview Function
            const fetchPreview = async (secId, productId) => { /* ... Keep existing ... */ if (!secId || !productId) { console.error('Missing sectionId or productId for preview.'); showToast('Cannot generate preview: Missing information.', 'error'); return; } const previewUrl = `/section/preview/${secId}`; const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); if (!csrfToken) { console.error('CSRF token missing!'); showToast('Cannot generate preview: Missing security token.', 'error'); return; } showModal(previewModal); previewLoading?.classList.remove('hidden'); previewError?.classList.add('hidden'); if(previewIframe) previewIframe.srcdoc = ''; try { const response = await fetch(previewUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ productId: productId }) }); const data = await response.json(); if (!response.ok) { throw new Error(data.message || `HTTP error! status: ${response.status}`); } console.log('Preview Success:', data); if(previewIframe) previewIframe.srcdoc = data.previewContent || '<p class="p-4 text-center text-gray-500">No preview content received.</p>'; } catch (error) { console.error('Error fetching preview:', error); if(previewErrorText) previewErrorText.textContent = `Failed to load preview: ${error.message}`; previewError?.classList.remove('hidden'); if(previewIframe) previewIframe.srcdoc = ''; } finally { previewLoading?.classList.add('hidden'); } };
            // Event listener for Clear Default Button
            if (clearDefaultBtn) { /* ... Keep existing ... */ clearDefaultBtn.addEventListener('click', () => { console.log(`Clearing default product for key ${PREVIEW_PRODUCT_STORAGE_KEY}`); localStorage.removeItem(PREVIEW_PRODUCT_STORAGE_KEY); hideModal(previewModal); if (typeof showToast === 'function') { showToast('Default preview product cleared.', 'success'); } }); }


            // --- Function to Submit Editor Content for UPDATE (Refactored for Save & Preview) ---
            const submitEditorContent = async (buttonElement, onSuccessCallback = null) => {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const currentSectionId = sectionId;

                if (!htmlEditor || !cssEditor || !jsEditor || !buttonElement || !currentSectionId || !csrfToken) {
                    console.error("Submit prerequisites missing.");
                    showToast('Cannot save: Missing required elements or token.', 'error');
                    return false;
                }

                let htmlCode, cssCode, jsCode;
                try {
                    htmlCode = htmlEditor.getValue();
                    cssCode = cssEditor.getValue();
                    jsCode = jsEditor.getValue();
                } catch (editorError) {
                    console.error("Error getting editor content:", editorError);
                    showToast('Cannot save: Editor content unavailable.', 'error');
                    return false;
                }

                const apiUrl = `/section/edit/${currentSectionId}`;
                const buttonTextElement = buttonElement.querySelector('.button-text');
                const icon = buttonElement.querySelector('svg');

                console.log("Submitting update to:", apiUrl);
                buttonElement.disabled = true;
                buttonElement.classList.add('button-loading');
                if(buttonTextElement) buttonTextElement.style.visibility = 'hidden';
                if(icon) icon.style.visibility = 'hidden';

                try {
                    const response = await fetch(apiUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'PUT' },
                        body: JSON.stringify({ html_code: htmlCode, css_code: cssCode, js_code: jsCode })
                    });
                    const data = await response.json();
                    if (!response.ok) { throw new Error(data.message || `HTTP error! status: ${response.status}`); }

                    console.log('Update Success Response:', data);
                    showToast(data.message || 'Section content saved successfully!', 'success');

                    if (typeof onSuccessCallback === 'function') {
                        onSuccessCallback();
                    }
                    return true;

                } catch (error) {
                    console.error('Update Error:', error);
                    showToast(`Error saving section: ${error.message}`, 'error');
                    return false;
                } finally {
                    buttonElement.disabled = false;
                    buttonElement.classList.remove('button-loading');
                    if(buttonTextElement) buttonTextElement.style.visibility = 'visible';
                    if(icon) icon.style.visibility = 'visible';
                }
            };

            // --- Attach Event Listeners for Buttons ---
            if (submitButton) { submitButton.addEventListener('click', (event) => { submitEditorContent(event.currentTarget); }); } else { console.error("Save button element not found!"); }
            if (previewBtn) { previewBtn.addEventListener('click', async (event) => { const buttonElement = event.currentTarget; const success = await submitEditorContent(buttonElement, handlePreviewClick); if (success) { console.log("Save successful, proceeding to preview logic."); } else { console.log("Save failed, preview cancelled."); buttonElement.disabled = false; buttonElement.classList.remove('button-loading'); const textSpan = buttonElement.querySelector('.button-text'); const iconSpan = buttonElement.querySelector('svg'); if(textSpan) textSpan.style.visibility = 'visible'; if(iconSpan) iconSpan.style.visibility = 'visible'; } }); } else { console.error("Save & Preview button element not found!"); }

            // --- Variables Modal Logic ---
            if (showVariablesBtn && variablesModal && variableListContainer) {
                displayVariables(availableVariables);
                showVariablesBtn.addEventListener('click', () => showModal(variablesModal));
                variableListContainer.addEventListener('click', (event) => { const nameElement = event.target.closest('.variable-item-name-wrapper'); if(nameElement) { copyToClipboard(nameElement.dataset.copyValue, nameElement); } });
            }

            // --- NEW: Asset Upload Logic ---
            if (assetDropZone && assetFileInput && assetUploadPreview && uploadAssetsBtn) {
                // Prevent default drag behaviors
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => { assetDropZone.addEventListener(eventName, (e) => { e.preventDefault(); e.stopPropagation(); }, false); });
                // Highlight drop zone
                ['dragenter', 'dragover'].forEach(eventName => { assetDropZone.addEventListener(eventName, () => assetDropZone.classList.add('dragover'), false); });
                ['dragleave', 'drop'].forEach(eventName => { assetDropZone.addEventListener(eventName, () => assetDropZone.classList.remove('dragover'), false); });
                // Handle dropped files
                assetDropZone.addEventListener('drop', (e) => { let dt = e.dataTransfer; let files = dt.files; assetFileInput.files = files; handleFiles(files); }, false);
                // Handle files selected via input click
                assetFileInput.addEventListener('change', (e) => { handleFiles(e.target.files); });
                // Function to handle selected/dropped files
                function handleFiles(files) { assetUploadPreview.innerHTML = ''; if (files.length > 0) { let fileNames = Array.from(files).map(file => `<span class="block p-1 bg-gray-100 rounded text-xs mb-1">${file.name} (${(file.size / 1024).toFixed(1)} KB)</span>`).join(''); assetUploadPreview.innerHTML = `Selected files:<div class="mt-2">${fileNames}</div>`; uploadAssetsBtn.disabled = false; } else { assetUploadPreview.innerHTML = ''; uploadAssetsBtn.disabled = true; } }
                // Handle form submission (Upload button click)
                uploadAssetsBtn.addEventListener('click', async (event) => {
                    event.preventDefault();
                    const files = assetFileInput.files;
                    if (!files || files.length === 0) { showToast('No files selected for upload.', 'error'); return; }
                    const formData = new FormData();
                    for (let i = 0; i < files.length; i++) { formData.append('assets[]', files[i]); }
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (csrfToken) { formData.append('_token', csrfToken); }
                    const uploadUrl = `/section/assets/${sectionId}`; // <<< Needs backend route
                    const originalButtonText = event.target.textContent;
                    event.target.disabled = true; event.target.textContent = 'Uploading...';
                    try {
                        const response = await fetch(uploadUrl, { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } });
                        const result = await response.json();
                        if (!response.ok) { throw new Error(result.message || `HTTP error! status: ${response.status}`); }
                        showToast(result.message || 'Assets uploaded successfully!', 'success');
                        assetFileInput.value = ''; assetUploadPreview.innerHTML = '';
                        // TODO: Refresh the 'Current Assets' list
                    } catch (error) { console.error('Asset Upload Error:', error); showToast(`Error uploading assets: ${error.message}`, 'error'); }
                    finally { event.target.disabled = false; event.target.textContent = originalButtonText; }
                });
                // TODO: Implement fetchAndDisplayCurrentAssets()
            }


        }); // End DOMContentLoaded

        // --- Copy to Clipboard Function ---
        function copyToClipboard(text, element) { // Changed second param to element for clarity
            if (navigator && navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    const feedbackSpan = element.querySelector('.copy-feedback'); // Find feedback within item
                    if (feedbackSpan) {
                        feedbackSpan.classList.add('visible');
                        setTimeout(() => { feedbackSpan.classList.remove('visible'); }, 1500);
                    }
                    console.log('Copied:', text);
                }).catch(err => { console.error('Failed to copy text: ', err); showToast('Failed to copy text.', 'error'); });
            } else { showToast('Copy functionality not fully supported.', 'error'); }
        }

        // --- Function to display grouped variables ---
        function displayVariables(variablesData) {
            const container = document.getElementById('variable-list-container');
            if (!container || typeof variablesData !== 'object' || variablesData === null) {
                container.innerHTML = '<p class="text-center text-gray-500 py-8">No variables defined or failed to load.</p>';
                return;
            }

            let html = '';
            // Iterate over the main groups (product, images, etc.)
            for (const groupName in variablesData) {
                if (variablesData.hasOwnProperty(groupName) && Array.isArray(variablesData[groupName])) {
                    html += `<h3 class="variable-group-title">${groupName}<span class="bg-white text-sm text-gray-500 border-gray-300 p-1">(object)</span></h3>`; // Group heading
                    const variables = variablesData[groupName];

                    if (variables.length > 0) {
                        variables.forEach(variable => {
                            // Sanitize potential HTML in description/example if needed before rendering
                            const safeName = variable.name ? variable.name.replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
                            const safeDesc = variable.description ? variable.description.replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
                            const safeExample = variable.example ? variable.example.replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';


                            html += `
                                <div class="variable-item">
                                    <div class="flex justify-between items-center">
                                        {{-- Use safeName for display, copyName for the copy function --}}
                            <span onclick="copyToClipboard('${groupName}.${safeName}', this)" title="Copy to clipboard"  class=" variable-item-tag cursor-pointer hover:text-blue-500 transition">
                                            <code class="variable-item-name"">${groupName}.${safeName}  </code>
                                            <button type="button" class="copy-button" >
                                            <svg class="icon line-color w-5 h-5 rounded-md" fill="#000000" viewBox="0 0 24 24" id="clipboard" data-name="Line Color" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path id="primary" d="M15,5h3a1,1,0,0,1,1,1V20a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V6A1,1,0,0,1,6,5H9" style="fill: none; stroke: #000000; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><path id="secondary" d="M15,4a1,1,0,0,0-1-1H10A1,1,0,0,0,9,4V7h6ZM9,17h6M9,13h6" style="fill: none; stroke: #2ca9bc; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg>
                                            </button>
                                        </span>
                                    </div>
                                    ${safeDesc ? `<p class="variable-item-desc">${safeDesc}</p>` : ''}
                                    ${safeExample ? `<pre><span onclick="copyToClipboard('${safeExample}', this)" title="Copy to clipboard"  class=" variable-item-tag cursor-pointer hover:text-blue-500 transition"><code class="variable-item-example hover:bg-blue-100" >${safeExample}</code><button type="button" class="copy-button" ><svg class="icon line-color w-5 h-5 rounded-md" fill="#000000" viewBox="0 0 24 24" id="clipboard" data-name="Line Color" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path id="primary" d="M15,5h3a1,1,0,0,1,1,1V20a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V6A1,1,0,0,1,6,5H9" style="fill: none; stroke: #000000; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><path id="secondary" d="M15,4a1,1,0,0,0-1-1H10A1,1,0,0,0,9,4V7h6ZM9,17h6M9,13h6" style="fill: none; stroke: #2ca9bc; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg></button> </span></pre>` : ''}
                                </div>
                            `;
                        });
                    } else {
                        html += `<p class="text-sm text-gray-500 italic">No variables defined for ${groupName}.</p>`;
                    }
                }
            }

            container.innerHTML = html || '<p class="text-center text-gray-500 py-8">No variable groups found.</p>'; // Fallback if object was empty
        }


        // --- Ensure showToast is available (should be in layout) ---
        function showToast(message, type = 'success', duration = 5000) {
            const container = document.getElementById('toast-container');
            if (!container || !message) return;
            const toastElement = document.createElement('div');
            toastElement.setAttribute('role', 'alert');
            toastElement.className = ` relative w-full p-4 rounded-lg shadow-lg text-white text-sm transition-all duration-300 ease-in-out transform opacity-0 translate-y-2 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} `;
            toastElement.textContent = message;
            container.appendChild(toastElement);
            setTimeout(() => { toastElement.classList.remove('opacity-0', 'translate-y-2'); toastElement.classList.add('opacity-100', 'translate-y-0'); }, 10);
            const timerId = setTimeout(() => { dismissToast(toastElement); }, duration);
            const dismissToast = (element) => { if (!element) return; clearTimeout(timerId); element.classList.remove('opacity-100', 'translate-y-0'); element.classList.add('opacity-0', 'translate-y-2'); setTimeout(() => { element.remove(); }, 350); };
        }

    </script>
@endpush
