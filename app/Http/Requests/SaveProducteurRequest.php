<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveProducteurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'genre_id' => 'required',
            'matricule' => 'required',
            'nom' => 'required',
            'prenoms' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'genre_id.required' => 'Selectionnez le genre',
            'matricule.required' => 'Le matricule est requis',
            'nom.required' => 'Le nom est requis',
            'prenoms.required' => 'Les prénoms sont requis',
        ];
    }
}
