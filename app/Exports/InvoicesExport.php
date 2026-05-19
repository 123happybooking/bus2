<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
// 需要引入 Border 类来定义线条
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InvoicesExport implements FromView, WithStyles 
{
    protected $datas;
    protected $view; 

    public function __construct(array $datas , string $view)
    {
        $this->datas = $datas;
        $this->view = $view;
    }

    public function view(): View
    {
        return view($this->view, $this->datas);
    }

    /**
     * 删除 registerEvents 方法，我们全部在这里处理
     */
    public function styles(Worksheet $sheet)
    {
        // 1. 获取表格范围
        // 注意：因为数据是从 View 渲染进来的，我们需要确保获取到正确的范围
        // 如果你的表格从 A1 开始，可以直接用下面的逻辑
        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();
        
        // 如果表格可能为空，做个简单的保护
        if ($highestRow < 1) $highestRow = 1;
        if (!$highestColumn) $highestColumn = 'A';

        $cellRange = 'A1:' . $highestColumn . $highestRow;

        // 2. 返回样式数组
        return [
            // 对整个范围应用样式
            $cellRange => [
                // --- 边框设置 ---
                'borders' => [
                    // 设置所有边框为细实线
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'], // 黑色线条
                    ],
                ],

                // --- 对齐设置 ---
                'alignment' => [
                    // 水平居中 (你可以改成 HORIZONTAL_LEFT 或 HORIZONTAL_RIGHT)
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    // 垂直居中
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}