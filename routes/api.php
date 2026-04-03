<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PedagogueController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\GradeController;

// Public routes - nuk kërkojnë login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes - kërkojnë token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Students
    Route::apiResource('students', StudentController::class);

    // Pedagogues
    Route::apiResource('pedagogues', PedagogueController::class);

    // Courses
    Route::apiResource('courses', CourseController::class);

    // Schedules
    Route::apiResource('schedules', ScheduleController::class);

    // Grades
    Route::apiResource('grades', GradeController::class);
});