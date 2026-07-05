<?php

namespace App\Http\Requests;

use App\Support\WorkTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user(); }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['required', Rule::in(array_keys(WorkTypes::TASKS))],
            'stage'       => ['required', Rule::in(WorkTypes::STAGES)],
            'user_id'     => ['required', 'exists:users,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'due_date'    => ['required', 'date'],
            'is_late'     => ['nullable', 'boolean'],
            'is_creative' => ['nullable', 'boolean'],
        ];
    }
}
