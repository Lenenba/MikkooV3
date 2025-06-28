<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<string|int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number'                => ['required', 'string', 'max:255'],
            'babysitter_id'         => ['required', 'integer', 'exists:users,id'],
            'start_date'            => ['required', 'date'],
            'start_time'            => ['required', 'date_format:H:i'],
            'end_time'              => ['required', 'date_format:H:i', 'after:start_time'],
            'services'              => ['required', 'array', 'min:1'],
            'services.*.id'         => ['required', 'integer', 'exists:services,id'],
            'services.*.quantity'   => ['required', 'integer', 'min:1'],
            'services.*.price'      => ['required', 'numeric', 'min:0'],
            'notes'                 => ['nullable', 'string'],
            'subtotal'              => ['required', 'numeric', 'min:0'],
            'total_amount'          => ['required', 'numeric', 'min:0'],
        ];
    }
}
