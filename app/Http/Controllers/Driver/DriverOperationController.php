<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Driver\DriverOperationLog;
use App\Models\Driver\DriverDailyReport;
use App\Models\Masters\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriverOperationController extends Controller
{
    public function runOperation($id)
    {
        $driverId = session('driver_id');
        $today = Carbon::today()->format('Y-m-d');
        
        $itinerary = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $driverId)
            ->findOrFail($id);
        
        $itineraryDate = Carbon::parse($itinerary->date)->format('Y-m-d');
        
        if ($itineraryDate !== $today) {
            return redirect()->route('driver.daily-itineraries', $today)
                ->with('error_alert', '本日以外の運行は操作できません。');
        }
        
        $dailyReport = DriverDailyReport::where('driver_id', $driverId)
            ->where('date', $today)
            ->first();
        
        if (!$dailyReport) {
            return redirect()->route('driver.daily-itineraries', $today)
                ->with('error_alert', '本日の運転日報が作成されていません。先に日報を作成してください。');
        }
        
        $previousIncompleteItinerary = DailyItinerary::where('driver_id', $driverId)
            ->whereDate('date', $today)
            ->where('time_start', '<', $itinerary->time_start)
            ->where(function($query) {
                $query->whereNull('operation_status')
                    ->orWhere('operation_status', '!=', '終了');
            })
            ->first();
        
        if ($previousIncompleteItinerary) {
            return redirect()->route('driver.daily-itineraries', $today)
                ->with('error_alert', '前の運行がまだ完了していません。先に前の運行を完了してください。');
        }
        
        $logs = DriverOperationLog::where('itinerary_id', $id)
            ->orderBy('logged_at', 'desc')
            ->get();
        
        $currentStatus = $itinerary->operation_status;
        
        $vehicles = Vehicle::where('is_active', true)
            ->orderBy('registration_number')
            ->get(['id', 'registration_number']);
        
        $defaultVehicleId = null;
        $firstItinerary = DailyItinerary::where('driver_id', $driverId)
            ->whereDate('date', $today)
            ->orderBy('time_start', 'asc')
            ->first();
        
        if ($firstItinerary && $firstItinerary->busAssignment && $firstItinerary->busAssignment->vehicle_id) {
            $defaultVehicleId = $firstItinerary->busAssignment->vehicle_id;
        } else {
            $defaultVehicleId = $dailyReport->vehicle_id;
        }
        
        return view('driver.operation-run', compact('itinerary', 'logs', 'currentStatus', 'vehicles', 'defaultVehicleId'));
    }
    
    public function logAction(Request $request, $id)
    {
        $driverId = session('driver_id');
        
        $itinerary = DailyItinerary::where('driver_id', $driverId)
            ->findOrFail($id);
        
        $request->validate([
            'action' => 'required|in:迎車,到着,空車,下車,終了',
            'mileage' => 'nullable|integer|min:0',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string',
        ]);
        
        $vehicleId = $request->vehicle_id ?? $itinerary->vehicle_id;
        
        $statusMap = [
            '迎車' => '迎車中',
            '到着' => '到着',
            '空車' => '空車',
            '下車' => '下車',
            '終了' => '終了',
        ];
        $status = $statusMap[$request->action] ?? '空車';
        
        $log = DriverOperationLog::create([
            'driver_id' => $driverId,
            'vehicle_id' => $vehicleId,
            'itinerary_id' => $itinerary->id,
            'action' => $request->action,
            'mileage' => $request->mileage,
            'status' => $status,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'logged_at' => Carbon::now(),
        ]);
        
        $itinerary->update([
            'operation_status' => $request->action,
        ]);
        
        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'action' => $log->action,
                'mileage' => $log->mileage,
                'address' => $log->address,
                'logged_at' => $log->logged_at->format('Y/m/d H:i:s'),
                'status' => $log->status,
            ]
        ]);
    }
    
    public function getLogs($id)
    {
        $driverId = session('driver_id');
        
        $itinerary = DailyItinerary::where('driver_id', $driverId)
            ->findOrFail($id);
        
        $logs = DriverOperationLog::where('itinerary_id', $itinerary->id)
            ->orderBy('logged_at', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'mileage' => $log->mileage,
                    'logged_at' => $log->logged_at->format('Y/m/d H:i:s'),
                    'status' => $log->status,
                ];
            });
        
        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }
    
    public function updateLog($id, Request $request)
    {
        $driverId = session('driver_id');
        
        $log = DriverOperationLog::where('driver_id', $driverId)
            ->findOrFail($id);
        
        $request->validate([
            'action' => 'required|in:迎車,到着,空車,下車,終了',
            'mileage' => 'nullable|integer|min:0',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string',
        ]);
        
        $log->update([
            'action' => $request->action,
            'mileage' => $request->mileage,
            'vehicle_id' => $request->vehicle_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
        ]);
        
        return response()->json(['success' => true, 'log' => $log]);
    }
}