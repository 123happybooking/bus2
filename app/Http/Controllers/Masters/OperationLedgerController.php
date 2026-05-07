<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Vehicle;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\ReservationCategory;
use App\Models\Masters\VehicleType;
use App\Models\Masters\Agency;
use App\Models\Masters\Branch;
use App\Models\Masters\GroupInfoDateRemark;
use App\Helpers\HolidayHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OperationLedgerController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $period = $request->input('period');
        $attendanceStatus = $request->input('attendance_status');
        $vehicleTypeId = $request->input('vehicle_type_id');
        $agencyId = $request->input('agency_id');
        $reservationStatus = $request->input('reservation_status');
        $hasGuide = $request->input('has_guide');
        
        $reservationId = $request->input('reservation_id');
        $groupName = $request->input('group_name');
        $branchIds = $request->input('branch_ids', []);
        
        $displayDays = $request->input('display_days', 7);
        
        if ($period && !$startDate && !$endDate) {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->addDays($period * 7 - 1)->format('Y-m-d');
            $displayDays = $period * 7;
        }
        
        if (!$startDate && !$endDate && !$period) {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->addDays(6)->format('Y-m-d');
            $displayDays = 7;
        }
        
        if ($request->has('start_date') && !$request->has('end_date') && !$request->has('period')) {
            $start = Carbon::parse($startDate);
            $end = $start->copy()->addDays($displayDays - 1);
            $endDate = $end->format('Y-m-d');
        }
        
        $startDate = $startDate ?? Carbon::today()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::today()->addDays(6)->format('Y-m-d');
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $displayDays = $start->diffInDays($end) + 1;
        
        $dates = [];
        $current = clone $start;
        while ($current <= $end) {
            $holidayInfo = HolidayHelper::getHolidayInfo($current);
            
            $dates[] = [
                'date' => $current->copy(),
                'day_of_week' => $this->getJapaneseDayOfWeek($current->dayOfWeek),
                'display' => $current->format('n/j') . '（' . $this->getJapaneseDayOfWeek($current->dayOfWeek) . '）',
                'is_saturday' => $current->dayOfWeek == 6,
                'is_sunday' => $current->dayOfWeek == 0,
                'is_holiday' => $holidayInfo['is_holiday'],
                'holiday_name' => $holidayInfo['name'],
            ];
            $current->addDay();
        }
        
        $dateRemarks = GroupInfoDateRemark::getRemarksByDateRange($startDate, $endDate);
        
        $vehicles = Vehicle::with(['vehicleModel', 'branch'])
            ->where('is_active', true)
            ->when($vehicleTypeId, function($query) use ($vehicleTypeId) {
                $query->where('vehicle_type_id', $vehicleTypeId);
            })
            ->when($branchIds, function($query) use ($branchIds) {
                if (is_array($branchIds) && !empty($branchIds)) {
                    $query->whereIn('branch_id', $branchIds);
                }
            })
            ->orderByRaw("FIELD(ownership_type, 'own', 'reservable', 'rental')")
            ->orderBy('display_order', 'asc')
            ->orderBy('vehicle_code', 'asc')
            ->get();
        
        $ownershipMap = [
            'own' => '自社',
            'reservable' => '予約用',
            'rental' => '傭車',
        ];
        
        $groupedVehicles = [];
        foreach ($vehicles as $index => $vehicle) {
            $groupedVehicles[] = [
                'vehicle' => $vehicle,
                'group_name' => $ownershipMap[$vehicle->ownership_type] ?? 'その他',
                'is_first_in_group' => ($index == 0 || $vehicles[$index - 1]->ownership_type != $vehicle->ownership_type),
            ];
        }
        
        $branches = Branch::orderBy('display_order', 'asc')
            ->orderBy('branch_code', 'asc')
            ->get();
        
        $vehicleQuery = Vehicle::where('is_active', true);
        if ($vehicleTypeId) {
            $vehicleQuery->where('vehicle_type_id', $vehicleTypeId);
        }
        $filteredVehicleIds = $vehicleQuery->pluck('id');
        
        $allItineraries = DailyItinerary::with(['busAssignment', 'groupInfo', 'busAssignment.driver', 'busAssignment.guide'])
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('vehicle_id', $filteredVehicleIds)
            ->whereNotNull('vehicle_id')
            ->when($agencyId, function($query) use ($agencyId) {
                $query->whereHas('groupInfo', function($q) use ($agencyId) {
                    $q->where('agency_id', $agencyId);
                });
            })
            ->when($reservationId, function($query) use ($reservationId) {
                $query->whereHas('groupInfo', function($q) use ($reservationId) {
                    $q->where('id', $reservationId);
                });
            })
            ->when($groupName, function($query) use ($groupName) {
                $query->whereHas('groupInfo', function($q) use ($groupName) {
                    $q->where('group_name', 'like', '%' . $groupName . '%');
                });
            })
            ->when($reservationStatus, function($query) use ($reservationStatus) {
                $query->whereHas('groupInfo', function($q) use ($reservationStatus) {
                    $q->where('reservation_status', $reservationStatus);
                });
            }, function($query) {
                $query->whereHas('groupInfo', function($q) {
                    $q->whereNotIn('reservation_status', ['見積', 'キャンセル']);
                });
            })
            ->when($hasGuide, function($query) {
                $query->whereHas('busAssignment', function($q) {
                    $q->whereNotNull('guide_id');
                });
            })
            ->when($attendanceStatus, function($query) use ($attendanceStatus) {
                $query->whereHas('busAssignment.driver', function($q) use ($attendanceStatus) {
                    $q->where('attendance_status', $attendanceStatus);
                });
            })
            ->orderBy('date', 'asc')
            ->orderBy('time_start', 'asc')
            ->get();
        
        $reservationCategories = ReservationCategory::pluck('color_code', 'id')->toArray();
        
        $busColors = [];
        foreach ($allItineraries as $itinerary) {
            $busId = $itinerary->bus_assignment_id;
            if (!isset($busColors[$busId])) {
                $groupInfo = $itinerary->groupInfo;
                if ($groupInfo) {
                    $statusColor = $this->getReservationStatusColor($groupInfo->reservation_status ?? '');
                    $categoryId = $groupInfo->reservation_categories_id;
                    $categoryColor = 'transparent';
                    if ($categoryId && $categoryId != 0 && isset($reservationCategories[$categoryId])) {
                        $categoryColor = $reservationCategories[$categoryId];
                    }
                    $busColors[$busId] = [
                        'status_color' => $statusColor,
                        'category_color' => $categoryColor,
                    ];
                } else {
                    $busColors[$busId] = [
                        'status_color' => '#ffffff',
                        'category_color' => 'transparent',
                    ];
                }
            }
        }
        
        $vehicleTypes = VehicleType::orderBy('type_name')->get();
        
        $agencies = Agency::orderBy('agency_name')->get();
        
        $scheduleData = [];
        foreach ($vehicles as $vehicle) {
            $vehicleId = $vehicle->id;
            $vehicleSchedule = [];
            
            foreach ($dates as $dateInfo) {
                $dateStr = $dateInfo['date']->format('Y-m-d');
                
                $dayItineraries = $allItineraries->filter(function($itinerary) use ($dateStr, $vehicleId) {
                    $itineraryDate = Carbon::parse($itinerary->date)->format('Y-m-d');
                    return $itineraryDate == $dateStr && $itinerary->vehicle_id == $vehicleId;
                });
                
                if ($dayItineraries->count() > 0) {
                    $vehicleSchedule[$dateStr] = $this->formatItineraries($dayItineraries, $busColors);
                } else {
                    $vehicleSchedule[$dateStr] = null;
                }
            }
            
            $scheduleData[$vehicleId] = [
                'vehicle' => $vehicle,
                'schedule' => $vehicleSchedule
            ];
        }
        
        return view('masters.operation-ledger.index', compact(
            'dates',
            'groupedVehicles',
            'scheduleData',
            'startDate',
            'endDate',
            'vehicleTypes',
            'agencies',
            'dateRemarks',
            'branches',
            'displayDays',
            'reservationId',
            'groupName',
            'branchIds'
        ));
    }
    
    private function getJapaneseDayOfWeek($dayOfWeek)
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dayOfWeek];
    }
    
    private function formatItineraries($itineraries, $busColors)
    {
        $result = [];
        foreach ($itineraries as $itinerary) {
            $busAssignment = $itinerary->busAssignment;
            $groupInfo = $itinerary->groupInfo;
            
            $busId = $itinerary->bus_assignment_id;
            $colors = $busColors[$busId] ?? [
                'status_color' => '#ffffff',
                'category_color' => 'transparent',
            ];
            
            $agency = $groupInfo ? $groupInfo->agencyInfo : null;
            $driver = $busAssignment ? $busAssignment->driver : null;
            $guide = $busAssignment ? $busAssignment->guide : null;
            
            $startTime = Carbon::parse($itinerary->time_start);
            $endTime = Carbon::parse($itinerary->time_end);
            $startMinutes = $startTime->hour * 60 + $startTime->minute;
            $endMinutes = $endTime->hour * 60 + $endTime->minute;
            $duration = $endMinutes - $startMinutes;
            
            if ($duration > 0) {
                $groupInfoId = $groupInfo ? $groupInfo->id : ($itinerary->group_info_id ?? null);
                $busAssignmentId = $busAssignment ? $busAssignment->id : ($itinerary->bus_assignment_id ?? null);
                $driverName = $driver ? $driver->name : ($busAssignment ? $busAssignment->driver_name : ($itinerary->driver ?? ''));
                $guideName = $guide ? $guide->name : ($busAssignment ? $busAssignment->guide_name : ($itinerary->guide ?? ''));
                $groupName = $groupInfo ? $groupInfo->group_name : '';
                
                $result[] = [
                    'start_minutes' => $startMinutes,
                    'end_minutes' => $endMinutes,
                    'duration' => $duration,
                    'group_info_id' => $groupInfoId,
                    'bus_assignment_id' => $busAssignmentId,
                    'driver_name' => $driverName,
                    'driver_name_kana' => $driver->name_kana ?? '',
                    'driver_phone' => $driver->phone_number ?? '',
                    'is_temporary_driver' => $busAssignment ? $busAssignment->temporary_driver : false,
                    'vehicle_type_spec_check' => $busAssignment ? $busAssignment->vehicle_type_spec_check : false,
                    'status_finalized' => $busAssignment ? $busAssignment->status_finalized : false,
                    'guide_name' => $guideName,
                    'agency_code' => $agency ? $agency->agency_code : '',
                    'group_name' => $groupName,
                    'remarks' => $itinerary->remarks ?? '',
                    'reservation_status' => $groupInfo ? $groupInfo->reservation_status : '',
                    'status_color' => $colors['status_color'],
                    'category_color' => $colors['category_color'],
                    'category_id' => $groupInfo ? $groupInfo->reservation_categories_id : null,
                ];
            }
        }
        
        usort($result, function($a, $b) {
            return $a['start_minutes'] - $b['start_minutes'];
        });
        
        return $result;
    }
    
    private function getReservationStatusColor($status)
    {
        $colors = [
            '予約' => '#ccf5ff',
            '仮押さえ' => '#ffff99',
            '見積' => '#ccffcc',
            '危ない' => '#ffcccc',
            '確定待ち' => '#ffd9b3',
            '確定' => '#cbb87c',
            '送信済' => '#e6e6fa',
            '実績待ち' => '#e0b0ff',
            '運行済' => '#c0c0c0',
            '請求済' => '#b0e0e6',
            'キャンセル' => '#d3d3d3',
            '稼働不可' => '#2c2c2c',
        ];
        return $colors[$status] ?? '#ffffff';
    }
}