<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VehiclePerformanceExport implements FromArray, WithEvents
{
    protected $dates;
    protected $vehicles;
    protected $statistics;
    protected $companyInfo;
    protected $username;
    protected $exportOptions;
    
    public function __construct($dates, $vehicles, $statistics, $companyInfo, $username, $exportOptions)
    {
        $this->dates = $dates;
        $this->vehicles = $vehicles;
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
        
        $row1 = [];
        for ($i = 0; $i <= $dateCount; $i++) {
            $row1[$i] = '';
        }
        $row1[0] = '車両実績';
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
        
        $headerRow = ['車両名'];
        foreach ($this->dates as $date) {
            $headerRow[] = $date['display'];
        }
        $data[] = $headerRow;
        
        foreach ($this->vehicles as $vehicle) {
            $row = [$vehicle->registration_number];
            foreach ($this->dates as $date) {
                $dateStr = $date['date_str'];
                $stat = $this->statistics[$vehicle->id][$dateStr] ?? ['count' => 0, 'workload' => 0];
                
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
        
        return $data;
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dateCount = count($this->dates);
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dateCount + 1);
                $lastRow = 3 + count($this->vehicles);
                
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
                
                $sheet->getStyle('A4:' . $lastColumn . $lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                $sheet->getStyle('A4:A' . $lastRow)->applyFromArray([
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
                
                for ($row = 4; $row <= $lastRow; $row++) {
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