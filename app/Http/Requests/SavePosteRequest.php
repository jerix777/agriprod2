<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePosteRequest extends FormRequest
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
            'departement_id' => 'required',
            'role_id' => 'required',
            'nom_reseau' => 'required|unique:postes,nom_reseau',
            'numero_de_serie' => 'required|unique:postes,numero_de_serie',
            'description' => 'required',
            'proprietaire' => '',
            'status' => '',
        ];
    }

    public function messages()
    {
        return [
            'departement_id.required' => 'Selectionnez un département',
            'role_id.required' => 'Selectionnez un rôle',
            'nom_reseau.required' => 'Sasir le Nom reseau identifiant le Poste',
            'nom_reseau.unique' => 'Le Nom reseau saisi existe déjà',
            'numero_de_serie.required' => 'Sasir le Numéro identifiant le Poste',
            'numero_de_serie.unique' => 'Le Numéro saisi existe déjà',
            'description.required' => 'Saisir une description du Poste',
        ];
    }
}
