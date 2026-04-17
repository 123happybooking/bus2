<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverDailyReport;
use App\Models\Masters\Driver;
use App\Models\Masters\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;
// use Barryvdh\DomPDF\Facade\Pdf;
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
        
        return view('masters.daily-reports.edit', compact('report'));
    }
    
    public function update(Request $request, $id)
    {
        $report = DriverDailyReport::findOrFail($id);
        
        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'start_mileage' => 'nullable|integer|min:0',
            'end_time' => 'nullable|date_format:H:i',
            'end_mileage' => 'nullable|integer|min:0',
        ]);
        
        $report->update([
            'start_time' => $request->start_time,
            'start_mileage' => $request->start_mileage,
            'end_time' => $request->end_time,
            'end_mileage' => $request->end_mileage,
        ]);
        
        return redirect()->route('masters.daily-reports.index')
            ->with('success', '運行日報を更新しました。');
    }
    
    public function exportPdf($id)
    {
        $report = DriverDailyReport::with(['driver', 'vehicle'])->findOrFail($id);
        
        // 生成 HTML
        $html = view('masters.daily-reports.pdf', compact('report'))->render();
        
        // 创建 Mpdf 实例
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => storage_path('temp/pdf'),
            'default_font' => 'kozgopromedium',  // 内置日文字体
        ]);
        
        // 自动识别语言和字体
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        
        // 写入 HTML
        $mpdf->WriteHTML($html);
        
        // 下载 PDF
        return $mpdf->Output('daily_report_' . $report->date . '.pdf', 'D');
    }
}