<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Project;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   $projectId = $request->route('projectId'); // الحصول على معرف المشروع من الرابط

        if ($projectId) {
            // الحصول على المشروع
            $project = Project::find($projectId);

            // التحقق مما إذا كان المستخدم جزءًا من المشروع
            if ($project && $userProject = $project->users()->where('user_id', auth()->id())->first()) {
                // تحديث `last_activity`
                $userProject->pivot->last_activity = now();
                $userProject->pivot->save();
            }
        }
        return $next($request);
    }
}
