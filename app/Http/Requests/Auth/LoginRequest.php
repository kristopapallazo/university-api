<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email-i është i detyrueshëm.',
            'email.email' => 'Formati i email-it nuk është i saktë.',
            'password.required' => 'Fjalëkalimi është i detyrueshëm.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Adresa email e pedagog-ut ose admin-it.',
                'example' => 'arjan.hoxha@uamd.edu.al',
            ],
            'password' => [
                'description' => 'Fjalëkalimi.',
                'example' => 'secret123',
            ],
        ];
    }
}
