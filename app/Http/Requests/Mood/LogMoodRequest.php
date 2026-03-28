<?php

namespace App\Http\Requests\Mood;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LogMoodRequest extends FormRequest
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
            'mood_score' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'mood_label' => ['sometimes', 'nullable', 'string', 'max:255'],
            'emoji' => ['sometimes', 'nullable', 'string', 'max:32'],
            'sleep_score' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'stress_score' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'productivity_score' => ['sometimes', 'nullable', 'integer', 'between:1,5'],
            'ate_well' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $keys = ['mood_score', 'mood_label', 'emoji', 'sleep_score', 'stress_score', 'productivity_score', 'ate_well'];
            $has = false;
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    $has = true;
                    break;
                }
            }
            if (! $has) {
                $validator->errors()->add('payload', 'At least one field is required.');
            }
        });
    }
}
