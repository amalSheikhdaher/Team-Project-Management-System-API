<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'users' => $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'role' => $user->pivot->role, // from the pivot table
                    'email' => $user->email,
                    'contribution_hours' => $user->pivot->contribution_hours, // Get contribution hours
                    'last_activity' => $user->pivot->last_activity, // Get last activity
                ];
            }),
            'tasks' => $this->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date,
                    'note' => $task->note,
                ];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
