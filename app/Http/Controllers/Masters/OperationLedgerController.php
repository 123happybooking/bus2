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
use App\Models\Masters\Driver;
use App\Helpers\HolidayHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationLedgerController extends Controller
{
    public function index(Request $request)
    {
        $sessionKey = 'operation_ledger_search';
        
        $searchFields = ['start_date', 'period', 'display_days'];
        
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
        $period = $request->input('period', 1);
        $displayDays = $request->input('display_days', 7);
        $attendanceStatus = $request->input('attendance_status');
        $vehicleTypeId = $request->input('vehicle_type_id');
        $agencyId = $request->input('agency_id');
        $reservationStatus = $request->input('reservation_status');
        $hasGuide = $request->input('has_guide');
        
        $reservationId = $request->input('reservation_id');
        $operationId = $request->input('operation_id');
        $groupName = $request->input('group_name');
        $branchIds = $request->input('branch_ids', []);
        $reservationCategoriesId = $request->input('reservation_categories_id');
        $driverId = $request->input('driver_id');
        
        if (!$startDate) {
            $startDate = Carbon::today()->format('Y-m-d');
        }
        
        $start = Carbon::parse($startDate);
        
        if ($period == 10) {
            $end = $start;
            $displayDays = 1;
        } elseif ($period == 1) {
            $end = $start->copy()->addDays(6);
            $displayDays = 7;
        } elseif ($period == 2) {
            $end = $start->copy()->addDays(13);
            $displayDays = 14;
        } elseif ($period == 3) {
            $end = $start->copy()->addDays(20);
            $displayDays = 21;
        } elseif ($period == 4) {
            $end = $start->copy()->addMonth()->subDay();
            $displayDays = $start->diffInDays($end) + 1;
        } else {
            $end = $start->copy()->addDays(6);
            $displayDays = 7;
        }
        
        $endDate = $end->format('Y-m-d');
        
        $hasExactSearch = !empty($reservationId) || !empty($operationId);
        
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
        
        $allItineraries = DailyItinerary::with(['busAssignment', 'groupInfo', 'busAssignment.driver'])
            ->when(!$hasExactSearch, function($query) use ($filteredVehicleIds, $startDate, $endDate) {
                return $query->whereIn('vehicle_id', $filteredVehicleIds)
                             ->whereNotNull('vehicle_id')
                             ->whereBetween('date', [$startDate, $endDate]);
            })
            ->when($hasExactSearch, function($query) use ($reservationId, $operationId) {
                if ($reservationId) {
                    $query->where('group_info_id', $reservationId);
                }
                if ($operationId) {
                    $query->where('bus_assignment_id', $operationId);
                }
                return $query;
            })
            ->when($agencyId, function($query) use ($agencyId) {
                $agency = Agency::find($agencyId);
                if ($agency) {
                    $query->whereHas('groupInfo', function($q) use ($agency) {
                        $q->where('agency', $agency->agency_name);
                    });
                }
            })
            ->when($reservationCategoriesId, function($query) use ($reservationCategoriesId) {
                $query->whereHas('groupInfo', function($q) use ($reservationCategoriesId) {
                    $q->where('reservation_categories_id', $reservationCategoriesId);
                });
            })
            ->when($driverId, function($query) use ($driverId) {
                $query->where('driver_id', $driverId);
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
        
        if ($hasExactSearch && $allItineraries->isNotEmpty()) {
            $minDate = $allItineraries->min('date');
            $maxDate = $allItineraries->max('date');
            
            if ($minDate && $maxDate) {
                $minCarbon = Carbon::parse($minDate);
                $maxCarbon = Carbon::parse($maxDate);
                $daysDiff = $minCarbon->diffInDays($maxCarbon) + 1;
                
                if ($daysDiff <= 7) {
                    $start = $minCarbon;
                    $end = $start->copy()->addDays(6);
                } else {
                    $start = $minCarbon;
                    $end = $maxCarbon;
                }
                
                $startDate = $start->format('Y-m-d');
                $endDate = $end->format('Y-m-d');
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
            }
        }
        
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
        
        $reservationCategories = ReservationCategory::where('is_active', true)->orderBy('display_order', 'asc')->get();
        $drivers = Driver::where('is_active', true)->orderBy('display_order', 'asc')->orderBy('driver_code', 'asc')->get();
        $agencies = Agency::where('is_active', true)->orderBy('display_order', 'asc')->orderBy('agency_code', 'asc')->get();
        
        
        $currentCompanyId = session('company_id');
        $sharedVehiclesData = $this->getSharedVehicles($currentCompanyId, $vehicleTypeId, $branchIds, $startDate, $endDate, $dates);
        
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
            'branchIds',
            'reservationCategories',
            'drivers',
            'sharedVehiclesData'
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
                $guideName = $guide ?: ($busAssignment ? $busAssignment->guide_name : ($itinerary->guide ?? ''));
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
    
    
    private function getSharedVehicles($currentCompanyId, $vehicleTypeId, $branchIds, $startDate, $endDate, $dates)
    {
        if (!$currentCompanyId) {
            return ['vehicles' => collect(), 'schedules' => []];
        }
        
        $friendCompanyIds = DB::table('friends')
            ->where('status', 'accepted')
            ->pluck('friend_company_id')
            ->toArray();
        
        if (empty($friendCompanyIds)) {
            return ['vehicles' => collect(), 'schedules' => []];
        }
        
        $sharedVehicles = [];
        $sharedSchedules = [];
        
        foreach ($friendCompanyIds as $friendCompanyId) {
            $friendDb = 'bus_user_' . $friendCompanyId;
            
            try {
                $dbExists = DB::connection('mysql')->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$friendDb]);
                if (empty($dbExists)) {
                    continue;
                }
                
                config(['database.connections.friend_db' => [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => $friendDb,
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ]]);
                
                $query = DB::connection('friend_db')->table('vehicles')
                    ->where('is_active', true)
                    ->where('is_share', 1)
                    ->where(function($q) use ($currentCompanyId) {
                        $q->where('share_to', 'all')
                          ->orWhereRaw('JSON_CONTAINS(share_to, ?)', ['"' . $currentCompanyId . '"']);
                    });
                
                if ($vehicleTypeId) {
                    $query->where('vehicle_type_id', $vehicleTypeId);
                }
                
                if (!empty($branchIds)) {
                    $query->whereIn('branch_id', $branchIds);
                }
                
                $friendCompany = DB::connection('friend_db')->table('user_company_info')->first();
                $companyName = $friendCompany ? $friendCompany->company_name : '他社';
                
                $vehicles = $query->get()->map(function($vehicle) use ($friendCompanyId,$companyName) {
                    $vehicle->owner_company_id = $friendCompanyId;
                    $vehicle->owner_company_name = $companyName;
                    $vehicle->is_shared_vehicle = true;
                    $vehicle->vehicleModel = null;
                    $vehicle->branch = null;
                    return $vehicle;
                });
                
                if ($vehicles->isEmpty()) {
                    DB::purge('friend_db');
                    continue;
                }
                
                $vehicleIds = $vehicles->pluck('id')->toArray();
                
                $itineraries = DB::connection('friend_db')->table('daily_itinerary')
                    ->whereIn('vehicle_id', $vehicleIds)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->select('vehicle_id', 'date', 'time_start', 'time_end', 'remarks')
                    ->get()
                    ->groupBy('vehicle_id');
                
                foreach ($vehicles as $vehicle) {
                    $sharedVehicles[] = $vehicle;
                    
                    $vehicleId = $vehicle->id;
                    $vehicleSchedule = [];
                    
                    foreach ($dates as $dateInfo) {
                        $dateStr = $dateInfo['date']->format('Y-m-d');
                        $vehicleSchedule[$dateStr] = [];
                    }
                    
                    if (isset($itineraries[$vehicleId])) {
                        foreach ($itineraries[$vehicleId] as $itinerary) {
                            $dateStr = $itinerary->date;
                            
                            $startTime = \Carbon\Carbon::parse($itinerary->time_start);
                            $endTime = \Carbon\Carbon::parse($itinerary->time_end);
                            $startMinutes = $startTime->hour * 60 + $startTime->minute;
                            $endMinutes = $endTime->hour * 60 + $endTime->minute;
                            $duration = $endMinutes - $startMinutes;
                            
                            if ($duration > 0) {
                                $vehicleSchedule[$dateStr][] = [
                                    'has_schedule' => true,
                                    'start_minutes' => $startMinutes,
                                    'end_minutes' => $endMinutes,
                                    'duration' => $duration,
                                    'bus_assignment_id' => null,
                                    'group_info_id' => null,
                                    'driver_name' => null,
                                    'driver_name_kana' => null,
                                    'driver_phone' => null,
                                    'is_temporary_driver' => false,
                                    'vehicle_type_spec_check' => false,
                                    'status_finalized' => false,
                                    'guide_name' => null,
                                    'agency_code' => null,
                                    'group_name' => null,
                                    'remarks' => null,
                                    'reservation_status' => null,
                                    'status_color' => '#e0e0e0',
                                    'category_color' => 'transparent',
                                    'category_id' => null,
                                ];
                            }
                        }
                    }
                    
                    foreach ($vehicleSchedule as $dateStr => $dayItineraries) {
                        if (!empty($dayItineraries)) {
                            usort($dayItineraries, function($a, $b) {
                                return $a['start_minutes'] - $b['start_minutes'];
                            });
                            $vehicleSchedule[$dateStr] = $dayItineraries;
                        } else {
                            $vehicleSchedule[$dateStr] = null;
                        }
                    }
                    
                    $sharedSchedules[$vehicleId] = [
                        'vehicle' => $vehicle,
                        'schedule' => $vehicleSchedule,
                        'is_shared' => true,
                    ];
                }
                
                DB::purge('friend_db');
                
            } catch (\Exception $e) {
                \Log::error('Failed to get shared vehicles from company: ' . $friendCompanyId, [
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        return [
            'vehicles' => collect($sharedVehicles),
            'schedules' => $sharedSchedules,
        ];
    }
}