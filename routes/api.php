<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes for UAT form widget
Route::middleware([\App\Http\Middleware\HandleCors::class])->group(function () {
    Route::post('/submit/{token}', [\App\Http\Controllers\PublicFormController::class, 'submit']);
    Route::options('/submit/{token}', function () {
        return response('', 200);
    });
    
    // CTA widget API routes
    Route::get('/cta/rules/{token}', [\App\Http\Controllers\PublicCtaController::class, 'rules']);
    Route::post('/cta/click/{token}', [\App\Http\Controllers\PublicCtaController::class, 'trackClick']);
    Route::options('/cta/rules/{token}', function () {
        return response('', 200);
    });
    Route::options('/cta/click/{token}', function () {
        return response('', 200);
    });
    
    // Debug endpoint
    Route::get('/test-cors', function () {
        return response()->json([
            'message' => 'CORS test successful',
            'time' => now()->toISOString(),
        ])->header('Access-Control-Allow-Origin', '*');
    });
});
