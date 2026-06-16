<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Agency;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\DailyItinerary;
use App\Exports\AgencyPerformanceExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AgencyPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $sessionKey = 'agency_performance_search';
        
        $searchFields = ['start_date', 'period', 'agency_ids'];
        
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
        $agencyIds = $request->input('agency_ids', []);
        
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
        
        $agencies = Agency::orderBy('display_order', 'asc')
            ->orderBy('agency_code', 'asc')
            ->when($agencyIds, function($query) use ($agencyIds) {
                if (is_array($agencyIds) && !empty($agencyIds)) {
                    $query->whereIn('id', $agencyIds);
                }
            })
            ->get();
        
        $statistics = [];
        $agencyTotals = [];
        $totalStatistics = [];
        $grandTotalReservations = 0;
        $grandTotalPoints = 0;
        
        foreach ($agencies as $agency) {
            $agencyId = $agency->id;
            $agencyName = $agency->agency_name;
            
            $groupInfos = GroupInfo::where('agency', $agencyName)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->whereNotIn('reservation_status', ['見積', 'キャンセル'])
                ->get();
            
            $reservationGroups = [];
            $driverPointStats = [];
            
            foreach ($groupInfos as $groupInfo) {
                $dateStr = $groupInfo->start_date;
                if ($dateStr instanceof Carbon) {
                    $dateStr = $dateStr->format('Y-m-d');
                }
                
                if (!isset($reservationGroups[$dateStr])) {
                    $reservationGroups[$dateStr] = [];
                }
                if (!isset($reservationGroups[$dateStr][$groupInfo->id])) {
                    $reservationGroups[$dateStr][$groupInfo->id] = true;
                }
                
                $itineraries = DailyItinerary::where('group_info_id', $groupInfo->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();
                
                foreach ($itineraries as $itinerary) {
                    $itineraryDateStr = $itinerary->date;
                    if ($itineraryDateStr instanceof Carbon) {
                        $itineraryDateStr = $itineraryDateStr->format('Y-m-d');
                    }
                    
                    if (!isset($driverPointStats[$itineraryDateStr])) {
                        $driverPointStats[$itineraryDateStr] = 0;
                    }
                    $driverPointStats[$itineraryDateStr] += (float)($itinerary->driver_workload ?? 0);
                }
            }
            
            $agencyReservationTotal = 0;
            $agencyPointsTotal = 0;
            
            foreach ($dates as $date) {
                $dateStr = $date['date_str'];
                $reservations = 0;
                $points = 0;
                
                if (isset($reservationGroups[$dateStr])) {
                    $reservations = count($reservationGroups[$dateStr]);
                }
                if (isset($driverPointStats[$dateStr])) {
                    $points = $driverPointStats[$dateStr];
                }
                
                $statistics[$agencyId][$dateStr] = [
                    'reservations' => $reservations,
                    'points' => $points,
                ];
                
                $agencyReservationTotal += $reservations;
                $agencyPointsTotal += $points;
            }
            
            $agencyTotals[$agencyId] = [
                'reservations' => $agencyReservationTotal,
                'points' => $agencyPointsTotal,
            ];
            
            $grandTotalReservations += $agencyReservationTotal;
            $grandTotalPoints += $agencyPointsTotal;
        }
        
        foreach ($dates as $date) {
            $dateStr = $date['date_str'];
            $totalReservations = 0;
            $totalPoints = 0;
            
            foreach ($agencies as $agency) {
                $agencyId = $agency->id;
                $totalReservations += $statistics[$agencyId][$dateStr]['reservations'];
                $totalPoints += $statistics[$agencyId][$dateStr]['points'];
            }
            
            $totalStatistics[$dateStr] = [
                'reservations' => $totalReservations,
                'points' => $totalPoints,
            ];
        }
        
        $allAgencies = Agency::orderBy('display_order', 'asc')
            ->orderBy('agency_code', 'asc')
            ->get();
        
        $companyInfo = ['name' => ''];
        try {
            $userCompany = \DB::table('user_company_info')->first();
            if ($userCompany) {
                $companyInfo['name'] = $userCompany->company_name ?? ($userCompany->user_company_name ?? '');
            }
        } catch (\Exception $e) {}
        
        return view('masters.agency-performance.index', compact(
            'dates',
            'agencies',
            'statistics',
            'totalStatistics',
            'agencyTotals',
            'grandTotalReservations',
            'grandTotalPoints',
            'startDate',
            'endDate',
            'allAgencies',
            'period',
            'agencyIds',
            'companyInfo'
        ));
    }
    
    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $agencyIds = $request->input('agency_ids', []);
        $exportOptions = $request->input('export_options', ['reservations', 'points']);
        
        if (is_string($agencyIds)) {
            $agencyIds = json_decode($agencyIds, true);
        }
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
        
        $agencies = Agency::orderBy('display_order', 'asc')
            ->orderBy('agency_code', 'asc')
            ->when($agencyIds, function($query) use ($agencyIds) {
                if (is_array($agencyIds) && !empty($agencyIds)) {
                    $query->whereIn('id', $agencyIds);
                }
            })
            ->get();
        
        $statistics = [];
        $agencyTotals = [];
        
        foreach ($agencies as $agency) {
            $agencyId = $agency->id;
            $agencyName = $agency->agency_name;
            
            $groupInfos = GroupInfo::where('agency', $agencyName)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->whereNotIn('reservation_status', ['見積', 'キャンセル'])
                ->get();
            
            $reservationGroups = [];
            $driverPointStats = [];
            
            foreach ($groupInfos as $groupInfo) {
                $dateStr = $groupInfo->start_date;
                if ($dateStr instanceof Carbon) {
                    $dateStr = $dateStr->format('Y-m-d');
                }
                
                if (!isset($reservationGroups[$dateStr])) {
                    $reservationGroups[$dateStr] = [];
                }
                if (!isset($reservationGroups[$dateStr][$groupInfo->id])) {
                    $reservationGroups[$dateStr][$groupInfo->id] = true;
                }
                
                $itineraries = DailyItinerary::where('group_info_id', $groupInfo->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();
                
                foreach ($itineraries as $itinerary) {
                    $itineraryDateStr = $itinerary->date;
                    if ($itineraryDateStr instanceof Carbon) {
                        $itineraryDateStr = $itineraryDateStr->format('Y-m-d');
                    }
                    
                    if (!isset($driverPointStats[$itineraryDateStr])) {
                        $driverPointStats[$itineraryDateStr] = 0;
                    }
                    $driverPointStats[$itineraryDateStr] += (float)($itinerary->driver_workload ?? 0);
                }
            }
            
            $agencyReservationTotal = 0;
            $agencyPointsTotal = 0;
            
            foreach ($dates as $date) {
                $dateStr = $date['date_str'];
                $reservations = 0;
                $points = 0;
                
                if (isset($reservationGroups[$dateStr])) {
                    $reservations = count($reservationGroups[$dateStr]);
                }
                if (isset($driverPointStats[$dateStr])) {
                    $points = $driverPointStats[$dateStr];
                }
                
                $statistics[$agencyId][$dateStr] = [
                    'reservations' => $reservations,
                    'points' => $points,
                ];
                
                $agencyReservationTotal += $reservations;
                $agencyPointsTotal += $points;
            }
            
            $agencyTotals[$agencyId] = [
                'reservations' => $agencyReservationTotal,
                'points' => $agencyPointsTotal,
            ];
        }
        
        $grandTotalReservations = 0;
        $grandTotalPoints = 0;
        foreach ($agencyTotals as $total) {
            $grandTotalReservations += $total['reservations'];
            $grandTotalPoints += $total['points'];
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
            new AgencyPerformanceExport($dates, $agencies, $statistics, $agencyTotals, $grandTotalReservations, $grandTotalPoints, $companyInfo, $username, $exportOptions),
            '送客実績_' . $startDate . '_' . $endDate . '.xlsx'
        );
    }
}