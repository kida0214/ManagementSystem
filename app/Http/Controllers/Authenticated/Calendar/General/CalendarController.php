<?php

namespace App\Http\Controllers\Authenticated\Calendar\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Calendars\General\CalendarView;
use App\Models\Calendars\ReserveSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function show(){
        $calendar = new CalendarView(time());
        return view('authenticated.calendar.general.calendar', compact('calendar'));
    }

    public function reserve(Request $request){
        DB::beginTransaction();

        try{
            $dates = $request->getData;   // ['2025-01-01', '2025-01-02', ...]
            $parts = $request->getPart;   // ['1', '', '3', ...] 空が混じる

            $reserveDays = [];

            // -------------------------------
            // ★ array_combine を使わない安全実装
            // -------------------------------
            foreach ($dates as $index => $date) {

                $part = $parts[$index] ?? null;

                // 空データは無視（hidden 等）
                if (empty($date) || empty($part)) {
                    continue;
                }

                // 正常データだけまとめる
                $reserveDays[] = [
                    'date' => $date,
                    'part' => $part
                ];
            }

            // -------------------------------
            // ★ 予約処理
            // -------------------------------
            foreach ($reserveDays as $reserve) {

                $setting = ReserveSettings::where('setting_reserve', $reserve['date'])
                                          ->where('setting_part', $reserve['part'])
                                          ->first();

                // 念のため null チェック
                if (!$setting) continue;

                // limit_users を1減らす
                $setting->decrement('limit_users');

                // 多重登録防止（attach 前に exists チェック）
                if (!$setting->users()->where('user_id', Auth::id())->exists()) {
                    $setting->users()->attach(Auth::id());
                }
            }

            DB::commit();

        } catch (\Exception $e) {

            DB::rollback();
            return redirect()
                ->back()
                ->with('error', '予約処理中にエラーが発生しました');
        }

        return redirect()->route('calendar.general.show', [
            'user_id' => Auth::id()
        ]);
    }
}
