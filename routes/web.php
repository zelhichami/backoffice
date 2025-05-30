<?php

use App\Http\Controllers\CodeEditorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('/', [CodeEditorController::class, 'index'])->name('sections.racine');

// Section Listing (replaces old GET /editor)
    Route::get('/sections', [CodeEditorController::class, 'index'])->name('sections.index'); // Changed name

    // Initialize New Section (from popup)
    Route::post('/section/initialize', [CodeEditorController::class, 'initialize'])->name('section.initialize');

    // Edit & Update Section
    Route::get('/section/edit/{section}', [CodeEditorController::class, 'edit'])->name('section.edit');
    Route::put('/section/edit/{section}', [CodeEditorController::class, 'update'])->name('section.update');
    Route::put('/section/details/{section}', [CodeEditorController::class, 'updateDetails'])->name('section.updateDetails');
    Route::delete('/section/{section}', [CodeEditorController::class, 'destroy'])->name('section.destroy');

    // Route for updating only the status
    Route::patch('/section/status/{section}', [CodeEditorController::class, 'updateStatus'])->name('section.updateStatus'); // Using PATCH is suitable for partial updates

    // --- NEW PREVIEW ROUTES ---
    // Get products for preview selection (paginated)
    Route::get('products', [CodeEditorController::class, 'getProducts'])->name('section.products');

    // Generate preview via XPage API
    Route::post('section/preview/{section}', [CodeEditorController::class, 'generatePreview'])->name('section.preview');
    // --- END NEW PREVIEW ROUTES ---

    // Display the page listing available variables from variables.json
    Route::get('/variables', [CodeEditorController::class, 'showVariablesPage'])->name('variables.index');

    Route::post('section/assets/{section}', [CodeEditorController::class, 'uploadAssets'])->name('uploadAssets');
    Route::get('section/assets/{section}', [CodeEditorController::class, 'getAssets'])->name('getAssets');
    Route::delete('section/assets/{section}/{assetName}', [CodeEditorController::class, 'deleteAsset'])->name('deleteAsset');

});

require __DIR__.'/auth.php';
