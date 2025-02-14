<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAdminRequest extends FormRequest
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
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email|unique:email,users',
            'password' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'le nom de l\'utilisateur est requis',
            'surname.required' => 'le prenom de l\'utilisateur est requis',
            'email.required' => 'Le mail est requis',
            'email.unique' => 'Cette adresse mail est liée à un autre compte',
            'email.email' => 'Le mail saisie n\'est pas valide',
            'password.required' => 'le mot de passe est requis',
        ];
    }
}
