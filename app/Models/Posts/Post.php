<?php

namespace App\Models\Posts;

use Illuminate\Database\Eloquent\Model;
use App\Models\Categories\SubCategory;
use App\Models\Users\User;

class Post extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $fillable = [
        'user_id',
        'post_title',
        'post',
    ];

    /**
     * 投稿したユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 投稿に紐づくコメント
     */
    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }

    /**
     * 投稿への「いいね」
     * Likeモデルの外部キーは like_post_id、Postの主キーは id
     */
    public function likes()
    {
        return $this->hasMany(Like::class, 'like_post_id', 'id');
    }

    /**
     * 投稿に紐づくサブカテゴリー（多対多）
     */
    public function subCategories()
    {
        return $this->belongsToMany(
            SubCategory::class,   // 関連モデル
            'post_sub_categories',// 中間テーブル
            'post_id',            // 中間テーブルの投稿ID
            'sub_category_id'     // 中間テーブルのサブカテゴリーID
        );
    }
}
