<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectUser extends Pivot
{


        // The table associated with the model.
        protected $table = 'project_user';



        // Define the casts for your attributes if needed
        protected $casts = [
            'last_activity' => 'datetime',
            'contribution_hours' => 'integer',
        ];

        // Example of a custom method if needed
        public function updateLastActivity()
        {
            $this->last_activity = now();
            $this->save();
        }


    }

