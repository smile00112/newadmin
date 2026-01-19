<?php

namespace Webkul\RestApi\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class TelegramInitiateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'country_code'  => 'required|string|max:5',
            'phone_number'  => 'required|string|max:20',
            'device_name'   => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'country_code.required'  => 'Код страны обязателен.',
            'country_code.max'       => 'Код страны не может превышать 5 символов.',
            'phone_number.required'  => 'Номер телефона обязателен.',
            'phone_number.max'       => 'Номер телефона не может превышать 20 символов.',
            'device_name.required'   => 'Название устройства обязательно.',
            'device_name.max'        => 'Название устройства не может превышать 255 символов.',
        ];
    }

    /**
     * Get the full phone number with country code.
     */
    public function getFullPhoneNumber(): string
    {
        return $this->country_code . $this->phone_number;
    }
}
