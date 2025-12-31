<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
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

        $this->merge([
            'street' => $normalize($this->input('street')),
            'city' => $normalize($this->input('city')),
            'province' => $normalize($this->input('province')),
            'postal_code' => $normalize($this->input('postal_code')),
            'country' => $normalize($this->input('country')),
            'latitude' => $normalize($this->input('latitude')),
            'longitude' => $normalize($this->input('longitude')),
        ]);
    }
}
