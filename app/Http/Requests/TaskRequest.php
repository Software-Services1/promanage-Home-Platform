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
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'stage'                => ['required', Rule::in(WorkTypes::STAGES)],
            'supervisor_id'        => ['nullable', 'exists:users,id'],
            'due_date'             => ['required', 'date'],
            'is_late'              => ['nullable', 'boolean'],
            'is_creative'          => ['nullable', 'boolean'],
            // مصمّم واحد أو أكثر، ولكلٍّ نوع عمله
            'assignees'            => ['required', 'array', 'min:1'],
            'assignees.*.user_id'  => ['required', 'exists:users,id'],
            'assignees.*.type'     => ['required', 'exists:task_types,key'],
        ];
    }

    public function messages(): array
    {
        return [
            'assignees.required' => 'أضِف مصمّماً واحداً على الأقل.',
            'assignees.*.user_id.required' => 'اختر المصمّم.',
            'assignees.*.type.required' => 'اختر نوع العمل لكل مصمّم.',
        ];
    }
}
