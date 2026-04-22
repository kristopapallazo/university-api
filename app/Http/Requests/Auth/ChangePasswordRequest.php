<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Fjalëkalimi aktual është i detyrueshëm.',
            'current_password.current_password' => 'Fjalëkalimi aktual nuk është i saktë.',
            'new_password.required' => 'Fjalëkalimi i ri është i detyrueshëm.',
            'new_password.min' => 'Fjalëkalimi i ri duhet të ketë të paktën 8 karaktere.',
            'new_password.confirmed' => 'Konfirmimi i fjalëkalimit nuk përputhet.',
        ];
    }
}
