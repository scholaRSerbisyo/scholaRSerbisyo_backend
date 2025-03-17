<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventValidationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_id' => 'required|exists:admins,admin_id',
            'admin_type_name' => 'required|string',
            'event_image_uuid' => 'required|string',
            'event_name' => 'required|string',
            'description' => 'required|string',
            'date' => 'required|date',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i|after:time_from',
            'location' => 'required|string',
            'event_type_id' => 'required|exists:event_types,event_type_id',
            'school_id' => [
                'nullable',
                'exists:schools,school_id',
                function ($attribute, $value, $fail) {
                    if ($this->input('event_type_id') === 2 && is_null($value)) {
                        $fail('School ID is required for school events.');
                    }
                },
            ],
            'baranggay_id' => [
                'nullable',
                'exists:baranggays,baranggay_id',
                function ($attribute, $value, $fail) {
                    if ($this->input('event_type_id') === 3 && is_null($value)) {
                        $fail('Barangay ID is required for community events.');
                    }
                },
            ],
        ];
    }
}