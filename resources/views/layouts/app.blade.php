<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <title>{{ config('app.name', 'Laravel') }} - Code Editor</title>

    <script src="https://cdn.tailwindcss.com"></script> {{-- Or use Vite --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    {{-- Load Monaco Editor Loader Script Here --}}
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@latest/min/vs/loader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>


    {{-- Vite directives if you use Vite for assets --}}
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

    @stack('styles') {{-- For page-specific styles --}}
    <style>

        body { font-family: 'Inter', sans-serif; }
        /* --- Sidebar CSS (Keep your original styles) --- */
        .nav-item { display: flex; align-items: center; padding: 12px; color: white; border-radius: 8px; transition: background 0.3s ease-in-out; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.1); }
        .nav-item span { margin-left: 10px; transition: opacity 0.3s ease-in-out; }

    </style>

</head>
<body class="bg-gray-100 antialiased">

{{-- Use sidebarOpen consistent with your latest code --}}
<div x-data="{ sidebarOpen: true }" class="flex h-screen min-h-screen">

    <div :class="sidebarOpen ? 'w-64' : 'w-16'" class="bg-gray-900 text-white transition-all duration-300 h-full flex flex-col flex-shrink-0">
        <button @click="sidebarOpen = !sidebarOpen" class="p-4 focus:outline-none self-end">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 transition-transform duration-300"
                 :class="sidebarOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        {{--
            The <nav> element is now a flex column to allow pushing logout to the bottom.
            The main links are wrapped in a div.
        --}}
        <nav class="flex flex-col flex-1 mt-4 ml-2 px-2 text-gray-700">
            <div class="space-y-3"> {{-- Wrapper for main navigation items --}}
                <a href="/sections" class="nav-item flex items-center gap-3 px-2 py-2 rounded hover:bg-gray-700">
                    <svg fill="#000000" viewBox="0 0 24 24" id="file-code-3" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-blue-500"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><polyline id="secondary" points="19 21 21 19 19 17" style="fill: none; stroke: #2ca9bc; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></polyline><polyline id="secondary-2" data-name="secondary" points="15 17 13 19 15 21" style="fill: none; stroke: #2ca9bc; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></polyline><path id="secondary-3" data-name="secondary" d="M7,13h6M7,9h6" style="fill: none; stroke: #2ca9bc; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><path id="primary" d="M9,21H4a1,1,0,0,1-1-1V4A1,1,0,0,1,4,3H15l2,2v8" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><polygon id="primary-2" data-name="primary" points="15 3 15 5 17 5 15 3" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></polygon></g></svg>
                    <span x-show="sidebarOpen" x-transition>Sections</span>
                </a>

                <a href="#" class="nav-item flex items-center gap-3 px-2 py-2 rounded hover:bg-gray-700">
                    <svg fill="#000000" width="199px" height="199px" viewBox="-2.4 -2.4 28.80 28.80" id="add-file-6" data-name="Line Color" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-blue-500"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path id="secondary" d="M16,19h4m-2-2v4M8,13h6m0-4H8" style="fill: none; stroke: #2ca9bc; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path><path id="primary" d="M12,21H5a1,1,0,0,1-1-1V4A1,1,0,0,1,5,3h9l4,4v6" style="fill: none; stroke: #ffffff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;"></path></g></svg>
                    <span x-show="sidebarOpen" x-transition>LandingPages</span>
                </a>
                {{-- You can add other main navigation links here --}}
            </div>

            {{-- Log Out Link/Button - Placed at the end of the flex column, mt-auto pushes it down --}}
            <form method="POST" action="{{ route('logout') }}" class="w-full mt-auto mb-3"> {{-- Added mt-auto and mb-3 for spacing --}}
                @csrf
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); this.closest('form').submit();"
                   class="nav-item flex items-center gap-3 px-2 py-2 rounded hover:bg-gray-700 w-full text-left">
                    {{-- Logout Icon (Heroicons: outline/logout) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span x-show="sidebarOpen" x-transition>Log Out</span>
                </a>
            </form>
        </nav>
    </div>
    <main class="flex-1 overflow-auto">
        @yield('content')
    </main>
    <div id="toast-container" aria-live="assertive" aria-atomic="true" class="fixed bottom-0 right-0 z-90 p-4 space-y-2 w-full max-w-xs sm:max-w-sm" style="z-index: 90;">
        {{-- Toasts will be added here dynamically by JavaScript --}}
    </div>
</div>

@stack('scripts') {{-- For page-specific scripts --}}

<script>

    /**
     * Displays a toast notification.
     * @param {string} message The message to display.
     * @param {string} type 'success' or 'error'.
     * @param {number} duration Milliseconds before the toast auto-dismisses (default: 5000).
     */
    function showToast(message, type = 'success', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container || !message) return;

        // Create toast element
        const toastElement = document.createElement('div');
        toastElement.setAttribute('role', 'alert');
        toastElement.className = `
            relative w-full p-4 rounded-lg shadow-lg text-white text-sm
            transition-all duration-300 ease-in-out transform text-bold
            opacity-0 translate-y-2 z-90
            ${type === 'success' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'}
        `;
        toastElement.textContent = message;

        // Append to container
        container.appendChild(toastElement);

        // Animate in (slight delay to allow rendering)
        setTimeout(() => {
            toastElement.classList.remove('opacity-0', 'translate-y-2');
            toastElement.classList.add('opacity-100', 'translate-y-0');
        }, 10); // Small delay

        // Set timer to dismiss
        const timerId = setTimeout(() => {
            dismissToast(toastElement);
        }, duration);

        // Helper function to dismiss
        const dismissToast = (element) => {
            if (!element) return;
            clearTimeout(timerId); // Clear timer if closed manually (e.g., via button)
            element.classList.remove('opacity-100', 'translate-y-0');
            element.classList.add('opacity-0', 'translate-y-2'); // Animate out

            // Remove element after animation
            setTimeout(() => {
                element.remove();
            }, 350); // Match transition duration + buffer
        };
    }

    // Check for Laravel Flash Messages on page load
    document.addEventListener('DOMContentLoaded', () => {
        @if (session('success'))
        showToast("{{ session('success') }}", 'success');
        @endif

        @if (session('error'))
        showToast("{{ session('error') }}", 'error');
        @endif

        // You can also manually trigger toasts from other JS events:
        // Example: showToast('Manual success message!', 'success');
        // Example: showToast('Something went wrong!', 'error', 10000); // Longer duration
    });
</script>
</body>
</html>
