<?php
namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\Driver;
use App\Models\Masters\Branch;
use App\Exports\DriverPerformanceExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DriverPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $sessionKey = 'driver_performance_search';
        
        $searchFields = ['start_date', 'period', 'branch_ids', 'driver_id'];
        
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
        $driverId = $request->input('driver_id');
        
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
        
        $drivers = Driver::with('branch')
            ->where('is_active', true)
            ->when($branchIds, function($query) use ($branchIds) {
                if (is_array($branchIds) && !empty($branchIds)) {
                    $query->whereIn('branch_id', $branchIds);
                }
            })
            ->when($driverId, function($query) use ($driverId) {
                $query->where('id', $driverId);
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('driver_code', 'asc')
            ->get();
        
        $driverIds = $drivers->pluck('id')->toArray();
        
        $itineraries = DailyItinerary::whereIn('driver_id', $driverIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $statistics = [];
        foreach ($driverIds as $did) {
            $statistics[$did] = [];
        }
        
        foreach ($itineraries as $itinerary) {
            $did = $itinerary->driver_id;
            if ($itinerary->date instanceof \Carbon\Carbon) {
                $dateStr = $itinerary->date->format('Y-m-d');
            } else {
                $dateStr = (string)$itinerary->date;
            }
            
            if (!isset($statistics[$did][$dateStr])) {
                $statistics[$did][$dateStr] = [
                    'count' => 0,
                    'workload' => 0,
                ];
            }
            
            $statistics[$did][$dateStr]['count']++;
            $statistics[$did][$dateStr]['workload'] += (float)($itinerary->driver_workload ?? 0);
        }
        
        $branches = Branch::orderBy('display_order', 'asc')
            ->orderBy('branch_code', 'asc')
            ->get();
        
        $allDrivers = Driver::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->orderBy('driver_code', 'asc')
            ->get();
        
        $companyInfo = ['name' => ''];
        try {
            $userCompany = \DB::table('user_company_info')->first();
            if ($userCompany) {
                $companyInfo['name'] = $userCompany->company_name ?? ($userCompany->user_company_name ?? '');
            }
        } catch (\Exception $e) {}
        
        return view('masters.driver-performance.index', compact(
            'dates',
            'drivers',
            'statistics',
            'startDate',
            'endDate',
            'branches',
            'allDrivers',
            'period',
            'branchIds',
            'driverId',
            'companyInfo'
        ));
    }
    
    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchIds = $request->input('branch_ids', []);
        $driverId = $request->input('driver_id');
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
        
        $drivers = Driver::with('branch')
            ->where('is_active', true)
            ->when($branchIds, function($query) use ($branchIds) {
                if (is_array($branchIds) && !empty($branchIds)) {
                    $query->whereIn('branch_id', $branchIds);
                }
            })
            ->when($driverId, function($query) use ($driverId) {
                $query->where('id', $driverId);
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('driver_code', 'asc')
            ->get();
        
        $driverIds = $drivers->pluck('id')->toArray();
        
        $itineraries = DailyItinerary::whereIn('driver_id', $driverIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $statistics = [];
        foreach ($driverIds as $did) {
            $statistics[$did] = [];
        }
        
        foreach ($itineraries as $itinerary) {
            $did = $itinerary->driver_id;
            if ($itinerary->date instanceof \Carbon\Carbon) {
                $dateStr = $itinerary->date->format('Y-m-d');
            } else {
                $dateStr = (string)$itinerary->date;
            }
            
            if (!isset($statistics[$did][$dateStr])) {
                $statistics[$did][$dateStr] = [
                    'count' => 0,
                    'workload' => 0,
                ];
            }
            
            $statistics[$did][$dateStr]['count']++;
            $statistics[$did][$dateStr]['workload'] += (float)($itinerary->driver_workload ?? 0);
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
            new DriverPerformanceExport($dates, $drivers, $statistics, $companyInfo, $username, $exportOptions),
            '運転手実績_' . $startDate . '_' . $endDate . '.xlsx'
        );
    }
}