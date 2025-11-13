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
use App\Http\Requests\BulletinBoard\CommentFormRequest;
use App\Http\Requests\BulletinBoard\MaincategoryFormRequest;
use App\Http\Requests\BulletinBoard\SubcategoryFormRequest;
use Illuminate\Support\Facades\Auth;

class PostsController extends Controller
{
    // 投稿一覧
    public function show(Request $request)
    {
        $query = Post::with(['user', 'postComments', 'subCategory', 'likes']);

        // キーワード検索（サブカテゴリー名が完全一致したらその投稿のみ）
        if ($request->filled('keyword')) {
            $sub = SubCategory::where('sub_category', $request->keyword)->first();
            if ($sub) {
                $query->whereHas('subCategory', function ($q) use ($sub) {
                    $q->where('sub_categories.id', $sub->id);
                });
            } else {
                $query->where(function ($q) use ($request) {
                    $q->where('post_title', 'like', '%' . $request->keyword . '%')
                      ->orWhere('post', 'like', '%' . $request->keyword . '%');
                });
            }
        }

        // サブカテゴリーでの絞り込み（クリック）
        if ($request->filled('sub_category_id')) {
            $query->whereHas('subCategory', function ($q) use ($request) {
                $q->where('sub_categories.id', $request->sub_category_id);
            });
        }

        // いいねした投稿のみ
        if ($request->filled('like_posts')) {
            $query->whereHas('likes', function ($q) {
                $q->where('like_user_id', Auth::id());
            });
        }

        // 自分の投稿のみ
        if ($request->filled('my_posts')) {
            $query->where('user_id', Auth::id());
        }

        $posts = $query->latest()->get();
        $categories = MainCategory::with('subCategories')->get();
        $like = new Like;

        return view('authenticated.bulletinboard.posts', compact('posts', 'categories', 'like'));
    }

    // 投稿詳細
    public function postDetail($post_id)
    {
        $post = Post::with(['user', 'postComments', 'subCategory', 'likes'])->findOrFail($post_id);
        return view('authenticated.bulletinboard.post_detail', compact('post'));
    }

    // 投稿作成画面
    public function postInput()
    {
        $main_categories = MainCategory::with('subCategories')->get();
        return view('authenticated.bulletinboard.post_create', compact('main_categories'));
    }

    // 投稿作成
    public function postCreate(PostFormRequest $request)
    {
        $post = Post::create([
            'user_id' => Auth::id(),
            'post_title' => $request->post_title,
            'post' => $request->post_body
        ]);

        // サブカテゴリー1つだけ紐付け
        $post->subCategory()->sync([$request->sub_category_id]);

        return redirect()->route('post.show');
    }

    // 投稿編集
    public function postEdit(PostFormRequest $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'post_title' => 'required|string|max:255',
            'post_body' => 'nullable|string',
            'sub_category_id' => 'required|integer|exists:sub_categories,id',
        ]);

        $post = Post::findOrFail($request->post_id);

        if ($post->user_id !== Auth::id()) {
            abort(403, 'この操作は許可されていません。');
        }

        $post->update([
            'post_title' => $request->post_title,
            'post' => $request->post_body,
        ]);

        // サブカテゴリー更新
        $post->subCategory()->sync([$request->sub_category_id]);

        return redirect()->route('post.detail', ['post_id' => $request->post_id]);
    }

    // 投稿削除
    public function postDelete($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== Auth::id()) {
            abort(403, 'この操作は許可されていません。');
        }

        $post->delete();
        return redirect()->route('post.show');
    }

    // メインカテゴリー作成
    public function mainCategoryCreate(MaincategoryFormRequest $request)
    {
        MainCategory::create(['main_category' => $request->main_category_name]);
        return redirect()->route('post.input');
    }

    // サブカテゴリー作成
    public function subCategoryCreate(SubcategoryFormRequest $request)
    {
        SubCategory::create([
            'main_category_id' => $request->main_category_id,
            'sub_category' => $request->sub_category_name,
        ]);
        return redirect()->route('post.input');
    }

    // コメント作成
    public function commentCreate(CommentFormRequest $request)
    {
        PostComment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return redirect()->route('post.detail', ['post_id' => $request->post_id]);
    }

    // 自分の投稿一覧
    public function myBulletinBoard()
    {
        $posts = Auth::user()->posts()->with(['subCategory', 'likes', 'postComments'])->get();
        $like = new Like;
        return view('authenticated.bulletinboard.post_myself', compact('posts', 'like'));
    }

    // いいねした投稿一覧
    public function likeBulletinBoard()
    {
        $like_post_ids = Like::where('like_user_id', Auth::id())->pluck('like_post_id');
        $posts = Post::with(['user', 'subCategory', 'postComments', 'likes'])
            ->whereIn('id', $like_post_ids)->get();
        $like = new Like;
        return view('authenticated.bulletinboard.post_like', compact('posts', 'like'));
    }

    // 投稿いいね
    public function postLike(Request $request)
    {
        $user_id = Auth::id();
        $post_id = $request->post_id;

        Like::firstOrCreate([
            'like_user_id' => $user_id,
            'like_post_id' => $post_id,
        ]);

        $likeCount = Like::where('like_post_id', $post_id)->count();

        return response()->json(['like_count' => $likeCount]);
    }

    // 投稿いいね解除
    public function postUnLike(Request $request)
    {
        $user_id = Auth::id();
        $post_id = $request->post_id;

        Like::where('like_user_id', $user_id)
            ->where('like_post_id', $post_id)
            ->delete();

        $likeCount = Like::where('like_post_id', $post_id)->count();

        return response()->json(['like_count' => $likeCount]);
    }
}
