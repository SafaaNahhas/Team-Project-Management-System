<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TaskRequest\AddNoteRequest;
use App\Http\Requests\TaskRequest\StoreTaskRequest;
use App\Http\Requests\TaskRequest\UpdateTaskRequest;
use App\Http\Requests\TaskRequest\FilterTasksRequest;
use App\Http\Requests\TaskRequest\UpdateTaskStatusRequest;


class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the tasks for a given project.
     *
     * @param int $projectId
     * @return JsonResponse
     */
    public function index($projectId): JsonResponse
    {

            $project = Project::findOrFail($projectId);
            $tasks = $this->taskService->getTasksByProject($project);

            if (isset($tasks['error'])) {
                return response()->json(['error' => $tasks['error']], 403);
            }

            return response()->json($tasks);

    }

    /**
     * Display the specified task.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {    $task = $this->taskService->getTaskById($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }
        $currentUser = auth()->user();

        if (
            $currentUser->role === 'admin' ||
            $task->created_by === $currentUser->id ||
            $task->assigned_to === $currentUser->id
        ) {
            return response()->json($task);
        }
        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Store a newly created task.
     *
     * @param StoreTaskRequest $request
     * @param int $projectId
     * @return JsonResponse
     */

    public function store(StoreTaskRequest $request, $projectId): JsonResponse
    {
        $validatedData = $request->validated();

        $project = Project::findOrFail($projectId);
        $task = $this->taskService->createTask($project, $validatedData);

        if (isset($task['error'])) {
            return response()->json(['error' => $task['error']], 403);
        }

        return response()->json($task, 201);
    }

    /**
     * Update the status of the specified task.
     *
     * @param UpdateTaskStatusRequest $request
     * @param int $taskId
     * @return JsonResponse
     */
    public function updateStatus(UpdateTaskStatusRequest $request, $taskId): JsonResponse
    {
        $validatedData = $request->validated();

            $task = Task::findOrFail($taskId);
            $task = $this->taskService->updateTaskStatus($task, $validatedData['status']);

            if (isset($task['error'])) {
                return response()->json(['error' => $task['error']], 403);
            }

            return response()->json($task);

    }

    /**
     * Add a note to the specified task.
     *
     * @param AddNoteRequest $request
     * @param int $taskId
     * @return JsonResponse
     */
    public function addNote(AddNoteRequest $request, $taskId): JsonResponse
    {
        $validatedData = $request->validated();

            $task = Task::findOrFail($taskId);
            $task = $this->taskService->addNoteToTask($task, $validatedData['note']);

            if (isset($task['error'])) {
                return response()->json(['error' => $task['error']], 403);
            }

            return response()->json($task);

    }

    /**
     * Remove the specified task.
     *
     * @param int $taskId
     * @return JsonResponse
     */
    public function destroy($taskId): JsonResponse
    {

            $task = Task::findOrFail($taskId);
            $response = $this->taskService->deleteTask($task);

            if (isset($response['error'])) {
                return response()->json(['error' => $response['error']], 403);
            }

            return response()->json($response);

    }

    /**
     * Update the specified task.
     *
     * @param UpdateTaskRequest $request
     * @param int $taskId
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, $taskId): JsonResponse
    {
        $validatedData = $request->validated();


            $task = Task::findOrFail($taskId);
            $task = $this->taskService->updateTask($task, $validatedData);

            if (isset($task['error'])) {
                return response()->json(['error' => $task['error']], 403);
            }

            return response()->json($task);

    }

    /**
     * Filter tasks based on criteria.
     *
     * @param FilterTasksRequest $request
     * @param int $projectId
     * @return JsonResponse
     */
    public function filter(FilterTasksRequest $request, $projectId): JsonResponse
    {
        $filters = $request->validated();


            $project = Project::findOrFail($projectId);
            $tasks = $this->taskService->filterTasks($project, $filters);

            if (isset($tasks['error'])) {
                return response()->json(['error' => $tasks['error']], 403);
            }

            return response()->json($tasks);

    }

    /**
     * Permanently delete the specified task.
     *
     * @param int $taskId
     * @return JsonResponse
     */
    public function forceDelete($taskId): JsonResponse
    {

            $task = Task::findOrFail($taskId);
            $response = $this->taskService->forceDeleteTask($task);

            if (isset($response['error'])) {
                return response()->json(['error' => $response['error']], 403);
            }

            return response()->json($response);

    }

    /**
     * Restore the specified task.
     *
     * @param int $taskId
     * @return JsonResponse
     */
    public function restore($taskId): JsonResponse
    {

            $task = Task::onlyTrashed()->findOrFail($taskId);
            $response = $this->taskService->restoreTask($task);

            if (isset($response['error'])) {
                return response()->json(['error' => $response['error']], 403);
            }

            return response()->json($response);

    }

    /**
     * Get the highest priority task for a project.
     *
     * @param int $projectId
     * @return JsonResponse
     */

    public function getHighestPriorityTask($projectId, $titleCondition): JsonResponse
    {

        $project = Project::find($projectId);
        $task = $project->highestPriorityTask;

        if ($task) {
            return response()->json(['status' => 'success', 'data' => $task], 200);
        } else {
            return response()->json(['error' => 'No task found with the given condition.'], 404);
        }
    }
    /**
     * Display a listing of soft-deleted tasks for a given project.
     *
     * @param int $projectId
     * @return JsonResponse
     */

    public function softDeletedTasks($projectId): JsonResponse
    {
        $project = Project::withTrashed()->find($projectId);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $tasks = $project->tasks()->onlyTrashed()->get();

        if ($tasks->isEmpty()) {
            return response()->json(data: ['message' => 'No soft-deleted tasks found']);
        }


        return response()->json($tasks);
    }

}
