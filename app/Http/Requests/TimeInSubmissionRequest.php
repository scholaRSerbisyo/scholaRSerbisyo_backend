<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeInSubmissionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'event_id' => 'required|exists:events,event_id',
            'scholar_id' => 'required|exists:scholars,scholar_id',
            'time_in_location' => 'required|string',
            'time_in' => 'required|date_format:H:i',
            'time_in_image_uuid' => 'required|string',
            'time_in_image' => 'required|string',
            'description' => 'nullable|string',
        ];
    }
}

