<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriverDailyReportController extends Controller
{
    public function index($date)
    {
        $formattedDate = Carbon::parse($date)->format('Y年m月d日');
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[Carbon::parse($date)->dayOfWeek];
        $dateTitle = $formattedDate . " ({$weekday})";
        
        return view('driver.daily-report', compact('date', 'dateTitle'));
    }
}