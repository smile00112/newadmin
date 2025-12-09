<?php

namespace Webkul\RestApi\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
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
            'verification_code' => 'required|string|size:6',
            'phone_number' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
            'telegram_id' => 'nullable|string|max:255',
            'whatsapp_id' => 'nullable|string|max:255',
            'verification_token' => 'required|string|max:255',
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
            'verification_code.required' => 'Verification code is required.',
            'verification_code.size' => 'Verification code must be exactly 6 digits.',
            'phone_number.regex' => 'Phone number must be in valid international format.',
            'telegram_id.max' => 'Telegram ID cannot exceed 255 characters.',
            'whatsapp_id.max' => 'WhatsApp ID cannot exceed 255 characters.',
            'verification_token.required' => 'Verification token is required.',
            'verification_token.max' => 'Verification token cannot exceed 255 characters.',
        ];
    }
}
