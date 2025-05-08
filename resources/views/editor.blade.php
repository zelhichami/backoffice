@extends('layouts.app') {{-- Extend the layout --}}

@section('content') {{-- Define the content section --}}
<div class="p-6">
    {{-- Display Success/Error Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" role="alert">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="bg-white p-4 shadow-md rounded-md mb-4">
        <h2 class="text-xl font-semibold">Code Editor</h2>
    </div>

    {{-- Form element is not strictly needed for AJAX, but helps structure --}}
    {{-- <form id="code-editor-form" method="POST" action="{{ route('editor.store') }}"> --}}
    {{-- @csrf included via meta tag in layout head --}}

    <div class="flex flex-col md:flex-row">
        <div class="flex-1 mb-4 md:mb-0 md:mr-4" x-data="{ activeTab: 'html' }">

            <div class="tab-bar">
                <button type="button" @click="activeTab = 'html'" :class="{ 'active': activeTab === 'html' }" class="tab-button">HTML</button>
                <button type="button" @click="activeTab = 'css'" :class="{ 'active': activeTab === 'css' }" class="tab-button">CSS</button>
                <button type="button" @click="activeTab = 'js'" :class="{ 'active': activeTab === 'js' }" class="tab-button">JavaScript</button>
            </div>

            <div class="editor-wrapper bg-white">
                <div x-show="activeTab === 'html'" id="html-editor-container" class="editor-instance-container"></div>
                <div x-show="activeTab === 'css'" id="css-editor-container" class="editor-instance-container"></div>
                <div x-show="activeTab === 'js'" id="js-editor-container" class="editor-instance-container"></div>
            </div>

            <div class="mt-6 text-right">
                <button
                    id="submit-button"
                    type="button" {{-- Change type to button since fetch handles submission --}}
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow-md transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50"
                >
                    Submit Code
                </button>
            </div>
        </div>

        <div class="w-full md:w-64 bg-gray-200 p-4 rounded-md flex-shrink-0">
            <h3 class="text-lg font-semibold mb-3">Variables</h3>
            <div class="space-y-2">
                {{-- Use the $variables passed from the controller --}}
                @if (isset($variables) && !empty($variables))
                    @foreach ($variables as $variable)
                        <div
                            class="variable-tag"
                            title="{{ wordwrap($variable['description'], 30, "\n", true) }}"
                            {{-- data-value is not used in current JS, but keep if needed --}}
                            data-value="{{ $variable['example'] }}"
                            data-name="{{$variable['name']}}"
                        >
                            <span>{{ $variable['name'] }}</span>
                            <button type="button" class="copy-button" onclick="copyToClipboard('{{ $variable['name'] }}', this)" >Copy</button>
                        </div>
                    @endforeach
                @else
                    <div class="text-gray-500 text-sm">No variables available.</div>
                @endif
            </div>
        </div>
    </div>
    {{-- </form> --}}

    <div class="mt-4 bg-white p-4 shadow-md rounded-md">
        Other content (e.g., list of saved sections)...
    </div>
</div>
@endsection


@push('scripts') {{-- Push page-specific scripts --}}
<script>
    // --- Monaco Editor Initialization ---
    let htmlEditor, cssEditor, jsEditor; // Globally accessible editor instances

    // Configure the loader paths (this seems okay as is)
    require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@latest/min/vs' }});

    require(['vs/editor/editor.main'], function() {
        const commonEditorOptions = {
            theme: 'vs-dark',
            automaticLayout: true,
            minimap: { enabled: true },
            wordWrap: 'on',
            fontSize: 14,
            scrollBeyondLastLine: false,
        };

        // Initialize HTML Editor
        htmlEditor = monaco.editor.create(document.getElementById('html-editor-container'), {
            ...commonEditorOptions,
            // Use old() for repopulation on validation error, ensure proper JS escaping
            value: `{!! str_replace(['`', '${'], ['\\`', '\\${'], old('html_code', "<div>\n\t<h1>My Section Heading</h1>\n\t<p>This is the HTML content.</p>\n\t\n</div>")) !!}`,
            language: 'html'
        });

        // Initialize CSS Editor
        cssEditor = monaco.editor.create(document.getElementById('css-editor-container'), {
            ...commonEditorOptions,
            value: `{!! str_replace(['`', '${'], ['\\`', '\\${'], old('css_code', "/* Add your CSS rules here */\n.my-section {\n\tpadding: 20px;\n\tbackground-color: #f0f0f0;\n}\n")) !!}`,
            language: 'css'
        });

        // Initialize JavaScript Editor
        jsEditor = monaco.editor.create(document.getElementById('js-editor-container'), {
            ...commonEditorOptions,
            value: `{!! str_replace(['`', '${'], ['\\`', '\\${'], old('js_code', "// Add your JavaScript logic here\ndocument.addEventListener(\"DOMContentLoaded\", function() {\n\tconsole.log(\"Section Javascript Loaded!\");\n\t\n});\n")) !!}`,
            language: 'javascript'
        });

        // Optional change listeners...
    });

    // --- Copy to Clipboard Function (Keep as is) ---
    function copyToClipboard(text, buttonElement) {
        // ... (keep the function from previous steps) ...
        if (navigator && navigator.clipboard) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    buttonElement.textContent = 'Copied!';
                    setTimeout(() => { buttonElement.textContent ='Copy'; }, 3000);
                })
                .catch(err => { console.error('Failed to copy: ', err); alert('Failed to copy name.'); });
        } else { /* Fallback logic */
            const tempInput = document.createElement('textarea'); tempInput.value = text;
            tempInput.style.position = 'absolute'; tempInput.style.left = '-9999px'; document.body.appendChild(tempInput);
            tempInput.select(); tempInput.focus();
            try { document.execCommand('copy'); buttonElement.textContent = 'Copied!'; setTimeout(() => { buttonElement.textContent = 'Copy'; }, 3000); }
            catch (e) { console.warn('Fallback copy failed:', e); alert('Please copy manually.'); }
            finally { document.body.removeChild(tempInput); }
        }
    }

    // --- MODIFIED: Function to Submit Editor Content for CREATE ---
    function submitEditorContent() {
        const submitButton = document.getElementById('submit-button');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // Get CSRF token

        if (!htmlEditor || !cssEditor || !jsEditor) { /* Error handling */ return; }
        if (!submitButton) { /* Error handling */ return; }
        if (!csrfToken) { console.error('CSRF token not found!'); alert('Error: Missing security token.'); return; }

        const htmlCode = htmlEditor.getValue();
        const cssCode = cssEditor.getValue();
        const jsCode = jsEditor.getValue();
        // Use Laravel's route() helper to generate the URL
        const apiUrl = '{{ route('editor.store') }}';

        console.log("Submitting code to:", apiUrl);
        submitButton.disabled = true; submitButton.textContent = 'Submitting...';

        fetch(apiUrl, {
            method: 'POST', // Method is POST for create
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken // Include CSRF token
            },
            body: JSON.stringify({
                html_code: htmlCode,
                css_code: cssCode,
                js_code: jsCode
            })
        })
            .then(response => {
                return response.json().then(data => { // Expect JSON response
                    if (!response.ok) {
                        // Throw an error with the message from JSON response
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }
                    return data; // Pass successful JSON data { message: "...", edit_url: "..." }
                });
            })
            .then(data => {
                console.log('Create Success Response:', data);
                alert(data.message || 'Section saved successfully!'); // Show success message

                // <<< Redirect to the edit URL provided in the JSON response >>>
                if (data.edit_url) {
                    window.location.href = data.edit_url;
                } else {
                    // Fallback if URL is missing (shouldn't happen on success)
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Code';
                }
            })
            .catch(error => {
                console.error('Submission Error:', error);
                alert(`Error submitting code: ${error.message}`); // Show specific error
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Code';
            });
    }

    // --- Attach Event Listener to Submit Button ---
    document.addEventListener('DOMContentLoaded', (event) => {
        const submitButton = document.getElementById('submit-button');
        if (submitButton) {
            submitButton.addEventListener('click', submitEditorContent);
        } else {
            console.error("Submit button element not found after DOM loaded!");
        }
    });

</script>
@endpush
