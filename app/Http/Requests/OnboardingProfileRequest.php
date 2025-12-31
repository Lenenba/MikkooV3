<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $isBabysitter = $user?->isBabysitter() ?? false;

        if ($isBabysitter) {
            return [
                'bio' => ['nullable', 'string', 'max:2000'],
                'experience' => ['nullable', 'string', 'max:2000'],
                'price_per_hour' => ['nullable', 'numeric', 'min:0'],
                'payment_frequency' => [
                    'nullable',
                    'string',
                    Rule::in(['per_task', 'daily', 'weekly', 'biweekly', 'monthly']),
                ],
                'services' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return [
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'children_ages' => ['nullable', 'string', 'max:255'],
            'preferences' => ['nullable', 'string', 'max:1000'],
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
            'bio' => $normalize($this->input('bio')),
            'experience' => $normalize($this->input('experience')),
            'services' => $normalize($this->input('services')),
            'children_ages' => $normalize($this->input('children_ages')),
            'preferences' => $normalize($this->input('preferences')),
        ]);
    }
}
