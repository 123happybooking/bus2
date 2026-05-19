@extends('layouts.app')

@section('content')
<style>
    /* --- 全局通用样式 --- */
    .bs-container {
        padding: 15px;
        background-color: #f4f6f9;
        min-height: 100vh;
    }
    .main-section {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    .section-header {
        font-size: 1.25rem;
        font-weight: bold;
        color: #333;
        border-left: 5px solid #28a745;
        padding-left: 10px;
        margin-bottom: 20px;
        margin-top: 30px;
    }
    .section-header:first-of-type {
        margin-top: 0;
    }

    /* --- 表格基础样式 (无边框版) --- */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }
    /* 去掉所有默认的粗黑线 */
    .custom-table th,
    .custom-table td {
        padding: 6px 10px;
        vertical-align: middle;
        text-align: right; /* 默认右对齐 */
        font-size: 0.9rem;
        border: none; /* 核心：去掉单元格边框 */
    }
    /* 项目名称列左对齐 */
    .custom-table td.item-name {
        text-align: left;
        color: #444;
    }
    /* 表头样式 */
    .custom-table thead th {
        background-color: #f8f9fa;
        text-align: right;
        font-weight: bold;
        color: #555;
    }
    /* 只有表头的“项目”文字左对齐 */
    .custom-table thead th.text-start {
        text-align: left;
    }

    /* --- 输入框样式 --- */
    .form-input {
        width: 100%;
        max-width: 140px; /* 限制输入框宽度 */
        padding: 5px 8px;
        text-align: right;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: #fffbe6; /* 黄色背景 */
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .form-input:focus {
        background-color: #fff;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        outline: none;
    }

    /* --- B/S表特有样式 (带边框和合计) --- */
    .bs-table {
        border: 1px solid #dee2e6; /* B/S表保留外框 */
    }
    .bs-table th, .bs-table td {
        border: 1px solid #dee2e6; /* B/S表保留内框 */
    }
    .bs-section-title {
        background-color: #e9ecef;
        font-weight: bold;
        text-align: left !important;
    }
    .bs-subtotal {
        background-color: #f8f9fa;
    }

    /* 按钮 */
    .btn-action {
        padding: 0.15rem 0.5rem;
        font-size: 0.8rem;
        margin-left: 5px;
    }
</style>

<div class="container-fluid bs-container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="position-relative">
            <select id="periodSelect" name="period_id" class="form-select form-select-sm" style="min-width: 140px;"> 
                @foreach($periods as $period)
                    <option value="{{ $period->id }}" 
                        data-start="{{ $period->start }}" 
                        data-end="{{ $period->end }}"
                        {{ $period_id == $period->id ? 'selected' : '' }}>
                        {{ $period->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <!-- <a href="{{ route('masters.account-cash-ins.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> 新規追加
            </a> -->
            <a id="downloadPdf" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> PDFダウンロード
            </a>
        </div>
        
    </div>

    <!-- ======================================================= -->
    <!-- 1. 貸借対照表 (B/S) - 保持原有逻辑，应用新样式 -->
    <!-- ======================================================= -->
    <div class="main-section">
        <div class="section-header">貸借対照表 (B/S)</div>
        <table class="custom-table bs-table mb-0">
            <thead>
                <tr>
                    <th style="width: 35%;" class="text-start">項目</th>
                    <th style="width: 18%;">当期</th>
                    <th style="width: 18%;">前期</th>
                    <th style="width: 14%;">増減</th>
                    <th style="width: 15%;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashIns as $sectionIndex => $section)
                    @php 
                        $items = $section['items']; 
                        $title = $section['title'];
                        // 定义特定的标题常量（根据你的描述）
                        $carryOverAsset = '繰延資産'; // 繰延资产
                        $fixedLiability = '固定負債'; // 固定负债
                        $capitalSection = '資本の部'; // 资本的部
                    @endphp
                    
                    {{-- 分组标题 --}}
                    <tr class="section-title">
                        <td colspan="5" class="bs-section-title">{{ $title }}</td>
                    </tr>
                    
                    {{-- 数据行 --}}
                    @foreach($items as $item)
                    <tr>
                        <td class="item-name">{{ $item->title }}</td>
                        <td>
                            <input type="text" class="form-input input-current" value="{{ $item->cashInData?->current_amount ? number_format($item->cashInData?->current_amount) : ''}}" data-pk="{{ $item->id }}">
                        </td>
                        <td>
                            <input type="text" class="form-input input-previous" value="{{ $item->cashInData?->previous_amount ? number_format($item->cashInData?->previous_amount) : '' }}" data-pk="{{ $item->id }}">
                        </td>
                        <td>
                            <input type="text" class="form-input input-diff bg-light" readonly value="{{ number_format(($item->cashInData->current_amount ?? 0) - ($item->cashInData->previous_amount ?? 0)) }}">
                        </td>
                        <td>
                            <a href="{{ route('masters.account-cash-ins.edit', $item->id) }}" class="btn btn-primary btn-sm btn-action">編集</a>
                            <!-- <form action="{{ route('masters.account-cash-ins.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('本当に削除しますか？');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-action">削除</button>
                            </form> -->
                        </td>
                    </tr>
                    @endforeach
                    
                    {{-- 小计行 (原有的) --}}
                    <tr class="bs-subtotal">
                        <td class="text-end">{{ $section['title'] }}合計</td>
                        <td><span class="sum-current">0</span></td>
                        <td><span class="sum-previous">0</span></td>
                        <td><span class="sum-diff"></span></td>
                        <td></td>
                    </tr>

                    {{-- 新增：资产合计行 (当标题是 "繰延資産" 时添加) --}}
                    @if($title == '繰延資產')
                    <tr   id="total-assets-row" class="bs-total-row asset-total">
                        <td class="text-end">資産合計</td>
                        <td><span class="total-current">0</span></td>
                        <td><span class="total-previous">0</span></td>
                        <td><span class="total-diff"></span></td>
                        <td></td>
                    </tr>
                    @endif
                    @if($title == '固定負債')
                    <tr id="total-liabilities-row" class="bs-total-row asset-total">
                        <td class="text-end">負債合計</td>
                        <td><span class="total-current">0</span></td>
                        <td><span class="total-previous">0</span></td>
                        <td><span class="total-diff"></span></td>
                        <td></td>
                    </tr>
                    @endif
                    @if($title == '資本の部')
                    <tr id="total-final-row" class="bs-total-row asset-total">
                        <td class="text-end">負債·資本合計</td>
                        <td><span class="total-current">0</span></td>
                        <td><span class="total-previous">0</span></td>
                        <td><span class="total-diff"></span></td>
                        <td></td>
                    </tr>
                    @endif



                @endforeach
                <tr class="table-active fw-bold">
                    <td class="text-end">貸借差額</td>
                    <td class="text-center" id="current-balance-cell">
                        <!-- 内容由 JS 动态生成 -->
                    </td>

                    <td class="text-center" id="previous-balance-cell">
                        <!-- 内容由 JS 动态生成 -->
                    </td>
                </tr>
                {{-- 新增：负债合计行 和 负债资本合计行 (在所有循环结束后添加) --}}
                @php
                @endphp
            </tbody>
        </table>
    </div>
    

    <!-- ======================================================= -->
    <!-- 2. 損益計算書 (P/L) - 新增部分 -->
    <!-- ======================================================= -->
    <div class="main-section">
        <div class="section-header">【損益計算書】</div>

        <table class="custom-table mb-0">
            <thead>
                <tr>
                    <th style="width: 60%;" class="text-start">項目</th>
                    <th style="width: 25%;">当期</th>
                    <th style="width: 15%;">操作</th> <!-- 备注列 -->
                </tr>
            </thead>
            <tbody>
                @foreach($cashSunIns as $item)
                    <tr>
                        <td class="item-name">{{ $item->title }}</td>
                        <td>
                            <input type="text" class="form-input pl-input"
                                   value="{{$item->cashInData?->current_amount ? number_format($item->cashInData?->current_amount) : '' }}"
                                   data-pk="{{ $item->id }}">
                        </td>
                        <td>
                            <a href="{{ route('masters.account-cash-ins.edit', $item->id) }}" class="btn btn-primary btn-sm btn-action">編集</a>
                            <!-- <form action="{{ route('masters.account-cash-ins.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('本当に削除しますか？');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-action">削除</button>
                            </form> -->
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ======================================================= -->
    <!-- 3. 前期利益処分計算書 - 新增部分 -->
    <!-- ======================================================= -->
    <div class="main-section">
        <div class="section-header">【前期の利益処分計算書のうち】</div>

        <table class="custom-table mb-0">
            <thead>
                <tr>
                    <th style="width: 60%;" class="text-start">項目</th>
                    <th style="width: 25%;">当期</th>
                    <th style="width: 15%;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashQianIns as $item)
                    <tr>
                        <td class="item-name">{{ $item->title }}</td>
                        <td>
                            <input type="text" class="form-input ql-input"
                                   value="{{ $item->cashInData?->current_amount ? number_format($item->cashInData?->current_amount) : '' }}"
                                   data-pk="{{ $item->id }}">
                        </td>
                        <td>
                            <a href="{{ route('masters.account-cash-ins.edit', $item->id) }}" class="btn btn-primary btn-sm btn-action">編集</a>
                            <!-- <form action="{{ route('masters.account-cash-ins.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('本当に削除しますか？');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-action">削除</button>
                            </form> -->
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-end mt-4 mb-5">
        <button id="saveAllBtn" class="btn btn-primary">保存</button>
    </div>

</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. 下拉框监听逻辑 ---
        const periodSelect = document.getElementById('periodSelect');
        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                const selectedPeriodId = this.value;
                if (!selectedPeriodId) return;
                const url = new URL(window.location.href);
                url.searchParams.set('period_id', selectedPeriodId);
                url.hash = '';
                window.location.href = url.toString();
            });
        }

        // --- 2. 输入监听逻辑 ---
        // B/S 表逻辑 (带计算)
        const bsInputs = document.querySelectorAll('.bs-table .form-input:not(.bg-light)');
        bsInputs.forEach(input => {
            input.addEventListener('input', function() {
                formatNumber(this);
                const row = this.closest('tr');
                calculateRowDiff(row);
                // 关键：小计更新后，立即触发最终合计更新
                updateSectionSubtotal(row);
                updateFinalTotals();
            });
        });

        // P/L & 前期 逻辑 (仅格式化)
        const simpleInputs = document.querySelectorAll('.pl-input, .ql-input');
        simpleInputs.forEach(input => {
            input.addEventListener('input', function() {
                formatNumber(this);
            });
        });

        // --- 公共函数 ---
        // 1. 数字格式化 (千分位)
        function formatNumber(input) {
            let val = input.value.replace(/,/g, '');
            if (val === '' || isNaN(val)) {
                input.value = '';
                return;
            }
            const isNegative = val.startsWith('-');
            val = val.replace('-', '');
            const formatted = parseInt(val).toLocaleString('ja-JP');
            const finalVal = isNegative ? '-' + formatted : formatted;
            if (input.value !== finalVal) {
                input.value = finalVal;
            }
        }

        // 2. 计算行差额
        function calculateRowDiff(row) {
            const currentVal = parseInt(row.querySelector('.input-current').value.replace(/,/g, '') || 0);
            const prevVal = parseInt(row.querySelector('.input-previous').value.replace(/,/g, '') || 0);
            const diffVal = currentVal - prevVal;
            const diffInput = row.querySelector('.input-diff');
            diffInput.value = diffVal.toLocaleString('ja-JP');
        }

        // 3. 更新分组小计
        function updateSectionSubtotal(row) {
            // 1. 找到当前行所属的分组标题
            let currentRow = row;
            let sectionTitle = null;
            while ((currentRow = currentRow.previousElementSibling)) {
                if (currentRow.classList.contains('section-title')) {
                    sectionTitle = currentRow;
                    break;
                }
            }

            // 2. 从分组标题开始，向下累加数据
            let sumCurrent = 0;
            let sumPrevious = 0;
            let scanRow = sectionTitle ? sectionTitle : row.closest('tbody').firstElementChild;
            while ((scanRow = scanRow.nextElementSibling)) {
                // 遇到下一个分组标题，停止
                if (scanRow.classList.contains('section-title')) {
                    break;
                }
                // 遇到小计行，跳过
                if (scanRow.classList.contains('bs-subtotal')) {
                    continue;
                }
                
                const cInput = scanRow.querySelector('.input-current');
                const pInput = scanRow.querySelector('.input-previous');
                if (cInput) {
                    const cVal = parseInt(cInput.value.replace(/,/g, '') || 0);
                    const pVal = parseInt(pInput.value.replace(/,/g, '') || 0);
                    sumCurrent += cVal;
                    sumPrevious += pVal;
                }
            }

            // 3. 更新UI
            let subtotalRow = null;
            let tempRow = row;
            while ((tempRow = tempRow.nextElementSibling)) {
                if (tempRow.classList.contains('bs-subtotal')) {
                    subtotalRow = tempRow;
                    break;
                }
                if (tempRow.classList.contains('section-title')) break;
            }

            if (subtotalRow) {
                subtotalRow.querySelector('.sum-current').textContent = sumCurrent.toLocaleString('ja-JP');
                subtotalRow.querySelector('.sum-previous').textContent = sumPrevious.toLocaleString('ja-JP');
                // subtotalRow.querySelector('.sum-diff').textContent = (sumCurrent - sumPrevious).toLocaleString('ja-JP');
            }
        }

        // --- 新增：计算最终合计 (资产合计、负债合计、负债资本合计) ---
        function updateFinalTotals() {
            // 初始化变量
            let assetsCurrent = 0, assetsPrevious = 0; // 资产合计
            let liabilitiesCurrent = 0, liabilitiesPrevious = 0; // 负债合计
            let capitalCurrent = 0, capitalPrevious = 0; // 资本合计

            // 遍历所有的小计行进行分类累加
            document.querySelectorAll('.bs-table .bs-subtotal').forEach(row => {
                // 获取该行的标题
                let titleRow = row.previousElementSibling;
                while (titleRow && !titleRow.classList.contains('section-title')) {
                    titleRow = titleRow.previousElementSibling;
                }
                if (!titleRow) return;

                const titleText = titleRow.querySelector('.bs-section-title').textContent.trim();
                const current = parseInt(row.querySelector('.sum-current').textContent.replace(/,/g, '') || 0);
                const previous = parseInt(row.querySelector('.sum-previous').textContent.replace(/,/g, '') || 0);

                // 分类累加逻辑
                if (titleText === '流動資産' || titleText === '固定資産' || titleText === '繰延資產') {
                    assetsCurrent += current;
                    assetsPrevious += previous;
                }
                if (titleText === '流動負債' || titleText === '固定負債') {
                    liabilitiesCurrent += current;
                    liabilitiesPrevious += previous;
                }
                if (titleText === '資本の部') {
                    capitalCurrent += current;
                    capitalPrevious += previous;
                }
            });

            // 1. 渲染【资产合计】
            const assetRow = document.getElementById('total-assets-row');
            if (assetRow) {
                assetRow.querySelector('.total-current').textContent = assetsCurrent.toLocaleString('ja-JP');
                assetRow.querySelector('.total-previous').textContent = assetsPrevious.toLocaleString('ja-JP');
                // assetRow.querySelector('.total-diff').textContent = (assetsCurrent - assetsPrevious).toLocaleString('ja-JP');
            }

            // 2. 渲染【负债合计】
            const liabilityRow = document.getElementById('total-liabilities-row');
            if (liabilityRow) {
                liabilityRow.querySelector('.total-current').textContent = liabilitiesCurrent.toLocaleString('ja-JP');
                liabilityRow.querySelector('.total-previous').textContent = liabilitiesPrevious.toLocaleString('ja-JP');
                // liabilityRow.querySelector('.total-diff').textContent = (liabilitiesCurrent - liabilitiesPrevious).toLocaleString('ja-JP');
            }

            // 3. 渲染【负债资本合计】
            const finalRow = document.getElementById('total-final-row');
            if (finalRow) {
                const totalCurrent = liabilitiesCurrent + capitalCurrent;
                const totalPrevious = liabilitiesPrevious + capitalPrevious;
                finalRow.querySelector('.total-current').textContent = totalCurrent.toLocaleString('ja-JP');
                finalRow.querySelector('.total-previous').textContent = totalPrevious.toLocaleString('ja-JP');
                // finalRow.querySelector('.total-diff').textContent = (totalCurrent - totalPrevious).toLocaleString('ja-JP');
                
                // 传递参数给平衡检查
                updateBalanceCheck(assetsCurrent, totalCurrent);
            }
        }


        function updateBalanceCheck(assetTotal, liabilityCapitalTotal) {
            // 1. 获取显示容器
            const currentCell = document.getElementById('current-balance-cell');
            const previousCell = document.getElementById('previous-balance-cell');
            
            // 2. 获取表格中的原始数值 (用于计算前期差额)
            // 假设资产合计行的当期值就是 assetTotal，我们需要获取资产合计的前期值
            const assetRow = document.getElementById('total-assets-row');
            const liabilityCapitalRow = document.getElementById('total-final-row'); // 负债资本合计行

            if (!assetRow || !liabilityCapitalRow) return;

            // 获取资产合计的前期值
            const assetPrevText = assetRow.querySelector('.total-previous').textContent.replace(/,/g, '');
            const assetPrevious = parseInt(assetPrevText) || 0;

            // 获取负债资本合计的前期值
            const lcPrevText = liabilityCapitalRow.querySelector('.total-previous').textContent.replace(/,/g, '');
            const lcPrevious = parseInt(lcPrevText) || 0;

            // 3. 计算差额
            const currentDiff = assetTotal - liabilityCapitalTotal; // 当期差额
            const previousDiff = assetPrevious - lcPrevious;       // 前期差额

            // 4. 渲染当期状态 (第1列)
            let currentStatus = currentDiff === 0 ? 'OK' : 'NG';
            let currentClass = currentDiff === 0 ? 'bg-success' : 'bg-danger';
            // 显示格式：OK (1,000) 或 NG (1,000)
            currentCell.innerHTML = `
                <span class="badge ${currentClass}">
                    ${currentStatus} (${currentDiff.toLocaleString('ja-JP')})
                </span>
            `;

            // 5. 渲染前期状态 (第2列)
            let previousStatus = previousDiff === 0 ? 'OK' : 'NG';
            let previousClass = previousDiff === 0 ? 'bg-success' : 'bg-danger';
            // 显示格式：OK (500) 或 NG (500)
            previousCell.innerHTML = `
                <span class="badge ${previousClass}">
                    ${previousStatus} (${previousDiff.toLocaleString('ja-JP')})
                </span>
            `;
        }

        // --- 3. 保存按钮逻辑 ---
        document.getElementById('saveAllBtn')?.addEventListener('click', function() {
            const allData = [];
            const periodId = document.getElementById('periodSelect').value;

            // 1. 收集 B/S 表数据
            document.querySelectorAll('.bs-table tbody tr').forEach(row => {
                const input = row.querySelector('.input-current');
                if (input) {
                    allData.push({
                        id: input.dataset.pk,
                        mod: 1,
                        current_amount: parseInt(input.value.replace(/,/g, '') || 0),
                        previous_amount: parseInt(row.querySelector('.input-previous')?.value.replace(/,/g, '') || 0)
                    });
                }
            });

            // 2. 收集 P/L 表数据
            document.querySelectorAll('.pl-input').forEach(input => {
                allData.push({
                    id: input.dataset.pk,
                    mod: 2,
                    current_amount: parseInt(input.value.replace(/,/g, '') || 0),
                });
            });

            // 3. 收集 前期利益处分 表数据
            document.querySelectorAll('.ql-input').forEach(input => {
                allData.push({
                    id: input.dataset.pk,
                    mod: 3,
                    current_amount: parseInt(input.value.replace(/,/g, '') || 0),
                });
            });

            const requestData = {
                period_id: periodId,
                data: allData
            };

            // 提交数据
            fetch('{{ route("masters.account-cash-ins.save-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('保存失败');
            });
        });

        // --- 页面加载完成时初始化 ---
        window.addEventListener('load', function() {
            // 1. 先计算所有分组的小计
            document.querySelectorAll('.bs-table .bs-subtotal').forEach(subtotalRow => {
                // 找到该组内的任意一个数据行来触发计算
                let dataRow = subtotalRow.previousElementSibling;
                while (dataRow && !dataRow.classList.contains('section-title')) {
                    if (dataRow.querySelector('.input-current')) {
                        updateSectionSubtotal(dataRow);
                        break;
                    }
                    dataRow = dataRow.previousElementSibling;
                }
            });
            // 2. 最后计算最终的大合计
            updateFinalTotals();
        });
    });


    const currentParams = new URLSearchParams(window.location.search);

    const baseUrl = "{{ route('masters.account-cash-ins.pdf') }}";

    // 构建最终 URL
    let finalUrl = baseUrl;
    if (currentParams.toString()) {
        // 判断 baseUrl 是否已包含参数
        finalUrl += baseUrl.includes('?') ? '&' : '?';
        finalUrl += currentParams.toString();
    }

    // 赋值给按钮
    document.getElementById('downloadPdf').href = finalUrl;
</script>
@endsection