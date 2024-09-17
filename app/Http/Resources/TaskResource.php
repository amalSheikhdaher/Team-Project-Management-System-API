<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'project_id' => $this->project_id,
            'users' => $this->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'role' => $user->pivot->role, // from the pivot table
                    'email' => $user->email,
                    'contribution_hours' => $user->pivot->contribution_hours, // Get contribution hours
                    'last_activity' => $user->pivot->last_activity, // Get last activity
                ];
            }),
            //'users' => UserResource::collection($this->whenLoaded('users')),  // Load users
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
