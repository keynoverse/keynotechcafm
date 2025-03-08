<?php

namespace App\Http\Requests\Floor;

use Illuminate\Validation\Rule;

class UpdateFloorRequest extends CreateFloorRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Make all required fields optional for updates
        $rules['building_id'] = 'sometimes|exists:buildings,id';
        $rules['name'] = 'sometimes|string|max:255';
        $rules['level'] = 'sometimes|integer';
        $rules['status'] = 'sometimes|string|in:active,inactive,maintenance,renovation,closed';
        $rules['occupancy_status'] = 'sometimes|string|in:occupied,vacant,partial,under_maintenance';
        $rules['floor_type'] = 'sometimes|string|in:office,retail,residential,parking,mechanical,mixed_use,storage';
        
        // Update unique rule for code to exclude current floor
        $rules['code'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('floors', 'code')->ignore($this->route('id'))
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.unique' => 'The provided floor code is already in use by another floor.'
        ]);
    }
} 