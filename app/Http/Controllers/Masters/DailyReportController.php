<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverDailyReport;
use App\Models\Driver\DriverOperationLog;
use App\Models\Driver\DriverOperationStatus;
use App\Models\Driver\DriverExpense;
use App\Models\Driver\DriverExpenseType;
use App\Models\Driver\DriverPaymentMethod;
use App\Models\Driver\DriverVehicleCheck;
use App\Models\Driver\DriverVehicleCheckItems;
use App\Models\Driver\DriverVehicleCheckRemark;
use App\Models\Driver\DriverExpensesReceipt;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\Driver;
use App\Models\Masters\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $sessionKey = 'daily_reports_search';
        
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
        
        if (!$startDate) {
            $startDate = Carbon::today()->format('Y-m-d');
        }
        
        $start = Carbon::parse($startDate);
        
        if ($period == 1) {
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
        
        $query = DriverDailyReport::with(['driver', 'vehicle']);
        
        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }
        
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        
        $perPage = $request->input('per_page', 20);
        $reports = $query->orderBy('date', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        
        $drivers = Driver::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $vehicles = Vehicle::where('is_active', true)
            ->orderBy('registration_number')
            ->get(['id', 'registration_number']);
        
        $reportDates = $reports->getCollection()->pluck('date')->toArray();
        $reportDriverIds = $reports->getCollection()->pluck('driver_id')->toArray();
        
        $itineraryMap = [];
        $busAssignmentIds = [];
        
        if (!empty($reportDates) && !empty($reportDriverIds)) {
            $itineraries = DailyItinerary::whereIn('date', $reportDates)
                ->whereIn('driver_id', $reportDriverIds)
                ->get();
            
            foreach ($itineraries as $itinerary) {
                $key = $itinerary->driver_id . '_' . $itinerary->date;
                $groupId = $itinerary->group_info_id ?? '';
                $busId = $itinerary->bus_assignment_id ?? '';
                
                if ($groupId && $busId) {
                    if (!isset($itineraryMap[$key])) {
                        $itineraryMap[$key] = [];
                    }
                    $itineraryMap[$key][] = $groupId . '-' . $busId;
                    
                    if (!in_array($busId, $busAssignmentIds)) {
                        $busAssignmentIds[] = $busId;
                    }
                }
            }
        }
        
        $expenseTotals = [];
        $reimbursableExpenseTotals = [];
        
        if (!empty($busAssignmentIds)) {
            $expenses = DriverExpense::whereIn('bus_assignment_id', $busAssignmentIds)
                ->get();
            
            foreach ($expenses as $expense) {
                $busId = $expense->bus_assignment_id;
                
                if (!isset($expenseTotals[$busId])) {
                    $expenseTotals[$busId] = 0;
                }
                $expenseTotals[$busId] += $expense->amount;
                
                if ($expense->payment_method_id) {
                    $paymentMethod = DriverPaymentMethod::find($expense->payment_method_id);
                    if ($paymentMethod && $paymentMethod->is_reimbursable == 1) {
                        if (!isset($reimbursableExpenseTotals[$busId])) {
                            $reimbursableExpenseTotals[$busId] = 0;
                        }
                        $reimbursableExpenseTotals[$busId] += $expense->amount;
                    }
                }
            }
        }
        
        $reports->getCollection()->transform(function($report) use ($itineraryMap, $expenseTotals, $reimbursableExpenseTotals) {
            $key = $report->driver_id . '_' . $report->date;
            
            if (isset($itineraryMap[$key])) {
                $report->display_id = implode(',', $itineraryMap[$key]);
            } else {
                $report->display_id = '-';
            }
            
            $busAssignments = DailyItinerary::where('driver_id', $report->driver_id)
                ->whereDate('date', $report->date)
                ->get();
            
            $totalExpense = 0;
            $totalReimbursable = 0;
            
            foreach ($busAssignments as $busAssignment) {
                $busId = $busAssignment->bus_assignment_id;
                if ($busId) {
                    $totalExpense += $expenseTotals[$busId] ?? 0;
                    $totalReimbursable += $reimbursableExpenseTotals[$busId] ?? 0;
                }
            }
            
            $report->expense_total = $totalExpense;
            $report->reimbursable_expense_total = $totalReimbursable;
            
            return $report;
        });
        
        return view('masters.daily-reports.index', compact('reports', 'drivers', 'vehicles', 'displayDays'));
    }
    
    public function edit($id)
    {
        $report = DriverDailyReport::with(['driver', 'vehicle'])->findOrFail($id);
        
        $itineraries = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $report->driver_id)
            ->whereDate('date', $report->date)
            ->orderBy('time_start', 'asc')
            ->get();
        
        foreach ($itineraries as $itinerary) {
            $itinerary->operationLogs = DriverOperationLog::where('itinerary_id', $itinerary->id)
                ->orderBy('logged_at', 'asc')
                ->get();
        }
        
        $expenseTypes = DriverExpenseType::orderBy('id')->get();
        $paymentMethods = DriverPaymentMethod::orderBy('id')->get();
        
        $expenses = DriverExpense::with(['expenseType', 'paymentMethod'])
            ->where('driver_id', $report->driver_id)
            ->whereDate('expense_date', $report->date)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $expenses = DriverExpense::with(['expenseType', 'paymentMethod'])
            ->where('driver_id', $report->driver_id)
            ->whereDate('expense_date', $report->date)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $expenseIds = $expenses->pluck('id')->toArray();
        $receiptsByExpense = [];
        if (!empty($expenseIds)) {
            $receipts = DriverExpensesReceipt::whereIn('expense_id', $expenseIds)
                ->orderBy('created_at', 'asc')
                ->get();
            
            foreach ($receipts as $receipt) {
                $receiptsByExpense[$receipt->expense_id][] = $receipt;
            }
        }
        
        $expensesByItinerary = [];
        foreach ($expenses as $expense) {
            $itineraryId = $expense->itinerary_id;
            if (!isset($expensesByItinerary[$itineraryId])) {
                $expensesByItinerary[$itineraryId] = [];
            }
            $expensesByItinerary[$itineraryId][] = $expense;
        }
        
        $operationTypes = DriverOperationStatus::orderBy('display_order', 'asc')->get();
        
        $remarkText = '';
        if ($report->checkRemark && $report->checkRemark->remark) {
            $decoded = json_decode($report->checkRemark->remark, true);
            if (is_array($decoded)) {
                $remarkText = implode("\n", $decoded);
            } else {
                $remarkText = $report->checkRemark->remark;
            }
        }
        
        return view('masters.daily-reports.edit', compact('report', 'itineraries', 'operationTypes', 'expensesByItinerary', 'expenseTypes', 'paymentMethods', 'remarkText', 'receiptsByExpense'));
    }
    
    public function update(Request $request, $id)
    {
        $report = DriverDailyReport::findOrFail($id);
        
        $request->validate([
            'start_work_time' => 'nullable|date_format:H:i',
            'end_work_time' => 'nullable|date_format:H:i',
            'start_time' => 'nullable|date_format:H:i',
            'start_mileage' => 'nullable|integer|min:0',
            'end_time' => 'nullable|date_format:H:i',
            'end_mileage' => 'nullable|integer|min:0',
            'actual_distance' => 'nullable|integer|min:0',
            'empty_distance' => 'nullable|integer|min:0',
            'weather' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:500',
            'allow_edit' => 'nullable|boolean',
            'logs' => 'nullable|array',
        ]);
        
        $userId = session('user_id', auth()->id() ?? 0);
        
        $report->update([
            'start_work_time' => $request->start_work_time,
            'end_work_time' => $request->end_work_time,
            'start_time' => $request->start_time,
            'start_mileage' => $request->start_mileage,
            'end_time' => $request->end_time,
            'end_mileage' => $request->end_mileage,
            'actual_distance' => $request->actual_distance,
            'empty_distance' => $request->empty_distance,
            'weather' => $request->weather,
            'remark' => $request->remark,
            'allow_edit' => $request->has('allow_edit'),
            'updated_by' => $userId,
        ]);
        
        $itineraries = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $report->driver_id)
            ->whereDate('date', $report->date)
            ->orderBy('time_start', 'asc')
            ->get();
        
        $submittedLogIds = [];
        $dateOnly = Carbon::parse($report->date)->format('Y-m-d');
        
        if ($request->has('logs')) {
            foreach ($request->logs as $itineraryIndex => $logs) {
                if (!isset($itineraries[$itineraryIndex])) {
                    continue;
                }
                
                $itinerary = $itineraries[$itineraryIndex];
                
                foreach ($logs as $logIndex => $logData) {
                    $logId = $logData['id'] ?? null;
                    
                    $loggedAt = null;
                    if (!empty($logData['logged_at'])) {
                        $loggedAt = Carbon::parse($dateOnly . ' ' . $logData['logged_at'] . ':00');
                    }
                    
                    if ($logId) {
                        $log = DriverOperationLog::find($logId);
                        if ($log) {
                            $log->update([
                                'logged_at' => $loggedAt,
                                'mileage' => $logData['mileage'] ?? null,
                                'address' => $logData['address'] ?? null,
                                'action' => $logData['action'] ?? '',
                            ]);
                            $submittedLogIds[] = $logId;
                        }
                    } else {
                        $newLog = DriverOperationLog::create([
                            'itinerary_id' => $itinerary->id,
                            'driver_id' => $report->driver_id,
                            'logged_at' => $loggedAt,
                            'mileage' => $logData['mileage'] ?? null,
                            'address' => $logData['address'] ?? null,
                            'action' => $logData['action'] ?? '',
                        ]);
                        $submittedLogIds[] = $newLog->id;
                    }
                }
            }
        }
        
        foreach ($itineraries as $itinerary) {
            DriverOperationLog::where('itinerary_id', $itinerary->id)
                ->whereNotIn('id', $submittedLogIds)
                ->delete();
        }
        
        foreach ($itineraries as $itinerary) {
            $latestLog = DriverOperationLog::where('itinerary_id', $itinerary->id)
                ->orderBy('logged_at', 'desc')
                ->first();
            if ($latestLog) {
                $itinerary->update(['operation_status' => $latestLog->action]);
            } else {
                $itinerary->update(['operation_status' => null]);
            }
        }
        
        if ($request->has('expenses')) {
            foreach ($request->expenses as $itineraryId => $expenses) {
                foreach ($expenses as $expenseData) {
                    if (isset($expenseData['id']) && $expenseData['id']) {
                        $expense = DriverExpense::find($expenseData['id']);
                        if ($expense) {
                            $updateData = [
                                'expense_date' => $expenseData['expense_date'],
                                'type_id' => $expenseData['type_id'],
                                'amount' => $expenseData['amount'],
                                'payment_method_id' => $expenseData['payment_method_id'],
                                'agency_flag' => isset($expenseData['agency_flag']) ? 1 : 0,
                                'remark' => $expenseData['remark'],
                                'updated_by' => $userId,
                            ];
                            
                            if (is_null($expense->bus_assignment_id)) {
                                $itinerary = DailyItinerary::find($expense->itinerary_id);
                                if ($itinerary && $itinerary->bus_assignment_id) {
                                    $updateData['bus_assignment_id'] = $itinerary->bus_assignment_id;
                                }
                            }
                            
                            $expense->update($updateData);
                        }
                    } else {
                        $itinerary = DailyItinerary::find($itineraryId);
                        $busAssignmentId = $itinerary ? $itinerary->bus_assignment_id : null;
                        
                        DriverExpense::create([
                            'bus_assignment_id' => $busAssignmentId,
                            'itinerary_id' => $itineraryId,
                            'driver_id' => $report->driver_id,
                            'expense_date' => $expenseData['expense_date'],
                            'amount' => $expenseData['amount'],
                            'type_id' => $expenseData['type_id'],
                            'payment_method_id' => $expenseData['payment_method_id'],
                            'agency_flag' => isset($expenseData['agency_flag']) ? 1 : 0,
                            'remark' => $expenseData['remark'],
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]);
                    }
                }
            }
        }
        
        if ($request->has('deleted_expense_ids')) {
            DriverExpense::whereIn('id', $request->deleted_expense_ids)->delete();
        }
        
        if ($request->has('checks')) {
            $vehicleId = $report->vehicle_id;
            $driverId = $report->driver_id;
            $date = $report->date;
            
            foreach ($request->checks as $itemId => $isOk) {
                DriverVehicleCheck::updateOrCreate(
                    [
                        'driver_id' => $driverId,
                        'vehicle_id' => $vehicleId,
                        'driver_vehicle_check_items_id' => $itemId,
                        'date' => $date,
                    ],
                    [
                        'is_ok' => $isOk,
                        'updated_by' => $userId,
                    ]
                );
            }
        }
        
        if ($request->has('check_remark')) {
            DriverVehicleCheckRemark::updateOrCreate(
                [
                    'driver_id' => $report->driver_id,
                    'vehicle_id' => $report->vehicle_id,
                    'date' => $report->date,
                ],
                [
                    'remark' => $request->check_remark,
                    'updated_by' => $userId,
                ]
            );
        }
        
        return redirect()->route('masters.daily-reports.edit', $report->id)
            ->with('success', '運行日報を更新しました。');
    }
    
    public function exportPdf($id)
    {
        $report = DriverDailyReport::with(['driver', 'vehicle'])->findOrFail($id);
        
        $itineraries = DailyItinerary::with(['busAssignment.groupInfo', 'operationLogs'])
            ->where('driver_id', $report->driver_id)
            ->whereDate('date', $report->date)
            ->orderBy('time_start', 'asc')
            ->get();
        
        $expenses = DriverExpense::with(['expenseType', 'paymentMethod'])
            ->where('driver_id', $report->driver_id)
            ->whereDate('expense_date', $report->date)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $expensesByItinerary = [];
        foreach ($expenses as $expense) {
            $itineraryId = $expense->itinerary_id;
            if (!isset($expensesByItinerary[$itineraryId])) {
                $expensesByItinerary[$itineraryId] = [];
            }
            $expensesByItinerary[$itineraryId][] = [
                'expense_date' => Carbon::parse($expense->expense_date)->format('m/d'),
                'type_name' => $expense->expenseType->type_name ?? '',
                'amount' => $expense->amount,
                'payment_method_name' => $expense->paymentMethod->method_name ?? '',
                'agency_flag' => $expense->agency_flag,
                'remark' => $expense->remark,
            ];
        }
        
        $data = $this->preparePdfData($report, $itineraries, $expensesByItinerary);
        
        $html = view('masters.daily-reports.pdf', $data)->render();
        
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_footer' => 10,
            'tempDir' => sys_get_temp_dir(),
            'fontDir' => [
                storage_path('fonts'),
            ],
            'fontdata' => [
                'msyh' => [
                    'R' => 'msyh.ttf',
                    'B' => 'msyhbd.ttf',
                ],
                'genshin' => [
                    'R' => 'GenShinGothic-Normal.ttf',
                    'B' => 'GenShinGothic-Bold.ttf',
                ],
            ],
            'default_font' => 'msyh',
            
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'useSubstitutions'  => true,
        ]);
        
        $mpdf->shrink_tables_to_fit = 0;
        $mpdf->keep_table_proportions = true;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        
        $mpdf->SetHTMLFooter('
            <div style="text-align: center; font-size: 9pt; padding-top: 5px;">
                {PAGENO} / {nbpg}
            </div>
        ');
        
        $mpdf->WriteHTML($html);
        
        return $mpdf->Output('daily_report_' . $report->date . '.pdf', 'D');
    }
    
    private function preparePdfData($report, $itineraries, $expensesByItinerary)
    {
        $companyInfo = [
            'name' => '',
            'branch' => '',
            'tel' => '',
            'fax' => '',
        ];
        
        $companyLogo = null;
        
        try {
            $userCompany = DB::table('user_company_info')->first();
            if ($userCompany) {
                $companyInfo['name'] = $userCompany->user_company_name ?? '';
                $companyInfo['tel'] = $userCompany->phone_number ?? '';
                $companyInfo['fax'] = $userCompany->fax_number ?? '';
                if (!empty($userCompany->company_logo)) {
                    $logoPath = storage_path('app/public/' . $userCompany->company_logo);
                    $companyLogo = url('/storage/' . $userCompany->company_logo);
                }
            }
            
            $branch = DB::table('branches')
                ->join('drivers', 'branches.id', '=', 'drivers.branch_id')
                ->where('drivers.id', $report->driver_id)
                ->select('branches.*')
                ->first();
            
            if ($branch) {
                $companyInfo['branch'] = $branch->branch_name ?? '';
                if ($branch->phone_number) {
                    $companyInfo['tel'] = $branch->phone_number;
                }
                if ($branch->fax_number) {
                    $companyInfo['fax'] = $branch->fax_number;
                }
            }
        } catch (\Exception $e) {
        }
        
        $totalDistance = 0;
        if ($report->start_mileage && $report->end_mileage) {
            $totalDistance = $report->end_mileage - $report->start_mileage;
        }
        
        $actualDistance = $totalDistance;
        $emptyDistance = 0;
        
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $dateObj = Carbon::parse($report->date);
        $formattedDate = $dateObj->format('Y年n月j日') . '(' . $weekdays[$dateObj->dayOfWeek] . ')';
        
        $weather = $report->weather ?? '';
        
        $completedItineraries = [];
        foreach ($itineraries as $itinerary) {
            $logs = [];
            foreach ($itinerary->operationLogs as $log) {
                $logs[] = [
                    'time' => Carbon::parse($log->logged_at)->format('H:i'),
                    'location' => $log->address ?? '',
                    'meter' => $log->mileage ?? '',
                    'content' => $log->action ?? '',
                    'remark' => '',
                ];
            }
            
            $itineraryExpenses = $expensesByItinerary[$itinerary->id] ?? [];
            
            $completedItineraries[] = [
                'reservation_id' => ($itinerary->busAssignment->group_info_id ?? '') . '-' . ($itinerary->bus_assignment_id ?? ''),
                'start_time' => $itinerary->time_start ? Carbon::parse($itinerary->time_start)->format('H:i') : '',
                'end_time' => $itinerary->time_end ? Carbon::parse($itinerary->time_end)->format('H:i') : '',
                'logs' => $logs,
                'expenses' => $itineraryExpenses,
            ];
        }
        
        
        $checkItems = [];
        $checkRemark = '';
        
        try {
            $vehicleCheckItems = DriverVehicleCheckItems::where('is_active', true)
                ->orderBy('display_order')
                ->get()
                ->groupBy('category');
            
            $savedChecks = DriverVehicleCheck::where('driver_id', $report->driver_id)
                ->where('vehicle_id', $report->vehicle_id)
                ->where('date', $report->date)
                ->get()
                ->keyBy('driver_vehicle_check_items_id');
            
            foreach ($vehicleCheckItems as $category => $items) {
                foreach ($items as $item) {
                    $checkItems[$category][] = [
                        'item_name' => $item->item_name,
                        'is_ok' => $savedChecks[$item->id]->is_ok ?? null,
                    ];
                }
            }
            
            if ($report->checkRemark && $report->checkRemark->remark) {
                $decoded = json_decode($report->checkRemark->remark, true);
                if (is_array($decoded)) {
                    $checkRemark = implode("\n", $decoded);
                } else {
                    $checkRemark = $report->checkRemark->remark;
                }
            }
        } catch (\Exception $e) {
        }
        
        $data = [
            'companyInfo' => $companyInfo,
            'companyLogo' => $companyLogo,
            'report' => $report,
            'date' => $formattedDate,
            'weather' => $weather,
            'totalDistance' => $totalDistance,
            'actualDistance' => $actualDistance,
            'emptyDistance' => $emptyDistance,
            'completedItineraries' => $completedItineraries,
            'checkItems' => $checkItems,
            'checkRemark' => $checkRemark,
        ];
        
        return $data;
    }
    
    
    
    
    
    
    public function uploadReceipt(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $request->validate([
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'expense_id' => 'required|exists:driver_expenses,id',
        ]);
        
        $expense = DriverExpense::findOrFail($request->expense_id);
        
        $file = $request->file('receipt_image');
        $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('receipts/' . now()->format('Y/m'), $filename, 'public');
        
        $receipt = DriverExpensesReceipt::create([
            'bus_assignment_id' => $expense->bus_assignment_id,
            'itinerary_id' => $expense->itinerary_id,
            'expense_id' => $expense->id,
            'driver_id' => $expense->driver_id,
            'expense_date' => $expense->expense_date,
            'image_path' => $path,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '領収書をアップロードしました',
            'receipt' => [
                'id' => $receipt->id,
                'url' => asset('storage/' . $path),
            ],
        ]);
    }
    
    public function deleteReceipt($id)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => '認証エラー'], 401);
        }
        
        $receipt = DriverExpensesReceipt::findOrFail($id);
        
        if (Storage::disk('public')->exists($receipt->image_path)) {
            Storage::disk('public')->delete($receipt->image_path);
        }
        
        $receipt->delete();
        
        return response()->json([
            'success' => true,
            'message' => '削除しました',
        ]);
    }
    
    
    
    
    
    
    
    
    
    public function downloadAttachments(Request $request)
    {
        $startDate = $request->input('start_date');
        $period = $request->input('period', 1);
        $driverId = $request->input('driver_id');
        $vehicleId = $request->input('vehicle_id');
        
        if (!$startDate) {
            $startDate = Carbon::today()->format('Y-m-d');
        }
        
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
        
        $reportQuery = DriverDailyReport::query();
        
        if ($startDate) {
            $reportQuery->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $reportQuery->whereDate('date', '<=', $endDate);
        }
        if ($driverId) {
            $reportQuery->where('driver_id', $driverId);
        }
        if ($vehicleId) {
            $reportQuery->where('vehicle_id', $vehicleId);
        }
        
        $dates = $reportQuery->pluck('date')->toArray();
        
        if (empty($dates)) {
            return redirect()->back()->with('error', '指定された期間の日報が見つかりません。');
        }
        
        $expenses = DriverExpense::with(['receipts'])
            ->whereIn('expense_date', $dates)
            ->get();
        
        $expensesWithReceipts = $expenses->filter(function($expense) {
            return $expense->receipts && $expense->receipts->count() > 0;
        });
        
        if ($expensesWithReceipts->isEmpty()) {
            return redirect()->back()->with('error', '添付ファイルが見つかりません。');
        }
        
        $userId = session('user_id', auth()->id() ?? 0);
        $tempBaseDir = storage_path('app/temp/downloads/' . $userId);
        $tempDir = $tempBaseDir . '/立替';
        
        if (File::exists($tempDir)) {
            File::deleteDirectory($tempDir);
        }
        File::makeDirectory($tempDir, 0755, true);
        
        $fileCount = 0;
        
        foreach ($expensesWithReceipts as $expense) {
            $groupId = '0';
            $busId = $expense->bus_assignment_id ?? '0';
            
            if ($expense->itinerary_id) {
                $itinerary = DailyItinerary::find($expense->itinerary_id);
                if ($itinerary) {
                    $groupId = $itinerary->group_info_id ?? '0';
                }
            }
            
            $displayId = $groupId . '-' . $busId;
            
            $expenseDir = $tempDir . '/' . $displayId;
            
            if (!File::exists($expenseDir)) {
                File::makeDirectory($expenseDir, 0755, true);
            }
            
            foreach ($expense->receipts as $receipt) {
                $sourcePath = storage_path('app/public/' . $receipt->image_path);
                if (file_exists($sourcePath)) {
                    $filename = basename($receipt->image_path);
                    $destPath = $expenseDir . '/' . $filename;
                    copy($sourcePath, $destPath);
                    $fileCount++;
                }
            }
        }
        
        if ($fileCount === 0) {
            File::deleteDirectory($tempDir);
            return redirect()->back()->with('error', '添付ファイルが見つかりません。');
        }
        
        $zipFileName = '立替_' . $start->format('Y年m月d日') . '-' . $end->format('Y年m月d日') . '.zip';
        $zipPath = $tempBaseDir . '/' . $zipFileName;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            File::deleteDirectory($tempDir);
            return redirect()->back()->with('error', 'ZIPファイルの作成に失敗しました。');
        }
        
        $files = File::allFiles($tempDir);
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $relativePath);
        }
        
        $zip->close();
        
        File::deleteDirectory($tempDir);
        
        if (!file_exists($zipPath)) {
            return redirect()->back()->with('error', 'ZIPファイルの作成に失敗しました。');
        }
        
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
    
    
}