<?php

namespace App\Http\Controllers\Authenticated\BulletinBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories\MainCategory;
use App\Models\Categories\SubCategory;
use App\Models\Posts\Post;
use App\Models\Posts\PostComment;
use App\Models\Posts\Like;
use App\Models\Users\User;
use App\Http\Requests\BulletinBoard\PostFormRequest;
use Illuminate\Support\Facades\Auth;//編集削除機能で追記した項目
use App\Http\Requests\BulletinBoard\CommentFormRequest;
use App\Http\Requests\BulletinBoard\MaincategoryFormRequest;
use App\Http\Requests\BulletinBoard\SubcategoryFormRequest;

class PostsController extends Controller
{
    public function show(Request $request)
{
    $query = Post::with('user', 'postComments', 'subCategory');

    //キーワード検索（サブカテゴリ名と一致したらそれで絞る）
    if ($request->filled('keyword')) {
        $sub = SubCategory::where('sub_category', $request->keyword)->first();

        if ($sub) {
            $query->where('post_sub_category_id', $sub->id);// 完全一致したらカテゴリで絞る
        } else {
            $query->where(function ($q) use ($request) {
                $q->where('post_title', 'like', '%' . $request->keyword . '%')
                  ->orWhere('post', 'like', '%' . $request->keyword . '%');
            });
        }
    }

    //サブカテゴリーIDでの直接絞り込み（クリック対応）
    if ($request->filled('sub_category_id')) {
        $query->where('post_category_id', $request->sub_category_id);
    }

    //いいね投稿だけ
    if ($request->filled('like_posts')) {
        $likes = Auth::user()->likePostId()->pluck('like_post_id');
        $query->whereIn('id', $likes);
    }

    //自分の投稿
    if ($request->filled('my_posts')) {
        $query->where('user_id', Auth::id());
    }

    $posts = $query->get();
    $categories = MainCategory::with('subCategories')->get();
    $like = new Like;
    $post_comment = new Post;

    return view('authenticated.bulletinboard.posts', compact('posts', 'categories', 'like', 'post_comment'));
}

    public function toggleLike(Request $request)
{
    $post_id = $request->post_id;
    $user = Auth::user();

    $post = Post::findOrFail($post_id);
    $existingLike = Like::where('like_user_id', $user->id)
                        ->where('like_post_id', $post_id)
                        ->first();

    if ($existingLike) {
        $existingLike->delete();
        $liked = false;
    } else {
        Like::create([
            'like_user_id' => $user->id,
            'like_post_id' => $post_id,
        ]);
        $liked = true;
    }

    $likeCount = Like::where('like_post_id', $post_id)->count();

    return response()->json([
        'liked' => $liked,
        'like_count' => $likeCount,
    ]);
}

    public function postDetail($post_id){
        $post = Post::with('user', 'postComments')->findOrFail($post_id);
        return view('authenticated.bulletinboard.post_detail', compact('post'));
    }

    public function postInput(){
        $main_categories = MainCategory::get();
        return view('authenticated.bulletinboard.post_create', compact('main_categories'));
    }

    public function postCreate(PostFormRequest $request){
        $post = Post::create([
            'user_id' => Auth::id(),
            'post_title' => $request->post_title,
            'post' => $request->post_body
        ]);
        return redirect()->route('post.show');
    }

    public function postEdit(PostFormRequest $request){
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'post_title' => 'required|string|max:255',
            'post_body' => 'nullable|string',
        ]);

        $post = Post::findOrFail($request->post_id);

        if ($post->user_id !== Auth::id()) {
            abort(403, 'この操作は許可されていません。');
        }

        $post->update([
            'post_title' => $request->post_title,
            'post' => $request->post_body,
        ]);

        return redirect()->route('post.detail', ['id' => $request->post_id]);
    }


    public function postDelete($id){
        $post = Post::findOrFail($id);
        // ログインユーザーが投稿の所有者か確認
        if ($post->user_id !== Auth::id()) {
            abort(403, 'この操作は許可されていません。');
        }

        $post->delete();
        return redirect()->route('post.show');
    }

    public function mainCategoryCreate(MaincategoryFormRequest $request){
        MainCategory::create(['main_category' => $request->main_category_name]);
        return redirect()->route('post.input');
    }

    public function subCategoryCreate(SubcategoryFormRequest $request){
        SubCategory::create([
            'main_category_id' => $request->main_category_id,
            'sub_category' => $request->sub_category_name,
        ]);
        return redirect()->route('post.input');
    }

    public function commentCreate(CommentFormRequest $request) {
        PostComment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);
        return redirect()->route('post.detail', ['id' => $request->post_id]);
    }

    public function myBulletinBoard(){
        $posts = Auth::user()->posts()->get();
        $like = new Like;
        return view('authenticated.bulletinboard.post_myself', compact('posts', 'like'));
    }

    public function likeBulletinBoard(){
        $like_post_id = Like::with('users')->where('like_user_id', Auth::id())->get('like_post_id')->toArray();
        $posts = Post::with('user')->whereIn('id', $like_post_id)->get();
        $like = new Like;
        return view('authenticated.bulletinboard.post_like', compact('posts', 'like'));
    }

    public function postLike(Request $request){
        $user_id = Auth::id();
        $post_id = $request->post_id;

        $like = new Like;

        $like->like_user_id = $user_id;
        $like->like_post_id = $post_id;
        $like->save();

        return response()->json();
    }

    public function postUnLike(Request $request){
        $user_id = Auth::id();
        $post_id = $request->post_id;

        $like = new Like;

        $like->where('like_user_id', $user_id)
             ->where('like_post_id', $post_id)
             ->delete();

        return response()->json();
    }
}
