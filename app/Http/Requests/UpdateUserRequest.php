<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
            ],

            'email' => 'required|email|unique:users,email',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => "Anh bạn à, hãy điền :attribute vào chỗ trống đi nào ",
            'string' => "Anh bạn à, hãy điền CHỮ ",
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'tên',
        ];
    }
}