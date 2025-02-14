<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveParcelleRequest extends FormRequest
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
            'producteur_id' => 'required',
            'reference' => 'required|unique:parcelles,reference',
            'localisation' => '',
            'superficie' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'producteur_id.required' => 'Veuillez sélectionner le propriétaire de la parcelle',
            'reference.required' => 'La référence de la parcelle est requis',
            'reference.unique' => 'La référence de la parcelle existe déjà',
            'superficie.required' => 'La superficie de la parcelle est requise',
        ];
    }
}
