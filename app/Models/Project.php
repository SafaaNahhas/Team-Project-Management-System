<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'description'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'contribution_hours', 'last_activity')->withTimestamps();
    }

    public function latestTask()
    {
        return $this->hasOne(Task::class)->latestOfMany();
    }

    public function oldestTask()
    {
        return $this->hasOne(Task::class)->oldestOfMany();
    }
    public static function scopeOfMany(Builder $query, $column, $method)
    {
        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];

        return $query->orderByRaw("FIELD({$column}, '".implode("','", array_keys($priorityOrder))."') ASC")
                    ->{$method}();
    }

    public function highestPriorityTask()
{
    return $this->hasOne(Task::class)->ofMany([
        'priority' => 'MAX',
    ], function ($query) {
        $query->where('title', 'like', '%p%');
    });
}

}
