<?php

namespace App\Http\Requests\Floor;

use App\Http\Requests\BaseFormRequest;

class CreateFloorRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:floors,code',
            'level' => 'required|integer',
            'description' => 'nullable|string',
            'floor_plan_url' => 'nullable|string|max:255',
            'area' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'accessibility_features' => 'nullable|json',
            'emergency_exits' => 'nullable|json',
            'facilities' => 'nullable|json',
            'status' => 'required|string|in:active,inactive,maintenance,renovation,closed',
            'occupancy_status' => 'required|string|in:occupied,vacant,partial,under_maintenance',
            'floor_type' => 'required|string|in:office,retail,residential,parking,mechanical,mixed_use,storage',
            'metadata' => 'nullable|json'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'building_id.required' => 'Building ID is required.',
            'building_id.exists' => 'Selected building does not exist.',
            'name.required' => 'Floor name is required.',
            'name.max' => 'Floor name cannot exceed 255 characters.',
            'code.unique' => 'Floor code must be unique.',
            'code.max' => 'Floor code cannot exceed 50 characters.',
            'level.required' => 'Floor level is required.',
            'level.integer' => 'Floor level must be an integer.',
            'floor_plan_url.max' => 'Floor plan URL cannot exceed 255 characters.',
            'area.numeric' => 'Area must be a number.',
            'area.min' => 'Area cannot be negative.',
            'capacity.integer' => 'Capacity must be an integer.',
            'capacity.min' => 'Capacity cannot be negative.',
            'accessibility_features.json' => 'Accessibility features must be a valid JSON string.',
            'emergency_exits.json' => 'Emergency exits must be a valid JSON string.',
            'facilities.json' => 'Facilities must be a valid JSON string.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status provided.',
            'occupancy_status.required' => 'Occupancy status is required.',
            'occupancy_status.in' => 'Invalid occupancy status provided.',
            'floor_type.required' => 'Floor type is required.',
            'floor_type.in' => 'Invalid floor type provided.',
            'metadata.json' => 'Metadata must be a valid JSON string.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'building_id' => 'building',
            'name' => 'floor name',
            'code' => 'floor code',
            'level' => 'floor level',
            'description' => 'description',
            'floor_plan_url' => 'floor plan URL',
            'area' => 'area',
            'capacity' => 'capacity',
            'accessibility_features' => 'accessibility features',
            'emergency_exits' => 'emergency exits',
            'facilities' => 'facilities',
            'status' => 'status',
            'occupancy_status' => 'occupancy status',
            'floor_type' => 'floor type',
            'metadata' => 'metadata'
        ];
    }
} 