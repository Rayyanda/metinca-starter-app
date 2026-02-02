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
                DamageReport::STATUS_WAITING,
                DamageReport::STATUS_IN_PROGRESS,
                DamageReport::STATUS_DONE,
            ])],
            'notes' => ['nullable', 'string'],
            'after_photos' => ['required_if:status,' . DamageReport::STATUS_DONE, 'array', 'min:1'],
            'after_photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
