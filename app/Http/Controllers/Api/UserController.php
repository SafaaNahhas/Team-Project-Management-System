<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;

use App\Models\User;

use App\Http\Requests\UserRequest\AssignRoleRequest;

use App\Http\Requests\UserRequest\UpdateUserRequest;

use App\Http\Requests\UserRequest\StoreUserRequest;


use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users in a project.
     *
     * @param  int  $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($projectId)
    {

            $response = $this->userService->getUsersByProject($projectId);
            return response()->json($response);

    }

    /**
     * Display a specific user in a project.
     *
     * @param  int  $id
     * @param  int  $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($projectId, $id)
    {

            $response = $this->userService->getUserByIdInProject($id, $projectId);
            return response()->json($response);

    }

    /**
     * Store a newly created user in a project.
     *
     * @param  StoreUserRequest  $request
     * @param  int  $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request, $projectId)
    {

            $response = $this->userService->createUserInProject($request, $projectId);
            return response()->json($response, 201);

    }

    /**
     * Update the specified user in a project.
     *
     * @param UpdateUserRequest  $request
     * @param  int  $projectId
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, $projectId, $id)
    {

            $response = $this->userService->updateUserInProject($request, $projectId, $id);
            return response()->json($response);

    }

    /**
     * Remove the specified user from the database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy(int $id)
    {
        try {
            $response = $this->userService->deleteUser($id);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore the specified user from the database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {

            $response = $this->userService->restoreUser($id);
            return response()->json($response);

    }

    /**
     * Permanently delete the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {

            $response = $this->userService->forceDeleteUser($id);
            return response()->json($response);

    }

    /**
     * Assign a role to a user in a project.
     *
     * @param  AssignRoleRequest  $request
     * @param  int  $projectId
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRoleToUserInProject(AssignRoleRequest $request, $projectId, $userId)
    {

            $response = $this->userService->assignRoleToUser($request, $projectId, $userId);
            return response()->json($response);

    }

/**
     * Display a list of tasks for a given user.
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function userTasks($userId)
{
    $user = User::findOrFail($userId);

    $projects = $user->projects;
    $tasks = Task::whereIn('project_id', $projects->pluck('id'))->get();

    return response()->json([
        'status' => 'success',
        'data' => $tasks
    ], 200);
}
/**
 * Display a listing of soft-deleted users.
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function softDeletedUsers()
{
    $users = User::onlyTrashed()->get();

    if ($users->isEmpty()) {
        return response()->json(['message' => 'No soft-deleted users found'], 404);
    }

    return response()->json($users);
}

}



