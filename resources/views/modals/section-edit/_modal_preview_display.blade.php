<div id="preview-display-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-90 overflow-hidden h-full w-full z-[60] flex items-center justify-center hidden">
    <div class="modal-content relative w-full h-full max-w-full max-h-full bg-white rounded-lg shadow-xl flex flex-col">
        <div class="flex border-b border-b-grey-800 justify-between items-center p-3 sm:p-4 border-b flex-shrink-0 bg-gray-100 relative">
            <h3 class="text-sm font-semibold text-gray-900">Section Preview</h3>
            <div class="absolute left-1/2 -translate-x-1/2 flex items-center space-x-2">
                <button data-width="100%" class="device-btn flex items-center justify-center gap-1 text-gray-800  px-2 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"  viewBox="0 0 24 24" id="monitor"><path fill="currentColor" d="M19,3H5A3,3,0,0,0,2,6v8a3,3,0,0,0,3,3h6v2H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2H13V17h6a3,3,0,0,0,3-3V6A3,3,0,0,0,19,3Zm1,11a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V6A1,1,0,0,1,5,5H19a1,1,0,0,1,1,1Z"></path></svg>
                </button>
                <button data-width="768" class="device-btn flex items-center justify-center gap-1 text-gray-800 text-md px-2 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"   viewBox="0 0 64 64" id="tablet"><path fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="4" d="M58.23,53.89,5.85,54a3.77,3.77,0,0,1-3.78-3.76L2,13.89a3.77,3.77,0,0,1,3.76-3.78L58.14,10a3.77,3.77,0,0,1,3.78,3.76L62,50.11A3.78,3.78,0,0,1,58.23,53.89Z"></path><line x1="28.01" x2="35.98" y1="45.96" y2="45.96" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="4"></line></svg>
                </button>
                <button data-width="375" class="device-btn flex items-center justify-center gap-1 text-gray-800 text-xl px-2 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"  viewBox="0 0 24 24" id="mobile"><path fill="currentColor" d="M12.71,16.29l-.15-.12a.76.76,0,0,0-.18-.09L12.2,16a1,1,0,0,0-.91.27,1.15,1.15,0,0,0-.21.33,1,1,0,0,0,1.3,1.31,1.46,1.46,0,0,0,.33-.22,1,1,0,0,0,.21-1.09A1,1,0,0,0,12.71,16.29ZM16,2H8A3,3,0,0,0,5,5V19a3,3,0,0,0,3,3h8a3,3,0,0,0,3-3V5A3,3,0,0,0,16,2Zm1,17a1,1,0,0,1-1,1H8a1,1,0,0,1-1-1V5A1,1,0,0,1,8,4h8a1,1,0,0,1,1,1Z"></path></svg>
                </button>
            </div>
            <div class="flex items-center space-x-4">
                <button id="style-settings-btn" type="button" class="device-btn flex items-center justify-center gap-1 text-gray-800  px-2 py-2 bg-gray-300 rounded hover:bg-gray-400 transition" title="Style Settings">
                    <svg  xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 18 18" stroke="currentColor" stroke-width="1.3" >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 11.25C10.2426 11.25 11.25 10.2426 11.25 9C11.25 7.75736 10.2426 6.75 9 6.75C7.75736 6.75 6.75 7.75736 6.75 9C6.75 10.2426 7.75736 11.25 9 11.25Z" ></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 9.66007V8.34007C1.5 7.56007 2.1375 6.91507 2.925 6.91507C4.2825 6.91507 4.8375 5.95507 4.155 4.77757C3.765 4.10257 3.9975 3.22507 4.68 2.83507L5.9775 2.09257C6.57 1.74007 7.335 1.95007 7.6875 2.54257L7.77 2.68507C8.445 3.86257 9.555 3.86257 10.2375 2.68507L10.32 2.54257C10.6725 1.95007 11.4375 1.74007 12.03 2.09257L13.3275 2.83507C14.01 3.22507 14.2425 4.10257 13.8525 4.77757C13.17 5.95507 13.725 6.91507 15.0825 6.91507C15.8625 6.91507 16.5075 7.55257 16.5075 8.34007V9.66007C16.5075 10.4401 15.87 11.0851 15.0825 11.0851C13.725 11.0851 13.17 12.0451 13.8525 13.2226C14.2425 13.9051 14.01 14.7751 13.3275 15.1651L12.03 15.9076C11.4375 16.2601 10.6725 16.0501 10.32 15.4576L10.2375 15.3151C9.5625 14.1376 8.4525 14.1376 7.77 15.3151L7.6875 15.4576C7.335 16.0501 6.57 16.2601 5.9775 15.9076L4.68 15.1651C3.9975 14.7751 3.765 13.8976 4.155 13.2226C4.8375 12.0451 4.2825 11.0851 2.925 11.0851C2.1375 11.0851 1.5 10.4401 1.5 9.66007Z" ></path>
                    </svg>
                </button>
                <button id="reload-preview-btn" type="button" class="device-btn flex items-center justify-center gap-1 text-gray-800  px-2 py-2 bg-gray-300 rounded hover:bg-gray-400 transition" title="Reload Preview">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <div class="flex items-center space-x-2">
                    <button id="clear-default-product-btn" type="button" class="text-xs text-gray-800 hover:text-blue-800 underline">
                        <img id="preview-header-product-image" src="" alt="Selected Product" class="h-9 w-9 rounded object-cover hidden border border-gray-300">
                        {{-- Change Product Text if needed, or keep image as the button content --}}
                    </button>
                </div>
                <button type="button" class="modal-close-btn text-gray-800 hover:text-white text-3xl leading-none">&times;</button>
            </div>
        </div>
        <div class="flex-grow overflow-auto relative">
            <div id="preview-loading-state" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden z-10">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600 text-lg ml-3">Generating preview...</p>
            </div>
            <div id="preview-error-state" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden z-10 p-8">
                <p class="text-red-600 text-lg text-center"></p>
            </div>
            <div id="preview-wrapper" class="mx-auto h-full transition-all duration-300">
                <iframe id="preview-iframe" src="about:blank" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>
</div>
