<?php
namespace App\Calendars\General;

use Carbon\Carbon;
use Auth;

class CalendarView
{
    private $carbon;

    function __construct($date)
    {
        $this->carbon = new Carbon($date);
    }

    public function getTitle()
    {
        return $this->carbon->format('Y年n月');
    }

    function render()
    {
        $html = [];
        $html[] = '<div class="calendar text-center">';
        $html[] = '<table class="table">';
        $html[] = '<thead>';
        $html[] = '<tr>';
        $html[] = '<th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th><th>日</th>';
        $html[] = '</tr>';
        $html[] = '</thead>';
        $html[] = '<tbody>';

        // 判定に必要な日付を取得
        $today = Carbon::today()->format("Y-m-d");
        $startDay = $this->carbon->copy()->firstOfMonth()->format("Y-m-d");

        $weeks = $this->getWeeks();
        foreach ($weeks as $week) {
            $html[] = '<tr class="' . $week->getClassName() . '">';

            $days = $week->getDays();
            foreach ($days as $day) {

    $dayString = $day->everyDay();
    $isPast = $dayString < $today;                 // 過去日判定
    $isBeforeCurrentMonth = $dayString < $startDay; // 表示中の月より前の日か
    $cellClass = 'calendar-td';


    // ============================================================
    // 1. 日付セルの背景色クラスを判定
    // ============================================================
    if ($isPast) {

        // ● 過去日（ベース：濃いグレー）
        $cellClass .= ' past-day-general';

        if (!$isBeforeCurrentMonth) {
            // ● 今月内の過去日 → 薄いグレー
            $cellClass .= ' past-day-current-month';
        } else {
            // ● 先月など → 元々のクラス（例：前月の余白セル）
            $cellClass .= ' ' . $day->getClassName();
        }

    } else {

        // ● 今日以降
        $cellClass .= ' ' . $day->getClassName();

    }


    // ============================================================
    // 2. <td> 開始
    // ============================================================
    $html[] = '<td class="' . $cellClass . '">';

    // 日付の数字（○日）
    $html[] = $day->render();


    // ============================================================
    // 3. 予約状況の表示
    // ============================================================
    if (in_array($dayString, $day->authReserveDay())) {

        // ---------------------------------------------------------
        // 予約がある場合
        // ---------------------------------------------------------
        $reservePart = $day->authReserveDate($dayString)->first()->setting_part;

        if ($reservePart == 1) {
            $displayPart = "リモ1部";
        } elseif ($reservePart == 2) {
            $displayPart = "リモ2部";
        } else {
            $displayPart = "リモ3部";
        }

        if ($isPast) {
            // 過去日（予約済） → 参加部数を表示
            $html[] = '<p class="m-auto p-0 w-75 text-dark font-weight-bold"
                          style="font-size:12px; margin-top: 5px !important;">'
                          . $displayPart .
                      '</p>';

            $html[] = '<input type="hidden" name="getPart[]" value="" form="reserveParts">';

        } else {
            // 今日/未来（予約済） → 削除ボタン
            $html[] = '<button type="submit" class="btn btn-danger p-0 w-75"
                          name="delete_date"
                          style="font-size:12px"
                          value="' . $day->authReserveDate($dayString)->first()->setting_reserve . '">'
                          . $displayPart .
                      '</button>';

            $html[] = '<input type="hidden" name="getPart[]" value="" form="reserveParts">';
        }


    } else {

        // ---------------------------------------------------------
        // 予約がない場合
        // ---------------------------------------------------------
        if ($isPast) {

            // ★ 今月の過去日だけ「受付終了」
            if (!$isBeforeCurrentMonth) {

                $html[] = '<p class="m-auto p-0 w-75 text-secondary"
                              style="font-size:12px; margin-top: 5px !important;">受付終了</p>';

            } else {

                // 先月などは何も表示しない
                $html[] = '<p class="m-auto p-0 w-75"
                              style="font-size:12px; margin-top: 5px !important;"></p>';
            }

        } else {

            // 今日/未来 → プルダウン
            $html[] = $day->selectPart($dayString);

        }
    }


    // 日付（数字部分とは別の追加情報?）
    $html[] = $day->getDate();

    // 閉じタグ
    $html[] = '</td>';
}
$html[] = '</tr>';

        }

        $html[] = '</tbody>';
        $html[] = '</table>';
        $html[] = '</div>';

        $html[] = '<form action="/reserve/calendar" method="post" id="reserveParts">' . csrf_field() . '</form>';
        $html[] = '<form action="/delete/calendar" method="post" id="deleteParts">' . csrf_field() . '</form>';

        return implode('', $html);
    }

    protected function getWeeks()
    {
        $weeks = [];
        $firstDay = $this->carbon->copy()->firstOfMonth();
        $lastDay = $this->carbon->copy()->lastOfMonth();

        $week = new CalendarWeek($firstDay->copy());
        $weeks[] = $week;

        $tmpDay = $firstDay->copy()->addDay(7)->startOfWeek();
        while ($tmpDay->lte($lastDay)) {
            $week = new CalendarWeek($tmpDay, count($weeks));
            $weeks[] = $week;
            $tmpDay->addDay(7);
        }

        return $weeks;
    }
}
