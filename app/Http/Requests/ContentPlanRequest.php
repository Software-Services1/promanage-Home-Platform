<?php

namespace App\Http\Requests;

use App\Support\WorkTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentPlanRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user(); }

    public function rules(): array
    {
        return [
            'platform'       => ['required', Rule::in(WorkTypes::PLATFORMS)],
            'company_name'   => ['nullable', Rule::in(WorkTypes::COMPANIES)],
            'plan_date'      => ['required', 'date'],
            'plan_time'      => ['nullable', 'string'],
            'content_type'   => ['required', Rule::in(WorkTypes::CONTENT_TYPES)],
            'post_type'      => ['required', Rule::in(WorkTypes::POST_TYPES)],
            'design_content' => ['nullable', 'string'],
            'design_text'    => ['nullable', 'string'],
            'caption'        => ['nullable', 'string'],
            'post_text'      => ['nullable', 'string'],
            'reference_link' => ['nullable', 'url', 'max:2048'],
            'reference_file' => ['nullable', 'file', 'max:5120'],
            'notes'          => ['nullable', 'string'],
            'status'         => ['required', Rule::in(WorkTypes::PLAN_STATUSES)],
            'assigned_to'    => ['nullable', 'exists:users,id'],
            'work_type'      => ['nullable', 'exists:task_types,key'],
            'supervisor_id'  => ['nullable', 'exists:users,id'],
            // مصمّمون مرتّبون، لكلٍّ نوع عمله (اختياري بالكامل)
            'designers'            => ['nullable', 'array'],
            'designers.*.user_id'  => ['required_with:designers', 'exists:users,id'],
            'designers.*.work_type'=> ['nullable', 'exists:task_types,key'],
        ];
    }

    public function messages(): array
    {
        return ['designers.*.user_id.required_with' => 'اختر المصمّم في كل صفّ.'];
    }
}
