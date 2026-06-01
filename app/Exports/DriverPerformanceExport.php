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
        $lastColumnIndex = $dateCount + 2; // +2 因为增加了合计列
        
        $exportTypeText = '';
        if (in_array('count', $this->exportOptions) && in_array('workload', $this->exportOptions)) {
            $exportTypeText = '予約数_工数';
        } elseif (in_array('count', $this->exportOptions)) {
            $exportTypeText = '予約数';
        } elseif (in_array('workload', $this->exportOptions)) {
            $exportTypeText = '工数';
        }
        
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
        
        // 表头 - 添加合计列
        $headerRow = ['運転手名'];
        foreach ($this->dates as $date) {
            $headerRow[] = $date['display'];
        }
        $headerRow[] = '合計';  // 添加合计列表头
        $data[] = $headerRow;
        
        // 数据行 - 计算每行合计
        foreach ($this->drivers as $driver) {
            $row = [$driver->name];
            $rowTotalCount = 0;
            $rowTotalWorkload = 0;
            
            foreach ($this->dates as $date) {
                $dateStr = $date['date_str'];
                $stat = $this->statistics[$driver->id][$dateStr] ?? ['count' => 0, 'workload' => 0];
                
                $rowTotalCount += $stat['count'];
                $rowTotalWorkload += $stat['workload'];
                
                $exportCount = in_array('count', $this->exportOptions);
                $exportWorkload = in_array('workload', $this->exportOptions);
                
                // 0值显示为空
                $displayCount = ($stat['count'] != 0) ? $stat['count'] : '';
                $displayWorkload = ($stat['workload'] != 0) ? $stat['workload'] : '';
                
                if ($exportCount && $exportWorkload) {
                    if ($stat['count'] == 0 && $stat['workload'] == 0) {
                        $row[] = '';
                    } else {
                        $row[] = $displayCount . "\n" . $displayWorkload;
                    }
                } elseif ($exportCount) {
                    $row[] = $displayCount;
                } elseif ($exportWorkload) {
                    $row[] = $displayWorkload;
                } else {
                    $row[] = "";
                }
            }
            
            // 添加该行的合计
            $formattedRowTotalWorkload = is_numeric($rowTotalWorkload) && floor($rowTotalWorkload) == $rowTotalWorkload 
                ? (int)$rowTotalWorkload 
                : $rowTotalWorkload;
            
            $displayTotalCount = ($rowTotalCount != 0) ? $rowTotalCount : '';
            $displayTotalWorkload = ($formattedRowTotalWorkload != 0) ? $formattedRowTotalWorkload : '';
            
            $exportCount = in_array('count', $this->exportOptions);
            $exportWorkload = in_array('workload', $this->exportOptions);
            
            if ($exportCount && $exportWorkload) {
                if ($rowTotalCount == 0 && $rowTotalWorkload == 0) {
                    $row[] = '';
                } else {
                    $row[] = $displayTotalCount . "\n" . $displayTotalWorkload;
                }
            } elseif ($exportCount) {
                $row[] = $displayTotalCount;
            } elseif ($exportWorkload) {
                $row[] = $displayTotalWorkload;
            } else {
                $row[] = "";
            }
            
            $data[] = $row;
        }
        
        // 合计行 - 计算每天的总计和总结算
        $totalRow = ['合計'];
        $dailyTotals = [];
        $grandTotalCount = 0;
        $grandTotalWorkload = 0;
        
        foreach ($this->dates as $date) {
            $dateStr = $date['date_str'];
            $dailyTotals[$dateStr] = ['count' => 0, 'workload' => 0];
        }
        
        foreach ($this->drivers as $driver) {
            foreach ($this->dates as $date) {
                $dateStr = $date['date_str'];
                $stat = $this->statistics[$driver->id][$dateStr] ?? ['count' => 0, 'workload' => 0];
                $dailyTotals[$dateStr]['count'] += $stat['count'];
                $dailyTotals[$dateStr]['workload'] += $stat['workload'];
            }
        }
        
        foreach ($this->dates as $date) {
            $dateStr = $date['date_str'];
            $totalCount = $dailyTotals[$dateStr]['count'];
            $totalWorkload = $dailyTotals[$dateStr]['workload'];
            $formattedWorkload = is_numeric($totalWorkload) && floor($totalWorkload) == $totalWorkload ? (int)$totalWorkload : $totalWorkload;
            
            $grandTotalCount += $totalCount;
            $grandTotalWorkload += $totalWorkload;
            
            $displayCount = ($totalCount != 0) ? $totalCount : '';
            $displayWorkload = ($formattedWorkload != 0) ? $formattedWorkload : '';
            
            $exportCount = in_array('count', $this->exportOptions);
            $exportWorkload = in_array('workload', $this->exportOptions);
            
            if ($exportCount && $exportWorkload) {
                if ($totalCount == 0 && $totalWorkload == 0) {
                    $totalRow[] = '';
                } else {
                    $totalRow[] = $displayCount . "\n" . $displayWorkload;
                }
            } elseif ($exportCount) {
                $totalRow[] = $displayCount;
            } elseif ($exportWorkload) {
                $totalRow[] = $displayWorkload;
            } else {
                $totalRow[] = "";
            }
        }
        
        // 添加总结算到合计行
        $formattedGrandTotalWorkload = is_numeric($grandTotalWorkload) && floor($grandTotalWorkload) == $grandTotalWorkload 
            ? (int)$grandTotalWorkload 
            : $grandTotalWorkload;
        
        $displayGrandCount = ($grandTotalCount != 0) ? $grandTotalCount : '';
        $displayGrandWorkload = ($formattedGrandTotalWorkload != 0) ? $formattedGrandTotalWorkload : '';
        
        $exportCount = in_array('count', $this->exportOptions);
        $exportWorkload = in_array('workload', $this->exportOptions);
        
        if ($exportCount && $exportWorkload) {
            if ($grandTotalCount == 0 && $grandTotalWorkload == 0) {
                $totalRow[] = '';
            } else {
                $totalRow[] = $displayGrandCount . "\n" . $displayGrandWorkload;
            }
        } elseif ($exportCount) {
            $totalRow[] = $displayGrandCount;
        } elseif ($exportWorkload) {
            $totalRow[] = $displayGrandWorkload;
        } else {
            $totalRow[] = "";
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
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateCount + 2); // +2 因为有合计列
                
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