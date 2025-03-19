<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    abstract public function rules(): array;

    protected function prepareForValidation(): void
    {
        if ($this->has('openid')) {
            // 解密 openid  
            $decryptedOpenid = decrypt($this->input('openid'));
            $this->merge([
                'openid' => $decryptedOpenid
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'code.required' => '微信授权码不能为空',
            'openid.required' => 'OpenID 不能为空',
        ];
    }

}
