<?php

namespace App\Http\Requests;

use App\Support\WorkTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasAnyRole(['admin', 'supervisor']); }

    public function rules(): array
    {
        return [
            'title'     => ['required', 'string', 'max:255'],
            'type'      => ['required', Rule::in(array_keys(WorkTypes::MAINTENANCE))],
            'work_date' => ['required', 'date'],
            'status'    => ['required', Rule::in(WorkTypes::MAINTENANCE_STATUSES)],
            'user_id'   => ['nullable', 'exists:users,id'],
        ];
    }
}
