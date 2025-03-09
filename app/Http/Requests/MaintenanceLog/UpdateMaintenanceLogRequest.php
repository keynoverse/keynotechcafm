<?php

namespace App\Http\Requests\MaintenanceLog;

class UpdateMaintenanceLogRequest extends CreateMaintenanceLogRequest
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
        
        // Add validation for the log ID
        $rules['id'] = 'required|exists:maintenance_logs,id';
        
        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'id.required' => 'The maintenance log ID is required.',
            'id.exists' => 'The selected maintenance log does not exist.'
        ]);
    }
} 