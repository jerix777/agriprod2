<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCampagneRequest extends FormRequest
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
            'annee' => 'required|unique:campagnes,annee',
        ];
    }

    public function messages()
    {
        return [
            'annee.required' => 'L\'annee de la campagne est requise',
            'annee.unique' => 'L\'annee de la campagne saisie existe déjà',
        ];
    }
}
