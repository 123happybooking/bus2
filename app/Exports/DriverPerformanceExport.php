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
    
    public function __construct($dates, $drivers, $statistics, $companyInfo, $username, $exportOptions)
    {
        $this->dates = $dates;
        $this->drivers = $drivers;
        $this->statistics = $statistics;
        $this->companyInfo = $companyInfo;
        $this->username = $username;
        $this->exportOptions = $exportOptions;
    }
    
    public function array(): array
    {
        $data = [];
        
        $dateCount = count($this->dates);
        $lastColumnIndex = $dateCount + 1;
        
        $exportTypeText = '';
        if (in_array('count', $this->exportOptions) && in_array('workload', $this->exportOptions)) {
            $exportTypeText = '予約数_工数';
        } elseif (in_array('count', $this->exportOptions)) {
            $exportTypeText = '予約数';
        } elseif (in_array('workload', $this->exportOptions)) {
            $exportTypeText = '工数';
        }
        
        $row1 = [];
        for ($i = 0; $i <= $dateCount; $i++) {
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
        for ($i = 0; $i <= $dateCount; $i++) {
            $row2[$i] = '';
        }
        $row2[0] = '期間：' . $startDate . ' - ' . $endDate;
        $row2[$lastColumnIndex - 1] = $today . '    ' . $this->username;
        $data[] = $row2;
        
        $headerRow = ['運転手名'];
        foreach ($this->dates as $date) {
            $headerRow[] = $date['display'];
        }
        $data[] = $headerRow;
        
        foreach ($this->drivers as $driver) {
            $row = [$driver->name];
            foreach ($this->dates as $date) {
                $dateStr = $date['date_str'];
                $stat = $this->statistics[$driver->id][$dateStr] ?? ['count' => 0, 'workload' => 0];
                
                $exportCount = in_array('count', $this->exportOptions);
                $exportWorkload = in_array('workload', $this->exportOptions);
                
                if ($exportCount && $exportWorkload) {
                    $row[] = $stat['count'] . "\n" . $stat['workload'];
                } elseif ($exportCount) {
                    $row[] = $stat['count'];
                } elseif ($exportWorkload) {
                    $row[] = $stat['workload'];
                } else {
                    $row[] = "";
                }
            }
            $data[] = $row;
        }
        
        $totalRow = ['合計'];
        foreach ($this->dates as $date) {
            $dateStr = $date['date_str'];
            $totalCount = 0;
            $totalWorkload = 0;
            foreach ($this->drivers as $driver) {
                $stat = $this->statistics[$driver->id][$dateStr] ?? ['count' => 0, 'workload' => 0];
                $totalCount += $stat['count'];
                $totalWorkload += $stat['workload'];
            }
            $formattedWorkload = is_numeric($totalWorkload) && floor($totalWorkload) == $totalWorkload ? (int)$totalWorkload : $totalWorkload;
            
            $exportCount = in_array('count', $this->exportOptions);
            $exportWorkload = in_array('workload', $this->exportOptions);
            
            if ($exportCount && $exportWorkload) {
                $totalRow[] = $totalCount . "\n" . $formattedWorkload;
            } elseif ($exportCount) {
                $totalRow[] = $totalCount;
            } elseif ($exportWorkload) {
                $totalRow[] = $formattedWorkload;
            } else {
                $totalRow[] = "";
            }
        }
        $data[] = $totalRow;
        
        return $data;
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dateCount = count($this->dates);
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateCount + 1);
                
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
                    for ($col = 2; $col <= $dateCount + 1; $col++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $cell = $sheet->getCell($colLetter . $row);
                        $value = $cell->getValue();
                        if (is_string($value) && strpos($value, "\n") !== false) {
                            $cell->getStyle()->getAlignment()->setWrapText(true);
                        }
                    }
                }
                
                for ($i = 1; $i <= $dateCount + 1; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }
            },
        ];
    }
}