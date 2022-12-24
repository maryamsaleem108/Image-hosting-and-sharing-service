<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class loginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email'=>'required|email',
            'password'=>'required'
        ];
    }

    public function failedValidation(Validator $validator)

    {

        throw new HttpResponseException(response()->json([

            'success'   => false,
            'message'   => 'Inputs are not Valid',
            'data'      => $validator->errors()

        ]));

    }

    public function messages()

    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Invalid Email Address',
            'password.required' => 'Password is required'

        ];

    }
}
