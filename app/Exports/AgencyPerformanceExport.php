<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AgencyPerformanceExport implements FromArray, WithEvents
{
    protected $dates;
    protected $agencies;
    protected $statistics;
    protected $agencyTotals;
    protected $grandTotalReservations;
    protected $grandTotalPoints;
    protected $companyInfo;
    protected $username;
    protected $exportOptions;
    
    public function __construct($dates, $agencies, $statistics, $agencyTotals, $grandTotalReservations, $grandTotalPoints, $companyInfo, $username, $exportOptions)
    {
        $this->dates = $dates;
        $this->agencies = $agencies;
        $this->statistics = $statistics;
        $this->agencyTotals = $agencyTotals;
        $this->grandTotalReservations = $grandTotalReservations;
        $this->grandTotalPoints = $grandTotalPoints;
        $this->companyInfo = $companyInfo;
        $this->username = $username;
        $this->exportOptions = $exportOptions;
    }
    
    public function array(): array
    {
        $data = [];
        
        $dateCount = count($this->dates);
        $lastColumnIndex = $dateCount + 2;
        
        $exportTypeText = '';
        if (in_array('reservations', $this->exportOptions) && in_array('points', $this->exportOptions)) {
            $exportTypeText = '予約数_点数';
        } elseif (in_array('reservations', $this->exportOptions)) {
            $exportTypeText = '予約数';
        } elseif (in_array('points', $this->exportOptions)) {
            $exportTypeText = '点数';
        }
        
        $row1 = [];
        for ($i = 0; $i <= $dateCount + 1; $i++) {
            $row1[$i] = '';
        }
        $row1[0] = '送客実績';
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
        
        $headerRow = ['代理店'];
        foreach ($this->dates as $date) {
            $headerRow[] = $date['display'];
        }
        $headerRow[] = '合計';
        $data[] = $headerRow;
        
        foreach ($this->agencies as $agency) {
            $agencyId = $agency->id;
            $displayName = $agency->agency_name;
            if ($agency->country || $agency->type) {
                $displayName .= '（';
                if ($agency->country) {
                    $displayName .= $agency->country;
                }
                if ($agency->type) {
                    if ($agency->country) {
                        $displayName .= '、';
                    }
                    $displayName .= $agency->type;
                }
                $displayName .= '）';
            }
            
            $row = [$displayName];
            
            $totalReservations = 0;
            $totalPoints = 0;
            
            foreach ($this->dates as $date) {
                $dateStr = $date['date_str'];
                $stat = $this->statistics[$agencyId][$dateStr] ?? ['reservations' => 0, 'points' => 0];
                
                $totalReservations += $stat['reservations'];
                $totalPoints += $stat['points'];
                
                $displayReservations = ($stat['reservations'] != 0) ? $stat['reservations'] : '';
                $displayPoints = ($stat['points'] != 0) ? $stat['points'] : '';
                
                $showReservations = in_array('reservations', $this->exportOptions);
                $showPoints = in_array('points', $this->exportOptions);
                
                if ($showReservations && $showPoints) {
                    if ($stat['reservations'] == 0 && $stat['points'] == 0) {
                        $row[] = '';
                    } else {
                        $row[] = $displayReservations . "\n" . $displayPoints;
                    }
                } elseif ($showReservations) {
                    $row[] = $displayReservations;
                } elseif ($showPoints) {
                    $formattedPoints = is_numeric($displayPoints) && floor($displayPoints) == $displayPoints ? (int)$displayPoints : $displayPoints;
                    $row[] = $formattedPoints;
                } else {
                    $row[] = "";
                }
            }
            
            $totalPointsFormatted = is_numeric($totalPoints) && floor($totalPoints) == $totalPoints ? (int)$totalPoints : $totalPoints;
            $displayTotalReservations = ($totalReservations != 0) ? $totalReservations : '';
            $displayTotalPoints = ($totalPointsFormatted != 0) ? $totalPointsFormatted : '';
            
            $showReservations = in_array('reservations', $this->exportOptions);
            $showPoints = in_array('points', $this->exportOptions);
            
            if ($showReservations && $showPoints) {
                if ($totalReservations == 0 && $totalPoints == 0) {
                    $row[] = '';
                } else {
                    $row[] = $displayTotalReservations . "\n" . $displayTotalPoints;
                }
            } elseif ($showReservations) {
                $row[] = $displayTotalReservations;
            } elseif ($showPoints) {
                $row[] = $displayTotalPoints;
            } else {
                $row[] = "";
            }
            
            $data[] = $row;
        }
        
        $totalRow = ['合計'];
        
        foreach ($this->dates as $date) {
            $dateStr = $date['date_str'];
            $totalReservations = 0;
            $totalPoints = 0;
            
            foreach ($this->agencies as $agency) {
                $agencyId = $agency->id;
                $stat = $this->statistics[$agencyId][$dateStr] ?? ['reservations' => 0, 'points' => 0];
                $totalReservations += $stat['reservations'];
                $totalPoints += $stat['points'];
            }
            
            $displayReservations = ($totalReservations != 0) ? $totalReservations : '';
            $displayPoints = ($totalPoints != 0) ? $totalPoints : '';
            $formattedPoints = is_numeric($displayPoints) && floor($displayPoints) == $displayPoints ? (int)$displayPoints : $displayPoints;
            
            $showReservations = in_array('reservations', $this->exportOptions);
            $showPoints = in_array('points', $this->exportOptions);
            
            if ($showReservations && $showPoints) {
                if ($totalReservations == 0 && $totalPoints == 0) {
                    $totalRow[] = '';
                } else {
                    $totalRow[] = $displayReservations . "\n" . $formattedPoints;
                }
            } elseif ($showReservations) {
                $totalRow[] = $displayReservations;
            } elseif ($showPoints) {
                $totalRow[] = $formattedPoints;
            } else {
                $totalRow[] = "";
            }
        }
        
        $displayGrandReservations = ($this->grandTotalReservations != 0) ? $this->grandTotalReservations : '';
        $displayGrandPoints = ($this->grandTotalPoints != 0) ? $this->grandTotalPoints : '';
        $formattedGrandPoints = is_numeric($displayGrandPoints) && floor($displayGrandPoints) == $displayGrandPoints ? (int)$displayGrandPoints : $displayGrandPoints;
        
        $showReservations = in_array('reservations', $this->exportOptions);
        $showPoints = in_array('points', $this->exportOptions);
        
        if ($showReservations && $showPoints) {
            if ($this->grandTotalReservations == 0 && $this->grandTotalPoints == 0) {
                $totalRow[] = '';
            } else {
                $totalRow[] = $displayGrandReservations . "\n" . $formattedGrandPoints;
            }
        } elseif ($showReservations) {
            $totalRow[] = $displayGrandReservations;
        } elseif ($showPoints) {
            $totalRow[] = $formattedGrandPoints;
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
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateCount + 2);
                
                $dataRowCount = count($this->agencies) + 1;
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
                $dataEndRow = 3 + count($this->agencies);
                
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