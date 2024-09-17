<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Define a one-to-many relationship between Project and Task.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Define a many-to-many relationship between Project and User.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'contribution_hours', 'last_activity')
            ->withTimestamps();
    }
    /**
     * Retrieve the oldest task related to the project.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestTask(): HasOne
    {
        return $this->hasOne(Task::class)->oldestOfMany();
    }

    /**
     * Retrieve the oldest task related to the project.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestTask(): HasOne
    {
        return $this->hasOne(Task::class)->latestOfMany();
    }

    /**
     * Retrieve the highest priority task related to the project, with an optional title condition.
     * 
     * @param string $titleCondition (Optional) A string used to filter tasks by title.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function highestPriorityTaskWithCondition($titleCondition = ''): HasOne
    {
        return $this->hasOne(Task::class)
            ->ofMany([], function ($query) use ($titleCondition) {
                if ($titleCondition) {
                    $query->where('title', 'like', "%{$titleCondition}%")
                        ->where('priority', 'high');
                }
            });
    }
}
