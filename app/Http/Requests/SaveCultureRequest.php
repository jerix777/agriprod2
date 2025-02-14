<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCultureRequest extends FormRequest
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
            'identifiant_unique' => 'required|unique:cultures,identifiant_unique',
            'nom_commun' => 'required',
            'nom_scientifique' => 'required|unique:cultures,nom_scientifique',
            'status' => '',
        ];
    }

    public function messages()
    {
        return [
            'nom_commun.required' => 'Le nom de la culture est requis',
            'identifiant_unique.required' => 'L\'identifiant unique de la culture est requis',
            'identifiant_unique.unique' => 'L\'identifiant unique de la culture existe déjà',
            'nom_scientifique.required' => 'Le nom scientifique de la culture est requis',
            'nom_scientifique.unique' => 'Ce nom scientifique de la culture existe déjà',
        ];
    }
}
