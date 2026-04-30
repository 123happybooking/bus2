<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Driver\DriverOperationStatus; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DriverDashboardController extends Controller
{
    public function index()
    {
        return view('driver.dashboard');
    }

    public function getCalendarData(Request $request)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json([
                'success' => true,
                'events' => [],
                'monthlyTaskCount' => 0,
                'year' => $request->input('year', date('Y')),
                'month' => $request->input('month', date('m'))
            ]);
        }
        
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $itineraries = DailyItinerary::where('driver_id', $driverId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('date', DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get();
        
        $events = [];
        foreach ($itineraries as $itinerary) {
            $events[] = [
                'date' => Carbon::parse($itinerary->date)->format('Y-m-d'),
                'count' => $itinerary->count
            ];
        }
        
        $monthlyTaskCount = DailyItinerary::where('driver_id', $driverId)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();
        
        return response()->json([
            'success' => true,
            'events' => $events,
            'monthlyTaskCount' => $monthlyTaskCount,
            'year' => $year,
            'month' => $month
        ]);
    }
    
    private function getFinalStatusName()
    {
        $lastStatus = DriverOperationStatus::orderBy('display_order', 'desc')->first();
        return $lastStatus ? $lastStatus->name : null;
    }
    
    public function getTabItineraries(Request $request)
    {
        $driverId = session('driver_id');
        $tab = $request->input('tab', 'upcoming');
        $today = date('Y-m-d');
        $finalStatusName = $this->getFinalStatusName();
        
        if (!$driverId) {
            return response()->json([
                'success' => true,
                'itineraries' => []
            ]);
        }
        
        $query = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $driverId);
        
        switch ($tab) {
            case 'upcoming':
                $query->whereDate('date', '>=', $today)
                      ->where(function($q) use ($finalStatusName) {
                          $q->whereNull('operation_status');
                          if ($finalStatusName) {
                              $q->orWhere('operation_status', '!=', $finalStatusName);
                          }
                      });
                break;
            case 'today':
                $query->whereDate('date', '=', $today);
                break;
            case 'completed':
                $query->where(function($q) use ($today, $finalStatusName) {
                    $q->whereDate('date', '<', $today);
                    if ($finalStatusName) {
                        $q->orWhere('operation_status', '=', $finalStatusName);
                    }
                });
                break;
            default:
                $query->whereDate('date', '>=', $today)
                      ->where(function($q) use ($finalStatusName) {
                          $q->whereNull('operation_status');
                          if ($finalStatusName) {
                              $q->orWhere('operation_status', '!=', $finalStatusName);
                          }
                      });
                break;
        }
        
        if ($tab === 'completed') {
            $itineraries = $query->orderBy('date', 'desc')
                ->orderBy('time_start', 'desc')
                ->limit(20)
                ->get();
        } else {
            $itineraries = $query->orderBy('date', 'asc')
                ->orderBy('time_start', 'asc')
                ->limit(20)
                ->get();
        }
        
        $formattedItineraries = [];
        foreach ($itineraries as $itinerary) {
            $groupInfo = $itinerary->busAssignment->groupInfo ?? null;
            $reservationCategory = $groupInfo ? $groupInfo->reservationCategory : null;
            
            $formattedItineraries[] = [
                'id' => $itinerary->id,
                'time_start' => substr($itinerary->time_start, 0, 5),
                'time_end' => substr($itinerary->time_end, 0, 5),
                'start_location' => $itinerary->start_location,
                'end_location' => $itinerary->end_location,
                'vehicle' => $itinerary->vehicle,
                'date' => Carbon::parse($itinerary->date)->format('m月d日'),
                'itinerary' => $itinerary->itinerary,
                'group_name' => $itinerary->busAssignment->groupInfo->group_name ?? '',
                'group_info_id' => $groupInfo->id ?? '',
                'bus_assignment_id' => $itinerary->bus_assignment_id ?? '',
                'category_name' => $reservationCategory->category_name ?? '',
                'operation_status' => $itinerary->operation_status,
                'is_completed' => $finalStatusName && $itinerary->operation_status === $finalStatusName,
                'agency_contact_name' => $itinerary->busAssignment->groupInfo->agency_contact_name ?? '',
            ];
        }
        
        return response()->json([
            'success' => true,
            'itineraries' => $formattedItineraries
        ]);
    }
}