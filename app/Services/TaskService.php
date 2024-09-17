<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TaskService
{
      /**
     * Get all tasks for a given project.
     *
     * @param Project $project
     * @return array|Task[] The list of tasks or an error message.
     */
    public function getTasksByProject(Project $project)
    {
        $userRole = $project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        if ($userRole !== 'admin' && !$project->tasks()->where('created_by', Auth::id())->exists()) {
            return ['error' => 'Unauthorized'];
        }

        return $project->tasks;
    }
     /**
     * Get a specific task by ID.
     *
     * @param int $id
     * @return Task|array The task or an error message.
     */
    public function getTaskById($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return ['error' => 'No tasks found'];
        }

        $project = Project::findOrFail($task->project_id);
        $userRole = $project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        if ($userRole !== 'admin' && $task->created_by !== Auth::id()) {
            return ['error' => 'Unauthorized'];
        }

        return $task;
    }
      /**
     * Create a new task in the given project.
     *
     * @param Project $project
     * @param array $data The task data.
     * @return Task|array The created task or an error message.
     */
      public function createTask(Project $project, array $data)
{
    if (isset($data['assigned_to'])) {
        $assignedUserRole = $project->users()->where('user_id', $data['assigned_to'])->first()->pivot->role ?? null;
        if ($assignedUserRole !== 'tester' && $assignedUserRole !== 'developer') {
            return ['error' => 'The assigned user must be a tester or developer'];
        }
    }

    $task = Task::create(array_merge($data, [
        'created_by' => Auth::id(),
        'project_id' => $project->id, // Ensure project_id is included
    ]));

    $project->users()->updateExistingPivot(Auth::id(), ['last_activity' => now()]);

    return $task; // Return the created task
}

     /**
     * Update the status of a specific task.
     *
     * @param Task $task
     * @param string $status The new status for the task.
     * @return Task|array The updated task or an error message.
     */
    public function updateTaskStatus(Task $task, $status)
    {
        $userRole = $task->project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        $assignedUserId = $task->assigned_to;

        if ($userRole !== 'admin' && ($userRole !== 'developer' || $assignedUserId !== Auth::id())) {
            return ['error' => 'Unauthorized'];
        }

        if (now()->greaterThan($task->due_date)) {
            return ['error' => 'Cannot update status because the due date has passed'];
        }

        $task->update(['status' => $status]);
        $task->project->users()->updateExistingPivot(Auth::id(), ['last_activity' => now()]);

        return $task;
    }
      /**
     * Add a note to a specific task.
     *
     * @param Task $task
     * @param string $note The note to be added.
     * @return Task|array The updated task or an error message.
     */
    public function addNoteToTask(Task $task, $note)
    {
        $userRole = $task->project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        if ($userRole !== 'admin' && ($userRole !== 'tester' || $task->assigned_to !== Auth::id())) {
            return ['error' => 'Unauthorized'];
        }

        if ($task->status !== 'completed') {
            return ['error' => 'Notes can only be added to tasks with a status of completed'];
        }

        $task->update(['note' => $note]);
        $task->project->users()->updateExistingPivot(Auth::id(), ['last_activity' => now()]);

        return $task;
    }
     /**
     * Soft delete a specific task.
     *
     * @param Task $task
     * @return array The success message or an error message.
     */
    public function deleteTask(Task $task)
    {
        $userRole = $task->project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        if ($userRole !== 'admin' && ($userRole !== 'manager' || $task->created_by !== Auth::id())) {
            return ['error' => 'Unauthorized'];
        }

        $task->delete();
        return ['message' => 'Task deleted successfully'];
    }
     /**
     * Update a specific task with new data.
     *
     * @param Task $task
     * @param array $data The updated task data.
     * @return Task|array The updated task or an error message.
     */
    public function updateTask(Task $task, $data)
    {
        $userRole = $task->project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        if ($userRole !== 'admin' && ($userRole !== 'manager' || $task->created_by !== Auth::id())) {
            return ['error' => 'Unauthorized'];
        }

        $task->update($data);
        $task->project->users()->updateExistingPivot(Auth::id(), ['last_activity' => now()]);

        return $task;
    }
    /**
     * Filter tasks based on given criteria.
     *
     * @param Project $project
     * @param array $filters The filter criteria (status, priority).
     */
    public function filterTasks(Project $project, $filters)
    {
        $userRole = $project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        if (!in_array($userRole, ['manager', 'admin', 'tester', 'developer'])) {
            return ['error' => 'Unauthorized'];
        }

        $query = $project->tasks();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->get();
    }
    /**
     * Permanently delete a specific task.
     *
     * @param Task $task
     * @return array The success message or an error message.
     */
    public function forceDeleteTask(Task $task)
    {
        $userRole = $task->project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        if ($userRole !== 'admin' && ($userRole !== 'manager' || $task->created_by !== Auth::id())) {
            return ['error' => 'Unauthorized'];
        }

        $task->forceDelete();
        return ['message' => 'Task permanently deleted successfully'];
    }
      /**
     * Restore a soft-deleted task.
     *
     * @param Task $task
     * @return array The success message or an error message.
     */
    public function restoreTask(Task $task)
    {
        $userRole = $task->project->users()->where('user_id', Auth::id())->first()->pivot->role ?? null;

        if ($userRole !== 'admin' && ($userRole !== 'manager' || $task->created_by !== Auth::id())) {
            return ['error' => 'Unauthorized'];
        }

        $task->restore();
        return ['message' => 'Task restored successfully'];
    }
       /**
     * Get the highest priority task for a given project.
     *
     * @param Project $project
     * @return Task|array The highest priority task or an error message.
     */

    public function getHighestPriorityTask($project, $titleCondition)
    {
        
        return $project->tasks()
            ->ofMany('priority', 'max')
            ->where('title', 'LIKE', '%' . $titleCondition . '%')
            ->first();
    }
}
