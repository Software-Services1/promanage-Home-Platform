<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-users');
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password'  => [$id ? 'nullable' : 'required', 'string', 'min:6'],
            'role'      => ['required', Rule::in(['admin', 'supervisor', 'designer', 'editor'])],
            'salary'    => ['nullable', 'numeric', 'min:0'],
            'target'    => ['nullable', 'integer', 'min:0'],
            'supervisor_share' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'join_date' => ['nullable', 'date'],
        ];
    }
}
