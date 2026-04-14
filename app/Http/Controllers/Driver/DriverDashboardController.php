<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\BusAssignment;
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

    public function getItineraries(Request $request, $date)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return response()->json([
                'success' => true,
                'date' => $date,
                'itineraries' => []
            ]);
        }
        
        $itineraries = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $driverId)
            ->whereDate('date', $date)
            ->orderBy('time_start', 'asc')
            ->get();
        
        $formattedItineraries = [];
        foreach ($itineraries as $itinerary) {
            $formattedItineraries[] = [
                'id' => $itinerary->id,
                'time_start' => substr($itinerary->time_start, 0, 5),
                'time_end' => substr($itinerary->time_end, 0, 5),
                'itinerary' => $itinerary->itinerary,
                'group_name' => $itinerary->busAssignment->groupInfo->group_name ?? '',
                'start_location' => $itinerary->start_location,
                'end_location' => $itinerary->end_location,
                'vehicle' => $itinerary->vehicle ?? '',
                'date' => $itinerary->date ? \Carbon\Carbon::parse($itinerary->date)->format('m月d日') : '',
            ];
        }
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'itineraries' => $formattedItineraries
        ]);
    }

    public function search(Request $request)
    {
        $driverId = session('driver_id');
        $keyword = $request->input('keyword', '');
        
        $query = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $driverId);
        
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('itinerary', 'like', "%{$keyword}%")
                  ->orWhere('start_location', 'like', "%{$keyword}%")
                  ->orWhere('end_location', 'like', "%{$keyword}%")
                  ->orWhereHas('busAssignment.groupInfo', function($sub) use ($keyword) {
                      $sub->where('group_name', 'like', "%{$keyword}%");
                  });
            });
        }
        
        $itineraries = $query->orderBy('date', 'asc')
            ->orderBy('time_start', 'asc')
            ->paginate(20);
        
        return view('driver.search', compact('itineraries', 'keyword'));
    }

    public function showItinerary($id)
    {
        $driverId = session('driver_id');
        
        $itinerary = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $driverId)
            ->findOrFail($id);
        
        return view('driver.itinerary-show', compact('itinerary'));
    }

    public function logout(Request $request)
    {
        session()->forget('driver_id');
        session()->forget('driver_name');
        
        return redirect()->route('driver.login');
    }
    
    public function settings()
    {
        return view('driver.settings');
    }
    
    
    public function dailyItineraries($date)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return redirect()->route('driver.dashboard');
        }
        
        $itineraries = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $driverId)
            ->whereDate('date', $date)
            ->orderBy('time_start', 'asc')
            ->get();
        
        $dateObj = Carbon::parse($date);
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[$dateObj->dayOfWeek];
        $formattedDate = $dateObj->format('Y年m月d日') . " ({$weekday})";
        
        return view('driver.daily-itineraries', compact('itineraries', 'formattedDate', 'date'));
    }
    
    
    public function getTabItineraries(Request $request)
    {
        $driverId = session('driver_id');
        $tab = $request->input('tab', 'upcoming');
        $today = date('Y-m-d');
        
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
                $query->whereDate('date', '>=', $today);
                break;
            case 'today':
                $query->whereDate('date', '=', $today);
                break;
            case 'completed':
                $query->whereDate('date', '<', $today);
                break;
            default:
                $query->whereDate('date', '>', $today);
                break;
        }
        
        $itineraries = $query->orderBy('date', 'asc')
            ->orderBy('time_start', 'asc')
            ->get();
        
        $formattedItineraries = [];
        foreach ($itineraries as $itinerary) {
            $formattedItineraries[] = [
                'id' => $itinerary->id,
                'time_start' => substr($itinerary->time_start, 0, 5),
                'time_end' => substr($itinerary->time_end, 0, 5),
                'start_location' => $itinerary->start_location,
                'end_location' => $itinerary->end_location,
                'vehicle' => $itinerary->vehicle,
                'date' => \Carbon\Carbon::parse($itinerary->date)->format('m月d日'),
                'itinerary' => $itinerary->itinerary,
                'group_name' => $itinerary->busAssignment->groupInfo->group_name ?? '',
            ];
        }
        
        return response()->json([
            'success' => true,
            'itineraries' => $formattedItineraries
        ]);
    }
}