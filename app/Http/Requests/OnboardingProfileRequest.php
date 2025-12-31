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
            'children' => ['required', 'array', 'min:1', 'max:10'],
            'children.*.name' => ['nullable', 'string', 'max:120'],
            'children.*.age' => ['nullable', 'integer', 'min:0', 'max:20'],
            'children.*.allergies' => ['nullable', 'string', 'max:255'],
            'children.*.details' => ['nullable', 'string', 'max:1000'],
            'children.*.photo' => ['nullable', 'string', 'max:1000000'],
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

        $children = $this->input('children');
        if (is_array($children)) {
            $normalizedChildren = [];
            foreach ($children as $child) {
                if (! is_array($child)) {
                    $normalizedChildren[] = [
                        'name' => null,
                        'age' => null,
                        'allergies' => null,
                        'details' => null,
                        'photo' => null,
                    ];
                    continue;
                }

                $age = $child['age'] ?? null;
                $photo = $child['photo'] ?? null;
                $normalizedChildren[] = [
                    'name' => $normalize($child['name'] ?? null),
                    'age' => is_numeric($age) ? (int) $age : $age,
                    'allergies' => $normalize($child['allergies'] ?? null),
                    'details' => $normalize($child['details'] ?? null),
                    'photo' => is_string($photo) && $photo !== '' ? $photo : null,
                ];
            }

            $this->merge(['children' => $normalizedChildren]);
        }

        $this->merge([
            'bio' => $normalize($this->input('bio')),
            'experience' => $normalize($this->input('experience')),
            'services' => $normalize($this->input('services')),
            'preferences' => $normalize($this->input('preferences')),
        ]);
    }
}
