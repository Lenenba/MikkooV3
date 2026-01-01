<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class BabysitterProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'birthdate'    => ['required', 'date'],
            'phone'        => ['required', 'string', 'max:15'],
            'bio'          => ['nullable', 'string'],
            'experience'   => ['nullable', 'string'],
            'price_per_hour' => ['required', 'numeric', 'min:5'],
            'payment_frequency' => ['required', 'string', 'in:per_task,daily,weekly,biweekly,monthly'],
            'services'     => ['nullable', 'string', 'max:1000'],
            'availability' => ['nullable', 'string', 'max:1000'],
            'availability_notes' => ['nullable', 'string', 'max:1000'],

            // L'objet d'adresse doit être un array
            'address'              => ['required', 'array'],
            'address.street'       => ['required', 'string', 'max:255'],
            'address.city'         => ['required', 'string', 'max:255'],
            'address.province'     => ['nullable', 'string', 'max:255'],
            'address.postal_code'  => ['required', 'string', 'max:20'],
            'address.country'      => ['required', 'string', 'max:255'],
            'address.latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'address.longitude'    => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalize = static function ($value): ?string {
            if ($value === null) {
                return null;
            }
            $value = trim((string) $value);
            return $value === '' ? null : $value;
        };

        $address = $this->input('address');
        if (is_array($address)) {
            $address['latitude'] = ($address['latitude'] ?? null) === '' ? null : $address['latitude'] ?? null;
            $address['longitude'] = ($address['longitude'] ?? null) === '' ? null : $address['longitude'] ?? null;
            $this->merge(['address' => $address]);
        }

        $this->merge([
            'bio' => $normalize($this->input('bio')),
            'experience' => $normalize($this->input('experience')),
            'services' => $normalize($this->input('services')),
            'availability' => $normalize($this->input('availability')),
            'availability_notes' => $normalize($this->input('availability_notes')),
        ]);
    }
}
