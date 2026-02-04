<?php

namespace App\Modules\Repair\Requests;

use App\Modules\Repair\Models\DamageReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDamageReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('repair.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'machine_id' => ['required', 'exists:machines,id'],
            'damage_type' => ['required', 'string', 'max:255'],
            'damage_type_other' => ['nullable', 'string', 'max:255', 'required_if:damage_type,Other'],
            'department' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::in([
                DamageReport::PRIORITY_LOW,
                DamageReport::PRIORITY_MEDIUM,
                DamageReport::PRIORITY_HIGH,
                DamageReport::PRIORITY_CRITICAL,
            ])],
            'target_completed_at' => ['nullable', 'date', 'after_or_equal:today'],
            'assigned_technician_id' => ['nullable', 'exists:users,id'],
            'before_photos' => ['required', 'array', 'min:1'],
            'before_photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
