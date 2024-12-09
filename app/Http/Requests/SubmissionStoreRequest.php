<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmissionStoreRequest extends FormRequest
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
            'event_id' => 'required|exists:events,event_id',
            'scholar_id' => 'required|exists:scholars,scholar_id',
            'time_in_image_uuid' => 'required|string',
            'time_in_location' => 'required|string',
            'time_out_image_uuid' => 'required|string',
            'time_out_location' => 'required|string',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i|after:time_from',
        ];
    }
}
