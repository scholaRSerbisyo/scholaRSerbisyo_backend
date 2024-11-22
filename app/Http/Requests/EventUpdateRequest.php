<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventUpdateRequest extends FormRequest
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
            'event_name' => 'required|string',
            'description' => 'required|string',
            'date' => 'required|date',
            'time_from' => 'required|string',
            'time_to' => 'required|string',
            'location' => 'required|string',
            'status' => 'required|string',
            'event_type_id' => 'required|exists:event_types,event_type_id',
        ];
    }
}
