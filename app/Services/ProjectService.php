<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProjectService
{
    /**
     * Get authorized projects for the current user.
     *
     * @return Collection
     * @throws \Exception
     */
    public function getAuthorizedProjects(): Collection
    {
        try {
            $userId = auth()->id();
            $projects = Project::with('users')->get();

            // Filter projects to include only those where the user is an admin
            $authorizedProjects = $projects->filter(function ($project) use ($userId) {
                $userRole = $project->users->where('id', $userId)->first()->pivot->role ?? null;
                return $userRole === 'admin';
            });

            return $authorizedProjects;
        } catch (\Exception $e) {
            Log::error('Failed to fetch authorized projects: ' . $e->getMessage());
            throw $e; // Rethrow the exception to be handled in the controller
        }
    }
      /**
     * Get a project by ID and ensure the user is an admin.
     *
     * @param int $id
     * @return Project
     * @throws \Exception
     */
    public function showProject(int $id): Project
    {
        try {
            $project = Project::with('users')->findOrFail($id);
            $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            if ($userRole !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            return $project;
        } catch (\Exception $e) {
            Log::error('Failed to fetch project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new project and assign an admin role to a user.
     *
     * @param array $data
     * @return Project
     * @throws \Exception
     */
    public function storeProject(array $data): Project
    {
        try {
            $project = Project::create($data);

            $adminUser = User::where('email', 'safaa@gmail.com')->first();

            $project->users()->attach($adminUser->id, [
                'role' => 'admin',
                'contribution_hours' => 0,
                'last_activity' => now(),
            ]);

            return $project;
        } catch (\Exception $e) {
            Log::error('Failed to create project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing project.
     *
     * @param Project $project
     * @param array $data
     * @return Project
     * @throws \Exception
     */
    public function updateProject(Project $project, array $data): Project
    {
        try {
            // $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            // if ($userRole !== 'admin') {
            //     throw new \Exception('Unauthorized');
            // }

            $project->update($data);

            return $project;
        } catch (\Exception $e) {
            Log::error('Failed to update project: ' . $e->getMessage());
            throw $e;
        }
    }


       /**
     * Soft delete a project and its tasks.
     *
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function destroyProject(int $id): void
    {
        try {
            $project = Project::findOrFail($id);
            $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            if ($userRole !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            $project->tasks()->delete();
            $project->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted project and its tasks.
     *
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function restoreProject(int $id): void
    {
        try {
            $project = Project::withTrashed()->findOrFail($id);
            $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            if ($userRole !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            $project->restore();
            $project->tasks()->restore();
        } catch (\Exception $e) {
            Log::error('Failed to restore project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Permanently delete a project and its tasks.
     *
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function forceDestroyProject(int $id): void
    {
        try {
            $project = Project::withTrashed()->findOrFail($id);
            $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            if ($userRole !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            $project->tasks()->forceDelete();
            $project->forceDelete();
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assign a user to a project.
     *
     * @param Project $project
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function assignUser(Project $project, array $data): void
    {
        try {
            $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            if ($userRole !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            $project->users()->attach($data['user_id'], [
                'role' => $data['role'],
                'contribution_hours' => $data['contribution_hours'] ?? 0,
                'last_activity' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to assign user to project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove a user from a project.
     *
     * @param Project $project
     * @param int $userId
     * @return void
     * @throws \Exception
     */
    public function removeUser(Project $project, int $userId): void
    {
        try {
            $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            if ($userRole !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            $project->users()->detach($userId);
        } catch (\Exception $e) {
            Log::error('Failed to remove user from project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the latest task for a project.
     *
     * @param Project $project
     * @return \App\Models\Task|null
     * @throws \Exception
     */
    public function getLatestTask(Project $project)
    {
        try {
            $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            if ($userRole !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            return $project->latestTask;
        } catch (\Exception $e) {
            Log::error('Failed to fetch latest task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the oldest task for a project.
     *
     * @param Project $project
     * @return \App\Models\Task|null
     * @throws \Exception
     */
    public function getOldestTask(Project $project)
    {
        try {
            $userRole = $project->users()->where('user_id', auth()->id())->first()->pivot->role ?? null;

            if ($userRole !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            return $project->oldestTask;
        } catch (\Exception $e) {
            Log::error('Failed to fetch oldest task: ' . $e->getMessage());
            throw $e;
        }
    }
     /**
     * Get the highest priority task for a given project with a title condition.
     *
     * @param int $projectId
     * @param string $titleCondition
     * @return JsonResponse
     * @throws \Exception
     */
    public function getHighestPriorityTask(int $projectId, string $titleCondition): JsonResponse
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json(['error' => 'Project not found.'], 404);
            }

            // Query to find the highest priority task matching the title condition
            $task = $project->tasks()
                            ->where('title', 'like', "%{$titleCondition}%")
                            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low') ASC")
                            ->first();

            if ($task) {
                return response()->json(['status' => 'success', 'data' => $task], 200);
            } else {
                return response()->json(['error' => 'No task found with the given condition.'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch the highest priority task: ' . $e->getMessage());
            throw $e; // Rethrow the exception to be handled in the controller
        }
    }
}
