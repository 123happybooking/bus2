<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverDailyReport;
use App\Models\Driver\DriverOperationStatus;
use App\Models\Driver\DriverExpense;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriverDailyReportController extends Controller
{
    public function index($date, $vehicleId = null)
    {
        $driverId = session('driver_id');
        
        $today = Carbon::today()->format('Y-m-d');
        
        $formattedDate = Carbon::parse($date)->format('Y年m月d日');
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[Carbon::parse($date)->dayOfWeek];
        $dateTitle = $formattedDate . " ({$weekday})";
        
        $allReports = DriverDailyReport::where('driver_id', $driverId)
            ->where('date', $date)
            ->get();
        
        if ($vehicleId) {
            $report = DriverDailyReport::where('driver_id', $driverId)
                ->where('date', $date)
                ->where('vehicle_id', $vehicleId)
                ->first();
            
            if ($report) {
                return $this->showReportForm($date, $dateTitle, $report, $vehicleId);
            } else {
                $vehicles = Vehicle::where('id', $vehicleId)->get(['id', 'registration_number']);
                $defaultVehicleId = $vehicleId;
                $allowEdit = true;
                $totalReports = $allReports->count();
                $completedItineraries = collect();
                
                return view('driver.daily-report', compact('date', 'dateTitle', 'report', 'completedItineraries', 'vehicles', 'defaultVehicleId', 'allowEdit', 'vehicleId', 'totalReports'));
            }
        }
        
        if ($allReports->count() === 1) {
            $report = $allReports->first();
            return $this->showReportForm($date, $dateTitle, $report, $report->vehicle_id);
        }
        
        $orderedVehicleIds = DailyItinerary::where('driver_id', $driverId)
            ->whereDate('date', $date)
            ->whereNotNull('vehicle_id')
            ->orderBy('time_start', 'asc')
            ->pluck('vehicle_id')
            ->unique()
            ->values()
            ->toArray();
        
        $vehiclesData = Vehicle::whereIn('id', $orderedVehicleIds)
            ->get(['id', 'registration_number'])
            ->keyBy('id');
        
        $sortedVehicles = [];
        foreach ($orderedVehicleIds as $vehicleId) {
            if (isset($vehiclesData[$vehicleId])) {
                $sortedVehicles[] = $vehiclesData[$vehicleId];
            }
        }
        
        $reports = [];
        foreach ($sortedVehicles as $vehicle) {
            $report = DriverDailyReport::where('driver_id', $driverId)
                ->where('date', $date)
                ->where('vehicle_id', $vehicle->id)
                ->first();
            
            $reports[] = [
                'vehicle_id' => $vehicle->id,
                'vehicle_name' => $vehicle->registration_number,
                'report' => $report,
                'exists' => $report ? true : false,
                'allow_edit' => $report ? ($report->allow_edit ?? true) : true,
            ];
        }
        
        return view('driver.daily-report-list', compact('date', 'dateTitle', 'reports'));
    }
    
    private function getFinalStatusName()
    {
        $lastStatus = DriverOperationStatus::orderBy('display_order', 'desc')->first();
        return $lastStatus ? $lastStatus->name : null;
    }
    
    private function showReportForm($date, $dateTitle, $report, $vehicleId)
    {
        $driverId = session('driver_id');
        
        $allowEdit = $report ? ($report->allow_edit ?? true) : true;
        
        $vehicles = Vehicle::where('id', $vehicleId)->get(['id', 'registration_number']);
        $defaultVehicleId = $vehicleId;
        
        $totalReports = DriverDailyReport::where('driver_id', $driverId)
            ->where('date', $date)
            ->count();
        
        $finalStatusName = $this->getFinalStatusName();
        
        $completedItineraries = DailyItinerary::with(['busAssignment.groupInfo', 'operationLogs'])
            ->where('driver_id', $driverId)
            ->where('vehicle_id', $vehicleId)
            ->whereDate('date', $date)
            ->where('operation_status', $finalStatusName)
            ->orderBy('time_start', 'asc')
            ->get();
            
        $expensesByItinerary = [];
        foreach ($completedItineraries as $itinerary) {
            $expenses = DriverExpense::with(['expenseType', 'paymentMethod'])
                ->where('driver_id', $driverId)
                ->whereDate('expense_date', $date)
                ->where('itinerary_id', $itinerary->id)
                ->orderBy('expense_date', 'desc')
                ->get();
            $expensesByItinerary[$itinerary->id] = $expenses;
        }
        
        return view('driver.daily-report', compact('date', 'dateTitle', 'report', 'completedItineraries', 'expensesByItinerary', 'vehicles', 'defaultVehicleId', 'allowEdit', 'vehicleId', 'totalReports'));
    }
    
    public function create(Request $request)
    {
        $driverId = session('driver_id');
        $userId = session('user_id', auth()->id() ?? 0);
        $date = $request->input('date');
        $vehicleId = $request->input('vehicle_id');
        $today = Carbon::today()->format('Y-m-d');
        
        if ($date !== $today) {
            return response()->json([
                'success' => false,
                'message' => '本日の日報のみ作成できます。'
            ]);
        }
        
        if (!$vehicleId) {
            return response()->json([
                'success' => false,
                'message' => '車両を選択してください。'
            ]);
        }
        
        $existingReport = DriverDailyReport::where('driver_id', $driverId)
            ->where('date', $date)
            ->where('vehicle_id', $vehicleId)
            ->first();
        
        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => '日報は既に存在します。'
            ]);
        }
        
        $itinerary = DailyItinerary::where('driver_id', $driverId)
            ->whereDate('date', $date)
            ->where('vehicle_id', $vehicleId)
            ->first();
        
        $report = DriverDailyReport::create([
            'driver_id' => $driverId,
            'vehicle_id' => $vehicleId,
            'date' => $date,
            'start_time' => null,
            'start_mileage' => null,
            'end_time' => null,
            'end_mileage' => null,
            'weather' => null,
            'start_work_time' => null,
            'end_work_time' => null,
            'actual_distance' => null,
            'empty_distance' => null,
            'remark' => null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
        
        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $driverId = session('driver_id');
        $userId = session('user_id', auth()->id() ?? 0); 
        
        $report = DriverDailyReport::where('driver_id', $driverId)
            ->where('id', $id)
            ->firstOrFail();
            
        if (isset($report->allow_edit) && !$report->allow_edit) {
            return response()->json([
                'success' => false,
                'message' => 'この日報は編集できません。'
            ]);
        }
        
        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'start_mileage' => 'nullable|integer|min:0',
            'end_time' => 'nullable|date_format:H:i',
            'end_mileage' => 'nullable|integer|min:0',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'weather' => 'nullable|string|max:50',
            'start_work_time' => 'nullable|date_format:H:i',
            'end_work_time' => 'nullable|date_format:H:i',
            'actual_distance' => 'nullable|integer|min:0',
            'empty_distance' => 'nullable|integer|min:0',
            'remark' => 'nullable|string|max:500', 
        ]);
        
        $report->update([
            'start_time' => $request->start_time,
            'start_mileage' => $request->start_mileage,
            'end_time' => $request->end_time,
            'end_mileage' => $request->end_mileage,
            'vehicle_id' => $request->vehicle_id ?? $report->vehicle_id,
            'weather' => $request->weather,
            'start_work_time' => $request->start_work_time,
            'end_work_time' => $request->end_work_time,
            'actual_distance' => $request->actual_distance,
            'empty_distance' => $request->empty_distance,
            'remark' => $request->remark,
            'updated_by' => $userId,
        ]);
        
        $distance = $report->distance;
        
        return response()->json([
            'success' => true,
            'report' => $report,
            'distance' => $distance
        ]);
    }
}