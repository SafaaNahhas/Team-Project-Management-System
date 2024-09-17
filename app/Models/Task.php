<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['title', 'description', 'status', 'priority', 'due_date', 'note','project_id','assigned_to','created_by'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function user()
{
    return $this->belongsTo(User::class);
}
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public static function scopeOfMany(Builder $query, $column, $method)
    {
        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];

        return $query->orderByRaw("FIELD({$column}, '".implode("','", array_keys($priorityOrder))."') ASC")
                    ->{$method}();
    }
  
}
