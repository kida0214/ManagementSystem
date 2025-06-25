<?php

namespace App\Models\Categories;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $fillable = [
        'main_category_id',
        'sub_category',
    ];

    /**
     * 親メインカテゴリーとのリレーション
     */
    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class, 'main_category_id');
    }

    /**
     * 投稿とのリレーション（サブカテゴリが紐づく投稿）
     */
    public function posts()
    {
        return $this->hasMany(\App\Models\Posts\Post::class, 'post_category_id');
    }
}
