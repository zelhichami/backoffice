<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    /**
     * Save the settings.
     */
    public function save(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'palette_prompt' => 'nullable|string',
        ]);

        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('settings.index')->with('success', 'Settings saved successfully!');
    }
}
