<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * AuthController Routes
 * Routes for handling authentication such as login, logout, and token refresh.
 */
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

/**
 * Project Management Routes
 * 
 * Routes for managing projects (CRUD operations) and assigning users to projects.
 * 
 * These routes are protected by `auth:api` middleware to ensure authentication
 * and `role:super admin` middleware to restrict access to super admins.
 */
Route::controller(ProjectController::class)->group(function () {
    Route::post('/projects', 'store');
    Route::put('/projects/{project}', 'update');
    Route::delete('/projects/{project}', 'delete');
    Route::post('projects/{project}/assign-users', 'assignUsers');
})->middleware(['auth:api', 'role:super admin']);

/**
 * Project Viewing Routes
 * 
 * These routes are protected by the `auth:api` middleware, ensuring only authenticated
 * users can access project data.
 */
Route::controller(ProjectController::class)->group(function () {
    Route::get('/projects/{project}', 'show');
    Route::get('/projects', 'index');
})->middleware(['auth:api']);

/**
 * UserController Routes
 * 
 * Routes for managing users. Only accessible by users with the `super admin` role.
 * Uses API resource routing for standard actions (index, show, store, update, destroy).
 */
Route::apiResource('/users', UserController::class)
    ->middleware(['auth:api', 'role:super admin']);

/**
 * Task Management Routes (For Super Admins and Managers)
 * 
 * Routes for creating, updating, and deleting tasks within a project, and assigning users to tasks.
 * 
 * These routes are protected by both `auth:api` and `role:super admin, manager` middleware.
 */
Route::controller(TaskController::class)->group(function () {
    Route::post('/projects/{project}/tasks', 'store');
    Route::put('/projects/{project}/tasks/{task}', 'update');
    Route::delete('/projects/{project}/tasks/{task}', 'destroy');
    Route::post('/projects/{project}/tasks/{task}/assign', 'assignUserToTask');
})->middleware(['auth:api', 'role:super admin, manager']);

Route::post('/projects/{project}/tasks/{task}/assign', [TaskController::class,'assignUserToTask'])
    ->middleware(['auth:api', 'role:super admin']);

/**
 * Task Interaction Routes (For Developers and Testers)
 * 
 * These routes are protected by both `auth:api` middleware to ensure authentication and
 * `role:developer` or `role:tester` middleware to restrict access to appropriate roles.
 */
Route::controller(TaskController::class)->group(function () {
    Route::put('/tasks/{task}/status', 'updateTaskStatus')
        ->middleware(['auth:api', 'role:developer']);
    Route::put('/tasks/{task}/note', 'addNoteToTask')
        ->middleware(['auth:api', 'role:tester']);
});

/**
 * General Task Routes (For All Authenticated Users)
 * 
 * These routes are protected by the `auth:api` middleware to ensure only authenticated users can access them.
 */
Route::controller(TaskController::class)->group(function () {
    Route::get('/tasks',  'index');
    Route::get('/projects/{project}/tasks/{task}', 'show');
    Route::get('/tasks/filter',  'filterTasks');
    Route::post('/tasks/{task}/start', 'startTask');
    Route::post('tasks/{task}/end', 'endTask');
})->middleware(['auth:api']);