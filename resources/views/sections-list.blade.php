@extends('layouts.app')

@section('content')
    {{-- Removed Alpine x-data from main div --}}
    <div class="p-12">

        {{-- Page Header --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-700">My Sections</h1>
            {{-- Added ID for create button --}}
            @if(auth()->user()->hasRole(\App\Models\User::ROLE_INTEGRATOR))
                <button id="create-section-btn" class=" py-2 px-4 :opacity-50 modal-cancel-btn !text-sm !py-1.5 !px-3 !w-fit bg-gray-700 text-white rounded-lg border border-border hover:bg-gray-600 shadow-sm inline-flex items-center">
                    <svg fill="#000000" viewBox="0 0 24 24" id="file-new" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path id="secondary" d="M18,15v6m3-3H15" style="fill: none; stroke: #2ca9bc; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><path id="primary" d="M10,20H4a1,1,0,0,1-1-1V4A1,1,0,0,1,4,3h9.59a1,1,0,0,1,.7.29l3.42,3.42a1,1,0,0,1,.29.7V11" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg>
                    Create
                </button>
            @endif
        </div>


        {{-- Section Grid - Added ID for event delegation --}}
        @if($sections->count() > 0)
            <div id="section-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($sections as $section)
                    {{-- Removed Alpine x-data --}}
                    <div class="relative bg-white rounded-lg shadow-md transition-shadow duration-300 hover:shadow-xl">
                        {{-- Link for main content - Using section.edit route --}}
                        <a href="{{ route('section.edit', $section) }}" class="block">
                            <img src="{{ $section->screenshot_url }}" alt="Screenshot of {{ $section->name }}" class="w-full h-40 object-cover">
                            <div class="p-4 pb-2">
                                <h3 class="text-lg font-semibold text-gray-800 truncate" title="{{ $section->name }}">{{ $section->name }}</h3>
                            </div>
                        </a>
                        {{-- Info and Actions Row --}}
                        <div class="px-4 pb-4 pt-1 flex justify-between items-center">
                            <p class="text-sm text-gray-500">Updated: {{ $section->updated_at->diffForHumans() }}</p>

                            {{-- Dropdown Container --}}
                            <div class="relative">
                                {{-- Dropdown Toggle Button - Added data attribute --}}
                                <button type="button" class="dropdown-toggle text-gray-500 hover:text-gray-700 focus:outline-none p-1 rounded-full hover:bg-gray-100" data-dropdown-target="dropdown-{{ $section->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 pointer-events-none"> {{-- Added pointer-events-none to SVG --}}
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                    </svg>
                                </button>

                                {{-- Dropdown Menu - Added ID, initially hidden --}}
                                <div id="dropdown-{{ $section->id }}" class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
                                    {{-- Edit Details Button - Added class and data attributes --}}
                                    <button
                                        type="button"
                                        class="edit-details-btn block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        data-section-id="{{ $section->id }}"
                                        data-section-name="{!! htmlspecialchars(json_encode($section->name), ENT_QUOTES, 'UTF-8') !!}"
                                        data-screenshot-url="{{ $section->screenshot_url }}"
                                    >
                                        Edit Details
                                    </button>

                                    <form method="POST" action="{{ route('section.destroy', $section->id) }}" onsubmit="return confirm('Are you sure you want to delete this section and its assets? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 focus:outline-none focus:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div class="mt-8">
                {{ $sections->links() }}
            </div>
        @else
            {{-- No sections found message --}}
            <div class="text-center py-12 px-6 bg-white rounded-lg shadow-md"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-4"> <path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125Z" /> </svg> <p class="text-xl font-semibold text-gray-600">No sections found.</p> <p class="text-gray-500 mt-2">Click the button above to create your first section!</p> </div>
        @endif

        {{-- Create Section Modal - Added ID, initially hidden --}}
        <div id="create-modal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center hidden">
            <div class="modal-content relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white">
                {{-- Header --}}
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-sm font-semibold">Create New Section</h3>
                </div>
                {{-- Form using section.initialize route --}}
                <form action="{{ route('section.initialize') }}" method="POST" enctype="multipart/form-data">
                    @csrf <input type="hidden" name="form_type" value="create">
                    <div class="mb-4"> <label for="create_section_name" class="block text-sm font-medium text-gray-700 mb-1">Section Name <span class="text-red-500">*</span></label>
                        <input type="text" name="section_name" placeholder="Enter section name" id="create_section_name" value="{{ old('form_type') === 'create' ? old('section_name') : '' }}" required maxlength="255" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-black">

                    </div>
                    <div class="mb-6"> <label for="create_section_screenshot" class="block text-sm font-medium text-gray-700 mb-1">Screenshot (Optional)</label> <input type="file" name="section_screenshot" id="create_section_screenshot" accept="image/*" class="mt-1 block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"> </div>
                    <div class="flex justify-end space-x-3">
                        {{-- Added close button class --}}
                        <button type="button" class="modal-cancel-btn !text-xs !py-1.5 !px-3 !w-fit bg-gray-100 text-grey-700 rounded-lg border border-border hover:bg-gray-200 shadow-sm">Cancel</button>
                        <button type="submit" data-section-id="f8cef90e" class=" py-2 px-4 :opacity-50 modal-cancel-btn !text-sm !py-1.5 !px-3 !w-fit bg-gray-700 text-white rounded-lg border border-border hover:bg-gray-600 shadow-sm inline-flex items-center">
                            <svg fill="#000000" viewBox="0 0 24 24" id="plus" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path id="primary" d="M5,12H19M12,5V19" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg>
                            Create & Edit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Section Details Modal - Added ID, form ID, field IDs, initially hidden --}}
        <div id="edit-modal" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center hidden">
            <div class="modal-content relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white">
                {{-- Header --}}
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-xl font-semibold text-gray-700">Edit Section Details</h3>
                    {{-- Added close button class --}}
                    <button type="button" class="modal-close-btn text-gray-400 hover:text-gray-600">X</button>
                </div>
                {{-- Form - Added ID --}}
                <form id="edit-modal-form" action="" method="POST" enctype="multipart/form-data"> {{-- Action will be set by JS --}}
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="edit_details">
                    {{-- Removed hidden ID input, ID will be in action URL --}}
                    <div class="mb-4">
                        <label for="edit_section_name_input" class="block text-sm font-medium text-gray-700 mb-1">Section Name <span class="text-red-500">*</span></label>
                        {{-- Added ID --}}
                        <input type="text" name="section_name" placeholder="Enter section name" id="edit_section_name_input" value="" required maxlength="255" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">

                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Screenshot</label>
                        {{-- Added ID and placeholder class --}}
                        <img id="edit_current_screenshot_img" src="" alt="Current screenshot" class="max-w-full h-24 object-contain border rounded mb-2 hidden">
                        {{-- Added ID --}}
                        <p id="edit_no_screenshot_text" class="text-xs text-gray-500 italic">No current screenshot.</p>
                    </div>
                    <div class="mb-6">
                        <label for="edit_section_screenshot_input" class="block text-sm font-medium text-gray-700 mb-1">Upload New Screenshot (Optional)</label>
                        {{-- Added ID --}}
                        <input type="file" name="section_screenshot" id="edit_section_screenshot_input" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-1">Uploading a new file will replace the current screenshot.</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        {{-- Added close button class --}}
                        <button type="button" class="modal-cancel-btn px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Update Details</button>
                    </div>
                </form>
            </div>
        </div>

    </div> {{-- End p-6 --}}
