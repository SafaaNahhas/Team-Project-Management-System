<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Laravel\Sanctum\HasApiTokens;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use  HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
    public function projects()
    {
        return $this->belongsToMany(Project::class)->withPivot('role', 'contribution_hours', 'last_activity')->withTimestamps();
    }

    
public function tasks()
{
    return $this->hasManyThrough(
        Task::class,           // Model of the final result (Task)
        ProjectUser::class,   // Intermediate table model (ProjectUser)
        'user_id',             // Foreign key on the intermediate table (ProjectUser)
        'project_id',          // Foreign key on the final result table (Task)
        'id',                  // Local key on the primary model (User)
        'project_id'           // Local key on the intermediate table (ProjectUser)
    );
}

}
