<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (!function_exists('generateUniqueUuid')) {
    function generateUniqueUuid(string $modelClass, string $column = 'uuid'): string
    {
        do {
            $uuid = substr(md5(uniqid()), 0, 8);
        } while ($modelClass::where($column, $uuid)->exists());

        return $uuid;
    }
if (!function_exists('getAssetPath')) {
    function getAssetPath(string $type): ?string
    {

        // Base path within the storage disk
        $basePath = "sections_assets/default";

        // Determine filename based on type
        $filename = match ($type) {
            'layout_section' => 'layout_section.html',
            'css' => 'style.css',
            'js' => 'script.js',
            default => null, // Handle unknown types
        };

        if (!$filename) {
            // Log::debug("Invalid asset type '{$type}' requested for Section {$this->id}.");
            return null;
        }

        // Return the full relative path
        return "{$basePath}/{$filename}";
    }
}


if (!function_exists('getAssetContent')) {
    function getAssetContent(string $name)
    {

        $disk = Storage::disk('public');
        $path = getAssetPath($name);
        if ($path && $disk->exists($path)) {
            try {
                return $disk->get($path);
            } catch (\Exception $e) {
                Log::error("Error reading HTML file for section at path {$path}: " . $e->getMessage());
                return null; // Return null on read error
            }
        }
    }
}

}
