<?php

namespace App\Http\Controllers\Authenticated\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users\User;
use App\Models\Users\Subjects;

class UsersController extends Controller
{
    // ユーザーの検索結果を表示
    public function showUsers(Request $request)
    {
        $keyword = $request->keyword;
        $category = $request->category;
        $updown = $request->updown;
        $gender = $request->sex;
        $role = $request->role;
        $subjects = $request->subjects; // 選択された科目ID（配列）

        // ユーザー検索
        $users = User::with('subjects')
            ->when($keyword, function($query, $keyword) use ($category) {
                $query->where($category, 'like', "%{$keyword}%");
            })
            ->when($gender, function($query, $gender) {
                $query->where('sex', $gender);
            })
            ->when($role, function($query, $role) {
                $query->where('role', $role);
            })
            ->when($subjects, function($query, $subjects) {
                // OR条件で科目検索
                $query->whereHas('subjects', function($q) use ($subjects) {
                    $q->whereIn('subjects.id', $subjects);
                });
            })
            ->orderBy('id', $updown ?? 'ASC')
            ->get();

        // 全科目を取得してビューに渡す
        $allSubjects = Subjects::all();

        return view('authenticated.users.search', compact('users', 'allSubjects', 'subjects'));
    }

    // ユーザープロフィールを表示
    public function userProfile($id)
    {
        $user = User::with('subjects')->findOrFail($id); // ユーザー情報と選択科目を取得
        $subject_lists = Subjects::all(); // 全科目取得
        return view('authenticated.users.profile', compact('user', 'subject_lists'));
    }

    // ユーザーが選択した科目を更新
    public function userEdit(Request $request)
    {
        $user = User::findOrFail($request->user_id); // 編集対象ユーザー取得
        $user->subjects()->sync($request->subjects ?? []); // 選択された科目で同期
        return redirect()->route('user.profile', ['id' => $request->user_id]);
    }
}
