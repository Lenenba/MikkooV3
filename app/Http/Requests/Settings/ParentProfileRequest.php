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
        ];
    }
}
