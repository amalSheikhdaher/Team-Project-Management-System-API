<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Requests\Tasks\AssignUserToTaskRequest;
use App\Http\Requests\Tasks\UpdateStatusTaskRequest;
use App\Http\Requests\Tasks\AddNoteRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $taskService;

    // Inject TaskService in the controller
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Get all tasks with optional filters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $tasks = $this->taskService->getAllTasks();
        return response()->json([
            'status' => 'success',
            'message' => 'All tasks fetched successfully',
            'data' => TaskResource::collection($tasks),
        ], 200);
    }

    /**
     * Store a new task for the specified project.
     * 
     * @param StoreTaskRequest $request The validated request containing the task data.
     * @param Project $project The project in which the task will be created.
     * @return \Illuminate\Http\JsonResponse A response with the created task and a success message.
     */
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        // Delegate task creation to the service
        $task = $this->taskService->storeTask($project, $request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * Show a specific task within the specified project.
     * 
     * @param Project $project The project that contains the task.
     * @param Task $task The task to be retrieved.
     * @return \Illuminate\Http\JsonResponse A response containing the task details and assigned users.
     */
    public function show(Project $project, Task $task): JsonResponse
    {
        // Ensure that the task belongs to the project
        if ($task->project_id !== $project->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Load the users assigned to the task
        $task->load('project');

        return response()->json([
            'message' => 'Task retrieved successfully',
            'task' => $task,
            'users' => $task->users
        ], 200);
    }

    /**
     * Update an existing task for the specified project.
     * 
     * @param UpdateTaskRequest $request The validated request containing the updated task data.
     * @param Project $project The project in which the task exists.
     * @param Task $task The task to be updated.
     * @return \Illuminate\Http\JsonResponse A response with the updated task and a success message.
     */
    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        // Delegate task update to the service
        $updatedTask = $this->taskService->updateTask($task, $request->validated(), $project);
        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully',
            'data' => new TaskResource($updatedTask),
        ], 200);
    }


    /**
     * Delete a task from the specified project.
     * 
     * @param Project $project The project that contains the task.
     * @param Task $task The task to be deleted.
     * @return \Illuminate\Http\JsonResponse A response with a success message.
     */
    public function destroy(Project $project, Task $task)
    {
        // Delegate task deletion to the service
        $this->taskService->deleteTask($task, $project);
        return response()->json([
            'message' => 'Task deleted successfully'
        ], 200);
    }

    /**
     * Assign a user to a specific task within a project.
     * 
     * @param AssignUserToTaskRequest $request The validated request containing the user ID and role.
     * @param Project $project The project containing the task.
     * @param Task $task The task to which the user will be assigned.
     * @return \Illuminate\Http\JsonResponse A response with the updated task and a success message.
     */
    public function assignUserToTask(AssignUserToTaskRequest $request, Project $project, Task $task)
    {
        // Find the user by ID
        $user = User::findOrFail($request->user_id);

        // Delegate the task assignment to the service
        $updatedTask = $this->taskService->assignUserToTask($project, $task, $user, $request->role);

        // Return a success response
        return response()->json([
            'message' => 'User assigned to task successfully with role',
            'task' => $updatedTask,
        ], 200);
    }

    /**
     * Update the status of a specific task.
     * 
     * @param UpdateStatusTaskRequest $request The validated request containing the new task status.
     * @param Task $task The task whose status will be updated.
     * @return \Illuminate\Http\JsonResponse A response with the updated task and a success message.
     */
    public function updateTaskStatus(UpdateStatusTaskRequest $request, Task $task)
    {
        // The validation and authorization are already handled in the form request.

        // Update the task status
        $task->status = $request->validated()['status'];
        $task->save();

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => new TaskResource($task),
        ], 200);
    }

    /**
     * Update the note for a task.
     *
     * @param AddNoteRequest $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNoteToTask(AddNoteRequest $request, Task $task)
    {
        // Get the authenticated user
        $user = $request->user();

        // Ensure the user is a tester for the project
        $isTester = $task->project->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('role', 'tester')
            ->exists();

        if (!$isTester) {
            return response()->json(['message' => 'Unauthorized to add notes.'], 403);
        }
        // Update the task with the note from validated request
        $task->note = $request->note;
        $task->save();

        return response()->json([
            'message' => 'Note added successfully',
            'task' => $task,  // Optionally return the updated task
        ]);
    }

    /**
     * Filter tasks based on status or priority for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterTasks(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Fetch status and priority from the request query parameters
        $status = $request->query('status');
        $priority = $request->query('priority');

        // Call the method from the User model to filter tasks
        $tasks = $user->filterTasksByStatusOrPriority($status, $priority);
        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Start a task for the authenticated user by recording the start time.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function startTask(Request $request, Task $task)
    {
        $user = $request->user();  // Use request->user() instead of Auth::user()
        return response()->json($this->taskService->startTask($user, $task));
    }

    /**
     * End a task for the authenticated user, calculate contribution hours, and reset the task start time.
     *
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function endTask(Task $task)
    {
        $user = auth()->user(); // Get the authenticated user
        $result = app('App\Services\TaskService')->endTask($user, $task); // Call your service
        return response()->json($result);
    }
}
