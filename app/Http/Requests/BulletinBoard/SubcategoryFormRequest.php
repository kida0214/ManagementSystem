<?php

namespace App\Http\Requests\BulletinBoard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Categories\MainCategory;
use App\Models\Categories\SubCategory;

class SubcategoryFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
{
    return [
        'main_category_id' => [
            'required', // ← サブカテゴリ追加には必須
            'exists:main_categories,id',
        ],
        'sub_category_name' => [
            'required', // ← ★ ここ！ nullable → required に変更
            'string',
            'max:100',
            Rule::unique('sub_categories', 'sub_category'),
        ],
    ];
    }

    public function messages()
    {
        return [
            'main_category_id.required' => 'メインカテゴリーを選択してください。',
            'main_category_id.exists' => '選択されたメインカテゴリーは存在しません。',

            'sub_category_name.required' => 'サブカテゴリー名は必須項目です。',
            'sub_category_name.string' => 'サブカテゴリー名は文字列で入力してください。',
            'sub_category_name.max' => 'サブカテゴリー名は100文字以内で入力してください。',
            'sub_category_name.unique' => 'このサブカテゴリー名は既に登録されています。',
        ];
    }
}
