<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public widget route - must be before other routes
Route::get('/embed/widget.js', [\App\Http\Controllers\PublicFormController::class, 'widget'])
    ->middleware(\App\Http\Middleware\HandleCors::class);

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('forms', \App\Http\Controllers\FormController::class)->except(['edit', 'update']);
    Route::patch('forms/{form}/submissions/{submission}/status', [\App\Http\Controllers\FormSubmissionController::class, 'updateStatus'])
        ->name('forms.submissions.update-status');
    Route::delete('forms/{form}/submissions/{submission}', [\App\Http\Controllers\FormSubmissionController::class, 'destroy'])
        ->name('forms.submissions.destroy');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
