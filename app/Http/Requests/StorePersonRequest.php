<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Kita set true karena otorisasi sudah ditangani oleh Policy atau Middleware.
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
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'phone_number' => 'nullable|string|max:20', // <-- TAMBAHKAN INI
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'death_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string', // Aturan ini sudah benar (tanpa batas max)
            'father_id' => 'nullable|exists:people,id',
            'mother_id' => 'nullable|exists:people,id',
            'is_key_figure' => 'nullable|boolean',
        ];
    }
}