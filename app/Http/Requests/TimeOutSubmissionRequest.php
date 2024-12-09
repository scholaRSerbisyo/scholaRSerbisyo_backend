<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeOutSubmissionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'submission_id' => 'required|exists:submissions,submission_id',
            'time_out_location' => 'required|string',
            'time_out' => 'required|date_format:H:i|after:time_in',
            'time_out_image_uuid' => 'required|string',
            'time_out_image' => 'required|string',
        ];
    }
}

