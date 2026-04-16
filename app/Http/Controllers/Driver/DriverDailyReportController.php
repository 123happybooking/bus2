<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverDailyReport;
use App\Models\Masters\DailyItinerary;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriverDailyReportController extends Controller
{
    public function index($date)
    {
        $driverId = session('driver_id');
        
        $today = Carbon::today()->format('Y-m-d');
        
        if ($date !== $today) {
            return redirect()->route('driver.daily-itineraries', $today)
                ->with('error_alert', '本日の日報のみ編集できます。');
        }
        
        $formattedDate = Carbon::parse($date)->format('Y年m月d日');
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[Carbon::parse($date)->dayOfWeek];
        $dateTitle = $formattedDate . " ({$weekday})";
        
        $report = DriverDailyReport::where('driver_id', $driverId)
            ->where('date', $date)
            ->first();
            
        if ($report && $report->driver_id !== $driverId) {
            return redirect()->route('driver.daily-itineraries', $today)
                ->with('error_alert', '権限がありません。');
        }
        
        $vehicleId = null;
        if (!$report) {
            $itinerary = DailyItinerary::where('driver_id', $driverId)
                ->whereDate('date', $date)
                ->first();
            if ($itinerary) {
                $vehicleId = $itinerary->vehicle_id;
            }
        }
        
        return view('driver.daily-report', compact('date', 'dateTitle', 'report', 'vehicleId'));
    }
    
    public function create(Request $request)
    {
        $driverId = session('driver_id');
        $date = $request->input('date');
        $today = Carbon::today()->format('Y-m-d');
        
        if ($date !== $today) {
            return response()->json([
                'success' => false,
                'message' => '本日の日報のみ作成できます。'
            ]);
        }
        
        $existingReport = DriverDailyReport::where('driver_id', $driverId)
            ->where('date', $date)
            ->first();
        
        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => '日报已存在'
            ]);
        }
        
        $itinerary = DailyItinerary::where('driver_id', $driverId)
            ->whereDate('date', $date)
            ->first();
        
        $report = DriverDailyReport::create([
            'driver_id' => $driverId,
            'vehicle_id' => $itinerary ? $itinerary->vehicle_id : null,
            'date' => $date,
            'start_time' => null,
            'start_mileage' => null,
            'end_time' => null,
            'end_mileage' => null,
        ]);
        
        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $driverId = session('driver_id');
        
        $report = DriverDailyReport::where('driver_id', $driverId)
            ->where('id', $id)
            ->firstOrFail();
            
        $today = Carbon::today()->format('Y-m-d');
        if ($report->date !== $today) {
            return response()->json([
                'success' => false,
                'message' => '本日の日報のみ更新できます。'
            ]);
        }
        
        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'start_mileage' => 'nullable|integer|min:0',
            'end_time' => 'nullable|date_format:H:i',
            'end_mileage' => 'nullable|integer|min:0',
        ]);
        
        $report->update([
            'start_time' => $request->start_time,
            'start_mileage' => $request->start_mileage,
            'end_time' => $request->end_time,
            'end_mileage' => $request->end_mileage,
        ]);
        
        $distance = $report->distance;
        
        return response()->json([
            'success' => true,
            'report' => $report,
            'distance' => $distance
        ]);
    }
}