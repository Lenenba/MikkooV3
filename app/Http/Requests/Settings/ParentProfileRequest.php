<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class ParentProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Champs du parent
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'birthdate'  => ['required', 'date'],
            'phone'      => ['required', 'string', 'max:15'],

            // L'objet d'adresse doit être un array
            'address'              => ['required', 'array'],
            'address.street'       => ['required', 'string', 'max:255'],
            'address.city'         => ['required', 'string', 'max:255'],
            'address.province'     => ['nullable', 'string', 'max:255'],
            'address.postal_code'  => ['required', 'string', 'max:20'],
            'address.country'      => ['required', 'string', 'max:255'],
            'address.latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'address.longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'preferences'          => ['nullable', 'string', 'max:1000'],
            'availability'         => ['nullable', 'string', 'max:1000'],
            'availability_notes'   => ['nullable', 'string', 'max:1000'],
            'children'             => ['nullable', 'array', 'min:1', 'max:10'],
            'children.*.name'       => ['nullable', 'string', 'max:120'],
            'children.*.age'        => ['nullable', 'integer', 'min:0', 'max:20'],
            'children.*.allergies'  => ['nullable', 'string', 'max:255'],
            'children.*.details'    => ['nullable', 'string', 'max:1000'],
            'children.*.photo'      => ['nullable', 'string', 'max:1000000'],
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
                    'age' => is_numeric($age) ? (int) $age : null,
                    'allergies' => $normalize($child['allergies'] ?? null),
                    'details' => $normalize($child['details'] ?? null),
                    'photo' => is_string($photo) && $photo !== '' ? $photo : null,
                ];
            }

            $this->merge(['children' => $normalizedChildren]);
        }

        $this->merge([
            'preferences' => $normalize($this->input('preferences')),
            'availability' => $normalize($this->input('availability')),
            'availability_notes' => $normalize($this->input('availability_notes')),
        ]);
    }
}
