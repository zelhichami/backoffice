<div id="status-confirm-modal" class="modal fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full z-[70] flex items-center justify-center hidden p-4">
    <div class="modal-content relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Confirm Status Change to 'Ready'</h3>
            <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-3">Setting the status to 'Ready' will make the editor read-only. To confirm this change, please type the section ID below:</p>
            <p class="text-center font-mono text-lg font-bold text-indigo-600 bg-indigo-50 p-2 rounded" id="status-confirm-required-id"></p>
        </div>
        <div class="mb-4">
            <label for="status-confirm-input" class="block text-sm font-medium text-gray-700 mb-1">Enter Section ID</label>
            <input type="text" id="status-confirm-input" name="status_confirm_input" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" autocomplete="off">
            <p id="status-confirm-error" class="text-red-600 text-xs mt-1 hidden">IDs do not match.</p>
        </div>
        <div class="flex justify-end items-center mt-4 pt-4 border-t space-x-3">
            <button type="button" class="modal-cancel-btn px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
            <button id="status-confirm-button" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Confirm & Set Ready
            </button>
        </div>
    </div>
</div>
