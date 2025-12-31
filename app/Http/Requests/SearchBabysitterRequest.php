<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchBabysitterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'payment_frequency' => [
                'nullable',
                Rule::in(['per_task', 'daily', 'weekly', 'biweekly', 'monthly']),
            ],
            'sort' => [
                'nullable',
                Rule::in(['distance', 'rating', 'price_low', 'price_high', 'recent']),
            ],
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
            'name' => $normalize($this->input('name')),
            'city' => $normalize($this->input('city')),
            'country' => $normalize($this->input('country')),
            'min_price' => $normalize($this->input('min_price')),
            'max_price' => $normalize($this->input('max_price')),
            'min_rating' => $normalize($this->input('min_rating')),
            'payment_frequency' => $this->input('payment_frequency') === 'all'
                ? null
                : $normalize($this->input('payment_frequency')),
            'sort' => $this->input('sort') === 'default'
                ? null
                : $normalize($this->input('sort')),
        ]);
    }

    protected function passedValidation(): void
    {
        $minPrice = $this->input('min_price');
        $maxPrice = $this->input('max_price');

        if ($minPrice !== null && $maxPrice !== null) {
            $min = (float) $minPrice;
            $max = (float) $maxPrice;
            if ($min > $max) {
                $this->merge([
                    'min_price' => $max,
                    'max_price' => $min,
                ]);
            }
        }
    }
}
