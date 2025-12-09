<?php

namespace Webkul\RestApi\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class TokenResetRequest extends FormRequest
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
            'phone_number' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'telegram_id' => 'nullable|string|max:255',
            'whatsapp_id' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'reset_method' => 'required|in:sms,whatsapp,telegram,email',
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
            'phone_number.regex' => 'Phone number must be in valid international format.',
            'telegram_id.max' => 'Telegram ID cannot exceed 255 characters.',
            'whatsapp_id.max' => 'WhatsApp ID cannot exceed 255 characters.',
            'email.email' => 'Email must be a valid email address.',
            'reset_method.required' => 'Reset method is required.',
            'reset_method.in' => 'Reset method must be one of: sms, whatsapp, telegram, email.',
        ];
    }
}
