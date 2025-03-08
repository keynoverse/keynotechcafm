<?php

namespace App\Http\Requests\Building;

use App\Http\Requests\BaseFormRequest;

class CreateBuildingRequest extends BaseFormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:buildings,code',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'year_built' => 'nullable|integer|min:1800|max:' . date('Y'),
            'total_floors' => 'nullable|integer|min:1',
            'total_area' => 'nullable|numeric|min:0',
            'occupancy_status' => 'required|string|in:occupied,vacant,partial,under_maintenance',
            'building_type' => 'required|string|in:commercial,residential,industrial,mixed_use,educational,healthcare',
            'status' => 'required|string|in:active,inactive,maintenance',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'facilities_contact' => 'nullable|string|max:255',
            'facilities_phone' => 'nullable|string|max:20',
            'operating_hours' => 'nullable|json',
            'metadata' => 'nullable|json'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Building name is required.',
            'name.max' => 'Building name cannot exceed 255 characters.',
            'code.unique' => 'Building code must be unique.',
            'code.max' => 'Building code cannot exceed 50 characters.',
            'address.required' => 'Building address is required.',
            'address.max' => 'Address cannot exceed 255 characters.',
            'city.required' => 'City is required.',
            'city.max' => 'City name cannot exceed 100 characters.',
            'state.required' => 'State is required.',
            'state.max' => 'State name cannot exceed 100 characters.',
            'country.required' => 'Country is required.',
            'country.max' => 'Country name cannot exceed 100 characters.',
            'postal_code.required' => 'Postal code is required.',
            'postal_code.max' => 'Postal code cannot exceed 20 characters.',
            'latitude.numeric' => 'Latitude must be a number.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.numeric' => 'Longitude must be a number.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'year_built.integer' => 'Year built must be an integer.',
            'year_built.min' => 'Year built cannot be earlier than 1800.',
            'year_built.max' => 'Year built cannot be later than current year.',
            'total_floors.integer' => 'Total floors must be an integer.',
            'total_floors.min' => 'Total floors must be at least 1.',
            'total_area.numeric' => 'Total area must be a number.',
            'total_area.min' => 'Total area cannot be negative.',
            'occupancy_status.required' => 'Occupancy status is required.',
            'occupancy_status.in' => 'Invalid occupancy status provided.',
            'building_type.required' => 'Building type is required.',
            'building_type.in' => 'Invalid building type provided.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status provided.',
            'emergency_contact.max' => 'Emergency contact name cannot exceed 255 characters.',
            'emergency_phone.max' => 'Emergency phone number cannot exceed 20 characters.',
            'facilities_contact.max' => 'Facilities contact name cannot exceed 255 characters.',
            'facilities_phone.max' => 'Facilities phone number cannot exceed 20 characters.',
            'operating_hours.json' => 'Operating hours must be a valid JSON string.',
            'metadata.json' => 'Metadata must be a valid JSON string.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'building name',
            'code' => 'building code',
            'description' => 'description',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'country' => 'country',
            'postal_code' => 'postal code',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'year_built' => 'year built',
            'total_floors' => 'total floors',
            'total_area' => 'total area',
            'occupancy_status' => 'occupancy status',
            'building_type' => 'building type',
            'status' => 'status',
            'emergency_contact' => 'emergency contact',
            'emergency_phone' => 'emergency phone',
            'facilities_contact' => 'facilities contact',
            'facilities_phone' => 'facilities phone',
            'operating_hours' => 'operating hours',
            'metadata' => 'metadata'
        ];
    }
} 