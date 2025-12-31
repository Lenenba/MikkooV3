<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'availability' => ['nullable', 'string', 'max:1000'],
            'availability_notes' => ['nullable', 'string', 'max:1000'],
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
            'availability' => $normalize($this->input('availability')),
            'availability_notes' => $normalize($this->input('availability_notes')),
        ]);
    }
}
