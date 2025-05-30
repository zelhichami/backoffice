<div id="product-select-modal" class="z-[70] modal fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center hidden p-4">
    <div class="modal-content relative mx-auto p-6 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-md font-semibold">Select a Product
                <a href="https://preprod-myxpage.shop/products" target="_blank" class="inline-flex items-center gap-1 bg-white text-xs  border-gray-300 p-1  underline text-blue-700 hover:text-blue-500">
                    Manage products
                    <svg fill="none" class="iconify iconify--solar w-4 h-4 rounded-md text-blue-700 hover:text-blue-500" width="1em" height="1em" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 6H6C4.89543 6 4 6.89543 4 8V18C4 19.1046 4.89543 20 6 20H16C17.1046 20 18 19.1046 18 18V14M14 4H20M20 4V10M20 4L10 14" stroke="#4A5568" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                </a>
            </h3>
            <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <p class="text-gray-500 text-xs mb-4">Switch to Display by assigned product to view it correctly on the preview.</p>
        <div id="product-list-container" class="max-h-[60vh] overflow-y-auto mb-4">
            <p class="text-center text-gray-500 py-8 product-list-state">Loading products...</p>
        </div>
        <div class="border-t flex justify-between items-center mt-4 pt-4">
            <div id="product-pagination-container" class=" flex justify-center items-center space-x-1">
                {{-- Pagination buttons --}}
            </div>
            <button type="button" class="modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm"> Cancel </button>
        </div>
    </div>
</div>
