<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GroupInfosExport implements FromArray, WithEvents
{
    protected $data;
    protected $companyInfo;
    protected $username;
    protected $startDate;
    protected $endDate;

    public function __construct($data, $companyInfo, $username, $startDate, $endDate)
    {
        $this->data = $data;
        $this->companyInfo = $companyInfo;
        $this->username = $username;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $exportData = [];
        
        $exportData[] = ['', '', '', '', '', '', '', '', '', '', ''];
        
        $exportData[] = [
            'No.',
            '期間',
            '予約ID',
            '代理店',
            '团体名',
            '状态',
            '人数',
            '等級',
            '車両',
            '请求',
            '備考'
        ];
        
        foreach ($this->data as $row) {
            $exportData[] = $row;
        }
        
        return $exportData;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'K';
                $lastRow = count($this->data) + 2;
                
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->setCellValue('A1', '予約一覧');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);

                $headerRow = 2;
                $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                ]);

                $dataStartRow = 3;
                $dataEndRow = $lastRow;
                
                $sheet->getStyle('A' . $dataStartRow . ':' . $lastColumn . $dataEndRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);

                $sheet->getStyle('D' . $dataStartRow . ':D' . $dataEndRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getStyle('E' . $dataStartRow . ':E' . $dataEndRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getStyle('I' . $dataStartRow . ':I' . $dataEndRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getStyle('K' . $dataStartRow . ':K' . $dataEndRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                foreach (range('A', $lastColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}