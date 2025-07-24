<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonRequest extends FormRequest
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
        // Mengambil objek 'person' dari parameter route.
        $person = $this->route('person');

        return [
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'death_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'father_id' => ['nullable', 'exists:people,id', 'different:'.$person->id, 'not_in:'.implode(',', $person->getDescendantIds())],
            'mother_id' => ['nullable', 'exists:people,id', 'different:'.$person->id, 'not_in:'.implode(',', $person->getDescendantIds())],
            'is_key_figure' => 'nullable|boolean',
        ];
    }
}