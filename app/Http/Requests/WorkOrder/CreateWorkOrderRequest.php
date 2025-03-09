<?php

namespace App\Http\Requests\WorkOrder;

use App\Http\Requests\BaseFormRequest;

class CreateWorkOrderRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'space_id' => 'nullable|exists:spaces,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:corrective,preventive,emergency,inspection',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:pending,assigned,in_progress,on_hold,completed,cancelled',
            'requested_by' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'required|date|after:now',
            'estimated_hours' => 'required|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'completion_date' => 'nullable|date',
            'completion_notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'parts_used' => 'nullable|array',
            'parts_used.*.name' => 'required|string',
            'parts_used.*.quantity' => 'required|integer|min:1',
            'parts_used.*.cost' => 'required|numeric|min:0',
            'metadata' => 'nullable|array'
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required' => 'The asset is required.',
            'asset_id.exists' => 'The selected asset does not exist.',
            'space_id.exists' => 'The selected space does not exist.',
            'title.required' => 'The work order title is required.',
            'title.max' => 'The work order title cannot exceed 255 characters.',
            'description.required' => 'The work order description is required.',
            'type.required' => 'The work order type is required.',
            'type.in' => 'Invalid work order type selected.',
            'priority.required' => 'The priority level is required.',
            'priority.in' => 'Invalid priority level selected.',
            'status.required' => 'The work order status is required.',
            'status.in' => 'Invalid work order status selected.',
            'requested_by.required' => 'The requestor is required.',
            'requested_by.exists' => 'The selected requestor does not exist.',
            'assigned_to.exists' => 'The selected assignee does not exist.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'Invalid due date.',
            'due_date.after' => 'The due date must be in the future.',
            'estimated_hours.required' => 'The estimated hours are required.',
            'estimated_hours.numeric' => 'The estimated hours must be a number.',
            'estimated_hours.min' => 'The estimated hours cannot be negative.',
            'actual_hours.numeric' => 'The actual hours must be a number.',
            'actual_hours.min' => 'The actual hours cannot be negative.',
            'completion_date.date' => 'Invalid completion date.',
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
            'metadata.array' => 'Invalid metadata format.'
        ];
    }

    public function attributes(): array
    {
        return [
            'asset_id' => 'asset',
            'space_id' => 'space',
            'title' => 'work order title',
            'description' => 'work order description',
            'type' => 'work order type',
            'priority' => 'priority level',
            'status' => 'work order status',
            'requested_by' => 'requestor',
            'assigned_to' => 'assignee',
            'due_date' => 'due date',
            'estimated_hours' => 'estimated hours',
            'actual_hours' => 'actual hours',
            'completion_date' => 'completion date',
            'completion_notes' => 'completion notes',
            'cost' => 'cost',
            'parts_used' => 'parts used',
            'metadata' => 'metadata'
        ];
    }
} 