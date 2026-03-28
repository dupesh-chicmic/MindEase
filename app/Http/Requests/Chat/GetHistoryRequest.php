<?php

namespace App\Http\Requests\Chat;

use App\Models\ChatThread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetHistoryRequest extends FormRequest
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
            'thread_id' => ['required', 'integer', 'exists:chat_threads,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $threadId = $this->input('thread_id');

            if ($threadId) {
                $thread = ChatThread::find($threadId);
                if ($thread && $thread->user_id !== Auth::guard('api')->id()) {
                    $validator->errors()->add('thread_id', 'This thread does not belong to you.');
                }
            }
        });
    }
}