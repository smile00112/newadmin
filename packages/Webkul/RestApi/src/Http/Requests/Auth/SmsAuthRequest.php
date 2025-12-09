<?php

namespace Webkul\RestApi\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SmsAuthRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'phone_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
            'country_code' => 'required|string|size:2',
            'device_name' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must be in valid international format.',
            'country_code.required' => 'Country code is required.',
            'country_code.size' => 'Country code must be exactly 2 characters.',
            'device_name.required' => 'Device name is required.',
            'device_name.max' => 'Device name cannot exceed 255 characters.',
        ];
    }
}
