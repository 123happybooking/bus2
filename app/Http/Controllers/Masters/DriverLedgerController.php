<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Driver;
use App\Models\Masters\Vehicle;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\ReservationCategory;
use App\Models\Masters\VehicleType;
use App\Models\Masters\Agency;
use App\Models\Masters\Branch;
use App\Models\Masters\GroupInfoDateRemark;
use App\Models\Masters\DriverAttendance;
use App\Helpers\HolidayHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DriverLedgerController extends Controller
{
    public function index(Request $request)
    {
        $sessionKey = 'driver_ledger_search';
        
        $searchFields = ['start_date', 'end_date', 'period', 'display_days'];
        
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
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $period = $request->input('period');
        $vehicleTypeId = $request->input('vehicle_type_id');
        $vehicleId = $request->input('vehicle_id');
        $driverId = $request->input('driver_id');
        $agencyId = $request->input('agency_id');
        $reservationStatus = $request->input('reservation_status');
        $hasGuide = $request->input('has_guide');
        $attendanceStatus = $request->input('attendance_status');
        $branchId = $request->input('branch_id');
        $reservationId = $request->input('reservation_id');
        $groupName = $request->input('group_name');
        
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
        
        $drivers = Driver::with('branch')
            ->where('is_active', true)
            ->when($attendanceStatus, function($query) use ($attendanceStatus) {
                $query->where('attendance_status', $attendanceStatus);
            })
            ->when($branchId, function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($driverId, function($query) use ($driverId) {
                $query->where('id', $driverId);
            })
            ->orderBy('branch_id', 'asc')
            ->orderBy('display_order', 'asc')
            ->orderBy('driver_code', 'asc')
            ->get();
        
        $groupedDrivers = [];
        $currentBranch = null;
        
        foreach ($drivers as $index => $driver) {
            $branchName = $driver->branch ? $driver->branch->branch_name : '未所属';
            
            if ($currentBranch !== $branchName) {
                $currentBranch = $branchName;
                $isFirstInGroup = true;
            } else {
                $isFirstInGroup = false;
            }
            
            $groupedDrivers[] = [
                'driver' => $driver,
                'group_name' => $branchName,
                'is_first_in_group' => $isFirstInGroup,
            ];
        }
        
        $vehicles = Vehicle::with(['vehicleModel', 'branch'])
            ->where('is_active', true)
            ->when($vehicleTypeId, function($query) use ($vehicleTypeId) {
                $query->where('vehicle_type_id', $vehicleTypeId);
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('vehicle_code', 'asc')
            ->get();
        
        $branches = Branch::orderBy('display_order', 'asc')
            ->orderBy('branch_code', 'asc')
            ->get();
        
        $vehicleTypes = VehicleType::orderBy('type_name')->get();
        $agencies = Agency::orderBy('agency_name')->get();
        
        $allItineraries = DailyItinerary::with(['busAssignment', 'groupInfo', 'busAssignment.vehicle', 'busAssignment.guide'])
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('driver_id')
            ->when($vehicleTypeId, function($query) use ($vehicleTypeId) {
                $query->whereHas('busAssignment.vehicle', function($q) use ($vehicleTypeId) {
                    $q->where('vehicle_type_id', $vehicleTypeId);
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
            ->when($vehicleId, function($query) use ($vehicleId) {
                $query->where('vehicle_id', $vehicleId);
            })
            ->when($driverId, function($query) use ($driverId) {
                $query->where('driver_id', $driverId);
            })
            ->when($agencyId, function($query) use ($agencyId) {
                $query->whereHas('groupInfo', function($q) use ($agencyId) {
                    $q->where('agency_id', $agencyId);
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
        
        $scheduleData = [];
        foreach ($drivers as $driver) {
            $driverIdVal = $driver->id;
            $driverSchedule = [];
            
            foreach ($dates as $dateInfo) {
                $dateStr = $dateInfo['date']->format('Y-m-d');
                
                $dayItineraries = $allItineraries->filter(function($itinerary) use ($dateStr, $driverIdVal) {
                    $itineraryDate = Carbon::parse($itinerary->date)->format('Y-m-d');
                    return $itineraryDate == $dateStr && $itinerary->driver_id == $driverIdVal;
                });
                
                if ($dayItineraries->count() > 0) {
                    $driverSchedule[$dateStr] = $this->formatItineraries($dayItineraries, $busColors);
                } else {
                    $driverSchedule[$dateStr] = null;
                }
            }
            
            $scheduleData[$driverIdVal] = [
                'driver' => $driver,
                'schedule' => $driverSchedule
            ];
        }
        
        $driverIds = $drivers->pluck('id')->toArray();
        $attendances = DriverAttendance::getAttendanceByDateRange($driverIds, $startDate, $endDate);
        
        // 预处理勤怠组信息
        $attendanceGroups = [];
        foreach ($drivers as $driver) {
            foreach ($dates as $dateInfo) {
                $dateStr = $dateInfo['date']->format('Y-m-d');
                $key = $driver->id . '_' . $dateStr;
                $current = $attendances[$key] ?? null;
                
                if ($current && $current->category) {
                    // 查找连续组
                    $groupStartDate = $dateStr;
                    $groupEndDate = $dateStr;
                    
                    // 向前查找连续日期
                    $tempDate = Carbon::parse($dateStr)->subDay();
                    while (true) {
                        $tempKey = $driver->id . '_' . $tempDate->format('Y-m-d');
                        if (isset($attendances[$tempKey]) && 
                            $attendances[$tempKey]->attendance_category_id == $current->attendance_category_id) {
                            $groupStartDate = $tempDate->format('Y-m-d');
                            $tempDate->subDay();
                        } else {
                            break;
                        }
                    }
                    
                    // 向后查找连续日期
                    $tempDate = Carbon::parse($dateStr)->addDay();
                    while (true) {
                        $tempKey = $driver->id . '_' . $tempDate->format('Y-m-d');
                        if (isset($attendances[$tempKey]) && 
                            $attendances[$tempKey]->attendance_category_id == $current->attendance_category_id) {
                            $groupEndDate = $tempDate->format('Y-m-d');
                            $tempDate->addDay();
                        } else {
                            break;
                        }
                    }
                    
                    $attendanceGroups[$driver->id . '_' . $dateStr] = [
                        'start_date' => $groupStartDate,
                        'end_date' => $groupEndDate,
                        'start_time' => $current->start_time,
                        'end_time' => $current->end_time,
                        'category' => $current->category,
                        'is_first_in_group' => ($dateStr == $groupStartDate),
                    ];
                } else {
                    $attendanceGroups[$driver->id . '_' . $dateStr] = null;
                }
            }
        }
        
        return view('masters.driver-ledger.index', compact(
            'dates',
            'groupedDrivers',
            'scheduleData',
            'startDate',
            'endDate',
            'vehicleTypes',
            'vehicles',
            'branches',
            'agencies',
            'dateRemarks',
            'displayDays',
            'vehicleTypeId',
            'vehicleId',
            'driverId',
            'agencyId',
            'reservationStatus',
            'hasGuide',
            'attendanceStatus',
            'branchId',
            'reservationId',
            'groupName',
            'attendances',
            'attendanceGroups'
        ));
    }
    
    private function getJapaneseDayOfWeek($dayOfWeek)
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dayOfWeek];
    }
    
    private function getAttendanceStatusColor($status)
    {
        $colors = [
            '出勤' => '#28a745',
            '休暇' => '#ffc107',
            '欠勤' => '#dc3545',
            '研修' => '#17a2b8',
            '有給' => '#fd7e14',
            '代休' => '#6f42c1',
        ];
        return $colors[$status] ?? '#6c757d';
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
            $vehicle = $busAssignment ? $busAssignment->vehicle : null;
            
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
                $vehicleName = $vehicle ? $vehicle->registration_number : ($itinerary->vehicle ?? '');
                
                $result[] = [
                    'start_minutes' => $startMinutes,
                    'end_minutes' => $endMinutes,
                    'duration' => $duration,
                    'group_info_id' => $groupInfoId,
                    'bus_assignment_id' => $busAssignmentId,
                    'driver_name' => $driverName,
                    'driver_name_kana' => $driver->name_kana ?? '',
                    'driver_phone' => $driver->phone_number ?? '',
                    'vehicle_name' => $vehicleName,
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