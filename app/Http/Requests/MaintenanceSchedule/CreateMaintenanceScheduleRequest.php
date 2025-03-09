<?php

namespace App\Http\Requests\MaintenanceSchedule;

use App\Http\Requests\BaseFormRequest;

class CreateMaintenanceScheduleRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_date' => 'required|date|after:now',
            'frequency' => 'required|integer|min:1',
            'frequency_unit' => 'required|in:days,weeks,months,years',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:scheduled,completed,cancelled,overdue',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array'
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required' => 'The asset is required.',
            'asset_id.exists' => 'The selected asset does not exist.',
            'title.required' => 'The maintenance title is required.',
            'title.max' => 'The maintenance title cannot exceed 255 characters.',
            'scheduled_date.required' => 'The scheduled date is required.',
            'scheduled_date.date' => 'The scheduled date must be a valid date.',
            'scheduled_date.after' => 'The scheduled date must be in the future.',
            'frequency.required' => 'The maintenance frequency is required.',
            'frequency.integer' => 'The frequency must be a number.',
            'frequency.min' => 'The frequency must be at least 1.',
            'frequency_unit.required' => 'The frequency unit is required.',
            'frequency_unit.in' => 'The frequency unit must be days, weeks, months, or years.',
            'priority.required' => 'The priority level is required.',
            'priority.in' => 'The priority must be low, medium, or high.',
            'status.required' => 'The status is required.',
            'status.in' => 'Invalid status value.',
            'assigned_to.exists' => 'The selected user does not exist.',
            'metadata.array' => 'The metadata must be a valid JSON object.'
        ];
    }

    public function attributes(): array
    {
        return [
            'asset_id' => 'asset',
            'title' => 'maintenance title',
            'description' => 'description',
            'scheduled_date' => 'scheduled date',
            'frequency' => 'frequency',
            'frequency_unit' => 'frequency unit',
            'priority' => 'priority level',
            'status' => 'status',
            'assigned_to' => 'assigned user',
            'notes' => 'notes',
            'metadata' => 'metadata'
        ];
    }
} 