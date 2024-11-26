<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaranggayStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'baranggay_name' => 'required|string|max:255',
            'address' => 'required|string|max:255'
        ];
    }
}