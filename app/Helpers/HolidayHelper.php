<?php

namespace App\Helpers;

use Carbon\Carbon;

class HolidayHelper
{
    public static function getJapaneseHolidays($year)
    {
        $holidays = [
            $year . '-01-01' => '元日',
            $year . '-02-11' => '建国記念の日',
            $year . '-02-23' => '天皇誕生日',
            $year . '-04-29' => '昭和の日',
            $year . '-05-03' => '憲法記念日',
            $year . '-05-04' => 'みどりの日',
            $year . '-05-05' => 'こどもの日',
            $year . '-08-11' => '山の日',
            $year . '-11-03' => '文化の日',
            $year . '-11-23' => '勤労感謝の日',
        ];
        
        $adultDay = Carbon::create($year, 1, 1, 0, 0, 0)->modify('second monday of january');
        $holidays[$adultDay->format('Y-m-d')] = '成人の日';
        
        $marineDay = Carbon::create($year, 7, 1, 0, 0, 0)->modify('third monday of july');
        $holidays[$marineDay->format('Y-m-d')] = '海の日';
        
        $respectDay = Carbon::create($year, 9, 1, 0, 0, 0)->modify('third monday of september');
        $holidays[$respectDay->format('Y-m-d')] = '敬老の日';
        
        $sportsDay = Carbon::create($year, 10, 1, 0, 0, 0)->modify('second monday of october');
        $holidays[$sportsDay->format('Y-m-d')] = 'スポーツの日';
        
        $springEquinox = self::calculateSpringEquinox($year);
        $holidays[$springEquinox->format('Y-m-d')] = '春分の日';
        
        $autumnEquinox = self::calculateAutumnEquinox($year);
        $holidays[$autumnEquinox->format('Y-m-d')] = '秋分の日';
        
        return $holidays;
    }
    
    private static function calculateSpringEquinox($year)
    {
        if ($year >= 2000 && $year <= 2099) {
            $day = floor(20.8431 + 0.242194 * ($year - 1980)) - floor(($year - 1980) / 4);
        } else {
            $day = 20;
        }
        return Carbon::create($year, 3, $day);
    }
    
    private static function calculateAutumnEquinox($year)
    {
        if ($year >= 2000 && $year <= 2099) {
            $day = floor(23.2488 + 0.242194 * ($year - 1980)) - floor(($year - 1980) / 4);
        } else {
            $day = 23;
        }
        return Carbon::create($year, 9, $day);
    }
    
    public static function getHolidayInfo($date)
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $holidays = self::getJapaneseHolidays($carbon->year);
        
        $dateKey = $carbon->format('Y-m-d');
        
        if (isset($holidays[$dateKey])) {
            return [
                'is_holiday' => true,
                'name' => $holidays[$dateKey]
            ];
        }
        
        return [
            'is_holiday' => false,
            'name' => null
        ];
    }
    
    public static function isHoliday($date)
    {
        $info = self::getHolidayInfo($date);
        return $info['is_holiday'];
    }
}