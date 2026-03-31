<?php

use App\Support\Platform\PlatformHealthCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function (PlatformHealthCheck $healthCheck) {
    $result = $healthCheck->run();

    $statusCode = match ($result['status']) {
        'healthy' => 200,
        'degraded' => 200,
        default => 503,
    };

    return response()->json($result, $statusCode);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
