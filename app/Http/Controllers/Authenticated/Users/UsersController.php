<?php

namespace App\Http\Controllers\Authenticated\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Gate;
use App\Models\Users\User;
use App\Models\Users\Subjects;
use App\Searchs\DisplayUsers;
use App\Searchs\SearchResultFactories;

class UsersController extends Controller
{
    // ユーザーの検索結果を表示
    public function showUsers(Request $request){
        $keyword = $request->keyword;
        $category = $request->category;
        $updown = $request->updown;
        $gender = $request->sex;
        $role = $request->role;
        $subjects = null; // 検索時に選択された科目
        $userFactory = new SearchResultFactories();
        $users = $userFactory->initializeUsers($keyword, $category, $updown, $gender, $role, $subjects);
        $subjects = Subjects::all(); // すべての科目を取得
        return view('authenticated.users.search', compact('users', 'subjects')); // ビューに渡す
    }

    // ユーザープロフィールを表示
    public function userProfile($id){
        $user = User::with('subjects')->findOrFail($id); // ユーザー情報とその選択した科目を取得
        $subject_lists = Subjects::all(); // すべての科目を取得
        return view('authenticated.users.profile', compact('user', 'subject_lists')); // ビューに渡す
    }

    // ユーザーが選択した科目を更新
    public function userEdit(Request $request){
        $user = User::findOrFail($request->user_id); // 編集対象のユーザーを取得
        $user->subjects()->sync($request->subjects); // ユーザーの科目を更新（選択された科目を同期）
        return redirect()->route('user.profile', ['id' => $request->user_id]); // プロフィールページにリダイレクト
    }
}
