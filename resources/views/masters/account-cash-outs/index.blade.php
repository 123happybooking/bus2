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
    .custom-table th,
    .custom-table td {
        padding: 6px 10px;
        vertical-align: middle;
        text-align: right;
        font-size: 0.9rem;
        border: none;
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
    .custom-table thead th.text-start {
        text-align: left;
    }

    /* --- 输入框样式 --- */
    .form-input {
        width: 100%;
        max-width: 140px;
        padding: 5px 8px;
        text-align: right;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: #fffbe6;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .form-input:focus {
        background-color: #fff;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        outline: none;
    }

    /* --- B/S表特有样式 --- */
    .bs-table {
        border: 1px solid #dee2e6;
    }
    .bs-table th,
    .bs-table td {
        border: 1px solid #dee2e6;
    }
    .bs-section-title {
        background-color: #e9ecef;
        font-weight: bold;
        text-align: left !important;
    }
    .bs-subtotal {
        background-color: #f8f9fa;
    }

    /* --- 通用 --- */
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
                    <option value="{{ $period->id }}" data-start="{{ $period->start }}" data-end="{{ $period->end }}" {{ $period_id == $period->id ? 'selected' : '' }}>
                        {{ $period->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <!-- <a href="{{ route('masters.account-cash-outs.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> 新規追加
            </a> -->
            <a id="downloadPdf" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> PDFダウンロード
            </a>
        </div>
    </div>


    <div class="main-section">
        <table class="custom-table bs-table mb-0">
            <thead>
                <tr>
                    <th style="width: 35%;" class="text-start">項目</th>
                    <th style="width: 18%;">金额</th> <!-- 注意：原代码colspan=5已修正为正常结构 -->
                    <th style="width: 15%;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashOuts as $sectionIndex => $section)
                    @php 
                        $items = $section['items']; 
                        $title = $section['title']; 
                    @endphp
                    {{-- 分组标题 --}}
                    <tr class="section-title">
                        <td colspan="3" class="bs-section-title">{{ $title }}</td> <!-- 修正：colspan改为3，匹配实际列数 -->
                    </tr>
                    {{-- 数据行 --}}
                    @foreach($items as $item)
                        <tr>
                            <td class="item-name">{{ $item->title }}</td>
                            <td>
                                <input type="text" class="form-input input-current" 
                                       value="{{ $item->cashOutData?->current_amount ? number_format($item->cashOutData?->current_amount) : ''}}" 
                                       data-pk="{{ $item->id }}" data-typeid="{{ $item->type_id }}">
                            </td>
                            <td>
                                <a href="{{ route('masters.account-cash-outs.edit', $item->id) }}" class="btn btn-primary btn-sm btn-action">編集</a>
                                <!-- <form action="{{ route('masters.account-cash-outs.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('本当に削除しますか？');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-action">削除</button>
                                </form> -->
                            </td>
                        </tr>
                    @endforeach
                    {{-- 小计行 --}}
                    <tr class="bs-subtotal">
                        <td class="text-end">小計</td>
                        <td><span class="sum-current">0</span></td>
                        <td></td>
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

        // --- 2. 输入监听逻辑 (仅保留B/S表逻辑) ---
        const bsInputs = document.querySelectorAll('.bs-table .form-input');
        bsInputs.forEach(input => {
            input.addEventListener('input', function() {
                formatNumber(this);
                const row = this.closest('tr');
                updateSectionSubtotal(row);
            });
        });

        // --- 公共函数 ---
        // 1. 数字格式化 (千分位)
        function formatNumber(input) {
            let val = input.value.replace(/,/g, '');
            if (val === '' || val === '-') {
                return; // 直接返回，不进行后面的格式化，也不清空
            }
            const isNegative = val.startsWith('-');
            val = val.replace('-', '');
            const formatted = parseInt(val).toLocaleString('ja-JP');
            const finalVal = isNegative ? '-' + formatted : formatted;
            if (input.value !== finalVal) {
                input.value = finalVal;
            }
        }

        // 3. 更新分组小计 (简化版，仅计算当前金额)
        function updateSectionSubtotal(row) {
            let currentRow = row;
            let sectionTitle = null;
            while ((currentRow = currentRow.previousElementSibling)) {
                if (currentRow.classList.contains('section-title')) {
                    sectionTitle = currentRow;
                    break;
                }
            }

            let sumCurrent = 0;
            let scanRow = sectionTitle ? sectionTitle : row.closest('tbody').firstElementChild;
            while ((scanRow = scanRow.nextElementSibling)) {
                if (scanRow.classList.contains('section-title')) break;
                if (scanRow.classList.contains('bs-subtotal')) continue;
                
                const cInput = scanRow.querySelector('.input-current');
                if (cInput) {
                    const cVal = parseInt(cInput.value.replace(/,/g, '') || 0);
                    sumCurrent += cVal;
                }
            }

            // 更新UI
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
            }
        }

        // --- 3. 保存按钮逻辑 ---
        document.getElementById('saveAllBtn')?.addEventListener('click', function() {
            const allData = [];
            const periodId = document.getElementById('periodSelect').value;

            // 仅收集 B/S 表数据
            document.querySelectorAll('.bs-table tbody tr').forEach(row => {
                const input = row.querySelector('.input-current');
                if (input) {
                    allData.push({
                        id: input.dataset.pk,
                        type_id: input.dataset.typeid,
                        current_amount: parseInt(input.value.replace(/,/g, '') || 0)
                    });
                }
            });

            const requestData = {
                period_id: periodId,
                data: allData
            };

            fetch('{{ route("masters.account-cash-outs.save-all") }}', {
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
            // 初始化所有分组小计
            document.querySelectorAll('.bs-table .bs-subtotal').forEach(subtotalRow => {
                let dataRow = subtotalRow.previousElementSibling;
                while (dataRow && !dataRow.classList.contains('section-title')) {
                    if (dataRow.querySelector('.input-current')) {
                        updateSectionSubtotal(dataRow);
                        break;
                    }
                    dataRow = dataRow.previousElementSibling;
                }
            });
        });
    });

    const currentParams = new URLSearchParams(window.location.search);

    const baseUrl = "{{ route('masters.account-cash-outs.pdf') }}";

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