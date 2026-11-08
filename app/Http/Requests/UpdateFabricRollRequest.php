<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFabricRollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rollId = $this->route('fabric_roll')?->id ?? $this->route('fabricRoll')?->id ?? $this->route('id');

        return [
            'roll_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fabric_rolls', 'roll_number')->ignore($rollId),
            ],
            'buyer' => ['required', 'string', 'max:100'],
            'style' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:100'],
            'lot' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'stage' => ['required', 'string', 'max:50'],
            'measurement_mode' => ['nullable', 'string', 'in:direct_percentage,before_after'],
            'l1' => ['required', 'numeric'],
            'l2' => ['required', 'numeric'],
            'l3' => ['required', 'numeric'],
            'w1' => ['required', 'numeric'],
            'w2' => ['required', 'numeric'],
            'w3' => ['required', 'numeric'],
            'raw_measurements' => ['nullable', 'array'],
        ];
    }
}

