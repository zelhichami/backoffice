<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="icon" href="{{ asset('images/xp-favicon.png') }}" type="image/x-icon"/>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .auth-background {
                /* You can replace this URL with your own image */
                background-image: url('/images/auth-bg.png');
                background-size: cover;
                background-position: center;
            }

            .auth-form-container {
                background-color: rgba(255, 255, 255, 0.15);
                -webkit-backdrop-filter: blur(20px);
                backdrop-filter: blur(2px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            }

            /* NEW: Style for the form inputs */
            .auth-form-container input {
                background-color: rgba(255, 255, 255, 0.2) !important;
                border: 1px solid rgba(255, 255, 255, 0.3) !important;
                color: white !important;
            }
            .auth-form-container input:-webkit-autofill,
            .auth-form-container input:-webkit-autofill:hover,
            .auth-form-container input:-webkit-autofill:focus,
            .auth-form-container input:-webkit-autofill:active{
                -webkit-box-shadow: 0 0 0 30px rgba(0, 0, 0, 0.1) inset !important;
                -webkit-text-fill-color: white !important;
            }

            .auth-form-container ::placeholder {
                color: rgba(255, 255, 255, 0.6) !important;
            }

            .auth-form-container .dark\\:text-gray-400 {
                color: rgba(255, 255, 255, 0.8) !important;
            }

            .auth-form-container .dark\\:focus\\:ring-offset-gray-800 {
                --tw-ring-offset-color: transparent !important;
            }

        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 auth-background">
           {{-- <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>
--}}
            <div class="w-full sm:max-w-md mt-6 px-6 py-4  bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg auth-form-container">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
