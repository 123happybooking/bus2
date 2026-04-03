<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Driver;
use App\Models\Masters\DriverAttendance;
use App\Models\Masters\AttendanceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DriverAttendanceController extends Controller
{
    public function edit(Request $request)
    {
        $driverId = $request->input('driver_id');
        $date = $request->input('date');
        
        $driver = Driver::findOrFail($driverId);
        $attendanceCategories = AttendanceCategory::orderBy('display_order', 'asc')->get();
        
        $attendance = DriverAttendance::getByDriverAndDate($driverId, $date);
        
        if ($attendance && $attendance->category) {
            $startDate = $date;
            $endDate = $date;
            
            $tempDate = Carbon::parse($date)->subDay();
            while (true) {
                $tempAttendance = DriverAttendance::getByDriverAndDate($driverId, $tempDate->format('Y-m-d'));
                if ($tempAttendance && 
                    $tempAttendance->attendance_category_id == $attendance->attendance_category_id) {
                    $startDate = $tempDate->format('Y-m-d');
                    $tempDate->subDay();
                } else {
                    break;
                }
            }
            
            $tempDate = Carbon::parse($date)->addDay();
            while (true) {
                $tempAttendance = DriverAttendance::getByDriverAndDate($driverId, $tempDate->format('Y-m-d'));
                if ($tempAttendance && 
                    $tempAttendance->attendance_category_id == $attendance->attendance_category_id) {
                    $endDate = $tempDate->format('Y-m-d');
                    $tempDate->addDay();
                } else {
                    break;
                }
            }
            
            $startTime = $attendance->start_time;
            $endTime = $attendance->end_time;
            $categoryId = $attendance->attendance_category_id;
            $remarks = $attendance->remarks;
            
            return view('masters.driver-ledger.attendance-modal', compact(
                'driver',
                'date',
                'startDate',
                'endDate',
                'startTime',
                'endTime',
                'attendanceCategories',
                'categoryId',
                'remarks'
            ));
        }
        
        $startDate = $date;
        $endDate = $date;
        $startTime = '08:00:00';
        $endTime = '18:00:00';
        $categoryId = null;
        $remarks = '';
        
        return view('masters.driver-ledger.attendance-modal', compact(
            'driver',
            'date',
            'startDate',
            'endDate',
            'startTime',
            'endTime',
            'attendanceCategories',
            'categoryId',
            'remarks'
        ));
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'driver_id' => 'required|exists:drivers,id',
                'start_date' => 'required|date|date_format:Y-m-d',
                'end_date' => 'required|date|date_format:Y-m-d',
                'attendance_category_id' => 'required|exists:attendance_categories,id',
                'start_time' => 'nullable',
                'end_time' => 'nullable',
                'remarks' => 'nullable|string|max:500',
            ]);
            
            $userId = session('user_id', auth()->id() ?? 0);
            $startTime = $validated['start_time'] ?? '08:00:00';
            $endTime = $validated['end_time'] ?? '18:00:00';
            
            if (strlen($startTime) == 5) {
                $startTime .= ':00';
            }
            if (strlen($endTime) == 5) {
                $endTime .= ':00';
            }
            
            $startDate = $validated['start_date'];
            $endDate = $validated['end_date'];
            $categoryId = $validated['attendance_category_id'];
            
            $allRecords = DriverAttendance::where('driver_id', $validated['driver_id'])
                ->where('attendance_category_id', $categoryId)
                ->get();
            
            $oldDates = [];
            if ($allRecords->isNotEmpty()) {
                $oldDates = $allRecords->pluck('date')->map(function($date) {
                    return $date->format('Y-m-d');
                })->toArray();
            }
            
            $newDates = [];
            $current = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            while ($current <= $end) {
                $newDates[] = $current->format('Y-m-d');
                $current->addDay();
            }
            
            $datesToDelete = array_diff($oldDates, $newDates);
            
            $datesToAdd = array_diff($newDates, $oldDates);
            
            $datesToKeep = array_intersect($oldDates, $newDates);
            
            $template = $allRecords->first();
            
            if (!empty($datesToDelete)) {
                DriverAttendance::where('driver_id', $validated['driver_id'])
                    ->where('attendance_category_id', $categoryId)
                    ->whereIn('date', $datesToDelete)
                    ->delete();
            }
            
            if (!empty($datesToKeep)) {
                foreach ($datesToKeep as $date) {
                    DriverAttendance::updateOrCreate(
                        [
                            'driver_id' => $validated['driver_id'],
                            'date' => $date,
                        ],
                        [
                            'attendance_category_id' => $categoryId,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'remarks' => $validated['remarks'] ?? null,
                            'updated_by' => $userId,
                            'updated_at' => now(),
                        ]
                    );
                }
            }
            
            if (!empty($datesToAdd)) {
                sort($datesToAdd);
                foreach ($datesToAdd as $date) {
                    DriverAttendance::updateOrCreate(
                        [
                            'driver_id' => $validated['driver_id'],
                            'date' => $date,
                        ],
                        [
                            'attendance_category_id' => $categoryId,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'remarks' => $validated['remarks'] ?? null,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => '保存しました。'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '入力エラーがあります。',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('ドライバー勤怠保存失敗', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => '保存中にエラーが発生しました。'
            ], 500);
        }
    }
    
    public function destroy($driverId, $date)
    {
        try {
            $attendance = DriverAttendance::getByDriverAndDate($driverId, $date);
            
            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => '削除するデータが見つかりません。'
                ]);
            }
            
            $groupDates = [$date];
            $categoryId = $attendance->attendance_category_id;
            
            $tempDate = Carbon::parse($date)->subDay();
            while (true) {
                $tempAttendance = DriverAttendance::getByDriverAndDate($driverId, $tempDate->format('Y-m-d'));
                if ($tempAttendance && $tempAttendance->attendance_category_id == $categoryId) {
                    $groupDates[] = $tempDate->format('Y-m-d');
                    $tempDate->subDay();
                } else {
                    break;
                }
            }
            
            $tempDate = Carbon::parse($date)->addDay();
            while (true) {
                $tempAttendance = DriverAttendance::getByDriverAndDate($driverId, $tempDate->format('Y-m-d'));
                if ($tempAttendance && $tempAttendance->attendance_category_id == $categoryId) {
                    $groupDates[] = $tempDate->format('Y-m-d');
                    $tempDate->addDay();
                } else {
                    break;
                }
            }
            
            $deleted = DriverAttendance::where('driver_id', $driverId)
                ->whereIn('date', $groupDates)
                ->delete();
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => '削除しました。'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => '削除するデータが見つかりません。'
            ]);
            
        } catch (\Exception $e) {
            Log::error('ドライバー勤怠削除失敗', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => '削除中にエラーが発生しました。'
            ], 500);
        }
    }
}