<?php

namespace App\Http\Requests\MaintenanceLog;

use App\Http\Requests\BaseFormRequest;

class CreateMaintenanceLogRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'schedule_id' => 'nullable|exists:maintenance_schedules,id',
            'type' => 'required|in:preventive,corrective,emergency',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'performed_by' => 'required|exists:users,id',
            'performed_at' => 'required|date',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:minutes,hours',
            'cost' => 'required|numeric|min:0',
            'parts_used' => 'nullable|array',
            'parts_used.*.name' => 'required|string',
            'parts_used.*.quantity' => 'required|integer|min:1',
            'parts_used.*.cost' => 'required|numeric|min:0',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array'
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required' => 'The asset is required.',
            'asset_id.exists' => 'The selected asset does not exist.',
            'schedule_id.exists' => 'The selected maintenance schedule does not exist.',
            'type.required' => 'The maintenance type is required.',
            'type.in' => 'Invalid maintenance type selected.',
            'title.required' => 'The maintenance title is required.',
            'title.max' => 'The maintenance title cannot exceed 255 characters.',
            'description.required' => 'The maintenance description is required.',
            'performed_by.required' => 'The technician who performed the maintenance is required.',
            'performed_by.exists' => 'The selected technician does not exist.',
            'performed_at.required' => 'The maintenance date and time is required.',
            'performed_at.date' => 'Invalid maintenance date and time.',
            'duration.required' => 'The maintenance duration is required.',
            'duration.integer' => 'The duration must be a whole number.',
            'duration.min' => 'The duration must be at least 1.',
            'duration_unit.required' => 'The duration unit is required.',
            'duration_unit.in' => 'Invalid duration unit selected.',
            'cost.required' => 'The maintenance cost is required.',
            'cost.numeric' => 'The cost must be a number.',
            'cost.min' => 'The cost cannot be negative.',
            'parts_used.array' => 'Invalid parts used format.',
            'parts_used.*.name.required' => 'Part name is required.',
            'parts_used.*.quantity.required' => 'Part quantity is required.',
            'parts_used.*.quantity.integer' => 'Part quantity must be a whole number.',
            'parts_used.*.quantity.min' => 'Part quantity must be at least 1.',
            'parts_used.*.cost.required' => 'Part cost is required.',
            'parts_used.*.cost.numeric' => 'Part cost must be a number.',
            'parts_used.*.cost.min' => 'Part cost cannot be negative.',
            'status.required' => 'The maintenance status is required.',
            'status.in' => 'Invalid maintenance status selected.',
            'metadata.array' => 'Invalid metadata format.'
        ];
    }

    public function attributes(): array
    {
        return [
            'asset_id' => 'asset',
            'schedule_id' => 'maintenance schedule',
            'type' => 'maintenance type',
            'title' => 'maintenance title',
            'description' => 'maintenance description',
            'performed_by' => 'technician',
            'performed_at' => 'maintenance date and time',
            'duration' => 'duration',
            'duration_unit' => 'duration unit',
            'cost' => 'maintenance cost',
            'parts_used' => 'parts used',
            'status' => 'maintenance status',
            'notes' => 'notes',
            'metadata' => 'metadata'
        ];
    }
} 