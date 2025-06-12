<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // 誰でもこのリクエストを許可
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'over_name' => 'required|string|max:10',
            'under_name' => 'required|string|max:10',
            'over_name_kana' => 'required|string|regex:/^[\p{Katakana}\s]+$/u|max:30',
            'under_name_kana' => 'required|string|regex:/^[\p{Katakana}\s]+$/u|max:30',
            'mail_address' => 'required|email|unique:users,mail_address|max:100',
            'sex' => 'required|in:男性,女性,その他',
            'old_year' => 'required|integer|digits:4|before_or_equal:today', // 4桁の数字（年）を確認
            'old_month' => 'required|integer|min:1|max:12', // 月が1〜12の間であることを確認
            'old_day' => 'required|integer|min:1|max:31', // 日が1〜31の間であることを確認
            'role' => 'required|in:講師(国語),講師(数学),教師(英語),生徒',
            'password' => 'required|string|min:8|max:30|confirmed', // 確認用パスワードが一致しているか
        ];
    }
}
