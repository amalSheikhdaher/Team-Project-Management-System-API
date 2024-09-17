<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Retrieve all tasks from the database.
     *
     * @return \Illuminate\Database\Eloquent\Collection The collection of tasks.
     * @throws Exception If an error occurs while retrieving tasks.
     */
    public function getAllTasks(): Collection
    {
        try {
            return Task::all();
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to retrieve tasks: ' . $e->getMessage());
        }
    }

    /**
     * Store a new task in the database.
     *
     * @param array $data An array containing task data.
     * @return Task The created task model.
     * @throws Exception If an error occurs while creating the task.
     */
    public function storeTask(Project $project, $data)
    {
        try {
            $task = $project->tasks()->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'due_date' => $data['due_date'],
            ]);
            return $task;
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to created task: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing task with new data.
     *
     * @param Task $task The task model to update.
     * @param array $data An array containing the updated task data.
     * @return bool True if the update was successful, false otherwise.
     * @throws Exception If an error occurs while updating the task.
     */
    public function updateTask(Task $task, $data, Project $project)
    {
        if ($task->project_id !== $project->id) {
            throw new Exception('Unauthorized access to update task.');
        }
        try {
            $updated = $task->update([
                'title' => $data['title'] ?? $task->title,
                'description' => $data['description'] ?? $task->description,
                'status' => $data['status'] ?? $task->status,
                'priority' => $data['priority'] ?? $task->priority,
                'due_date' => $data['due_date'] ?? $task->due_date,
            ]);
            // Check if the update was successful
            if ($updated) {
                // Return the updated task model
                return $task->fresh(); // Reload from the database to get the latest version
            }
        } catch (Exception $e) {
            Log::error('Failed to update task: ' . $e->getMessage());
            throw new Exception('Task update failed.');
        }
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTask(Task $task, Project $project): void
    {
        if ($task->project_id !== $project->id) {
            throw new Exception('Unauthorized access to delete task.');
        }
        try {
            $task->delete();
        } catch (Exception $e) {
            Log::error('Failed to delete task: ' . $e->getMessage());
            throw new Exception('Task deletion failed.');
        }
    }

    /**
     * Assign a user to a task within a project and ensure the user is also assigned to the project.
     * 
     * @param Project $project  The project to which the user is being assigned.
     * @param Task $task        The task within the project that the user is being assigned to.
     * @param User $user        The user being assigned to the task.
     * @param string $role      The role of the user within the project.
     * 
     * @return Task  Returns the updated task instance after the operation is complete.
     */
    public function assignUserToTask(Project $project, Task $task, User $user, string $role)
    {
        // Check if the user is already assigned to the project
        if (!$project->users()->where('user_id', $user->id)->exists()) {
            // Automatically assign the user to the project with the specified role
            $project->users()->attach($user->id, ['role' => $role, 'contribution_hours' => 0, 'last_activity' => now()]);
        } else {
            // If the user is already in the project, update their role
            $project->users()->updateExistingPivot($user->id, ['role' => $role, 'last_activity' => now()]);
        }
        return $task->fresh();
    }

    /**
     * Start a task by recording the start time for the user in the session.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Task $task
     * @return array
     */
    public function startTask($user, $task)
    {
        // Check if the task is already started
        $existingStartTime = DB::table('project_user')
            ->where('user_id', $user->id)
            ->where('project_id', $task->project->id)
            ->value('task_start_time');

        if ($existingStartTime) {
            return ['error' => 'Task already started.'];
        }

        // Set the task start time in the database
        $user->projects()->updateExistingPivot($task->project->id, [
            'task_start_time' => Carbon::now(),
        ]);

        return ['message' => 'Task started', 'start_time' => Carbon::now()];
    }

    /**
     * End a task and calculate the contribution hours for the user in the project.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Task $task
     * @return array
     */
    public function endTask($user, $task)
    {
        // Fetch the start time from the database
        $startTime = DB::table('project_user')
            ->where('user_id', $user->id)
            ->where('project_id', $task->project->id)
            ->value('task_start_time');
        if (!$startTime) {
            return ['error' => 'Task start time not found.'];
        }

        // Calculate the time difference in minutes
        $endTime = Carbon::now();
        $minutesWorked = Carbon::parse($startTime)->diffInMinutes($endTime);

        // Fetch the current contribution minutes for this user in the project
        $existingContributionMinutes = DB::table('project_user')
            ->where('user_id', $user->id)
            ->where('project_id', $task->project->id)
            ->value('contribution_hours');

        // Update the contribution hours in the pivot table
        $user->projects()->updateExistingPivot($task->project->id, [
            'contribution_hours' => $existingContributionMinutes + $minutesWorked,
            'last_activity' => $endTime,
            'task_start_time' => null,  // Reset task start time
        ]);
        return ['message' => 'Task ended, contribution hours updated', 'minutes_worked' => $minutesWorked];
    }
}
