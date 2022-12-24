<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
{
//    /**
//     * Determine if the user is authorized to make this request.
//     *
//     * @return bool
//     */
//    public function authorize()
//    {
//        return false;
//    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return[
            'name' => 'string|max:70|min:1',
            'age'=>'integer|digits_between:1,2',
            'email'=>'email|unique:users,email',
            'phone_number'=>'digits_between:11,13',
            'password'=>'max:8',
            'picturePath' => 'image'
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
            'name.string' => 'Name should be a string',
            'name.max' => 'Name cannot be longer than 70 characters',
            'age.integer' => 'Age should be a number',
            'age.digits_between' => 'Invalid Age. You are not a Vampire :)',
            'email.email' => 'Invalid Email Syntax',
            'email.unique' => 'Email already exists',
            'password.max' => 'Set a strong password',
        ];
    }
}