@endsection

@push('styles')
    <style>
        /* Keep styles for initially hidden elements if needed, though Tailwind 'hidden' class is preferred */
        /* [x-cloak] { display: none !important; } */ /* Removed x-cloak style */
        .modal { transition: opacity 0.3s ease; } /* Basic transition for modals */
    </style>
@endpush

@push('scripts')
    {{-- Removed Alpine Store script --}}
    {{-- Add Vanilla JS Here --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sectionGrid = document.getElementById('section-grid');
            const createModal = document.getElementById('create-modal');
            const editModal = document.getElementById('edit-modal');
            const createBtn = document.getElementById('create-section-btn');

            // --- Modal Handling ---
            const showModal = (modal) => {
                if (modal) {
                    modal.classList.remove('hidden');
                    // Optional: Add class to body to prevent scrolling
                    // document.body.classList.add('overflow-hidden');
                }
            };

            const hideModal = (modal) => {
                if (modal) {
                    modal.classList.add('hidden');
                    // Optional: Remove class from body
                    // document.body.classList.remove('overflow-hidden');
                }
            };

            // Show Create Modal
            if (createBtn && createModal) {
                createBtn.addEventListener('click', () => showModal(createModal));
            }

            // Hide Modals via buttons or backdrop
            document.querySelectorAll('.modal').forEach(modal => {
                // Backdrop click
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) { // Clicked on backdrop
                        hideModal(modal);
                    }
                });
                // Close/Cancel buttons
                modal.querySelectorAll('.modal-close-btn, .modal-cancel-btn').forEach(btn => {
                    btn.addEventListener('click', () => hideModal(modal));
                });
            });


            // --- Dropdown Handling ---
            let currentlyOpenDropdown = null;

            const closeAllDropdowns = (exceptElement = null) => {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    // Check if the click was inside the element that should be ignored (the button that was just clicked)
                    if (!exceptElement || !menu.previousElementSibling?.contains(exceptElement)) {
                        if (!menu.classList.contains('hidden')) {
                            menu.classList.add('hidden');
                            if (menu === currentlyOpenDropdown) {
                                currentlyOpenDropdown = null;
                            }
                        }
                    }
                });
            };

            // Global click listener to close dropdowns
            document.addEventListener('click', (event) => {
                // If the click is not on a toggle button and not inside an open dropdown menu
                const toggle = event.target.closest('.dropdown-toggle');
                const menu = event.target.closest('.dropdown-menu');

                if (!toggle && !menu) {
                    closeAllDropdowns();
                } else if (toggle && currentlyOpenDropdown && !toggle.nextElementSibling?.contains(currentlyOpenDropdown)) {
                    // Clicked a different toggle while one was open
                    closeAllDropdowns(toggle); // Close others before opening new one
                }
            });

            // --- Event Delegation for Dropdowns & Edit Buttons ---
            if (sectionGrid) {
                sectionGrid.addEventListener('click', function(event) {
                    const dropdownToggle = event.target.closest('.dropdown-toggle');
                    const editButton = event.target.closest('.edit-details-btn');

                    // Handle Dropdown Toggle
                    if (dropdownToggle) {
                        event.preventDefault(); // Prevent potential default actions
                        const targetId = dropdownToggle.dataset.dropdownTarget;
                        const dropdownMenu = document.getElementById(targetId);

                        if (dropdownMenu) {
                            const isHidden = dropdownMenu.classList.contains('hidden');
                            // Close all others before opening/toggling this one
                            closeAllDropdowns(dropdownToggle);

                            if (isHidden) {
                                dropdownMenu.classList.remove('hidden');
                                currentlyOpenDropdown = dropdownMenu;
                            } else {
                                dropdownMenu.classList.add('hidden');
                                currentlyOpenDropdown = null;
                            }
                        }
                    }

                    // Handle Edit Details Button Click
                    if (editButton) {
                        event.preventDefault();
                        const id = editButton.dataset.sectionId;
                        // Decode the name safely
                        let name = '';
                        try {
                            name = JSON.parse(editButton.dataset.sectionName);
                        } catch (e) {
                            console.error("Failed to parse section name:", e);
                            name = editButton.dataset.sectionName; // Fallback
                        }
                        const screenshotUrl = editButton.dataset.screenshotUrl;

                        console.log('Edit button clicked:', { id, name, screenshotUrl }); // Debugging

                        // Populate and show the edit modal
                        populateAndShowEditModal(id, name, screenshotUrl);

                        // Close the dropdown containing the button
                        const parentDropdown = editButton.closest('.dropdown-menu');
                        if(parentDropdown) {
                            parentDropdown.classList.add('hidden');
                            currentlyOpenDropdown = null;
                        }
                    }
                });
            }

            // --- Populate Edit Modal ---
            const editModalForm = document.getElementById('edit-modal-form');
            const editNameInput = document.getElementById('edit_section_name_input');
            const editScreenshotImg = document.getElementById('edit_current_screenshot_img');
            const editNoScreenshotText = document.getElementById('edit_no_screenshot_text');
            const editScreenshotInput = document.getElementById('edit_section_screenshot_input'); // File input

            const populateAndShowEditModal = (id, name, screenshotUrl) => {
                if (!editModal || !editModalForm || !editNameInput || !editScreenshotImg || !editNoScreenshotText) {
                    console.error('Edit modal elements not found!');
                    return;
                }

                // Set form action URL
                editModalForm.action = `/section/details/${id}`; // Adjust route as needed

                // Set name
                editNameInput.value = name;

                // Set screenshot preview
                if (screenshotUrl && !screenshotUrl.includes('placehold.co')) {
                    editScreenshotImg.src = screenshotUrl;
                    editScreenshotImg.classList.remove('hidden');
                    editNoScreenshotText.classList.add('hidden');
                } else {
                    editScreenshotImg.src = ''; // Clear src
                    editScreenshotImg.classList.add('hidden');
                    editNoScreenshotText.classList.remove('hidden');
                }

                // Clear the file input field
                editScreenshotInput.value = '';

                // Show the modal
                showModal(editModal);
            };

        });

    </script>
@endpush

