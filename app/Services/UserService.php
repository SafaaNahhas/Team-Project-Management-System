<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    /**
     * Get all users associated with a given project.
     *
     * @param int $projectId
     * @return array
     * @throws \Exception
     */
    public function getUsersByProject($projectId)
    {
        try {
            $currentUser = Auth::user();
            $project = Project::findOrFail($projectId);
            $currentUserRoleInProject = $project->users()->where('user_id', $currentUser->id)->first()->pivot->role ?? null;

            if ($currentUserRoleInProject !== 'admin') {
                return ['message' => 'Unauthorized'];
            }

            return $project->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'contribution_hours' => $user->pivot->contribution_hours,
                    'last_activity' => $user->pivot->last_activity,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            throw $e;
        }
    }
     /**
     * Get details of a specific user within a given project.
     *
     * @param int $id
     * @param int $projectId
     * @return array
     * @throws \Exception
     */
    public function getUserByIdInProject($id, $projectId)
    {
        try {
            $currentUser = Auth::user();
            $project = Project::findOrFail($projectId);
            $currentUserRoleInProject = $project->users()->where('user_id', $currentUser->id)->first()->pivot->role ?? null;

            $targetUser = $project->users()->where('user_id', $id)->first();

            if (!$targetUser) {
                return ['message' => 'User not found in this project'];
            }

            $targetUserPivot = $targetUser->pivot;

            if ($currentUserRoleInProject === 'admin') {
                return [
                    'user_id' => $id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'role' => $targetUserPivot->role,
                ];
            } elseif ($currentUserRoleInProject === 'manager') {
                if ($targetUserPivot->role !== 'manager') {
                    return ['message' => 'Unauthorized'];
                }
                return [
                    'user_id' => $id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'role' => $targetUserPivot->role,
                ];
            } elseif ($currentUserRoleInProject === 'user') {
                if ($currentUser->id !== (int)$id) {
                    return ['message' => 'Unauthorized'];
                }
                return [
                    'user_id' => $id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'role' => $targetUserPivot->role,
                ];
            }

            return ['message' => 'Unauthorized'];
        } catch (\Exception $e) {
            Log::error('Error fetching user by ID in project: ' . $e->getMessage());
            throw $e;
        }
    }
     /**
     * Create a new user and associate them with a given project.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $projectId
     * @return \App\Models\User
     * @throws \Exception
     */
    public function createUserInProject($request, $projectId)
    {
        try {
            $currentUser = Auth::user();
            $project = Project::findOrFail($projectId);
            $currentUserRoleInProject = $project->users()->where('user_id', $currentUser->id)->first()->pivot->role ?? null;

            if ($currentUserRoleInProject !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
            ]);

            $project->users()->attach($user->id, [
                'role' => $request->input('role'),
                'contribution_hours' => 0,
                'last_activity' => now(),
            ]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Error creating user in project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing user within a given project.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $projectId
     * @param int $id
     * @return \App\Models\User
     * @throws \Exception
     */
    public function updateUserInProject($request, $projectId, $id)
    {
        try {
            $currentUser = Auth::user();
            $project = Project::findOrFail($projectId);
            $currentUserRoleInProject = $project->users()->where('user_id', $currentUser->id)->first()->pivot->role ?? null;

            $targetUser = User::withTrashed()->findOrFail($id);

            if ($currentUserRoleInProject === 'admin' || $currentUser->id === $targetUser->id) {
                $targetUser->update($request->only(['name', 'email', 'password']));
                return $targetUser;
            } else {
                throw new \Exception('Unauthorized');
            }
        } catch (\Exception $e) {
            Log::error('Error updating user in project: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Delete a user from the system and detach them from all projects.
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */

    public function deleteUser(int $id): array
    {
        try {
            $currentUser = Auth::user();

            // Check if the current user is an admin
            $isAdmin = $currentUser->projects->contains(function ($project) use ($currentUser) {
                return $project->users()->where('user_id', $currentUser->id)->first()->pivot->role === 'admin';
            });

            if (!$isAdmin) {
                throw new \Exception('Unauthorized');
            }

            // Find the user to delete
            $user = User::findOrFail($id);

            // Detach the user from all projects
            $user->projects->each(function ($project) use ($user) {
                $project->users()->detach($user->id);
            });

            // Delete the user
            $user->delete();

            return ['message' => 'User deleted successfully'];
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            throw $e;
        }
    }


        /**
     * Restore a soft-deleted user.
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function restoreUser($id)
    {
        try {
            $currentUser = Auth::user();
            $isAdmin = $currentUser->projects->contains(function ($project) use ($currentUser) {
                return $project->users()->where('user_id', $currentUser->id)->first()->pivot->role === 'admin';
            });

            if (!$isAdmin) {
                throw new \Exception('Unauthorized');
            }

            $user = User::onlyTrashed()->findOrFail($id);
            $user->restore();

            return ['message' => 'User restored successfully'];
        } catch (\Exception $e) {
            Log::error('Error restoring user: ' . $e->getMessage());
            throw $e;
        }
    }
       /**
     * Permanently delete a soft-deleted user.
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function forceDeleteUser($id)
    {
        try {
            $currentUser = Auth::user();
            $isAdmin = $currentUser->projects->contains(function ($project) use ($currentUser) {
                return $project->users()->where('user_id', $currentUser->id)->first()->pivot->role === 'admin';
            });

            if (!$isAdmin) {
                throw new \Exception('Unauthorized');
            }

            $user = User::onlyTrashed()->findOrFail($id);
            $user->forceDelete();

            return ['message' => 'User permanently deleted'];
        } catch (\Exception $e) {
            Log::error('Error permanently deleting user: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Assign a role to a user within a given project.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $projectId
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function assignRoleToUser($request, $projectId, $userId)
    {
        try {
            $currentUser = Auth::user();
            $project = Project::findOrFail($projectId);
            $currentUserRoleInProject = $project->users()->where('user_id', $currentUser->id)->first()->pivot->role ?? null;

            if ($currentUserRoleInProject !== 'admin') {
                throw new \Exception('Unauthorized');
            }

            $user = User::findOrFail($userId);
            $project->users()->syncWithoutDetaching([
                $user->id => [
                    'role' => $request->input('role'),
                    'contribution_hours' => $request->input('contribution_hours') ?? 0,
                    'last_activity' => $request->input('last_activity') ?? now(),
                ],
            ]);

            return ['message' => 'Role assigned successfully'];
        } catch (\Exception $e) {
            Log::error('Error assigning role to user in project: ' . $e->getMessage());
            throw $e;
        }
    }
 /**
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getUserTasks($userId)
    {
        try {
            $user = User::findOrFail($userId);

            $tasks = $user->tasks()->with(['creator', 'assignee', 'project'])->get();

            return $tasks->map(function ($task) use ($userId) {
                return [
                    'id' => $task->id,
                    'project_id' => $task->project_id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date,
                    'note' => $task->note,
                    'created_at' => $task->created_at,
                    'updated_at' => $task->updated_at,
                    'deleted_at' => $task->deleted_at,
                    'created_by' => $task->creator ? $task->creator->name : null,
                    'assigned_to' => $task->assignee ? $task->assignee->name : null,
                    'user_id' => $userId,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching user tasks: ' . $e->getMessage());
            throw $e;
        }
    }
}
