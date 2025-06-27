@extends('layouts.app')

@push('styles')
    <style>
        .section-selector-item {
            transition: all 0.2s ease-in-out;
        }
        .section-selector-item.selected {
            transform: scale(1.03);
            box-shadow: 0 0 0 3px #4f46e5; /* indigo-600 */
            border-color: #4f46e5;
        }
        .order-badge {
            position: absolute;
            top: -10px;
            left: -10px;
            background-color: #4f46e5;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            border: 2px solid white;
        }

    </style>
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
    <div class="p-6 md:p-12">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-700">Landing Page Builder</h1>
            <button id="preview-lp-btn" class="py-2 px-4 bg-gray-700 text-white rounded-lg shadow-sm inline-flex items-center disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /> <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                Preview Landing Page
            </button>
        </div>

        @if($sections->count() > 0)
            <p class="text-gray-600 mb-6">Select two or more sections in the desired order to generate a preview.</p>
            <div id="section-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($sections as $section)
                    <div class="section-selector-item relative bg-white rounded-lg shadow-md cursor-pointer border-2 border-transparent" data-section-id="{{ $section->id }}">
                        <img src="{{ $section->screenshot_url }}" alt="Screenshot of {{ $section->name }}" class="w-full h-40 object-cover rounded-t-md">
                        <div class="p-4">
                            <h3 class="text-md font-semibold text-gray-800 truncate" title="{{ $section->name }}">{{ $section->name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Status: {{ ucwords(str_replace('_', ' ', $section->status)) }}</p>
                            <p class="text-xs text-gray-500 mt-1">ID: {{ ucwords(str_replace('_', ' ', $section->id)) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 px-6 bg-white rounded-lg shadow-md">
                <p class="text-xl font-semibold text-gray-600">No available sections to build a landing page.</p>
                <p class="text-gray-500 mt-2">Create and publish some sections first.</p>
            </div>
        @endif
    </div>

    {{-- Re-use existing modals --}}
    @include('modals.section-edit._modal_product_select')
    @include('modals.section-edit._modal_preview_display')
    @include('modals.section-edit._modal_style_settings')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sectionGrid = document.getElementById('section-grid');
            const previewBtn = document.getElementById('preview-lp-btn');
            const productModal = document.getElementById('product-select-modal');
            const previewModal = document.getElementById('preview-display-modal');
            const previewIframe = document.getElementById('preview-iframe');
            const previewLoading = document.getElementById('preview-loading-state');
            const productListContainer = document.getElementById('product-list-container');
            const productPaginationContainer = document.getElementById('product-pagination-container');
            const styleSettingsBtn = document.getElementById('style-settings-btn');
            const styleSettingsModal = document.getElementById('style-settings-modal');
            const styleSettingsTabBar = document.getElementById('style-settings-tab-bar');
            const styleSettingsTabButtons = styleSettingsTabBar?.querySelectorAll('.style-tab-button');
            const styleSettingsTabPanes = styleSettingsModal?.querySelectorAll('.style-tab-pane');
            const paletteGrid = document.getElementById('ssp-palette-grid');
            const reloadPreviewBtn = document.getElementById('reload-preview-btn');

            let selectedSections = [];
            let lastSelectedProductId = null; // Variable to store the last used product ID

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


            if (reloadPreviewBtn) reloadPreviewBtn.addEventListener('click', () => { /* ... existing code ... */
                const savedProductId = localStorage.getItem(PREVIEW_PRODUCT_STORAGE_KEY);
                if (savedProductId) fetchPreview(sectionId, savedProductId); else showToast('No default product set to reload preview.', 'error');
            });


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
            // NEW: Custom Palette JSON Logic
            const customPaletteTextarea = document.getElementById('custom-palette-json');
            const applyCustomPaletteBtn = document.getElementById('apply-custom-palette-btn');

            if(applyCustomPaletteBtn) {
                applyCustomPaletteBtn.addEventListener('click', () => {
                    try {
                        const jsonText = customPaletteTextarea.value;
                        const customPalette = JSON.parse(jsonText);

                        if (typeof customPalette !== 'object' || customPalette === null) {
                            throw new Error('Invalid palette format. Must be a JSON object.');
                        }

                        // Apply the custom palette
                        Object.entries(customPalette).forEach(([key, value]) => {
                            // We only apply if the key is a valid CSS variable name
                            if (key.startsWith('--')) {
                                const styleDef = stylesToLoad.find(s => s.cssVar === key);
                                if (styleDef) {
                                    const control = document.getElementById(styleDef.inputId);
                                    if (control) {
                                        // Handle different control types
                                        if(control.type === 'number' && styleDef.unit) {
                                            control.value = parseFloat(value) || 0;
                                        } else {
                                            control.value = value;
                                        }
                                    }
                                    applyAndSaveStyle(key, value, styleDef.key);
                                }
                            }
                        });

                        showToast('Custom palette applied!', 'success');
                    } catch (e) {
                        showToast('Error applying custom palette: ' + e.message, 'error');
                        console.error("Custom palette error:", e);
                    }
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

            // --- Show/Hide Modals ---
            const showModal = (modal) => modal?.classList.remove('hidden');
            const hideModal = (modal) => modal?.classList.add('hidden');
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', (event) => { if (event.target === modal) hideModal(modal); });
                modal.querySelectorAll('.modal-close-btn, .modal-cancel-btn').forEach(btn => btn.addEventListener('click', () => hideModal(modal)));
            });


            if (sectionGrid) {
                sectionGrid.addEventListener('click', (e) => {
                    const item = e.target.closest('.section-selector-item');
                    if (!item) return;

                    const sectionId = item.dataset.sectionId;
                    const index = selectedSections.indexOf(sectionId);

                    if (index > -1) {
                        // Deselect
                        selectedSections.splice(index, 1);
                        item.classList.remove('selected');
                        const badge = item.querySelector('.order-badge');
                        if (badge) badge.remove();
                    } else {
                        // Select
                        selectedSections.push(sectionId);
                        item.classList.add('selected');
                    }
                    updateSelectionState();
                });
            }

            function updateSelectionState() {
                document.querySelectorAll('.section-selector-item').forEach(item => {
                    const sectionId = item.dataset.sectionId;
                    const index = selectedSections.indexOf(sectionId);
                    let badge = item.querySelector('.order-badge');

                    if (index > -1) {
                        if (!badge) {
                            badge = document.createElement('div');
                            badge.className = 'order-badge';
                            item.appendChild(badge);
                        }
                        badge.textContent = index + 1;
                    } else {
                        if (badge) badge.remove();
                        item.classList.remove('selected');
                    }
                });

                previewBtn.disabled = selectedSections.length < 2;
            }

            if(previewBtn) {
                previewBtn.addEventListener('click', () => {
                    if (selectedSections.length < 2) return;
                    // Open product selection modal
                    productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8 product-list-state">Loading products...</p>';
                    productPaginationContainer.innerHTML = '';
                    fetchProducts(1);
                    showModal(productModal);
                });
            }

            // --- Product Selection & Preview Logic ---
            if (productListContainer) {
                productListContainer.addEventListener('click', (event) => {
                    const productItem = event.target.closest('.product-item');
                    if (productItem && productItem.dataset.productId) {
                        const productId = productItem.dataset.productId;
                        hideModal(productModal);
                        generateLandingPagePreview(productId);
                    }
                });
            }

            if(productPaginationContainer) {
                productPaginationContainer.addEventListener('click', (event) => {
                    const pageButton = event.target.closest('.product-page-btn');
                    if (pageButton && pageButton.dataset.page && !pageButton.disabled) {
                        fetchProducts(pageButton.dataset.page);
                    }
                });
            }

            // --- START: NEW AND UPDATED LOGIC ---

            // Add click listener for the reload button
            if(reloadPreviewBtn) {
                reloadPreviewBtn.addEventListener('click', () => {
                    if (lastSelectedProductId && selectedSections.length > 0) {
                        console.log('Reloading preview...');
                        generateLandingPagePreview(lastSelectedProductId);
                    } else {
                        // This case should ideally not be reached if the flow is correct
                        alert('Could not reload. Please close and try again.');
                    }
                });
            }

            async function generateLandingPagePreview(productId) {
                lastSelectedProductId = productId; // Store the product ID for reloading



                showModal(previewModal);
                previewLoading?.classList.remove('hidden');
                previewIframe.srcdoc = '';

                try {
                    const response = await fetch('{{ route("landing-pages.preview") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            section_ids: selectedSections,
                            product_id: productId
                        })
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'An unknown error occurred.');
                    }
                    previewIframe.srcdoc = data.previewContent || '<p>No preview content.</p>';

                } catch (error) {
                    console.error('Preview Error:', error);
                    previewIframe.srcdoc = `<p class="p-4 text-center text-red-500">Failed to load preview: ${error.message}</p>`;
                } finally {
                    previewLoading?.classList.add('hidden');
                }
            }
            // --- END: NEW AND UPDATED LOGIC ---


            async function fetchProducts(page = 1) {
                try {
                    const response = await fetch(`{{ route('section.products') }}?page=${page}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) throw new Error('Failed to fetch products.');
                    const data = await response.json();
                    displayProducts(data);
                } catch (error) {
                    console.error('Error fetching products:', error);
                    productListContainer.innerHTML = `<p class="text-center text-red-500 py-8">Error: ${error.message}</p>`;
                }
            }

            function displayProducts(data) {
                const products = data.products || [];
                const paginator = data.paginator || {};

                if (!products.length) {
                    productListContainer.innerHTML = '<p class="text-center text-gray-500 py-8">No products found.</p>';
                    productPaginationContainer.innerHTML = '';
                    return;
                }

                let productHtml = '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">';
                products.forEach(product => {
                    const imageUrl = product.image?.url || 'https://placehold.co/200x200/e2e8f0/475569?text=No+Image';
                    productHtml += `<div class="border rounded-lg overflow-hidden cursor-pointer hover:shadow-md product-item group" data-product-id="${product.id}">
                                <img src="${imageUrl}" alt="${product.title}" loading="lazy" class="w-full h-32 object-cover">
                                <p class="text-sm p-2 truncate">${product.title}</p>
                           </div>`;
                });
                productHtml += '</div>';
                productListContainer.innerHTML = productHtml;

                let paginationHtml = '';
                if (paginator.lastPage > 1) {
                    paginationHtml += `<button type="button" data-page="${paginator.currentPage - 1}" class="px-3 py-1 text-sm bg-gray-200 rounded ${paginator.currentPage === 1 ? 'opacity-50' : ''} product-page-btn" ${paginator.currentPage === 1 ? 'disabled' : ''}>« Prev</button>`;
                    paginationHtml += `<span class="px-3 py-1 text-sm text-gray-600">Page ${paginator.currentPage} of ${paginator.lastPage}</span>`;
                    paginationHtml += `<button type="button" data-page="${paginator.currentPage + 1}" class="px-3 py-1 text-sm bg-gray-200 rounded ${paginator.currentPage === paginator.lastPage ? 'opacity-50' : ''} product-page-btn" ${paginator.currentPage === paginator.lastPage ? 'disabled' : ''}>Next »</button>`;
                }
                productPaginationContainer.innerHTML = paginationHtml;
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
