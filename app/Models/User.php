<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Define a many-to-many relationship between User and Project.
     *  
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role', 'contribution_hours', 'last_activity')
            ->withTimestamps();
    }

    /**
     * Define a has-many-through relationship between User and Task through Project.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Project::class, 'user_id', 'project_id', 'id', 'id');
    }

    /**
     * Filter tasks based on their status or priority.
     * 
     * @param string|null $status (Optional) The task status to filter by (e.g., 'new', 'in-progress', 'completed').
     * @param string|null $priority (Optional) The task priority to filter by (e.g., 'low', 'medium', 'high').
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function filterTasksByStatusOrPriority($status = null, $priority = null)
    {
        return Task::whereRelation('project', function ($query) {
            // Fetch tasks that belong to projects the user is associated with
            $query->whereHas('users', function ($query) {
                $query->where('user_id', $this->id); // Ensure the user is part of the project
            });
        })
            ->when($status, function ($query) use ($status) {
                // Apply status filter if provided
                $query->where('status', $status);
            })
            ->when($priority, function ($query) use ($priority) {
                // Apply priority filter if provided
                $query->where('priority', $priority);
            })->get();
    }
}
