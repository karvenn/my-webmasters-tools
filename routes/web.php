<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public widget routes - must be before other routes
Route::get('/embed/widget.js', [\App\Http\Controllers\PublicFormController::class, 'widget'])
    ->middleware(\App\Http\Middleware\HandleCors::class);
Route::get('/embed/cta-widget.js', [\App\Http\Controllers\PublicCtaController::class, 'widget'])
    ->middleware(\App\Http\Middleware\HandleCors::class);

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('forms', \App\Http\Controllers\FormController::class);
    Route::post('forms/{form}/toggle-status', [\App\Http\Controllers\FormController::class, 'toggleStatus'])
        ->name('forms.toggle-status');
    Route::patch('forms/{form}/submissions/{submission}/status', [\App\Http\Controllers\FormSubmissionController::class, 'updateStatus'])
        ->name('forms.submissions.update-status');
    Route::delete('forms/{form}/submissions/{submission}', [\App\Http\Controllers\FormSubmissionController::class, 'destroy'])
        ->name('forms.submissions.destroy');
    
    // CTA Button routes
    Route::resource('cta-buttons', \App\Http\Controllers\CtaButtonController::class);
    Route::post('cta-buttons/{ctaButton}/toggle-status', [\App\Http\Controllers\CtaButtonController::class, 'toggleStatus'])
        ->name('cta-buttons.toggle-status');
    
    // CTA Button Rule routes
    Route::post('cta-buttons/{ctaButton}/rules', [\App\Http\Controllers\CtaButtonRuleController::class, 'store'])
        ->name('cta-buttons.rules.store');
    Route::put('cta-buttons/{ctaButton}/rules/{rule}', [\App\Http\Controllers\CtaButtonRuleController::class, 'update'])
        ->name('cta-buttons.rules.update');
    Route::delete('cta-buttons/{ctaButton}/rules/{rule}', [\App\Http\Controllers\CtaButtonRuleController::class, 'destroy'])
        ->name('cta-buttons.rules.destroy');
    Route::post('cta-buttons/{ctaButton}/rules/test', [\App\Http\Controllers\CtaButtonRuleController::class, 'test'])
        ->name('cta-buttons.rules.test');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
