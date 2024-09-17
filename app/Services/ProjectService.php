<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * Retrieve all projects with their associated tasks and users.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProjects(): Collection
    {
        return Project::with(['tasks', 'users' => function ($query) {
            $query->withPivot('role', 'contribution_hours', 'last_activity');
        }])->get();
    }

    /**
     * Store a new project in the database.
     *
     * @param array $data An array containing project data.
     * @return Project The created project model.
     * @throws Exception If an error occurs while creating the project.
     */
    public function createProject($data): Project
    {
        try {
            $project = Project::create([
                'name' => $data['name'],
                'description' => $data['description']
            ]);
            return $project;
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to created project: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing project with new data.
     *
     * @param Project $project The project model to update.
     * @param array $data An array containing the updated project data.
     * @return Project The project model with the user relation loaded.
     * @throws Exception If an error occurs while updating the project.
     */
    public function updateProject(Project $project, $data): Project
    {
        try {
            $project->update([
                'name' => $data['name'] ?? $project->name,
                'description' => $data['description'] ?? $project->description,
            ]);
            return $project;
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to updated project: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve a specific project along with its associated user.
     *
     * @param Project $project The project model to retrieve.
     * @return Project The project model with the user relation loaded.
     * @throws Exception If an error occurs while retrieving the project.
     */
    public function show(Project $project): Project
    {
        try {
            return Project::with(['users' => function ($query) {
                $query->withPivot('role', 'contribution_hours', 'last_activity');
            }])->findOrFail($project);
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to retrieve project: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific project from the database.
     *
     * @param Project $project The project model to delete.
     * @return void
     * @throws Exception If an error occurs while deleting the project.
     */
    public function deleteProject(Project $project): void
    {
        try {
            $project->delete();
        } catch (Exception $e) {
            Log::error($e);
            throw new Exception('Failed to deleted project: ' . $e->getMessage());
        }
    }

    /**
 * Assign multiple users to a project with specific roles.
 * @param \App\Models\Project $project The project to which the users will be assigned.
 * @param array $users An associative array where 
 * 
 * @return void
 */
    public function assignUsers(Project $project, $users): void
    {
        foreach ($users as $userId => $attributes) {
            // Attach each user with their respective role
            $project->users()->attach($userId, $attributes);
        }
    }
}
