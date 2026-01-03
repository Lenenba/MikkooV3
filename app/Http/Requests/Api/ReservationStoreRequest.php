<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReservationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'babysitter_id' => ['required', 'integer', 'exists:users,id'],
            'schedule_type' => ['required', 'string', 'in:single,recurring'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'recurrence_frequency' => ['nullable', 'required_if:schedule_type,recurring', 'in:daily,weekly,monthly'],
            'recurrence_interval' => ['nullable', 'integer', 'min:1'],
            'recurrence_days' => ['nullable', 'array', 'required_if:recurrence_frequency,weekly'],
            'recurrence_days.*' => ['integer', 'min:1', 'max:7'],
            'recurrence_end_date' => ['nullable', 'required_if:schedule_type,recurring', 'date', 'after_or_equal:start_date'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.id' => ['required', 'integer', 'exists:services,id'],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $days = $this->input('recurrence_days');
        if (is_array($days)) {
            $normalized = array_values(array_filter(array_map('intval', $days), function (int $day) {
                return $day >= 1 && $day <= 7;
            }));
            $this->merge(['recurrence_days' => $normalized]);
        }

        if (! $this->has('schedule_type')) {
            $this->merge(['schedule_type' => 'single']);
        }
    }
}
