<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
});

Route::post('/attendance', [AttendanceController::class, 'store'])
    ->middleware(['throttle:5,1', 'validate.form.token']);

Route::get('/form-token', function () {
    $token = Str::random(64);
    Cache::put('form_token_' . $token, true, now()->addMinutes(10));
    return response()->json(['token' => $token]);
})->middleware('throttle:5,1');

?>