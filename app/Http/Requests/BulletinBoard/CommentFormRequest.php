<?php

namespace App\Http\Requests\BulletinBoard;

use Illuminate\Foundation\Http\FormRequest;

class CommentFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'comment' => 'required|string|max:250',
        ];
    }

    public function messages()
    {
        return [
            'comment.required' => 'コメントは必ず入力してください。',
            'comment.string' => 'コメントは文字列で入力してください。',
            'comment.max' => 'コメントは250文字以内で入力してください。',
        ];
    }
}
