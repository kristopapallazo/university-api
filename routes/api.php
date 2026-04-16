<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\LendaController;
use App\Http\Controllers\Pedagog\SectionGradeController as PedagogSectionGradeController;
use App\Http\Controllers\PedagogController;
use App\Http\Controllers\ProgramStudimController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\Student\FatureController as StudentFatureController;
use App\Http\Controllers\Student\GradeController as StudentGradeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1/
|--------------------------------------------------------------------------
| Prefix is set in bootstrap/app.php via apiPrefix.
| All routes here are automatically under /api/v1/.
*/

// ── Public ──────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1');

// Google OAuth (students only — @students.uamd.edu.al)
Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback']);

// ── Protected ───────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Reference data — reads (any authenticated role)
    Route::get('/faculties', [FacultyController::class, 'index']);
    Route::get('/faculties/{id}', [FacultyController::class, 'show']);
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/{id}', [DepartmentController::class, 'show']);

    // Reference data — writes (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::post('/faculties', [FacultyController::class, 'store']);
        Route::put('/faculties/{id}', [FacultyController::class, 'update']);
        Route::delete('/faculties/{id}', [FacultyController::class, 'destroy']);

        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{id}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);
    });

    Route::get('/programs', [ProgramStudimController::class, 'index']);
    Route::get('/programs/{id}', [ProgramStudimController::class, 'show']);

    Route::get('/courses', [LendaController::class, 'index']);
    Route::get('/courses/{id}', [LendaController::class, 'show']);

    Route::get('/pedagogues', [PedagogController::class, 'index']);
    Route::get('/pedagogues/{id}', [PedagogController::class, 'show']);

    // Student reports (student role only)
    Route::middleware('role:student')->group(function () {
        Route::get('/student/grades', [StudentGradeController::class, 'index']);
        Route::get('/student/invoices', [StudentFatureController::class, 'index']);
    });

    // Pedagog reports (pedagog role only)
    Route::middleware('role:pedagog')->group(function () {
        Route::get('/pedagog/sections/{sectionId}/grades', [PedagogSectionGradeController::class, 'index']);
    });
});
