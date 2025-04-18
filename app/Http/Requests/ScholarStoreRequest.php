<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScholarStoreRequest extends FormRequest
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
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'birthdate' => 'required|date',
            'age' => 'required|string',
            'gender' => 'required|in:male,female',
            'course' => 'required|string',
            'mobilenumber' => 'required|string',
            'yearlevel' => 'required|string',
            'scholar_type_id' => 'required|exists:scholar_types,scholar_type_id',
            'user_id' => 'exists:users,user_id',
            'school_id' => 'required|exists:schools,school_id',
            'baranggay_id' => 'required|exists:baranggays,baranggay_id'
        ];
    }
}
