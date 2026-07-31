<?php

use App\Http\Controllers\Api\Jwt\AuthController as JwtAuthController;
use App\Http\Controllers\Api\Passport\ResourceController as PassportResourceController;
use App\Http\Controllers\Api\Sanctum\AuthController as SanctumAuthController;
use App\Http\Controllers\Api\Sanctum\CourseController as SanctumCourseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Phase 2 — Sanctum (own mobile / SPA apps)
|--------------------------------------------------------------------------
*/
Route::prefix('sanctum')->group(function () {
    Route::post('/login', [SanctumAuthController::class, 'login']);

    Route::middleware('sanctum.auth')->group(function () {
        Route::post('/logout', [SanctumAuthController::class, 'logout']);
        Route::get('/profile', [SanctumAuthController::class, 'profile']);
        Route::get('/courses', [SanctumCourseController::class, 'index']);
    });
});

/*
|--------------------------------------------------------------------------
| Phase 3 — Passport OAuth2 (third-party integrations)
|--------------------------------------------------------------------------
|
| Obtain tokens via:
|   POST /oauth/token  (client_credentials / authorization_code / refresh_token)
| Approve apps via:
|   GET  /oauth/authorize
*/
Route::prefix('oauth-demo')->group(function () {
    Route::get('/courses', [PassportResourceController::class, 'courses'])
        ->middleware('scopes:courses.read');

    Route::delete('/courses/{course}', [PassportResourceController::class, 'destroyCourse'])
        ->middleware('scopes:courses.write');

    Route::get('/students', [PassportResourceController::class, 'students'])
        ->middleware('scopes:students.read');

    Route::get('/grades', [PassportResourceController::class, 'grades'])
        ->middleware('scopes:grades.read');

    Route::get('/meetings', [PassportResourceController::class, 'meetings'])
        ->middleware('scopes:meetings.read');

    Route::post('/certificates', [PassportResourceController::class, 'storeCertificate'])
        ->middleware('scopes:certificates.write');
});

/*
|--------------------------------------------------------------------------
| Phase 4 — JWT (microservice style)
|--------------------------------------------------------------------------
*/
Route::prefix('jwt')->group(function () {
    Route::post('/login', [JwtAuthController::class, 'login']);

    Route::middleware('auth:jwt')->group(function () {
        Route::post('/refresh', [JwtAuthController::class, 'refresh']);
        Route::get('/profile', [JwtAuthController::class, 'profile']);
        Route::post('/logout', [JwtAuthController::class, 'logout']);
    });
});
