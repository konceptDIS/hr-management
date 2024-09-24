<?php

namespace App\Services;

use Carbon\Carbon;
use \App\Holiday;
use Illuminate\Support\Facades\Log;

class HolidayService {

    public static function createKnownHolidays(){
        try {
            $known_holidays = [
                [ 'name' => 'New Years Day', 'day' => 1, 'month' => 1 ],
                [ 'name' => 'Independence Day', 'day' => 1, 'month' => 10 ],
                [ 'name' => 'Christmas Day', 'day' => 25, 'month' => 12 ],
                [ 'name' => 'Boxing Day', 'day' => 26, 'month' => 12 ],
            ];
            foreach ($known_holidays as $entry) {
                $current_year = Carbon::now()->year;
                $hol = Holiday::whereYear('date', $current_year)->whereMonth('date', $entry['month'])->whereDay('date', $entry['day'])->first();
                if (!$hol) {
                    $holiday = new Holiday();
                    $holiday->name = $entry['name'];
                    $holiday->date = Carbon::create($current_year, $entry['month'], $entry['day']);
                    $holiday->created_by = "system";
                    $holiday->save();
                }
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }
}
?>