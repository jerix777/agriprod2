<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveProductionRequest extends FormRequest
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
            'parcelle_id' => 'required',
            'campagne_id' => 'required',
            'reference' => 'required',
            'date_de_production' => 'required',
            'quantite' => 'required',
            'qualite' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'parcelle_id.required' => 'Selectionner la parcelle',
            'campagne_id.required' => 'Selectionner la campagne',
            'reference.required' => 'La référence de production est requise',
            'date_de_production.required' => 'La date de production est requise',
            'quantite.required' => 'La quantite de production est requise',
            'qualite.required' => 'La qualite de production est requise',
        ];
    }
}
