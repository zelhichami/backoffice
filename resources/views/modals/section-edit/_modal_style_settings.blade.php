<div id="style-settings-modal" class="modal fixed inset-0 bg-gray-800 bg-opacity-0 overflow-y-auto h-full w-full z-[70] flex items-center justify-center hidden p-4">
    <div class="modal-content border-[#d6d6d6]  relative mx-auto p-0 border shadow-lg rounded-md bg-white overflow-hidden w-full max-w-2xl">
        <div id="style-modal-header" class="flex justify-between items-center border-b p-4 flex-shrink-0 cursor-move">
            <h3 class="text-lg font-semibold text-gray-800">Live Style Settings</h3>
            <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div id="style-settings-tab-bar" class="flex border-b px-4 bg-gray-50 flex-shrink-0">
            <button type="button" data-target="style-colors-pane" class="style-tab-button tab-button active px-4 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 hover:text-gray-700 focus:outline-none">Colors</button>
{{--
            <button type="button" data-target="style-fontsize-pane" class="style-tab-button tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 hover:text-gray-700 focus:outline-none">Font Size</button>
--}}
            <button type="button" data-target="style-fontfamily-pane" class="style-tab-button tab-button  px-4 py-2 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 hover:text-gray-700 focus:outline-none">Font Family</button>
        </div>
        <div class="p-6 overflow-y-auto flex-grow">
            {{-- Colors Pane (including the palette grid and individual color pickers) --}}
            <div id="style-colors-pane" class="style-tab-pane active">
                <div class="mb-6 border-b pb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Palettes</label>
                    <div id="ssp-palette-grid" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                        {{-- Palette items will be dynamically inserted here by JavaScript --}}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                    <div class="space-y-1">
                        <label for="ssp-color-primary" class="block text-xs font-medium text-gray-600">Primary</label>
                        <input type="color" id="ssp-color-primary" data-css-var="--color-primary" data-storage-key="style-color-primary" value="#0b4e24" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-primary-foreground" class="block text-xs font-medium text-gray-600">Primary Foreground</label>
                        <input type="color" id="ssp-color-primary-foreground" data-css-var="--color-primary-foreground" data-storage-key="style-color-primary-foreground" value="#f1f1f1" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-secondary" class="block text-xs font-medium text-gray-600">Secondary</label>
                        <input type="color" id="ssp-color-secondary" data-css-var="--color-secondary" data-storage-key="style-color-secondary" value="#17833e" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-secondary-foreground" class="block text-xs font-medium text-gray-600">Secondary Foreground</label>
                        <input type="color" id="ssp-color-secondary-foreground" data-css-var="--color-secondary-foreground" data-storage-key="style-color-secondary-foreground" value="#FFFFFF" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-accent" class="block text-xs font-medium text-gray-600">Accent</label>
                        <input type="color" id="ssp-color-accent" data-css-var="--color-accent" data-storage-key="style-color-accent" value="#1bb353" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-accent-foreground" class="block text-xs font-medium text-gray-600">Accent Foreground</label>
                        <input type="color" id="ssp-color-accent-foreground" data-css-var="--color-accent-foreground" data-storage-key="style-color-accent-foreground" value="#262729" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-muted" class="block text-xs font-medium text-gray-600">Muted Background</label>
                        <input type="color" id="ssp-color-muted" data-css-var="--color-muted" data-storage-key="style-color-muted" value="#FAF8F2" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-muted-foreground" class="block text-xs font-medium text-gray-600">Muted Foreground</label>
                        <input type="color" id="ssp-color-muted-foreground" data-css-var="--color-muted-foreground" data-storage-key="style-color-muted-foreground" value="#7f8083" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-active-link" class="block text-xs font-medium text-gray-600">Active Link</label>
                        <input type="color" id="ssp-color-active-link" data-css-var="--color-active-link" data-storage-key="style-color-active-link" value="#5c5c5c" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-active-link-foreground" class="block text-xs font-medium text-gray-600">Active Link Foreground</label>
                        <input type="color" id="ssp-color-active-link-foreground" data-css-var="--color-active-link-foreground" data-storage-key="style-color-active-link-foreground" value="#FFFFFF" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-background" class="block text-xs font-medium text-gray-600">Page Background</label>
                        <input type="color" id="ssp-color-background" data-css-var="--color-background" data-storage-key="style-color-background" value="#ffffff" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-foreground" class="block text-xs font-medium text-gray-600">Page Foreground (Text)</label>
                        <input type="color" id="ssp-color-foreground" data-css-var="--color-foreground" data-storage-key="style-color-foreground" value="#000000" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-border" class="block text-xs font-medium text-gray-600">Border</label>
                        <input type="color" id="ssp-color-border" data-css-var="--color-border" data-storage-key="style-color-border" value="#E5E7EB" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div class="space-y-1">
                        <label for="ssp-color-input" class="block text-xs font-medium text-gray-600">Input Background</label>
                        <input type="color" id="ssp-color-input" data-css-var="--color-input" data-storage-key="style-color-input" value="#F9FAFB" class="h-8 w-14 border border-gray-300 rounded cursor-pointer">
                    </div>
                </div>
            </div>
            {{-- Font Size Pane --}}
{{--            <div id="style-fontsize-pane" class="style-tab-pane hidden space-y-4">
                <div class="space-y-1">
                    <label for="heading-font-size-input" class="block text-xs font-medium text-gray-600">Headings (rem)</label>
                    <input type="number" id="heading-font-size-input" class="w-full p-2 border border-gray-300 rounded-md text-sm" placeholder="e.g., 1.5" min="0.5" max="5" step="0.1" data-css-var="--heading-font-size" data-storage-key="style-heading-font-size" data-unit="rem">
                </div>
                <div class="space-y-1">
                    <label for="body-font-size-input" class="block text-xs font-medium text-gray-600">Body Text (rem)</label>
                    <input type="number" id="body-font-size-input" class="w-full p-2 border border-gray-300 rounded-md text-sm" placeholder="e.g., 1" min="0.5" max="2" step="0.05" data-css-var="--body-font-size" data-storage-key="style-body-font-size" data-unit="rem">
                </div>
                <div class="space-y-1">
                    <label for="button-font-size-input" class="block text-xs font-medium text-gray-600">Buttons (rem)</label>
                    <input type="number" id="button-font-size-input" class="w-full p-2 border border-gray-300 rounded-md text-sm" placeholder="e.g., 0.875" min="0.5" max="2" step="0.05" data-css-var="--button-font-size" data-storage-key="style-button-font-size" data-unit="rem">
                </div>
            </div>--}}
            {{-- Font Family Pane --}}
            <div id="style-fontfamily-pane" class="style-tab-pane hidden space-y-4">
                <div class="space-y-1">
                    <label for="body-font-select" class="block text-xs font-medium text-gray-600">Body Font</label>
                    <select id="body-font-select" data-css-var="--font-primary" data-storage-key="style-body-font" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Inter, sans-serif">Inter (Default)</option>
                        <option value="Arial, Helvetica, sans-serif">Arial</option>
                        <option value="Verdana, Geneva, sans-serif">Verdana</option>
                        <option value="'Times New Roman', Times, serif">Times New Roman</option>
                        <option value="Georgia, serif">Georgia</option>
                        <option value="'Courier New', Courier, monospace">Courier New</option>
                        <option value="'Lucida Console', Monaco, monospace">Lucida Console</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="heading-font-select" class="block text-xs font-medium text-gray-600">Heading Font</label>
                    <select id="heading-font-select" data-css-var="--heading-font-family" data-storage-key="style-heading-font" class="w-full p-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Inter, sans-serif">Inter (Default)</option>
                        <option value="Arial, Helvetica, sans-serif">Arial</option>
                        <option value="Verdana, Geneva, sans-serif">Verdana</option>
                        <option value="'Times New Roman', Times, serif">Times New Roman</option>
                        <option value="Georgia, serif">Georgia</option>
                        <option value="'Courier New', Courier, monospace">Courier New</option>
                        <option value="'Lucida Console', Monaco, monospace">Lucida Console</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="flex justify-end p-4 border-t flex-shrink-0 bg-gray-50">
            <button type="button" class="modal-cancel-btn px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm">Close</button>
        </div>
    </div>
</div>
