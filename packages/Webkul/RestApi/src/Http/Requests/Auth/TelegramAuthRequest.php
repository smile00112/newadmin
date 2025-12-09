<?php

namespace Webkul\RestApi\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class TelegramAuthRequest extends FormRequest
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
            'telegram_id' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
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
            'telegram_id.required' => 'Telegram ID is required.',
            'telegram_id.max' => 'Telegram ID cannot exceed 255 characters.',
            'username.max' => 'Username cannot exceed 255 characters.',
            'first_name.max' => 'First name cannot exceed 255 characters.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',
            'device_name.required' => 'Device name is required.',
            'device_name.max' => 'Device name cannot exceed 255 characters.',
        ];
    }
}
