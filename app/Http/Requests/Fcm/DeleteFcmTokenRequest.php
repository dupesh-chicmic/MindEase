<?php

namespace App\Http\Requests\Fcm;

use Illuminate\Foundation\Http\FormRequest;

class DeleteFcmTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:512'],
        ];
    }
}
