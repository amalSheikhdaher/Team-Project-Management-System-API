<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProjectService;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Requests\Projects\AssignUsersRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;

use Illuminate\Http\JsonResponse;
use App\Models\Project;

class ProjectController extends Controller
{
    // The project service handles project-related operations.
    protected ProjectService $projectService;

    /**
     * Create a new controller instance.
     *
     * @param ProjectService $projectService
     */
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request): JsonResponse
    {
        // Retrieve projects with their highest priority task and latest task
        $titleCondition = $request->query('title_condition', '');
        $projects = Project::with(['users',
            'highestPriorityTaskWithCondition' => function ($query) use ($titleCondition) {
                $query->where('title', 'like', "%{$titleCondition}%");
            },
            'latestTask',
            'oldestTask'
        ])->get();

        return response()->json($projects);
    }

    /**
     * Create a new Project.
     *
     * @param StoreProjectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->createProject($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully',
            'data' => new ProjectResource($project),
        ], 201);
    }

    /**
     * Update an existing Project.
     *
     * @param UpdateProjectRequest $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $updatedProject = $this->projectService->updateProject($project, $request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Project updated successfully',
            'data' => new ProjectResource($updatedProject)
        ], 200);
    }

    /**
     * Get details of a single project.
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Project $project): JsonResponse
    {
        // Load the users for the given project
        $project->load('tasks');
        return response()->json([
            'status' => 'success',
            'message' => 'Project retrieved successfully',
            'data' => new ProjectResource($project),
        ], 200);
    }

    /**
     * Delete a task.
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Project $project): JsonResponse
    {
        $this->projectService->deleteProject($project);
        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully'
        ], 200);
    }

    /**
 * Assign users to a project with specific roles.
 * @param \App\Http\Requests\Projects\AssignUsersRequest $request
 * @param \App\Models\Project $project
 * @return \Illuminate\Http\JsonResponse
 */
    public function assignUsers(AssignUsersRequest $request, Project $project): JsonResponse
    {
        $validated = $request->validated();
        // Map the users to be assigned
        $users = collect($validated['users'])->mapWithKeys(function ($user) {
            return [$user['id'] => ['role' => $user['role']]];
        });
        $this->projectService->assignUsers($project, $users->toArray());
        return response()->json([
            'status' => 'success',
            'message' => 'Users assigned successfully'
        ], 200);
    }
}
