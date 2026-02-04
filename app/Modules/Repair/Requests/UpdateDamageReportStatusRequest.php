<?php

namespace App\Modules\Repair\Requests;

use App\Modules\Repair\Models\DamageReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDamageReportStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('repair.update-status') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                DamageReport::STATUS_UPLOADED_BY_OPERATOR,
                DamageReport::STATUS_RECEIVED_BY_FOREMAN,
                DamageReport::STATUS_APPROVED_BY_MANAGER,
                DamageReport::STATUS_ON_FIXING_PROGRESS,
                DamageReport::STATUS_DONE_FIXING,
                // Backward compatibility
                DamageReport::STATUS_WAITING,
                DamageReport::STATUS_IN_PROGRESS,
                DamageReport::STATUS_DONE,
            ])],
            'notes' => ['nullable', 'string'],
            'assigned_technician_id' => [
                'nullable',
                'exists:users,id',
                'required_if:status,' . DamageReport::STATUS_APPROVED_BY_MANAGER,
            ],
            'after_photos' => [
                'required_if:status,' . DamageReport::STATUS_DONE_FIXING,
                'required_if:status,' . DamageReport::STATUS_DONE, // Backward compatibility
                'array',
                'min:1'
            ],
            'after_photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
