<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class RegisterOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['parent', 'babysitter'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalize = static function (?string $value): ?string {
            if ($value === null) {
                return null;
            }
            $value = trim($value);
            return $value === '' ? null : $value;
        };

        $role = $normalize($this->input('role'));

        $this->merge([
            'first_name' => $normalize($this->input('first_name')),
            'last_name' => $normalize($this->input('last_name')),
            'email' => $normalize($this->input('email')),
            'role' => $role ? strtolower($role) : null,
        ]);
    }
}
