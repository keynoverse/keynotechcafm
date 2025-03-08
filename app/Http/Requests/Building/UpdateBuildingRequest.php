<?php

namespace App\Http\Requests\Building;

use Illuminate\Validation\Rule;

class UpdateBuildingRequest extends CreateBuildingRequest
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
        $rules['address'] = 'sometimes|string|max:255';
        $rules['city'] = 'sometimes|string|max:100';
        $rules['state'] = 'sometimes|string|max:100';
        $rules['country'] = 'sometimes|string|max:100';
        $rules['postal_code'] = 'sometimes|string|max:20';
        $rules['occupancy_status'] = 'sometimes|string|in:occupied,vacant,partial,under_maintenance';
        $rules['building_type'] = 'sometimes|string|in:commercial,residential,industrial,mixed_use,educational,healthcare';
        $rules['status'] = 'sometimes|string|in:active,inactive,maintenance';
        
        // Update unique rule for code to exclude current building
        $rules['code'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('buildings', 'code')->ignore($this->route('id'))
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.unique' => 'The provided building code is already in use by another building.'
        ]);
    }
} 