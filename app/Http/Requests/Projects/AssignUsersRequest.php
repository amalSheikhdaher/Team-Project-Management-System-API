<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class AssignUsersRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'users' => 'required|array',
            'users.*.id' => 'required|exists:users,id', // Check if the user ID exists in the users table
            'users.*.role' => 'required|string|in:super admin,manager,developer,tester', // Ensure valid role
        ];
    }

    public function messages(): array
    {
        return [
            'users.required' => 'You must provide users to assign.',
            'users.*.id.exists' => 'One or more user IDs are invalid.',
            'users.*.role.in' => 'Invalid role provided. Accepted roles are: Super Admin, Manager, Developer, Tester.',
        ];
    }
}
