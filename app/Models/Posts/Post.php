<?php

namespace App\Models\Posts;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;

    protected $fillable = [
        'user_id',
        'post_title',
        'post',
    ];

    public function user(){
        return $this->belongsTo('App\Models\Users\User');
    }

    public function postComments()
    {
        return $this->hasMany('App\Models\Posts\PostComment');
    }

    // 投稿へのいいね
    public function likes()
    {
        // Likeモデルの外部キーはlike_post_id、Postの主キーはid
        return $this->hasMany('App\Models\Posts\Like', 'like_post_id', 'id');
    }

    // 必要に応じて他のリレーションもここに
    public function subCategory()
{
    return $this->belongsTo(\App\Models\Categories\SubCategory::class, 'post_sub_category_id');
}
}
