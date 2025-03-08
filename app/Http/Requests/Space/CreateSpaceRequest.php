<?php

namespace App\Http\Requests\Space;

use App\Http\Requests\BaseFormRequest;

class CreateSpaceRequest extends BaseFormRequest
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
            'floor_id' => 'required|exists:floors,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:spaces,code',
            'type' => 'required|string|in:office,meeting,storage,common,facility',
            'area' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'status' => 'required|string|in:active,inactive,maintenance,occupied,vacant',
            'metadata' => 'nullable|json'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'floor_id.required' => 'Floor ID is required.',
            'floor_id.exists' => 'Selected floor does not exist.',
            'name.required' => 'Space name is required.',
            'name.max' => 'Space name cannot exceed 255 characters.',
            'code.unique' => 'Space code must be unique.',
            'type.required' => 'Space type is required.',
            'type.in' => 'Invalid space type provided.',
            'area.numeric' => 'Area must be a number.',
            'area.min' => 'Area cannot be negative.',
            'capacity.integer' => 'Capacity must be an integer.',
            'capacity.min' => 'Capacity cannot be negative.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status provided.',
            'metadata.json' => 'Metadata must be a valid JSON string.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'floor_id' => 'floor',
            'name' => 'space name',
            'code' => 'space code',
            'type' => 'space type',
            'area' => 'area',
            'capacity' => 'capacity',
            'status' => 'status',
            'metadata' => 'metadata'
        ];
    }
} 