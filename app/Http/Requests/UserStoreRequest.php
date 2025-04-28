<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UserStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
        'over_name' => ['required', 'string', 'max:10'],
        'under_name' => ['required', 'string', 'max:10'],
        'over_name_kana' => ['required', 'string', 'regex:/^[ァ-ヴー]+$/u', 'max:30'],
        'under_name_kana' => ['required', 'string', 'regex:/^[ァ-ヴー]+$/u', 'max:30'],
        'mail_address' => ['required', 'email', 'max:100', 'unique:users,mail_address'],
        'sex' => ['required', 'in:1,2,3'],
        'old_year' => ['required', 'integer', 'min:2000', 'max:' . date('Y')],
        'old_month' => ['required', 'integer', 'between:1,12'],
        'old_day' => ['required', 'integer', 'between:1,31'],
        'role' => ['required', 'in:1,2,3,4'],
        'password' => ['required', 'string', 'min:8', 'max:30', 'confirmed'],
    ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $year = $this->input('old_year');
        $month = $this->input('old_month');
        $day = $this->input('old_day');

        // ① 年月日が存在するか
        if (!checkdate($month, $day, $year)) {
            // 日付エラーをまとめて1回追加
            if (!$validator->errors()->has('old_year')) {
                $validator->errors()->add('old_year', '生年月日が正しくありません。');
            }
            return;
        }

        // ② 2000-01-01以降かつ今日以前かをチェック
        $inputDate = Carbon::createFromDate($year, $month, $day)->startOfDay();
        $minDate = Carbon::create(2000, 1, 1)->startOfDay();
        $today = Carbon::today();

        if ($inputDate->lt($minDate)) {
            if (!$validator->errors()->has('old_year')) {
                $validator->errors()->add('old_year', '生年月日は2000年1月1日以降の日付を入力してください。');
            }
        }

        // 姓名が空の場合は1回だけエラーを追加
        $overName = $this->input('over_name');
        $underName = $this->input('under_name');
        if (empty($overName) || empty($underName)) {
            // 名前とカタカナの両方が空の場合にエラーメッセージをまとめて追加
            if (empty($overName) && empty($underName)) {
                if (!$validator->errors()->has('name_and_kana')) {
                    $validator->errors()->add('name_and_kana', '名前は必ず入力してください。');
                }
            } else {
                // 一方が空の場合は通常のエラーメッセージを追加
                if (!$validator->errors()->has('over_name')) {
                    $validator->errors()->add('over_name', '名前は必ず入力してください。');
                }
            }
        }

        // カタカナの姓名が空の場合は1回だけエラーを追加
        $overNameKana = $this->input('over_name_kana');
        $underNameKana = $this->input('under_name_kana');
        if (empty($overNameKana) || empty($underNameKana)) {
            // 名前とカタカナの両方が空の場合にエラーメッセージをまとめて追加
            if (empty($overNameKana) && empty($underNameKana)) {
                if (!$validator->errors()->has('name_and_kana')) {
                    $validator->errors()->add('name_and_kana', 'カタカナの名前は必ず入力してください。');
                }
            } else {
                // 一方が空の場合は通常のエラーメッセージを追加
                if (!$validator->errors()->has('over_name_kana')) {
                    $validator->errors()->add('over_name_kana', 'カタカナの名前は必ず入力してください。');
                }
            }
        }

        // 名前とカタカナをまとめて1回だけエラーを追加
        if (empty($overName) && empty($underName) && empty($overNameKana) && empty($underNameKana)) {
            if (!$validator->errors()->has('name_and_kana')) {
                $validator->errors()->add('name_and_kana', '名前とカタカナの両方を入力してください。');
            }
        }
    });
}

    public function messages()
{
    return [
        'over_name.required' => '名前は必ず入力してください。',
        'under_name.required' => '名前は必ず入力してください。',
        'over_name.string' => '・文字列で入力してください。',
        'under_name.string' => '・文字列で入力してください。',
        'over_name.max' => '・10文字以下で入力してください。',
        'under_name.max' => '・10文字以下で入力してください。',
        'over_name_kana.required' => 'カタカナの名前は必ず入力してください。',
        'under_name_kana.required' => 'カタカナの名前は必ず入力してください。',
        'over_name_kana.regex' => '・カタカナのみで入力してください。',
        'under_name_kana.regex' => '・カタカナのみで入力してください。',
        'over_name_kana.max' => '・30文字以下で入力してください。',
        'under_name_kana.max' => '・30文字以下で入力してください。',
        'mail_address.required' => 'メールアドレスは必ず入力してください。',
        'mail_address.email' => '・メールアドレス形式で入力してください。',
        'mail_address.unique' => '・登録済みのメールアドレスです。',
        'mail_address.max' => '・100文字以下で入力してください。',
        'sex.required' => '・必須項目です。',
        'sex.in' => '・男性、女性、その他のいずれかを選択してください。',
        'old_year.required' => '生年月日は必ず入力してください。',
        'old_year.integer' => '・年は数字で入力してください。',
        'old_year.min' => '・2000年1月1日以降の日付を入力してください。',
        'old_year.max' => '・現在の年より未来の日付は無効です。',
        'old_month.required' => '・必須項目です。',
        'old_day.required' => '・必須項目です。',
        'role.required' => '・必須項目です。',
        'role.in' => '・講師(国語)、講師(数学)、教師(英語)、生徒のいずれかを選択してください。',
        'password.required' => '・必須項目です。',
        'password.string' => '・文字列で入力してください。',
        'password.min' => '・8文字以上で入力してください。',
        'password.max' => '・30文字以下で入力してください。',
        'password.confirmed' => '・パスワード確認と一致していません。',
    ];
}
}
