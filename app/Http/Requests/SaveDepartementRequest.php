<?php
/**
 * Class to manage Departements
 *
 * @category Models
 *
 * @author   Jérix<jerix@agripro.com>
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDepartementRequest extends FormRequest
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
            'libelle' => 'required|unique:departements,libelle',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages()
    {
        return [
            'libelle.required' => 'Le nom du département est requis',
            'libelle.unique' => 'Le nom du département saisi existe déjà',
        ];
    }
}
