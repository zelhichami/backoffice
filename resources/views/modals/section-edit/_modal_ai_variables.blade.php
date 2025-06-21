{{-- START: New AI Variable Modal --}}
<div id="variable-edit-modal" class="modal fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full z-[70] flex items-center justify-center hidden p-4">
    <div class="modal-content relative mx-auto p-0 border shadow-lg rounded-md bg-white w-full max-w-2xl">
        <form id="variable-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="variable-form-method" value="POST">
            <input type="hidden" name="variable_id" id="variable-form-id" value="">

            <div class="flex justify-between items-center border-b p-4">
                <h3 id="variable-modal-title" class="text-lg font-semibold text-gray-800">Create New AI Variable</h3>
                <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <div class="p-6 space-y-4 overflow-y-auto">
                <div>
                    <label for="variable-name" class="form-label">Variable Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="variable-name" class="form-input font-mono" placeholder="E.g., SECTION_1_HEADER" required>
                    <p class="text-xs text-gray-500 mt-1">Must be uppercase, numbers, and underscores only.</p>
                    <div id="variable-name-error" class="form-error"></div>
                </div>
                <div>
                    <label for="variable-type" class="form-label">Type <span class="text-red-500">*</span></label>
                    <select name="type" id="variable-type" class="form-input">
                        <option value="text">Text</option>
                        <option value="image">Image</option>
                    </select>
                </div>
                <div>
                    <label for="variable-prompt" class="form-label">Prompt <span class="text-red-500">*</span></label>
                    <textarea name="prompt" id="variable-prompt" rows="4" class="form-input" placeholder="E.g., Generate a catchy headline for a new tech product..." required></textarea>
                    <div id="variable-prompt-error" class="form-error"></div>
                </div>
                <hr>
                <p class="text-sm text-gray-600">Provide a default value if this variable doesn't need AI generation, or as a fallback.</p>

                {{-- Conditional Default Value Fields --}}
                <div id="default-text-container">
                    <label for="variable-default-text" class="form-label">Default Text Value</label>
                    <input type="text" name="default_text_value" id="variable-default-text" class="form-input" placeholder="E.g., Free Shipping">
                </div>

                <div id="default-image-container" class="hidden">
                    <label for="variable-default-image" class="form-label">Default Image</label>
                    <input type="file" name="default_image" id="variable-default-image" class="form-input">
                    <div id="current-default-image-preview" class="mt-2"></div>
                </div>

            </div>
            <div class="flex justify-end p-4 border-t bg-gray-50">
                <button type="button" class="modal-cancel-btn px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2">Cancel</button>
                <button type="submit" id="variable-form-submit-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Variable</button>
            </div>
        </form>
    </div>
</div>
{{-- END: New AI Variable Modal --}}
