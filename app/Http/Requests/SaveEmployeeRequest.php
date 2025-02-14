<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEmployeeRequest extends FormRequest
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
            'genre_id' => 'required',
            'matricule' => 'required|unique:employers',
            'nom' => 'required',
            'prenoms' => 'required',
            'fonction' => 'required',
            'email' => 'required|unique:employers,email',
            'photo' => '',
            'mot_de_passe' => '',
        ];
    }

    public function messages()
    {
        return [
            'matricule.required' => 'Le matricule de l\'employer est requis',
            'matricule.unique' => 'Le matricule saisi existe déjà',
            'genre_id.required' => 'veuillez selectionner le genre',
            'nom.required' => 'veuillez saisir le nom',
            'prenoms.required' => 'veuillez saisir le(s) prénom(s)',
            'fonction.required' => 'veuillez saisir la fonction',
            'email.required' => 'veuillez saisir l\'email',
            'email.email' => 'veuillez saisir un email valide',
        ];
    }
}
