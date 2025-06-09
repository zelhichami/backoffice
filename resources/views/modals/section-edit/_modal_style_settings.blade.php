{{-- MODIFIED: resources/views/modals/section-edit/_modal_style_settings.blade.php --}}
<div id="style-settings-modal" class="modal fixed inset-0 bg-gray-800 bg-opacity-0 overflow-y-auto h-full w-full z-[70] flex items-center justify-center hidden p-4">
    <div class="modal-content border-[#d6d6d6]  relative mx-auto p-0 border shadow-lg rounded-md bg-white overflow-hidden w-full max-w-2xl">
        <div id="style-modal-header" class="flex justify-between items-center border-b p-4 flex-shrink-0 cursor-move">
            <h3 class="text-lg font-semibold text-gray-800">Live Style Settings</h3>
            <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div id="style-settings-tab-bar" class="flex border-b px-4 bg-gray-50 flex-shrink-0">
            <button type="button" data-target="style-colors-pane" class="style-tab-button tab-button active px-4 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 hover:text-gray-700 focus:outline-none">Colors</button>
            <button type="button" data-target="style-fontfamily-pane" class="style-tab-button tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 hover:text-gray-700 focus:outline-none">Font Family</button>
            {{-- NEW TAB for Sizing --}}
            <button type="button" data-target="style-sizing-pane" class="style-tab-button tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 hover:text-gray-700 focus:outline-none">Borders & Sizing</button>
        </div>
        <div class="p-6 overflow-y-auto flex-grow">
            {{-- Colors Pane --}}
            <div id="style-colors-pane" class="style-tab-pane active">
                <div class="mb-6 border-b pb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Palettes</label>
                    <div id="ssp-palette-grid" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                        {{-- Palette items will be dynamically inserted here by JavaScript --}}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                    {{-- Section Backgrounds + Foregrounds --}}
                    <div class="space-y-1">
                        <label for="ssp-bg-section-primary" class="block text-xs font-medium text-gray-600">Section Primary BG</label>
                        <input type="color" id="ssp-bg-section-primary" data-css-var="--bg-section-primary" data-storage-key="bg-section-primary" value="#E0F2FE" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-fg-section-primary" class="block text-xs font-medium text-gray-600">Section Primary FG</label>
                        <input type="color" id="ssp-fg-section-primary" data-css-var="--fg-section-primary" data-storage-key="fg-section-primary" value="#0369A1" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-bg-section-secondary" class="block text-xs font-medium text-gray-600">Section Secondary BG</label>
                        <input type="color" id="ssp-bg-section-secondary" data-css-var="--bg-section-secondary" data-storage-key="bg-section-secondary" value="#FEF3C7" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-fg-section-secondary" class="block text-xs font-medium text-gray-600">Section Secondary FG</label>
                        <input type="color" id="ssp-fg-section-secondary" data-css-var="--fg-section-secondary" data-storage-key="fg-section-secondary" value="#92400E" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-bg-section-accent" class="block text-xs font-medium text-gray-600">Section Accent BG</label>
                        <input type="color" id="ssp-bg-section-accent" data-css-var="--bg-section-accent" data-storage-key="bg-section-accent" value="#ECFDF5" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-fg-section-accent" class="block text-xs font-medium text-gray-600">Section Accent FG</label>
                        <input type="color" id="ssp-fg-section-accent" data-css-var="--fg-section-accent" data-storage-key="fg-section-accent" value="#065F46" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>

                    {{-- UI Colors + Foregrounds --}}
                    <div class="space-y-1">
                        <label for="ssp-color-primary" class="block text-xs font-medium text-gray-600">UI Primary</label>
                        <input type="color" id="ssp-color-primary" data-css-var="--color-primary" data-storage-key="color-primary" value="#3B82F6" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-primary-fg" class="block text-xs font-medium text-gray-600">UI Primary FG</label>
                        <input type="color" id="ssp-color-primary-fg" data-css-var="--color-primary-fg" data-storage-key="color-primary-fg" value="#ffffff" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-secondary" class="block text-xs font-medium text-gray-600">UI Secondary</label>
                        <input type="color" id="ssp-color-secondary" data-css-var="--color-secondary" data-storage-key="color-secondary" value="#F59E0B" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-secondary-fg" class="block text-xs font-medium text-gray-600">UI Secondary FG</label>
                        <input type="color" id="ssp-color-secondary-fg" data-css-var="--color-secondary-fg" data-storage-key="color-secondary-fg" value="#000000" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-accent" class="block text-xs font-medium text-gray-600">UI Accent</label>
                        <input type="color" id="ssp-color-accent" data-css-var="--color-accent" data-storage-key="color-accent" value="#10B981" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-accent-fg" class="block text-xs font-medium text-gray-600">UI Accent FG</label>
                        <input type="color" id="ssp-color-accent-fg" data-css-var="--color-accent-fg" data-storage-key="color-accent-fg" value="#ffffff" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>

                    {{-- Muted Colors --}}
                    <div class="space-y-1">
                        <label for="ssp-text-muted" class="block text-xs font-medium text-gray-600">Muted Text</label>
                        <input type="color" id="ssp-text-muted" data-css-var="--text-muted" data-storage-key="text-muted" value="#6B7280" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-bg-muted" class="block text-xs font-medium text-gray-600">Muted Background</label>
                        <input type="color" id="ssp-bg-muted" data-css-var="--bg-muted" data-storage-key="bg-muted" value="#F3F4F6" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-muted-fg" class="block text-xs font-medium text-gray-600">Muted Foreground</label>
                        <input type="color" id="ssp-muted-fg" data-css-var="--muted-fg" data-storage-key="muted-fg" value="#1F2937" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>

                    {{-- Other General Colors --}}
                    <div class="space-y-1">
                        <label for="ssp-page-bg" class="block text-xs font-medium text-gray-600">Page Background</label>
                        <input type="color" id="ssp-page-bg" data-css-var="--background" data-storage-key="page-bg" value="#ffffff" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-page-fg" class="block text-xs font-medium text-gray-600">Page Foreground (Text)</label>
                        <input type="color" id="ssp-page-fg" data-css-var="--foreground" data-storage-key="page-fg" value="#000000" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                </div>
            </div>

            {{-- Font Family Pane --}}
            <div id="style-fontfamily-pane" class="style-tab-pane hidden space-y-4">
                <div class="space-y-1">
                    <label for="heading-font-select" class="block text-xs font-medium text-gray-600">Heading Font</label>
                    <select id="heading-font-select" data-css-var="--font-header" data-storage-key="style-heading-font" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Inter, sans-serif">Inter (Default)</option>
                        <option value="'Poppins', sans-serif">Poppins</option>
                        <option value="Arial, Helvetica, sans-serif">Arial</option>
                        <option value="Verdana, Geneva, sans-serif">Verdana</option>
                        <option value="'Times New Roman', Times, serif">Times New Roman</option>
                        <option value="Georgia, serif">Georgia</option>
                        <option value="'Courier New', Courier, monospace">Courier New</option>
                        <option value="'Lucida Console', Monaco, monospace">Lucida Console</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="body-font-select" class="block text-xs font-medium text-gray-600">Body Font</label>
                    <select id="body-font-select" data-css-var="--font-body" data-storage-key="style-body-font" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Inter, sans-serif">Inter (Default)</option>
                        <option value="'Poppins', sans-serif">Poppins</option>
                        <option value="Arial, Helvetica, sans-serif">Arial</option>
                        <option value="Verdana, Geneva, sans-serif">Verdana</option>
                        <option value="'Times New Roman', Times, serif">Times New Roman</option>
                        <option value="Georgia, serif">Georgia</option>
                        <option value="'Courier New', Courier, monospace">Courier New</option>
                        <option value="'Lucida Console', Monaco, monospace">Lucida Console</option>
                    </select>
                </div>
            </div>

            {{-- NEW PANE for Borders & Sizing --}}
            <div id="style-sizing-pane" class="style-tab-pane hidden space-y-4">
                <div class="space-y-1">
                    <label for="ssp-button-radius" class="block text-xs font-medium text-gray-600">Button Border Radius (rem)</label>
                    <input type="number" id="ssp-button-radius" class="w-full p-2 border border-gray-300 rounded-md text-sm" placeholder="e.g., 0.5" min="0" max="5" step="0.05" data-css-var="--button-border-raduis" data-storage-key="button-border-radius" data-unit="rem">
                </div>
                <div class="space-y-1">
                    <label for="ssp-card-radius" class="block text-xs font-medium text-gray-600">Card Border Radius (rem)</label>
                    <input type="number" id="ssp-card-radius" class="w-full p-2 border border-gray-300 rounded-md text-sm" placeholder="e.g., 0.5" min="0" max="5" step="0.05" data-css-var="--card-border-radius" data-storage-key="card-border-radius" data-unit="rem">
                </div>
            </div>
        </div>
        <div class="flex justify-end p-4 border-t flex-shrink-0 bg-gray-50">
            <button type="button" class="modal-cancel-btn px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Close</button>
        </div>
    </div>
</div>
