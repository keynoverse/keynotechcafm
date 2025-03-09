<?php

namespace App\Http\Requests\WorkOrder;

class UpdateWorkOrderRequest extends CreateWorkOrderRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Make all fields optional for update
        foreach ($rules as $field => $rule) {
            if (!str_starts_with($field, 'parts_used.')) {
                $rules[$field] = str_replace('required|', '', $rule);
            }
        }
        
        // Add validation for the work order ID
        $rules['id'] = 'required|exists:work_orders,id';
        
        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'id.required' => 'The work order ID is required.',
            'id.exists' => 'The selected work order does not exist.'
        ]);
    }
} 