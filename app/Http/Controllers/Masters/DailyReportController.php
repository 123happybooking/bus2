<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverDailyReport;
use App\Models\Driver\DriverOperationLog;
use App\Models\Driver\DriverOperationStatus;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\Driver;
use App\Models\Masters\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverDailyReport::with(['driver', 'vehicle']);
        
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        
        $reports = $query->orderBy('date', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $drivers = Driver::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $vehicles = Vehicle::where('is_active', true)
            ->orderBy('registration_number')
            ->get(['id', 'registration_number']);
        
        return view('masters.daily-reports.index', compact('reports', 'drivers', 'vehicles'));
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
        
        $operationTypes = DriverOperationStatus::orderBy('display_order', 'asc')->get();
        
        return view('masters.daily-reports.edit', compact('report', 'itineraries', 'operationTypes'));
    }
    
    public function update(Request $request, $id)
    {
        $report = DriverDailyReport::findOrFail($id);
        
        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'start_mileage' => 'nullable|integer|min:0',
            'end_time' => 'nullable|date_format:H:i',
            'end_mileage' => 'nullable|integer|min:0',
            'allow_edit' => 'nullable|boolean',
        ]);
        
        $userId = session('user_id', auth()->id() ?? 0);
        
        $report->update([
            'start_time' => $request->start_time,
            'start_mileage' => $request->start_mileage,
            'end_time' => $request->end_time,
            'end_mileage' => $request->end_mileage,
            'allow_edit' => $request->has('allow_edit'),
            'updated_by' => $userId,
        ]);
        
        return redirect()->route('masters.daily-reports.index')
            ->with('success', '運行日報を更新しました。');
    }
    
    public function updateOperationLog(Request $request, $id)
    {
        $log = DriverOperationLog::findOrFail($id);
        
        $request->validate([
            'daily_report_id' => 'required|exists:driver_daily_reports,id',
            'logged_at' => 'nullable|string',
            'mileage' => 'nullable|integer|min:0',
            'address' => 'nullable|string',
            'action' => 'required|string',
        ]);
        
        $updateData = [
            'action' => $request->action,
            'mileage' => $request->mileage,
            'address' => $request->address,
        ];
        
        if ($request->filled('logged_at')) {
            $updateData['logged_at'] = Carbon::parse($request->logged_at);
        }
        
        $log->update($updateData);
        
        $userId = session('user_id', auth()->id() ?? 0);
        
        DriverDailyReport::where('id', $request->daily_report_id)
            ->update([
                'updated_by' => $userId,
                'updated_at' => Carbon::now(),
            ]);
        
        $itinerary = DailyItinerary::find($log->itinerary_id);
        if ($itinerary) {
            $latestLog = DriverOperationLog::where('itinerary_id', $itinerary->id)
                ->orderBy('logged_at', 'desc')
                ->first();
            if ($latestLog) {
                $itinerary->update(['operation_status' => $latestLog->action]);
            }
        }
        
        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'action' => $log->action,
                'mileage' => $log->mileage,
                'address' => $log->address,
                'logged_at' => $log->logged_at->format('Y/m/d H:i:s'),
                'time' => $log->logged_at->format('H:i:s'),
            ]
        ]);
    }
    
    public function exportPdf($id)
    {
        $report = DriverDailyReport::with(['driver', 'vehicle'])->findOrFail($id);
        
        $itinerary = DailyItinerary::with(['busAssignment.groupInfo'])
            ->where('driver_id', $report->driver_id)
            ->whereDate('date', $report->date)
            ->first();
        
        $operationLogs = DB::table('driver_operation_logs')
            ->where('driver_id', $report->driver_id)
            ->whereDate('logged_at', $report->date)
            ->orderBy('logged_at', 'asc')
            ->get();
        
        $pairedLogs = [];
        $arrivalLog = null;
        
        foreach ($operationLogs as $log) {
            if ($log->action === '到着') {
                $arrivalLog = $log;
            } elseif ($log->action === '下車' && $arrivalLog) {
                $pairedLogs[] = [
                    'arrival' => $arrivalLog,
                    'disembark' => $log,
                ];
                $arrivalLog = null;
            }
        }
        
        $data = $this->preparePdfData($report, $itinerary, $pairedLogs);
        
        $html = view('masters.daily-reports.pdf', $data)->render();
        
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => sys_get_temp_dir(),
            'default_font' => 'kozgopromedium',
        ]);
        
        $mpdf->shrink_tables_to_fit = 0;
        $mpdf->keep_table_proportions = true;
        
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        
        $mpdf->WriteHTML($html);
        
        return $mpdf->Output('daily_report_' . $report->date . '.pdf', 'D');
    }
    
    private function preparePdfData($report, $itinerary, $pairedLogs)
    {
        $companyInfo = [
            'name' => '',
            'branch' => '',
            'tel' => '',
            'fax' => '',
        ];
        
        try {
            $userCompany = DB::table('user_company_info')->first();
            
            if ($userCompany) {
                $companyInfo['name'] = $userCompany->user_company_name ?? '';
                $companyInfo['tel'] = $userCompany->phone_number ?? '';
                $companyInfo['fax'] = $userCompany->fax_number ?? '';
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
        
        $distance = 0;
        if ($report->start_mileage && $report->end_mileage) {
            $distance = $report->end_mileage - $report->start_mileage;
        }
        
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $dateObj = Carbon::parse($report->date);
        $formattedDate = $dateObj->format('Y年n月j日') . '(' . $weekdays[$dateObj->dayOfWeek] . ')';
        
        $itineraryIds = [];
        foreach ($pairedLogs as $log) {
            $itineraryIds[] = $log['arrival']->itinerary_id;
        }
        
        $itineraries = DailyItinerary::whereIn('id', $itineraryIds)
            ->get()
            ->keyBy('id');
        
        $data = [
            'date' => $formattedDate,
            'report' => $report,
            'itinerary' => $itinerary,
            'pairedLogs' => $pairedLogs,
            'companyInfo' => $companyInfo,
            'distance' => $distance,
            'totalPassengers' => ($itinerary->busAssignment->adult_count ?? 0) + 
                                 ($itinerary->busAssignment->child_count ?? 0) + 
                                 ($itinerary->busAssignment->guide_count ?? 0),
            'adultCount' => $itinerary->busAssignment->adult_count ?? 0,
        ];
        
        $itineraryRows = [];
        for ($i = 0; $i < 12; $i++) {
            if (isset($pairedLogs[$i])) {
                $log = $pairedLogs[$i];
                $itineraryItem = $itineraries[$log['arrival']->itinerary_id] ?? null;
                
                $itineraryRows[] = [
                    'location' => ($itineraryItem->start_location ?? '未指定') . ' → ' . ($itineraryItem->end_location ?? '未指定'),
                    'start_time' => Carbon::parse($log['arrival']->logged_at)->format('H:i'),
                    'end_time' => Carbon::parse($log['disembark']->logged_at)->format('H:i'),
                    'start_mileage' => $log['arrival']->mileage,
                    'end_mileage' => $log['disembark']->mileage,
                    'distance' => ($log['disembark']->mileage - $log['arrival']->mileage),
                ];
            } else {
                $itineraryRows[] = [
                    'location' => '',
                    'start_time' => '',
                    'end_time' => '',
                    'start_mileage' => '',
                    'end_mileage' => '',
                    'distance' => '',
                ];
            }
        }
        $data['itineraryRows'] = $itineraryRows;
        
        return $data;
    }
}