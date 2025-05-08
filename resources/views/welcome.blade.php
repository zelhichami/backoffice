<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice Dev with Monaco Tabs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@latest/min/vs/loader.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* --- Original Sidebar CSS --- */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px;
            color: white;
            border-radius: 8px;
            transition: background 0.3s ease-in-out;
        }
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .nav-item span {
            margin-left: 10px;
            transition: opacity 0.3s ease-in-out;
            /* Text span transition is handled by x-transition */
        }
        /* Original rule for hiding text - Note: x-show primarily handles this */
        .sidebar.collapsed .nav-item span:last-child { /* More specific selector if needed */
            /* opacity: 0; */ /* x-show preferred */
            /* width: 0; */ /* x-show preferred */
            /* overflow: hidden; */ /* x-show preferred */
        }
        /* --- End Original Sidebar CSS --- */

        /* Variables Section */
        .variable-tag { background-color: #e2e8f0; color: #475569; padding: 8px 12px; border-radius: 6px; font-size: 14px; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s ease-in-out; cursor: pointer; }
        .variable-tag:hover { background-color: #cbd5e1; }
        .copy-button { background: #475569; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; opacity: 0; transition: opacity 0.3s ease-in-out; }
        .variable-tag:hover .copy-button { opacity: 1; }

        /* Editor Container Styling */
        .editor-wrapper {
            border: 1px solid #e2e8f0; /* Add a border around the editor area */
            border-radius: 0.375rem; /* rounded-md */
            overflow: hidden; /* Important for Monaco layout */
            height: 32rem; /* Adjust height as needed - approx h-96 + tab height */
        }
        /* Individual Editor Container Height */
        .editor-instance-container {
            height: calc(100% - 41px); /* Adjust based on tab height - subtract tab bar height */
            width: 100%;
            overflow: hidden; /* Needed for Monaco */
        }
        /* Tab Styling */
        .tab-button {
            padding: 0.5rem 1rem;
            border: none;
            cursor: pointer;
            background-color: #f8fafc; /* gray-50 */
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
        }
        .tab-button:hover {
            background-color: #f1f5f9; /* gray-100 */
        }
        .tab-button.active {
            background-color: white;
            color: #2563eb; /* blue-600 */
            border-bottom: 2px solid #2563eb; /* blue-600 */
            font-weight: 600;
        }
        .tab-bar {
            display: flex;
            border-bottom: 1px solid #e2e8f0; /* slate-200 */
            background-color: #f8fafc; /* gray-50 */
            border-top-left-radius: 0.375rem; /* Match parent */
            border-top-right-radius: 0.375rem; /* Match parent */
        }

    </style>
</head>
<body class="bg-gray-100">

<div x-data="{ sidebarOpen: true }" class="flex h-screen"> <div :class="sidebarOpen ? 'w-64' : 'w-16'" class="bg-gray-900 text-white transition-all duration-300 h-full flex flex-col flex-shrink-0"> <button @click="sidebarOpen = !sidebarOpen" class="p-4 focus:outline-none self-end">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 transition-transform duration-300"
                 :class="sidebarOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <nav class="flex-1 mt-4 space-y-2 sidebar" :class="!sidebarOpen && 'collapsed'"> <a href="#" class="nav-item">
                <span class="w-6 h-6 bg-blue-500 rounded flex-shrink-0"></span> <span x-show="sidebarOpen" x-transition>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <span class="w-6 h-6 bg-green-500 rounded flex-shrink-0"></span>
                <span x-show="sidebarOpen" x-transition>Projects</span>
            </a>
            <a href="#" class="nav-item">
                <span class="w-6 h-6 bg-yellow-500 rounded flex-shrink-0"></span>
                <span x-show="sidebarOpen" x-transition>Settings</span>
            </a>
            <a href="#" class="nav-item">
                <span class="w-6 h-6 bg-red-500 rounded flex-shrink-0"></span>
                <span x-show="sidebarOpen" x-transition>Profile</span>
            </a>
        </nav>
    </div>

    <div class="flex-1 p-6 overflow-auto">
        <div class="bg-white p-4 shadow-md rounded-md mb-4">
            <h2 class="text-xl font-semibold">Code Editor</h2>
        </div>

        <div class="flex flex-col md:flex-row"> <div class="flex-1 mb-4 md:mb-0 md:mr-4" x-data="{ activeTab: 'html' }">

                <div class="tab-bar">
                    <button @click="activeTab = 'html'" :class="{ 'active': activeTab === 'html' }" class="tab-button">HTML</button>
                    <button @click="activeTab = 'css'" :class="{ 'active': activeTab === 'css' }" class="tab-button">CSS</button>
                    <button @click="activeTab = 'js'" :class="{ 'active': activeTab === 'js' }" class="tab-button">JavaScript</button>
                </div>

                <div class="editor-wrapper bg-white">
                    <div x-show="activeTab === 'html'" id="html-editor-container" class="editor-instance-container"></div>
                    <div x-show="activeTab === 'css'" id="css-editor-container" class="editor-instance-container"></div>
                    <div x-show="activeTab === 'js'" id="js-editor-container" class="editor-instance-container"></div>
                </div>

                <div class="mt-6 text-right">
                    <button
                        id="submit-button"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow-md transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50"
                    >
                        Submit Code
                    </button>
                </div>
            </div>

            <div class="w-full md:w-64 bg-gray-200 p-4 rounded-md flex-shrink-0"> <h3 class="text-lg font-semibold mb-3">Variables</h3>
                <div class="space-y-2">
                    @if (isset($variables) && is_array($variables))
                        @foreach ($variables as $variable)
                            <div
                                class="variable-tag"
                                title="{{ wordwrap($variable['description'], 30, "\n", true) }}"
                                data-value="{{ $variable['example'] }}"
                                data-name="{{$variable['name']}}"
                            >
                                <span>{{ $variable['name'] }}</span>
                                <button class="copy-button" onclick="copyToClipboard('{{$variable['name']}}', this)" >Copy</button>
                            </div>
                        @endforeach
                    @else
                        <div class="text-gray-500 text-sm">No variables available.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4 bg-white p-4 shadow-md rounded-md">
            Other content...
        </div>
    </div>
</div>

<script>
    // --- Monaco Editor Initialization ---
    let htmlEditor, cssEditor, jsEditor; // Globally accessible editor instances

    require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@latest/min/vs' }});

    require(['vs/editor/editor.main'], function() {
        const commonEditorOptions = {
            theme: 'vs-dark',
            automaticLayout: true, // IMPORTANT: Allows editor to resize correctly when container is shown
            minimap: { enabled: true },
            wordWrap: 'on',
            fontSize: 14,
            scrollBeyondLastLine: false, // Optional: Prevent scrolling past the last line
        };

        // Initialize HTML Editor
        htmlEditor = monaco.editor.create(document.getElementById('html-editor-container'), {
            ...commonEditorOptions, // Spread common options
            value: [
                '<div>',
                '\t<h1>My Section Heading</h1>',
                '\t<p>This is the HTML content.</p>',
                '\t',
                '</div>'
            ].join('\n'),
            language: 'html'
        });

        // Initialize CSS Editor
        cssEditor = monaco.editor.create(document.getElementById('css-editor-container'), {
            ...commonEditorOptions,
            value: [
                '/* Add your CSS rules here */',
                '.my-section {',
                '\tpadding: 20px;',
                '\tbackground-color: #f0f0f0;',
                '}',
                ''
            ].join('\n'),
            language: 'css'
        });

        // Initialize JavaScript Editor
        jsEditor = monaco.editor.create(document.getElementById('js-editor-container'), {
            ...commonEditorOptions,
            value: [
                '// Add your JavaScript logic here',
                'document.addEventListener("DOMContentLoaded", function() {',
                '\tconsole.log("Section Javascript Loaded!");',
                '\t// Example: Add event listeners or manipulate the DOM',
                '});',
                ''
            ].join('\n'),
            language: 'javascript'
        });

        // Optional: Listen to changes (example for HTML editor)
        htmlEditor.onDidChangeModelContent(event => {
            // console.log('HTML Editor content changed:', htmlEditor.getValue());
        });
        cssEditor.onDidChangeModelContent(event => {
            // console.log('CSS Editor content changed:', cssEditor.getValue());
        });
        jsEditor.onDidChangeModelContent(event => {
            // console.log('JS Editor content changed:', jsEditor.getValue());
        });

    });

    // --- Copy to Clipboard Function (Unchanged) ---
    function copyToClipboard(text, buttonElement) {
        if (navigator && navigator.clipboard) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    buttonElement.textContent = 'Copied!';
                    setTimeout(() => {
                        buttonElement.textContent ='Copy';
                    }, 3000); // Reset after 3 seconds
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    alert('Failed to copy name. Please copy manually. Error: ' + err.message);
                });
        } else {
            // Fallback
            const tempInput = document.createElement('textarea');
            tempInput.value = text;
            tempInput.style.position = 'absolute';
            tempInput.style.left = '-9999px';
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.focus();
            try {
                document.execCommand('copy');
                buttonElement.textContent = 'Copied!';
                setTimeout(() => {
                    buttonElement.textContent = 'Copy';
                }, 3000);
            } catch (e) {
                console.warn('Fallback copy failed:', e);
                alert('Copy functionality is not available. Please copy manually.');
            } finally {
                document.body.removeChild(tempInput);
            }
        }
    }


    // --- MODIFIED: Function to Submit Editor Content ---
    function submitEditorContent() {
        const submitButton = document.getElementById('submit-button');

        // Check if all editors are initialized
        if (!htmlEditor || !cssEditor || !jsEditor) {
            console.error("One or more editors are not initialized yet.");
            alert("Error: Editors not ready.");
            return;
        }
        if (!submitButton) {
            console.error("Submit button not found.");
            return;
        }

        // Get content from all editors
        const htmlCode = htmlEditor.getValue();
        const cssCode = cssEditor.getValue();
        const jsCode = jsEditor.getValue();

        const apiUrl = 'http://api.mydomain.com/api/v2/save_section'; // Make sure this API endpoint expects html_code, css_code, js_code

        console.log("Submitting code to:", apiUrl);

        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // --- IMPORTANT ---
                // Add Authorization header if your API requires it
                // 'Authorization': 'Bearer YOUR_API_TOKEN',
                // Add any other required headers (e.g., CSRF token if needed)
                // 'X-CSRF-TOKEN': 'YOUR_CSRF_TOKEN_IF_APPLICABLE'
            },
            // *** MODIFIED BODY ***
            body: JSON.stringify({
                html_code: htmlCode, // Field name expected by your backend
                css_code: cssCode,   // Field name expected by your backend
                js_code: jsCode     // Field name expected by your backend
                // You might need to send other identifiers too, like section ID, page ID etc.
                // section_id: 'some_identifier'
            })
        })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(text || `HTTP error! status: ${response.status}`);
                    });
                }
                return response.json(); // Assuming API returns JSON on success
            })
            .then(data => {
                console.log('Success Response:', data);
                alert('Code submitted successfully!');
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Code';
            })
            .catch(error => {
                console.error('Submission Error:', error);
                alert(`Error submitting code: ${error.message}`);
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Code';
            });
    }

    // --- Attach Event Listener to Submit Button (Unchanged, but ensure it runs after DOM load) ---
    document.addEventListener('DOMContentLoaded', (event) => {
        const submitButton = document.getElementById('submit-button');
        if (submitButton) {
            submitButton.addEventListener('click', submitEditorContent);
        } else {
            console.error("Submit button element not found after DOM loaded!");
        }
    });

</script>

</body>
</html>
