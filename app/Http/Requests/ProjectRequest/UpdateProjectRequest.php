<?php

namespace App\Http\Requests\ProjectRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Authorization handled in the controller or service
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $projectId = $this->route('project'); // Get the project ID from the route

        return [
            'name' => 'required|string|max:255|unique:projects,name,' . $projectId,
            'description' => 'nullable|string',
        ];
    }
}
