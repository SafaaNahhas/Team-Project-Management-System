<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProjectRequest\AssignUserRequest;
use App\Http\Requests\ProjectRequest\StoreProjectRequest;
use App\Http\Requests\ProjectRequest\UpdateProjectRequest;



class ProjectController extends Controller
{

        protected $projectService;

        /**
         * Create a new controller instance.
         *
         * @param ProjectService $projectService
         */
        public function __construct(ProjectService $projectService)
        {
            $this->projectService = $projectService;
        }

        /**
         * Display a listing of the authorized projects.
         *
         * @return JsonResponse
         */
        public function index()
        {

                $projects = $this->projectService->getAuthorizedProjects();

                if ($projects->isEmpty()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }

                return response()->json($projects);
        }


    /**
     * Display the specified project.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show( $id): JsonResponse
    {

            $project = $this->projectService->showProject($id);
            return response()->json($project);

    }

    /**
     * Store a newly created project.
     *
     * @param StoreProjectRequest $request
     * @return JsonResponse
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {

            $project = $this->projectService->storeProject($request->validated());
            return response()->json(['message' => 'Project created and admin assigned successfully', 'project' => $project], 201);

    }

    /**
     * Update the specified project.
     *
     * @param UpdateProjectRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProjectRequest $request, $id): JsonResponse
    {

            $project = Project::findOrFail($id);
            $updatedProject = $this->projectService->updateProject($project, $request->validated());
            return response()->json(['project' => $updatedProject]);

    }

  /**
     * Soft delete a project and its tasks.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
           $this->projectService->destroyProject($id);
            return response()->json(['message' => 'Project and its tasks deleted (soft delete) successfully'], 200);

    }

    /**
     * Restore a soft-deleted project and its tasks.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {

            $this->projectService->restoreProject($id);
            return response()->json(['message' => 'Project and its tasks restored successfully'], 200);

    }

    /**
     * Permanently delete a project and its tasks.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function forceDestroy($id): JsonResponse
    {
            $this->projectService->forceDestroyProject($id);
            return response()->json(['message' => 'Project and its tasks permanently deleted'], 200);

    }

    /**
     * Assign a user to a project.
     *
     * @param AssignUserRequest $request
     * @param int $projectId
     * @return JsonResponse
     */
    public function assignUser(AssignUserRequest $request, $projectId): JsonResponse
    {

            $project = Project::findOrFail($projectId);
            $this->projectService->assignUser($project, $request->validated());
            return response()->json(['message' => 'User assigned successfully'], 200);

    }

    /**
     * Remove a user from a project.
     *
     * @param int $projectId
     * @param int $userId
     * @return JsonResponse
     */
    public function removeUser($projectId, $userId): JsonResponse
    {

            $project = Project::findOrFail($projectId);
            $this->projectService->removeUser($project, $userId);
            return response()->json(['message' => 'User removed from project'], 200);

    }

    /**
     * Get the latest task for a project.
     *
     * @param int $projectId
     * @return JsonResponse
     */
    public function latestTask($projectId): JsonResponse
    {

            $project = Project::findOrFail($projectId);
            $latestTask = $this->projectService->getLatestTask($project);
            return response()->json($latestTask);

    }

    /**
     * Get the oldest task for a project.
     *
     * @param int $projectId
     * @return JsonResponse
     */
    public function oldestTask($projectId): JsonResponse
    {

            $project = Project::findOrFail($projectId);
            $oldestTask = $this->projectService->getOldestTask($project);
            return response()->json($oldestTask);

    }

/**
     * Get the highest priority task for a given project with a title condition.
     *
     * @param int $projectId
     * @param string $titleCondition
     * @return JsonResponse
     */
    public function getHighestPriorityTask(int $projectId, string $titleCondition): JsonResponse
    {
        return $this->projectService->getHighestPriorityTask($projectId, $titleCondition);
    }
    /**
     * Get the soft-deleted projects.
     *
     * @return JsonResponse
     */
    public function getSoftDeletedProjects()
    {
        $projects = Project::onlyTrashed()->get();

        if ($projects->isEmpty()) {
            return response()->json(['message' => 'No soft-deleted projects found'], 404);
        }

        return response()->json($projects, 200);
    }


}
