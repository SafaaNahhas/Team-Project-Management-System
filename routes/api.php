<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProjectController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::get('projects/soft-deleted', [ProjectController::class, 'getSoftDeletedProjects']);
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{project}/assign-user', [ProjectController::class, 'assignUser']);
    Route::delete('projects/{project}/remove-user/{user}', [ProjectController::class, 'removeUser']);
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{id}', [ProjectController::class, 'show']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::put('projects/{id}', [ProjectController::class, 'update']);
    Route::delete('projects/{id}', [ProjectController::class, 'destroy']);
    Route::delete('projects/{id}/force', [ProjectController::class, 'forceDestroy']);
    Route::post('projects/{id}/restore', [ProjectController::class, 'restore']);
    Route::get('projects/{project}/latest-task', [ProjectController::class, 'latestTask']);
    Route::get('projects/{project}/oldest-task', [ProjectController::class, 'oldestTask']);
    Route::get('/tasks/highest-priority/{project_id}/{titleCondition}', [ProjectController::class, 'getHighestPriorityTask']);

    Route::post('projects/{projectId}/tasks', [TaskController::class, 'store']);
    Route::get('projects/{projectId}/tasks', [TaskController::class, 'index']);
    Route::get('tasks/{id}', [TaskController::class, 'show']);
    Route::put('tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::post('tasks/{id}/note', [TaskController::class, 'addNote']);
    Route::delete('tasks/{id}', [TaskController::class, 'destroy']);
    Route::put('tasks/{id}', [TaskController::class, 'update']);
    Route::get('projects/{projectId}/tasks/filter', [TaskController::class, 'filter']);
    Route::post('/{id}/restore', [TaskController::class, 'restore']);
    Route::delete('/{id}/force-delete', [TaskController::class, 'forceDelete']);
    Route::get('projects/{projectId}/soft-deleted-tasks', [TaskController::class, 'softDeletedTasks']);


    Route::get('/projects/{projectId}/users', [UserController::class, 'index']);
    Route::get('/projects/{projectId}/users/{id}', [UserController::class, 'show']);
    Route::post('/projects/{projectId}/users', [UserController::class, 'store']);
    Route::put('/projects/{projectId}/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);
    Route::delete('/users/{id}/force', [UserController::class, 'forceDelete']);
    Route::post('/projects/{projectId}/users/{userId}/assign-role', [UserController::class, 'assignRoleToUserInProject']);
    Route::get('/users/{userId}/tasks', [UserController::class, 'userTasks']);
    Route::post('projects/{projectId}/assign-user', [UserController::class, 'assignUser']);
    Route::delete('projects/{projectId}/remove-user/{userId}', [UserController::class, 'removeUser']);
    Route::get('users/soft-deleted', [UserController::class, 'softDeletedUsers']);

});

