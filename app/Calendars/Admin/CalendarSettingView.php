<?php
namespace App\Calendars\Admin;

use Carbon\Carbon;
use App\Models\Calendars\ReserveSettings;

class CalendarSettingView
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

    public function render()
    {
        $html = [];
        $html[] = '<div class="calendar text-center">';
        $html[] = '<table class="table m-auto border adjust-table">';
        $html[] = '<thead>';
        $html[] = '<tr>';
        $html[] = '<th class="border">月</th>';
        $html[] = '<th class="border">火</th>';
        $html[] = '<th class="border">水</th>';
        $html[] = '<th class="border">木</th>';
        $html[] = '<th class="border">金</th>';
        $html[] = '<th class="border">土</th>';
        $html[] = '<th class="border">日</th>';
        $html[] = '</tr>';
        $html[] = '</thead>';
        $html[] = '<tbody>';

        $weeks = $this->getWeeks();
        $today = Carbon::today()->format("Y-m-d"); // 今日の日付
        $startDay = $this->carbon->format("Y-m-01"); // 今月初日

        foreach ($weeks as $week) {
            $html[] = '<tr class="' . $week->getClassName() . '">';
            $days = $week->getDays();
            foreach ($days as $day) {
                $dayString = $day->everyDay();

                // 今日を含まず今月の過去日だけ薄いグレー
                if ($dayString < $today && $dayString >= $startDay) {
                    $html[] = '<td class="past-day border">'; // 薄いグレー用クラス
                } elseif ($dayString < $startDay) {
                    // 前月などの過去日
                    $html[] = '<td class="border ' . $day->getClassName() . '">';
                } else {
                    // 今日以降
                    $html[] = '<td class="border ' . $day->getClassName() . '">';
                }

                $html[] = $day->render();
                $html[] = '<div class="adjust-area">';

                if ($dayString) {
                    // 過去日（今日を含む）なら入力をdisabled
                    if ($dayString < $today) {
                        $disabled = 'disabled';
                    } else {
                        $disabled = '';
                    }

                    $html[] = '<p class="d-flex m-0 p-0">1部<input class="w-25" style="height:20px;" name="reserve_day[' . $dayString . '][1]" type="text" form="reserveSetting" value="' . $day->onePartFrame($dayString) . '" ' . $disabled . '></p>';
                    $html[] = '<p class="d-flex m-0 p-0">2部<input class="w-25" style="height:20px;" name="reserve_day[' . $dayString . '][2]" type="text" form="reserveSetting" value="' . $day->twoPartFrame($dayString) . '" ' . $disabled . '></p>';
                    $html[] = '<p class="d-flex m-0 p-0">3部<input class="w-25" style="height:20px;" name="reserve_day[' . $dayString . '][3]" type="text" form="reserveSetting" value="' . $day->threePartFrame($dayString) . '" ' . $disabled . '></p>';
                }

                $html[] = '</div>';
                $html[] = '</td>';
            }
            $html[] = '</tr>';
        }

        $html[] = '</tbody>';
        $html[] = '</table>';
        $html[] = '</div>';
        $html[] = '<form action="' . route('calendar.admin.update') . '" method="post" id="reserveSetting">' . csrf_field() . '</form>';

        return implode("", $html);
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
