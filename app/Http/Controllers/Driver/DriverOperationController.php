<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Driver\DriverOperationLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriverOperationController extends Controller
{
    public function runOperation($id)
    {
        $driverId = session('driver_id');
        
        $itinerary = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $driverId)
            ->findOrFail($id);
        
        $logs = DriverOperationLog::where('itinerary_id', $id)
            ->orderBy('logged_at', 'desc')
            ->get();
        
        return view('driver.operation-run', compact('itinerary', 'logs'));
    }
    
    public function logAction(Request $request, $id)
    {
        $driverId = session('driver_id');
        
        $itinerary = DailyItinerary::where('driver_id', $driverId)
            ->findOrFail($id);
        
        $request->validate([
            'action' => 'required|in:迎車,到着,空車,下車,終了',
            'mileage' => 'nullable|integer|min:0',
        ]);
        
        $log = DriverOperationLog::create([
            'itinerary_id' => $itinerary->id,
            'action' => $request->action,
            'mileage' => $request->mileage,
            'status' => '空車',
            'logged_at' => Carbon::now(),
        ]);
        
        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'action' => $log->action,
                'mileage' => $log->mileage,
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
}