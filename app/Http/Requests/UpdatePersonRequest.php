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
        // --- PERBAIKAN KRITIS ---
        // Panggil method 'update' yang ada di PersonPolicy.
        // 'person' diambil dari nama parameter di route: {person}.
        return $this->user()->can('update', $this->route('person'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $person = $this->route('person');

        return [
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'phone_number' => 'nullable|string|max:20',
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