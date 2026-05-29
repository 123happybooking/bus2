<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Driver\DriverOperationLog;
use App\Models\Driver\DriverDailyReport;
use App\Models\Driver\DriverOperationStatus;
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
        $vehicleId = $itinerary->vehicle_id;
        
        if ($vehicleId) {
            $dailyReport = DriverDailyReport::where('driver_id', $driverId)
                ->where('date', $itineraryDate)
                ->where('vehicle_id', $vehicleId)
                ->first();
            
            if (!$dailyReport) {
                return redirect("/driver/daily-reports/{$itineraryDate}/{$vehicleId}")
                    ->with('info_alert', 'この車両の運転日報を作成してください。');
            }
        } else {
            $dailyReport = DriverDailyReport::where('driver_id', $driverId)
                ->where('date', $itineraryDate)
                ->first();
            
            if (!$dailyReport) {
                return redirect()->route('driver.daily-reports', $itineraryDate)
                    ->with('info_alert', '本日の運転日報を作成してください。');
            }
        }
        
        $allowEdit = $dailyReport && $dailyReport->allow_edit;
        
        $operationButtons = DriverOperationStatus::orderBy('display_order', 'asc')->get();
        
        $lastStatus = DriverOperationStatus::orderBy('display_order', 'desc')->first();
        $lastStatusName = $lastStatus ? $lastStatus->name : null;
        
        $previousIncompleteItinerary = DailyItinerary::where('driver_id', $driverId)
            ->whereDate('date', $today)
            ->where('time_start', '<', $itinerary->time_start)
            ->where(function($query) use ($lastStatusName) {
                $query->whereNull('operation_status');
                if ($lastStatusName) {
                    $query->orWhere('operation_status', '!=', $lastStatusName);
                }
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
        
        return view('driver.operation-run', compact('itinerary', 'logs', 'currentStatus', 'vehicles', 'defaultVehicleId','operationButtons', 'allowEdit'));
    }
    
    public function logAction(Request $request, $id)
    {
        $driverId = session('driver_id');
        
        $itinerary = DailyItinerary::where('driver_id', $driverId)
            ->findOrFail($id);
        
        $request->validate([
            'action' => 'required|string',
            'mileage' => 'nullable|integer|min:0',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'address' => 'required|string',
        ]);
        
        $vehicleId = $request->vehicle_id ?? $itinerary->vehicle_id;
        
        $operationStatus = DriverOperationStatus::where('name', $request->action)->first();
        $status = $operationStatus ? $operationStatus->description : '';
        
        $log = DriverOperationLog::create([
            'driver_id' => $driverId,
            'vehicle_id' => $vehicleId,
            'itinerary_id' => $itinerary->id,
            'action' => $request->action,
            'mileage' => $request->mileage,
            'status' => $status,
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
                'date' => $log->logged_at->format('Y/m/d'),
                'time' => $log->logged_at->format('H:i'),
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
                    'address' => $log->address,
                    'date' => $log->logged_at->format('Y/m/d'),
                    'time' => $log->logged_at->format('H:i'),
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
        
        if ($request->has('_delete') && $request->_delete) {
            $log->delete();
            return response()->json(['success' => true]);
        }
        
        $request->validate([
            'action' => 'required|string',
            'mileage' => 'nullable|integer|min:0',
            'address' => 'required|string',
            'time' => 'nullable|string',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
        ]);
        
        $operationStatus = DriverOperationStatus::where('name', $request->action)->first();
        $status = $operationStatus ? $operationStatus->description : '';
        
        $updateData = [
            'action' => $request->action,
            'status' => $status,
            'mileage' => $request->mileage,
            'address' => $request->address,
            'vehicle_id' => $request->vehicle_id ?? $log->vehicle_id,
        ];
        
        if ($request->filled('time')) {
            $itinerary = DailyItinerary::find($log->itinerary_id);
            $date = Carbon::parse($itinerary->date)->format('Y-m-d');
            $loggedAt = Carbon::parse($date . ' ' . $request->time);
            $updateData['logged_at'] = $loggedAt;
        }
        
        $log->update($updateData);
        
        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'action' => $log->action,
                'mileage' => $log->mileage,
                'address' => $log->address,
                'date' => $log->logged_at->format('Y/m/d'),
                'time' => $log->logged_at->format('H:i'),
                'status' => $log->status,
            ]
        ]);
    }
    
    public function deleteLog($id, Request $request)
    {
        $driverId = session('driver_id');
        
        $log = DriverOperationLog::where('driver_id', $driverId)
            ->findOrFail($id);
        
        $log->delete();
        
        return response()->json(['success' => true]);
    }
}