<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_image_uuid' => 'required|string',
            'event_name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i|after:time_from',
            'location' => 'required|string|max:255',
            'status' => 'required|string|in:upcoming,ongoing,completed',
            'event_type_id' => 'required|exists:event_types,event_type_id',
            'school_id' => 'required_if:event_type_id,2|exists:schools,school_id',
            'baranggay_id' => 'required_if:event_type_id,3|exists:baranggays,baranggay_id',
            'image' => 'required|string',
        ];
    }
}
