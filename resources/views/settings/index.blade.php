@extends('layouts.app')

@section('content')
    <div class="p-8">
        <h1 class="text-2xl font-semibold text-gray-700 mb-6">Global Settings</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="{{ route('settings.save') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="palette_prompt" class="block text-sm font-medium text-gray-700 mb-2">Global Palette Prompt</label>
                    <p class="text-xs text-gray-500 mb-2">This prompt will be used as the default for generating color palettes for all sections.</p>
                    <textarea id="palette_prompt" name="palette_prompt"
                              class=" min-h-[33rem] max-h-[40rem] w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500"
                              rows="4"
                              placeholder="e.g., 'A professional and trustworthy palette for a financial services company'">{{ $settings['palette_prompt'] ?? '' }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
