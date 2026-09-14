<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DriverPerformanceExport implements FromArray, WithEvents
{
    protected $dates;
    protected $drivers;
    protected $statistics;
    protected $companyInfo;
    protected $username;
    protected $exportOptions;
    protected $totalStatistics;
    
    public function __construct($dates, $drivers, $statistics, $companyInfo, $username, $exportOptions, $totalStatistics = [])
    {
        $this->dates = $dates;
        $this->drivers = $drivers;
        $this->statistics = $statistics;
        $this->companyInfo = $companyInfo;
        $this->username = $username;
        $this->exportOptions = $exportOptions;
        $this->totalStatistics = $totalStatistics;
    }
    
    public function array(): array
    {
        $data = [];
        
        $dateCount = count($this->dates);
        $lastColumnIndex = $dateCount + 2;
        
        $exportCount = in_array('count', $this->exportOptions);
        $exportWorkload = in_array('workload', $this->exportOptions);
        $exportWorkHours = in_array('work_hours', $this->exportOptions);
        
        $exportTypeText = '';
        $typeTexts = [];
        if ($exportCount) $typeTexts[] = '予約数';
        if ($exportWorkload) $typeTexts[] = '工数';
        if ($exportWorkHours) $typeTexts[] = '勤務時間';
        $exportTypeText = implode('_', $typeTexts);
        
        $row1 = [];
        for ($i = 0; $i <= $dateCount + 1; $i++) {
            $row1[$i] = '';
        }
        $row1[0] = '運転手実績';
        $row1[1] = $exportTypeText;
        $row1[$lastColumnIndex - 1] = $this->companyInfo['name'] ?? '';
        $data[] = $row1;
        
        $startDate = $this->dates[0]['date']->format('Y/m/d');
        $endDate = $this->dates[$dateCount - 1]['date']->format('Y/m/d');
        $today = now()->format('Y/m/d');
        
        $row2 = [];
        for ($i = 0; $i <= $dateCount + 1; $i++) {
            $row2[$i] = '';
        }
        $row2[0] = '期間：' . $startDate . ' - ' . $endDate;
        $row2[$lastColumnIndex - 1] = $today . '    ' . $this->username;
        $data[] = $row2;
        
        $headerRow = ['運転手名'];
        foreach ($this->dates as $date) {
            $headerRow[] = $date['display'];
        }
        $headerRow[] = '合計';
        $data[] = $headerRow;
        
        foreach ($this->drivers as $driver) {
            $row = [$driver->name];
            $rowTotalCount = 0;
            $rowTotalWorkload = 0;
            $rowTotalWorkHours = 0;
            
            foreach ($this->dates as $date) {
                $dateStr = $date['date_str'];
                $stat = $this->statistics[$driver->id][$dateStr] ?? ['count' => 0, 'workload' => 0, 'work_hours' => 0];
                
                $rowTotalCount += $stat['count'];
                $rowTotalWorkload += $stat['workload'];
                $rowTotalWorkHours += $stat['work_hours'] ?? 0;
                
                $displayCount = ($stat['count'] != 0) ? $stat['count'] : '';
                $displayWorkload = ($stat['workload'] != 0) ? $stat['workload'] : '';
                $displayWorkHours = (($stat['work_hours'] ?? 0) > 0) ? $stat['work_hours'] : '';
                
                $row[] = $this->buildCellValue($displayCount, $displayWorkload, $displayWorkHours, $stat['count'], $stat['workload'], $stat['work_hours'] ?? 0, $exportCount, $exportWorkload, $exportWorkHours);
            }
            
            $formattedRowTotalWorkload = is_numeric($rowTotalWorkload) && floor($rowTotalWorkload) == $rowTotalWorkload 
                ? (int)$rowTotalWorkload 
                : $rowTotalWorkload;
            
            $displayTotalCount = ($rowTotalCount != 0) ? $rowTotalCount : '';
            $displayTotalWorkload = ($formattedRowTotalWorkload != 0) ? $formattedRowTotalWorkload : '';
            $displayTotalWorkHours = ($rowTotalWorkHours > 0) ? $rowTotalWorkHours : '';
            
            $row[] = $this->buildCellValue($displayTotalCount, $displayTotalWorkload, $displayTotalWorkHours, $rowTotalCount, $rowTotalWorkload, $rowTotalWorkHours, $exportCount, $exportWorkload, $exportWorkHours);
            
            $data[] = $row;
        }
        
        $totalRow = ['合計'];
        $dailyTotals = [];
        $grandTotalCount = 0;
        $grandTotalWorkload = 0;
        $grandTotalWorkHours = 0;
        
        foreach ($this->dates as $date) {
            $dateStr = $date['date_str'];
            $dailyTotals[$dateStr] = ['count' => 0, 'workload' => 0, 'work_hours' => 0];
        }
        
        foreach ($this->drivers as $driver) {
            foreach ($this->dates as $date) {
                $dateStr = $date['date_str'];
                $stat = $this->statistics[$driver->id][$dateStr] ?? ['count' => 0, 'workload' => 0, 'work_hours' => 0];
                $dailyTotals[$dateStr]['count'] += $stat['count'];
                $dailyTotals[$dateStr]['workload'] += $stat['workload'];
                $dailyTotals[$dateStr]['work_hours'] += $stat['work_hours'] ?? 0;
            }
        }
        
        foreach ($this->dates as $date) {
            $dateStr = $date['date_str'];
            $totalCount = $dailyTotals[$dateStr]['count'];
            $totalWorkload = $dailyTotals[$dateStr]['workload'];
            $totalWorkHours = $dailyTotals[$dateStr]['work_hours'];
            $formattedWorkload = is_numeric($totalWorkload) && floor($totalWorkload) == $totalWorkload ? (int)$totalWorkload : $totalWorkload;
            
            $grandTotalCount += $totalCount;
            $grandTotalWorkload += $totalWorkload;
            $grandTotalWorkHours += $totalWorkHours;
            
            $displayCount = ($totalCount != 0) ? $totalCount : '';
            $displayWorkload = ($formattedWorkload != 0) ? $formattedWorkload : '';
            $displayWorkHours = ($totalWorkHours > 0) ? $totalWorkHours : '';
            
            $totalRow[] = $this->buildCellValue($displayCount, $displayWorkload, $displayWorkHours, $totalCount, $totalWorkload, $totalWorkHours, $exportCount, $exportWorkload, $exportWorkHours);
        }
        
        $formattedGrandTotalWorkload = is_numeric($grandTotalWorkload) && floor($grandTotalWorkload) == $grandTotalWorkload 
            ? (int)$grandTotalWorkload 
            : $grandTotalWorkload;
        
        $displayGrandCount = ($grandTotalCount != 0) ? $grandTotalCount : '';
        $displayGrandWorkload = ($formattedGrandTotalWorkload != 0) ? $formattedGrandTotalWorkload : '';
        $displayGrandWorkHours = ($grandTotalWorkHours > 0) ? $grandTotalWorkHours : '';
        
        $totalRow[] = $this->buildCellValue($displayGrandCount, $displayGrandWorkload, $displayGrandWorkHours, $grandTotalCount, $grandTotalWorkload, $grandTotalWorkHours, $exportCount, $exportWorkload, $exportWorkHours);
        
        $data[] = $totalRow;
        
        return $data;
    }
    
    private function buildCellValue($displayCount, $displayWorkload, $displayWorkHours, $rawCount, $rawWorkload, $rawWorkHours, $exportCount, $exportWorkload, $exportWorkHours)
    {
        $parts = [];
        
        if ($exportCount && $displayCount !== '') {
            $parts[] = $displayCount;
        }
        if ($exportWorkload && $displayWorkload !== '') {
            $parts[] = $displayWorkload;
        }
        if ($exportWorkHours && $displayWorkHours !== '') {
            $parts[] = $displayWorkHours;
        }
        
        if (empty($parts)) {
            return '';
        }
        
        return implode("\n", $parts);
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dateCount = count($this->dates);
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateCount + 2);
                
                $dataRowCount = count($this->drivers) + 1;
                $lastRow = 3 + $dataRowCount;
                
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);
                
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10],
                ]);
                
                $sheet->getStyle($lastColumn . '1')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                
                $sheet->getStyle($lastColumn . '2')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                
                $dateRow = 3;
                $startCol = 'B';
                $endCol = $lastColumn;
                
                $sheet->getStyle($startCol . $dateRow . ':' . $endCol . $dateRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                ]);
                
                $sheet->getStyle('A' . $dateRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                ]);
                
                $dataStartRow = 4;
                $dataEndRow = 3 + count($this->drivers);
                
                $sheet->getStyle('A' . $dataStartRow . ':' . $lastColumn . $dataEndRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                $sheet->getStyle('A' . $dataStartRow . ':A' . $dataEndRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                $totalRow = $dataEndRow + 1;
                $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                ]);
                
                $sheet->getStyle('A' . $totalRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                $sheet->getStyle('A3:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                for ($row = $dataStartRow; $row <= $lastRow; $row++) {
                    for ($col = 2; $col <= $dateCount + 2; $col++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $cell = $sheet->getCell($colLetter . $row);
                        $value = $cell->getValue();
                        if (is_string($value) && strpos($value, "\n") !== false) {
                            $cell->getStyle()->getAlignment()->setWrapText(true);
                        }
                    }
                }
                
                for ($i = 1; $i <= $dateCount + 2; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }
            },
        ];
    }
}