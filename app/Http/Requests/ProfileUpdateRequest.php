<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama'     => ['sometimes', 'required_without:name', 'string', 'max:255'],
            'name'     => ['sometimes', 'required_without:nama', 'string', 'max:255'],
            'username' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique(User::class, 'username')->ignore($this->user()->id_user, 'id_user'),
            ],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class, 'email')->ignore($this->user()->id_user, 'id_user'),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ];
    }
}