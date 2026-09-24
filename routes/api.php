<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierWebhookController;
use App\Http\Controllers\Api\PublicTrackingApiController;
use App\Http\Controllers\Api\RateCalculationController;
use App\Http\Controllers\Api\SurchargeCheckController;
use App\Http\Controllers\Api\SystemHealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Only routes backed by implemented and reviewed controllers belong here.
| Add APIs feature-by-feature with authorization, validation, rate limiting,
| and tests. Public tracking will use a dedicated privacy-safe read model.
|
*/

Route::get('/health', [SystemHealthController::class, 'health'])
    ->middleware('throttle:60,1')
    ->name('api.health');
Route::get('/readiness', [SystemHealthController::class, 'readiness'])
    ->middleware('throttle:30,1')
    ->name('api.readiness');

// Public Privacy-Safe Tracking API
Route::get('/v1/track/{trackingNumber}', [PublicTrackingApiController::class, 'track'])
    ->middleware('throttle:60,1')
    ->name('api.v1.track');

// Global Last-Mile Carrier Tracking Webhooks
Route::post('/webhooks/carrier-tracking/{carrier}', [CarrierWebhookController::class, 'handle'])
    ->name('api.webhooks.carrier');

// AI Logistics Assistant Endpoints
Route::prefix('ai')->group(function () {
    Route::get('/greeting', [\App\Http\Controllers\AiAssistantController::class, 'greeting'])->name('api.ai.greeting');
    Route::post('/chat', [\App\Http\Controllers\AiAssistantController::class, 'chat'])->name('api.ai.chat');
    Route::get('/occasions', [\App\Http\Controllers\AiAssistantController::class, 'occasions'])->name('api.ai.occasions');
});

Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::put('/temporary-password', [AuthController::class, 'changeTemporaryPassword'])
        ->name('api.auth.temporary-password');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', static function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    })->name('api.user');

    Route::get('/auth/profile', [AuthController::class, 'profile'])->name('api.auth.profile');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::put('/auth/password', [AuthController::class, 'changePassword'])->name('api.auth.password');

    Route::post('/international/rates/calculate', [RateCalculationController::class, 'calculate'])
        ->name('api.international.rates.calculate');

    Route::prefix('surcharges')->group(function () {
        Route::post('/check', [SurchargeCheckController::class, 'check'])->name('api.surcharges.check');
        Route::post('/bulk-check', [SurchargeCheckController::class, 'bulkCheck'])->name('api.surcharges.bulk-check');
    });
});
