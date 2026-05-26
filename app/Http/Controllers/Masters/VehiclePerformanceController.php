<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Branch;
use App\Models\Masters\VehicleType;
use App\Exports\VehiclePerformanceExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VehiclePerformanceController extends Controller
{
    public function index(Request $request)
    {
        $sessionKey = 'vehicle_performance_search';
        
        $searchFields = ['start_date', 'period', 'branch_ids', 'vehicle_type_id'];
        
        $isNewSearch = false;
        foreach ($searchFields as $field) {
            if ($request->filled($field)) {
                $isNewSearch = true;
                break;
            }
        }
        
        if ($request->has('reset_search')) {
            session()->forget($sessionKey);
            $isNewSearch = false;
        }
        
        if ($isNewSearch) {
            $searchParams = $request->only($searchFields);
            session([$sessionKey => $searchParams]);
        } else {
            $searchParams = session($sessionKey, []);
            $request->merge($searchParams);
        }
        
        $defaultStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $startDate = $request->input('start_date', $defaultStartDate);
        $period = $request->input('period', 4);
        $branchIds = $request->input('branch_ids', []);
        $vehicleTypeId = $request->input('vehicle_type_id');
        
        $start = Carbon::parse($startDate);
        
        if ($period == 1) {
            $end = $start->copy()->addDays(6);
        } elseif ($period == 2) {
            $end = $start->copy()->addDays(13);
        } elseif ($period == 3) {
            $end = $start->copy()->addDays(20);
        } elseif ($period == 4) {
            $end = $start->copy()->addMonth()->subDay();
        } else {
            $end = $start->copy()->addDays(6);
        }
        
        $endDate = $end->format('Y-m-d');
        
        $dates = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dates[] = [
                'date' => $current->copy(),
                'display' => $current->format('n/j'),
                'date_str' => $current->format('Y-m-d'),
            ];
            $current->addDay();
        }
        
        $vehicles = Vehicle::with(['branch', 'vehicleModel', 'vehicleType'])
            ->where('is_active', true)
            ->when($branchIds, function($query) use ($branchIds) {
                if (is_array($branchIds) && !empty($branchIds)) {
                    $query->whereIn('branch_id', $branchIds);
                }
            })
            ->when($vehicleTypeId, function($query) use ($vehicleTypeId) {
                $query->where('vehicle_type_id', $vehicleTypeId);
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('vehicle_code', 'asc')
            ->get();
        
        $vehicleIds = $vehicles->pluck('id')->toArray();
        
        $itineraries = DailyItinerary::whereIn('vehicle_id', $vehicleIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $statistics = [];
        foreach ($vehicleIds as $vid) {
            $statistics[$vid] = [];
        }
        
        foreach ($itineraries as $itinerary) {
            $vid = $itinerary->vehicle_id;
            if ($itinerary->date instanceof \Carbon\Carbon) {
                $dateStr = $itinerary->date->format('Y-m-d');
            } else {
                $dateStr = (string)$itinerary->date;
            }
            
            if (!isset($statistics[$vid][$dateStr])) {
                $statistics[$vid][$dateStr] = [
                    'count' => 0,
                    'workload' => 0,
                ];
            }
            
            $statistics[$vid][$dateStr]['count']++;
            $statistics[$vid][$dateStr]['workload'] += (float)($itinerary->vehicle_workload ?? 0);
        }
        
        $totalStatistics = [];
        foreach ($dates as $date) {
            $dateStr = $date['date_str'];
            $totalStatistics[$dateStr] = [
                'count' => 0,
                'workload' => 0,
            ];
        }
        
        foreach ($itineraries as $itinerary) {
            if ($itinerary->date instanceof \Carbon\Carbon) {
                $dateStr = $itinerary->date->format('Y-m-d');
            } else {
                $dateStr = (string)$itinerary->date;
            }
            
            $totalStatistics[$dateStr]['count']++;
            $totalStatistics[$dateStr]['workload'] += (float)($itinerary->vehicle_workload ?? 0);
        }
        
        foreach ($totalStatistics as $dateStr => $stat) {
            $workload = $stat['workload'];
            $totalStatistics[$dateStr]['formatted_workload'] = is_numeric($workload) && floor($workload) == $workload ? (int)$workload : $workload;
        }
        
        $branches = Branch::orderBy('display_order', 'asc')
            ->orderBy('branch_code', 'asc')
            ->get();
        
        $vehicleTypes = VehicleType::orderBy('type_name')->get();
        
        $companyInfo = ['name' => ''];
        try {
            $userCompany = \DB::table('user_company_info')->first();
            if ($userCompany) {
                $companyInfo['name'] = $userCompany->company_name ?? ($userCompany->user_company_name ?? '');
            }
        } catch (\Exception $e) {}
        
        return view('masters.vehicle-performance.index', compact(
            'dates',
            'vehicles',
            'statistics',
            'totalStatistics',
            'startDate',
            'endDate',
            'branches',
            'vehicleTypes',
            'period',
            'branchIds',
            'vehicleTypeId',
            'companyInfo'
        ));
    }
    
    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchIds = $request->input('branch_ids', []);
        $vehicleTypeId = $request->input('vehicle_type_id');
        $exportOptions = $request->input('export_options', ['count', 'workload']);
        
        if (is_string($exportOptions)) {
            $exportOptions = json_decode($exportOptions, true);
        }
        
        if (!$startDate || !$endDate) {
            return redirect()->back()->with('error', '日付範囲を指定してください。');
        }
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $dates = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dates[] = [
                'date' => $current->copy(),
                'display' => $current->format('n/j'),
                'date_str' => $current->format('Y-m-d'),
            ];
            $current->addDay();
        }
        
        $vehicles = Vehicle::with(['branch', 'vehicleModel', 'vehicleType'])
            ->where('is_active', true)
            ->when($branchIds, function($query) use ($branchIds) {
                if (is_array($branchIds) && !empty($branchIds)) {
                    $query->whereIn('branch_id', $branchIds);
                }
            })
            ->when($vehicleTypeId, function($query) use ($vehicleTypeId) {
                $query->where('vehicle_type_id', $vehicleTypeId);
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('vehicle_code', 'asc')
            ->get();
        
        $vehicleIds = $vehicles->pluck('id')->toArray();
        
        $itineraries = DailyItinerary::whereIn('vehicle_id', $vehicleIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $statistics = [];
        foreach ($vehicleIds as $vid) {
            $statistics[$vid] = [];
        }
        
        foreach ($itineraries as $itinerary) {
            $vid = $itinerary->vehicle_id;
            if ($itinerary->date instanceof \Carbon\Carbon) {
                $dateStr = $itinerary->date->format('Y-m-d');
            } else {
                $dateStr = (string)$itinerary->date;
            }
            
            if (!isset($statistics[$vid][$dateStr])) {
                $statistics[$vid][$dateStr] = [
                    'count' => 0,
                    'workload' => 0,
                ];
            }
            
            $statistics[$vid][$dateStr]['count']++;
            $statistics[$vid][$dateStr]['workload'] += (float)($itinerary->vehicle_workload ?? 0);
        }
        
        $companyInfo = ['name' => ''];
        try {
            $userCompany = \DB::table('user_company_info')->first();
            if ($userCompany) {
                $companyInfo['name'] = $userCompany->company_name ?? ($userCompany->user_company_name ?? '');
            }
        } catch (\Exception $e) {}
        
        $username = session('username', auth()->user()->name ?? '');
        
        return Excel::download(
            new VehiclePerformanceExport($dates, $vehicles, $statistics, $companyInfo, $username, $exportOptions),
            '車両実績_' . $startDate . '_' . $endDate . '.xlsx'
        );
    }
}