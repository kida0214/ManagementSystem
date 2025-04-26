<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 初期データを挿入
        DB::table('users')->insert([
            [
                'over_name' => '羅琉',
                'under_name' => '太郎',
                'over_name_kana' => 'ラル',
                'under_name_kana' => 'タロウ',
                'mail_address' => 'lull@gmail.com',
                'sex' => 1,  // 男性
                'birth_day' => '1990-01-01',
                'role' => 1,  // 管理者
                'password' => Hash::make('password123'),  // パスワードをハッシュ化
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),   // シーダーを使う場合や DB ファサードを使って直接データを挿入する場合は、自分で now() を使って現在の日時を設定する必要がある
                'deleted_at' => null,
            ],
            [
                'over_name' => '喜田',
                'under_name' => '温子',
                'over_name_kana' => 'キダ',
                'under_name_kana' => 'アツコ',
                'mail_address' => 'atki@gmail.com',
                'sex' => 2,  // 女性
                'birth_day' => '1997-02-14',
                'role' => 2,  // 一般ユーザー
                'password' => Hash::make('kida0214'),  // パスワードをハッシュ化
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }
}
