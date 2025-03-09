<?php

namespace App\Http\Requests\MaintenanceSchedule;

class UpdateMaintenanceScheduleRequest extends CreateMaintenanceScheduleRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Make all fields optional for update
        foreach ($rules as $field => $rule) {
            $rules[$field] = str_replace('required|', '', $rule);
        }
        
        // Add validation for the schedule ID
        $rules['id'] = 'required|exists:maintenance_schedules,id';
        
        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'id.required' => 'The maintenance schedule ID is required.',
            'id.exists' => 'The selected maintenance schedule does not exist.'
        ]);
    }
} 