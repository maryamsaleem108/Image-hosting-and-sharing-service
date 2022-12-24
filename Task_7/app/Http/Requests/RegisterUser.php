<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterUser extends FormRequest
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
        return [
            'name' => 'required|string|max:70|min:1',
            'age'=>'required|integer|digits_between:1,2',
            'email'=>'required|email|unique:users,email',
            'phone_number'=>'digits_between:11,13',
            'password'=>'required|max:8',
            'picturePath' => 'sometimes|image'

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
            'name.required' => 'Name is required',
            'name.string' => 'Name should be a string',
            'name.max' => 'Name cannot be longer than 70 characters',
            'age.required' => 'Age is required',
            'age.integer' => 'Age should be a number',
            'age.digits_between' => 'Invalid Age. You are not a Vampire :)',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid Email Syntax',
            'email.unique' => 'Email already exists',
            'phone_number.digits_between' => 'Invalid Phone Number',
            'password.required' => 'Password is required',
            'password.max' => 'Set a strong password',

        ];
    }

    public function filters()
    {
        return [
            'email' => 'trim|lowercase',
            'name' => 'trim|capitalize|escape'
        ];
    }
}
