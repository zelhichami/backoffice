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
                    <div id="status-dropdown" class="hidden absolute left-0 mt-2 w-40 origin-top-left bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-20"> <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu"> @php $statuses = ['draft', 'ready']; @endphp @foreach($statuses as $statusOption) <button type="button" data-status="{{ $statusOption }}" class="status-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem"> {{ ucfirst($statusOption) }} </button> @endforeach </div> </div>
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
                    </div>
                    {{-- Show Variables Button (Moved Here) --}}
                    <button id="show-variables-btn" type="button" class="mr-2 my-1 py-1 px-3 text-xs text-[#006575] bg-[#ddecee] rounded-md border border-[#006575] hover:bg-[#f1f4f4] shadow-sm inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /> </svg>
                        Variables
                    </button>
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


        </div>
        @endsection

        @push('scripts')
            <script>
                // --- Monaco Editor Initialization ---
                let htmlEditor, cssEditor, jsEditor;
                require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@latest/min/vs' }});
                require(['vs/editor/editor.main'], function() {
                    const commonEditorOptions = { theme: 'vs-dark', automaticLayout: true, minimap: { enabled: true }, wordWrap: 'on', fontSize: 14, scrollBeyondLastLine: false, padding: { top: 20, bottom: 10 } };
                    htmlEditor = monaco.editor.create(document.getElementById('html-editor-container'), { ...commonEditorOptions, value: {!! json_encode(old('html_code', $htmlContent ?? '')) !!}, language: 'html' });
                    cssEditor = monaco.editor.create(document.getElementById('css-editor-container'), { ...commonEditorOptions, value:{!! json_encode(old('css_code', $cssContent ?? '')) !!}, language: 'css' });
                    jsEditor = monaco.editor.create(document.getElementById('js-editor-container'), { ...commonEditorOptions, value: {!! json_encode(old('js_code', $jsContent ?? '')) !!}, language: 'javascript' });
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
                    let currentStatus = initialStatus;
                    let isStatusLoading = false;
                    let currentlyOpenDropdown = null;
                    const editors = { 'html-editor-container': () => htmlEditor, 'css-editor-container': () => cssEditor, 'js-editor-container': () => jsEditor };
                    const PREVIEW_PRODUCT_STORAGE_KEY = `preview_default_product_${sectionId}`;
                    const PREVIEW_PRODUCT_IMAGE_URL_STORAGE_KEY = `preview_default_product_image_url_${sectionId}`;
                    const placeholderProductImageUrl = 'https://placehold.co/200x200/e2e8f0/475569?text=No+Image';
                    const availableVariables = @json($variables ?? []);

                    const predefinedPalettes = {
                        "user_default_theme": { // Based on your originally provided CSS variables
                            'style-color-primary': '#0b4e24',        'style-color-primary-foreground': '#f1f1f1',
                            'style-color-secondary': '#17833e',      'style-color-secondary-foreground': '#FFFFFF',
                            'style-color-accent': '#1bb353',         'style-color-accent-foreground': '#262729', // Dark text on light green
                            'style-color-muted': '#FAF8F2',          'style-color-muted-foreground': '#7f8083',
                            'style-color-active-link': '#5c5c5c',    'style-color-active-link-foreground': '#FFFFFF',
                            'style-color-background': '#ffffff',     'style-color-foreground': '#000000', // Black text on white bg
                            'style-color-border': '#E5E7EB',         'style-color-input': '#F9FAFB'
                        },
                        "arctic_white": { // Clean, minimalist, high-contrast
                            'style-color-primary': '#007AFF',        'style-color-primary-foreground': '#FFFFFF', // Apple Blue
                            'style-color-secondary': '#5AC8FA',      'style-color-secondary-foreground': '#000000', // Lighter Blue
                            'style-color-accent': '#FF2D55',         'style-color-accent-foreground': '#FFFFFF', // Bright Pink/Red
                            'style-color-muted': '#F2F2F7',          'style-color-muted-foreground': '#6C6C70',   // Light Gray
                            'style-color-active-link': '#007AFF',    'style-color-active-link-foreground': '#FFFFFF',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#1D1D1F', // Near Black for text
                            'style-color-border': '#D1D1D6',         'style-color-input': '#F2F2F7'
                        },
                        "midnight_bloom": { // Dark theme with vibrant floral accents
                            'style-color-primary': '#8A3FFC',        'style-color-primary-foreground': '#FFFFFF', // Vibrant Purple (IBM)
                            'style-color-secondary': '#00A78F',      'style-color-secondary-foreground': '#FFFFFF', // Teal
                            'style-color-accent': '#FF66A8',         'style-color-accent-foreground': '#161616', // Hot Pink
                            'style-color-muted': '#2C2C2E',          'style-color-muted-foreground': '#A0A0A5',   // Darker Gray
                            'style-color-active-link': '#FF66A8',    'style-color-active-link-foreground': '#161616',
                            'style-color-background': '#161616',     'style-color-foreground': '#F4F4F4', // Light Gray text
                            'style-color-border': '#3A3A3C',         'style-color-input': '#2C2C2E'
                        },
                        "forest_canopy": { // Earthy, natural greens and browns
                            'style-color-primary': '#2E7D32',        'style-color-primary-foreground': '#FFFFFF', // Forest Green
                            'style-color-secondary': '#689F38',      'style-color-secondary-foreground': '#FFFFFF', // Lighter Green
                            'style-color-accent': '#FF8F00',         'style-color-accent-foreground': '#FFFFFF', // Amber/Orange
                            'style-color-muted': '#F1F8E9',          'style-color-muted-foreground': '#556B2F',   // Light Olive Green
                            'style-color-active-link': '#689F38',    'style-color-active-link-foreground': '#FFFFFF',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#3E2723', // Dark Brown text
                            'style-color-border': '#DCEDC8',         'style-color-input': '#F1F8E9'
                        },
                        "oceanic_depth": { // Deep blues and teals
                            'style-color-primary': '#0D47A1',        'style-color-primary-foreground': '#FFFFFF', // Deep Blue
                            'style-color-secondary': '#00ACC1',      'style-color-secondary-foreground': '#FFFFFF', // Cyan/Teal
                            'style-color-accent': '#FFD600',         'style-color-accent-foreground': '#000000', // Yellow accent
                            'style-color-muted': '#E1F5FE',          'style-color-muted-foreground': '#0277BD',   // Lightest Blue
                            'style-color-active-link': '#00ACC1',    'style-color-active-link-foreground': '#FFFFFF',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#1A237E', // Navy text
                            'style-color-border': '#B3E5FC',         'style-color-input': '#E1F5FE'
                        },
                        "sunrise_citrus": { // Vibrant oranges and yellows
                            'style-color-primary': '#FF6F00',        'style-color-primary-foreground': '#FFFFFF', // Bright Orange
                            'style-color-secondary': '#FFC107',      'style-color-secondary-foreground': '#000000', // Amber
                            'style-color-accent': '#F44336',         'style-color-accent-foreground': '#FFFFFF', // Red
                            'style-color-muted': '#FFF8E1',          'style-color-muted-foreground': '#E65100',   // Light Orange/Yellow
                            'style-color-active-link': '#FFC107',    'style-color-active-link-foreground': '#000000',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#BF360C', // Deep Orange text
                            'style-color-border': '#FFECB3',         'style-color-input': '#FFF8E1'
                        },
                        "lavender_haze": { // Soft purples and pinks
                            'style-color-primary': '#9575CD',        'style-color-primary-foreground': '#FFFFFF', // Lavender
                            'style-color-secondary': '#CE93D8',      'style-color-secondary-foreground': '#000000', // Light Purple/Pink
                            'style-color-accent': '#F48FB1',         'style-color-accent-foreground': '#000000', // Soft Pink
                            'style-color-muted': '#F3E5F5',          'style-color-muted-foreground': '#6A1B9A',   // Very Light Purple
                            'style-color-active-link': '#CE93D8',    'style-color-active-link-foreground': '#000000',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#4A148C', // Deep Purple text
                            'style-color-border': '#E1BEE7',         'style-color-input': '#F3E5F5'
                        },
                        "charcoal_slate": { // Modern, sophisticated dark gray theme
                            'style-color-primary': '#455A64',        'style-color-primary-foreground': '#FFFFFF', // Blue Grey
                            'style-color-secondary': '#78909C',      'style-color-secondary-foreground': '#FFFFFF', // Lighter Blue Grey
                            'style-color-accent': '#00ACC1',         'style-color-accent-foreground': '#FFFFFF', // Cyan accent
                            'style-color-muted': '#37474F',          'style-color-muted-foreground': '#CFD8DC',   // Darker Shade
                            'style-color-active-link': '#00ACC1',    'style-color-active-link-foreground': '#FFFFFF',
                            'style-color-background': '#263238',     'style-color-foreground': '#ECEFF1', // Light Grey text
                            'style-color-border': '#546E7A',         'style-color-input': '#37474F'
                        },
                        "sandstone_beige": { // Warm, neutral, and calm
                            'style-color-primary': '#A1887F',        'style-color-primary-foreground': '#FFFFFF', // Brownish Grey
                            'style-color-secondary': '#D7CCC8',      'style-color-secondary-foreground': '#3E2723', // Lightest Brown/Beige
                            'style-color-accent': '#8D6E63',         'style-color-accent-foreground': '#FFFFFF', // Brown
                            'style-color-muted': '#F5F5F0',          'style-color-muted-foreground': '#6D4C41',   // Off-white/Beige
                            'style-color-active-link': '#A1887F',    'style-color-active-link-foreground': '#FFFFFF',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#4E342E', // Dark Brown text
                            'style-color-border': '#EFEBE9',         'style-color-input': '#F5F5F0'
                        },
                        "crimson_gold": { // Luxurious, bold
                            'style-color-primary': '#C62828',        'style-color-primary-foreground': '#FFFFFF', // Deep Red
                            'style-color-secondary': '#FBC02D',      'style-color-secondary-foreground': '#000000', // Gold/Yellow
                            'style-color-accent': '#D81B60',         'style-color-accent-foreground': '#FFFFFF', // Magenta/Pink
                            'style-color-muted': '#3E2723',          'style-color-muted-foreground': '#FFEBEE',   // Dark Brown
                            'style-color-active-link': '#FBC02D',    'style-color-active-link-foreground': '#000000',
                            'style-color-background': '#1B0000',     'style-color-foreground': '#FFCDD2', // Light Red text
                            'style-color-border': '#5D4037',         'style-color-input': '#3E2723'
                        },
                        "aqua_spark": { // Fresh, modern, energetic
                            'style-color-primary': '#00BCD4',        'style-color-primary-foreground': '#FFFFFF', // Cyan
                            'style-color-secondary': '#00E676',      'style-color-secondary-foreground': '#000000', // Bright Green
                            'style-color-accent': '#FFEB3B',         'style-color-accent-foreground': '#000000', // Yellow
                            'style-color-muted': '#E0F7FA',          'style-color-muted-foreground': '#006064',   // Light Cyan
                            'style-color-active-link': '#00E676',    'style-color-active-link-foreground': '#000000',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#004D40', // Dark Teal text
                            'style-color-border': '#B2EBF2',         'style-color-input': '#E0F7FA'
                        },
                        "graphite_lime": { // Dark industrial with a pop of color
                            'style-color-primary': '#343A40',        'style-color-primary-foreground': '#F8F9FA', // Dark Gray
                            'style-color-secondary': '#6C757D',      'style-color-secondary-foreground': '#FFFFFF', // Medium Gray
                            'style-color-accent': '#AFFF33',         'style-color-accent-foreground': '#1B2B00', // Electric Lime
                            'style-color-muted': '#212529',          'style-color-muted-foreground': '#ADB5BD',   // Near Black
                            'style-color-active-link': '#AFFF33',    'style-color-active-link-foreground': '#1B2B00',
                            'style-color-background': '#121212',     'style-color-foreground': '#E9ECEF', // Light Gray text
                            'style-color-border': '#495057',         'style-color-input': '#212529'
                        },
                        "peach_serenity": { // Soft, warm, inviting
                            'style-color-primary': '#FFB3A7',        'style-color-primary-foreground': '#5D4037', // Peach
                            'style-color-secondary': '#FFDAC1',      'style-color-secondary-foreground': '#5D4037', // Lighter Peach
                            'style-color-accent': '#B0E0E6',         'style-color-accent-foreground': '#3A5F6A', // Powder Blue
                            'style-color-muted': '#FFF5F3',          'style-color-muted-foreground': '#A1887F',   // Very Light Peach
                            'style-color-active-link': '#FFB3A7',    'style-color-active-link-foreground': '#5D4037',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#6D4C41', // Brown text
                            'style-color-border': '#FFEAE6',         'style-color-input': '#FFF5F3'
                        },
                        "vintage_wine": { // Rich, classic, sophisticated
                            'style-color-primary': '#722F37',        'style-color-primary-foreground': '#F5EBE0', // Wine Red
                            'style-color-secondary': '#8C5040',      'style-color-secondary-foreground': '#F5EBE0', // Muted Brown
                            'style-color-accent': '#D4AF37',         'style-color-accent-foreground': '#4A3B0C', // Old Gold
                            'style-color-muted': '#F5F5DC',          'style-color-muted-foreground': '#5E503F',   // Beige
                            'style-color-active-link': '#D4AF37',    'style-color-active-link-foreground': '#4A3B0C',
                            'style-color-background': '#FDF6E3',     'style-color-foreground': '#402F2B', // Dark Brown text
                            'style-color-border': '#EADDCA',         'style-color-input': '#F5F5DC'
                        },
                        "steel_blue_tech": { // Clean, modern, tech-focused
                            'style-color-primary': '#4682B4',        'style-color-primary-foreground': '#FFFFFF', // Steel Blue
                            'style-color-secondary': '#B0C4DE',      'style-color-secondary-foreground': '#000000', // Light Steel Blue
                            'style-color-accent': '#32CD32',         'style-color-accent-foreground': '#FFFFFF', // Lime Green
                            'style-color-muted': '#F0F8FF',          'style-color-muted-foreground': '#4682B4',   // Alice Blue
                            'style-color-active-link': '#32CD32',    'style-color-active-link-foreground': '#FFFFFF',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#2F4F4F', // Dark Slate Gray text
                            'style-color-border': '#D8E2EB',         'style-color-input': '#F0F8FF'
                        },
                        "muted_rose_gold": { // Elegant, trendy, soft
                            'style-color-primary': '#B76E79',        'style-color-primary-foreground': '#FFFFFF', // Muted Rose
                            'style-color-secondary': '#D9A9A9',      'style-color-secondary-foreground': '#4A2C2E', // Dusty Pink
                            'style-color-accent': '#E6B8A2',         'style-color-accent-foreground': '#5E3A27', // Rose Gold/Copper
                            'style-color-muted': '#FAF0E6',          'style-color-muted-foreground': '#7D5A5A',   // Linen
                            'style-color-active-link': '#E6B8A2',    'style-color-active-link-foreground': '#5E3A27',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#5C3E40', // Dark Rose Brown text
                            'style-color-border': '#F2E4E4',         'style-color-input': '#FAF0E6'
                        },
                        "terracotta_earth": { // Warm, grounded, natural clay
                            'style-color-primary': '#E2725B',        'style-color-primary-foreground': '#FFFFFF', // Terracotta
                            'style-color-secondary': '#FFA07A',      'style-color-secondary-foreground': '#5D2A18', // Light Salmon
                            'style-color-accent': '#8FBC8F',         'style-color-accent-foreground': '#2E452E', // Dark Sea Green
                            'style-color-muted': '#FAEBD7',          'style-color-muted-foreground': '#8B4513',   // Antique White
                            'style-color-active-link': '#FFA07A',    'style-color-active-link-foreground': '#5D2A18',
                            'style-color-background': '#FFF8DC',     'style-color-foreground': '#7A3B2E', // Sienna text
                            'style-color-border': '#F5DEB3',         'style-color-input': '#FAEBD7'
                        },
                        "vibrant_candy": { // Playful, bright, energetic
                            'style-color-primary': '#FF69B4',        'style-color-primary-foreground': '#FFFFFF', // Hot Pink
                            'style-color-secondary': '#00FFFF',      'style-color-secondary-foreground': '#000000', // Aqua
                            'style-color-accent': '#FFFF00',         'style-color-accent-foreground': '#000000', // Yellow
                            'style-color-muted': '#F0FFFF',          'style-color-muted-foreground': '#FF1493',   // Azure (for muted fg, deep pink on light)
                            'style-color-active-link': '#00FFFF',    'style-color-active-link-foreground': '#000000',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#4B0082', // Indigo text
                            'style-color-border': '#E0FEFE',         'style-color-input': '#F0FFFF'
                        },
                        "classic_cream_black": { // High contrast, timeless
                            'style-color-primary': '#2F2F2F',        'style-color-primary-foreground': '#FFFDD0', // Near Black
                            'style-color-secondary': '#555555',      'style-color-secondary-foreground': '#FFFDD0', // Dark Gray
                            'style-color-accent': '#E0A800',         'style-color-accent-foreground': '#2F2F2F', // Muted Gold
                            'style-color-muted': '#F5F5F5',          'style-color-muted-foreground': '#4A4A4A',   // Light Gray
                            'style-color-active-link': '#E0A800',    'style-color-active-link-foreground': '#2F2F2F',
                            'style-color-background': '#FFFDD0',     'style-color-foreground': '#1C1C1C', // Cream background, very dark text
                            'style-color-border': '#EAEAEA',         'style-color-input': '#F5F5F5'
                        },
                        "nordic_blue_gray": { // Calm, cool, Scandinavian inspired
                            'style-color-primary': '#607D8B',        'style-color-primary-foreground': '#FFFFFF', // Blue Gray
                            'style-color-secondary': '#90A4AE',      'style-color-secondary-foreground': '#263238', // Lighter Blue Gray
                            'style-color-accent': '#FFCC80',         'style-color-accent-foreground': '#5D4037', // Light Orange accent
                            'style-color-muted': '#ECEFF1',          'style-color-muted-foreground': '#546E7A',   // Very Light Blue Gray
                            'style-color-active-link': '#FFCC80',    'style-color-active-link-foreground': '#5D4037',
                            'style-color-background': '#FFFFFF',     'style-color-foreground': '#37474F', // Dark Slate text
                            'style-color-border': '#CFD8DC',         'style-color-input': '#ECEFF1'
                        }
                    };


                    // Single source of truth for style control definitions
                    const stylesToLoad = [
                        { key: 'style-color-primary',               inputId: 'ssp-color-primary',              cssVar: '--color-primary',              default: '#0b4e24' },
                        { key: 'style-color-primary-foreground',    inputId: 'ssp-color-primary-foreground',   cssVar: '--color-primary-foreground',   default: '#f1f1f1' },
                        { key: 'style-color-secondary',             inputId: 'ssp-color-secondary',            cssVar: '--color-secondary',            default: '#17833e' },
                        { key: 'style-color-secondary-foreground',  inputId: 'ssp-color-secondary-foreground', cssVar: '--color-secondary-foreground', default: '#FFFFFF' },
                        { key: 'style-color-accent',                inputId: 'ssp-color-accent',               cssVar: '--color-accent',               default: '#1bb353' },
                        { key: 'style-color-accent-foreground',     inputId: 'ssp-color-accent-foreground',    cssVar: '--color-accent-foreground',    default: '#262729' },
                        { key: 'style-color-muted',                 inputId: 'ssp-color-muted',                cssVar: '--color-muted',                default: '#FAF8F2' },
                        { key: 'style-color-muted-foreground',      inputId: 'ssp-color-muted-foreground',     cssVar: '--color-muted-foreground',     default: '#7f8083' },
                        { key: 'style-color-active-link',           inputId: 'ssp-color-active-link',          cssVar: '--color-active-link',          default: '#5c5c5c' },
                        { key: 'style-color-active-link-foreground',inputId: 'ssp-color-active-link-foreground',cssVar: '--color-active-link-foreground', default: '#FFFFFF' },
                        { key: 'style-color-background',            inputId: 'ssp-color-background',           cssVar: '--color-background',           default: '#ffffff' },
                        { key: 'style-color-foreground',            inputId: 'ssp-color-foreground',           cssVar: '--color-foreground',           default: '#000000' },
                        { key: 'style-color-border',                inputId: 'ssp-color-border',               cssVar: '--color-border',               default: '#E5E7EB' },
                        { key: 'style-color-input',                 inputId: 'ssp-color-input',                cssVar: '--color-input',                default: '#F9FAFB' },
                        /*
                        { key: 'style-heading-font-size',           inputId: 'heading-font-size-input',        cssVar: '--heading-font-size',        default: '1.5', unit: 'rem' },
                        { key: 'style-body-font-size',              inputId: 'body-font-size-input',           cssVar: '--body-font-size',           default: '1',   unit: 'rem' },
                        { key: 'style-button-font-size',            inputId: 'button-font-size-input',         cssVar: '--button-font-size',         default: '0.875', unit: 'rem' },
                        */
                        { key: 'style-body-font',                   inputId: 'body-font-select',               cssVar: '--font-primary',         default: 'Inter, sans-serif' }, // Changed key from font-primary
                        { key: 'style-heading-font',                inputId: 'heading-font-select',            cssVar: '--font-secondary',      default: 'Inter, sans-serif' }  // Changed key from font-secondary
                    ];

                    // --- Helper: Show/Hide Modal ---
                    const showModal = (modal) => modal?.classList.remove('hidden');
                    const hideModal = (modal) => modal?.classList.add('hidden');

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

                    const updateBadgeStyle = (status) => { /* ... existing code ... */
                        if (!statusBadgeButton || !statusBadgeText || !statusBadgeIndicator) return;
                        statusBadgeText.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        const baseButtonClasses = ['py-2 px-4 :opacity-50 modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit rounded-lg border border-border shadow-sm inline-flex items-center'];
                        const statusButtonClasses = { 'draft': 'bg-gray-100 text-gray-800', 'ready': 'bg-sky-100 text-sky-800 hover:bg-sky-200' };
                        const statusIndicatorClasses = { 'draft': 'bg-gray-400 hover:bg-gray-200', 'ready': 'bg-sky-500 hover:bg-sky-200' }; // Corrected hover for draft
                        statusBadgeButton.className = baseButtonClasses.join(' ') + ' ' + (statusButtonClasses[status] || 'bg-gray-100 text-gray-800'); // Removed hover:bg-sky-200 from default
                        statusBadgeIndicator.className = 'inline-block w-2 h-2 rounded-full mr-1.5 ' + (statusIndicatorClasses[status] || 'bg-gray-400');
                        toggleEditorInteractivity(status === 'ready');
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
                                            statusConfirmInput.value = '';
                                            statusConfirmError?.classList.add('hidden');
                                            statusConfirmButton.disabled = true;
                                            showModal(statusConfirmModal);
                                        } else { console.error("Status confirmation modal elements not found."); }
                                    } else { updateSectionStatus(sectionId, newStatus); }
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
                                updateSectionStatus(sectionId, 'ready');
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
                            const keyColorsForPreview = [palette['style-color-primary'], palette['style-color-secondary'], palette['style-color-accent'], palette['style-color-background'], palette['style-color-foreground']].filter(Boolean).slice(0, 5);
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
                                    if (palette[styleControl.key] && styleControl.inputId && styleControl.inputId.startsWith('ssp-color-')) {
                                        const newValue = palette[styleControl.key]; const inputElement = document.getElementById(styleControl.inputId);
                                        const cssVarName = styleControl.cssVar;
                                        if (inputElement && inputElement.type === 'color' && cssVarName) {
                                            inputElement.value = newValue; applyAndSaveStyle(cssVarName, newValue, styleControl.key);
                                        }
                                    }
                                });
                                //showToast(`Palette '${selectedPaletteKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}' applied!`, 'success');
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
