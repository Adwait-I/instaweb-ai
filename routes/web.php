<?php

use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\WebsiteBuilderController;
use Illuminate\Support\Facades\Route;

// Redirect root to Website Builder dashboard
Route::get('/', function () {
    return redirect()->route('builder.index');
});

// AI Website Builder Routes
Route::prefix('builder')->name('builder.')->group(function () {
    Route::get('/', [WebsiteBuilderController::class, 'index'])->name('index');
    Route::get('/create', [WebsiteBuilderController::class, 'create'])->name('create');
    Route::post('/store', [WebsiteBuilderController::class, 'store'])->name('store');
    Route::get('/{slug}/edit', [WebsiteBuilderController::class, 'edit'])->name('edit');
    Route::get('/{slug}/flyer', [WebsiteBuilderController::class, 'flyer'])->name('flyer');
    Route::get('/{slug}/audit', [WebsiteBuilderController::class, 'auditFacts'])->name('audit');
    
    // API Key Settings Endpoints
    Route::get('/settings/api-key', [WebsiteBuilderController::class, 'getApiKeyStatus'])->name('settings.api_key.status');
    Route::post('/settings/api-key', [WebsiteBuilderController::class, 'saveApiKey'])->name('settings.api_key.save');
    
    // Ajax / API Editor Endpoints
    Route::post('/{slug}/sections/{sectionId}', [WebsiteBuilderController::class, 'updateSection'])->name('section.update');
    Route::post('/{slug}/sections/{sectionId}/regenerate', [WebsiteBuilderController::class, 'regenerateSection'])->name('section.regenerate');
    Route::post('/{slug}/resolve-conflict', [WebsiteBuilderController::class, 'resolveConflict'])->name('conflict.resolve');
    Route::post('/{slug}/publish', [WebsiteBuilderController::class, 'togglePublish'])->name('publish');
    Route::post('/{slug}/reorder', [WebsiteBuilderController::class, 'reorderSections'])->name('reorder');
});

// Public Generated Websites
Route::get('/site/{slug}', [PublicSiteController::class, 'show'])->name('site.show');
Route::post('/site/{slug}/inquire', [PublicSiteController::class, 'submitInquiry'])->name('site.inquire');
