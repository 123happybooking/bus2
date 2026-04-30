<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\Option;
use App\Models\Masters\GroupInfoFile;
use App\Models\Driver\DriverDailyReport;
use App\Models\Driver\DriverOperationStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriverItineraryController extends Controller
{
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
                'date' => $itinerary->date ? Carbon::parse($itinerary->date)->format('m月d日') : '',
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
            
        $finalStatusName = $this->getFinalStatusName();
        $itinerary->is_completed = $finalStatusName && $itinerary->operation_status === $finalStatusName;
        
        $busAssignment = $itinerary->busAssignment;
        
        $options = Option::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
        
        $selectedOptionNames = [];
        if ($busAssignment && $busAssignment->options) {
            $selectedOptionIds = explode(',', $busAssignment->options);
            foreach ($options as $option) {
                if (in_array($option->id, $selectedOptionIds)) {
                    $selectedOptionNames[] = $option->name;
                }
            }
        }
        $selectedOptionsText = !empty($selectedOptionNames) ? implode('、', $selectedOptionNames) : 'なし';
        
        $files = GroupInfoFile::where('bus_assignment_id', $busAssignment->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('driver.itinerary-show', compact('itinerary', 'busAssignment', 'selectedOptionsText', 'files'));
    }
    
    public function downloadFile($id)
    {
        $driverId = session('driver_id');
        
        $file = GroupInfoFile::findOrFail($id);
        
        $busAssignment = BusAssignment::find($file->bus_assignment_id);
        if (!$busAssignment || $busAssignment->driver_id != $driverId) {
            abort(403, 'アクセス権限がありません。');
        }
        
        $filePath = storage_path("app/public/{$file->file_path}");
        
        if (!file_exists($filePath)) {
            abort(404, 'ファイルが見つかりません。');
        }
        
        return response()->download($filePath, $file->file_name);
    }
    
    public function dailyItineraries($date)
    {
        $driverId = session('driver_id');
        
        if (!$driverId) {
            return redirect()->route('driver.dashboard');
        }
        
        $itineraries = DailyItinerary::with(['busAssignment.groupInfo', 'busAssignment.guide'])
            ->where('driver_id', $driverId)
            ->whereDate('date', $date)
            ->orderBy('time_start', 'asc')
            ->get();
        
        $finalStatusName = $this->getFinalStatusName();
        
        foreach ($itineraries as $itinerary) {
            $itinerary->is_completed = $finalStatusName && $itinerary->operation_status === $finalStatusName;
        }
        
        $dateObj = Carbon::parse($date);
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[$dateObj->dayOfWeek];
        $formattedDate = $dateObj->format('Y年m月d日') . " ({$weekday})";
        
        $report = DriverDailyReport::where('driver_id', $driverId)
            ->where('date', $date)
            ->first();
        
        return view('driver.daily-itineraries', compact('itineraries', 'formattedDate', 'date'));
    }
    
    private function getFinalStatusName()
    {
        $lastStatus = DriverOperationStatus::orderBy('display_order', 'desc')->first();
        return $lastStatus ? $lastStatus->name : null;
    }
}