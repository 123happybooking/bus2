@extends('layouts.app')

@section('title', '請求書マスター')

@section('content')
<!-- 修改点：减小容器左右内边距 px-2 -->
<div class="container-fluid px-2">
    <!-- 标题与新建按钮: 减小间距 mb-2，标题变小 -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0 text-primary" style="font-size: 1.1rem !important;">
            <i class="bi bi-file-text me-2"></i>請求書
        </h5>
        <div>
            <a id="downloadPdf" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> Excel明細
            </a>
            <a id="downloadPdfSum" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> Excel合計
            </a>
        </div>

    </div>

    <!-- 成功/错误提示: 减小内边距 py-2，字体变小 -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-2" role="alert" style="font-size: 0.875rem;">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <!-- 修改点：移除 btn-sm，使用 style 1控制大小 -->
            <button type="button" class="btn-close" style="font-size: 0.875rem;" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-2 py-2" role="alert" style="font-size: 0.875rem; position: relative;">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <!-- 终极方案：手动定位 -->
        <button type="button" class="btn-close" 
                style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.875rem;"
                data-bs-dismiss="alert" aria-label="Close">
        </button>
    </div>
@endif

    <!-- 搜索区域: 减小间距 mb-2 -->
<div class="mb-4">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="GET" id="searchForm" action="{{ route('masters.invoices.sum') }}" class="row g-3 align-items-end">
                
                <!-- 隐藏域：用于提交选中的日期类型 -->
                <input type="hidden" name="date_type" id="date_type" value="{{ request('date_type', 'operation') }}">
                <input type="hidden" name="group_id" value="{{ request('group_id') }}">



                <!-- 2. 标题选择 (运行日/请求日/入金日) & 日期范围 -->
                <div class="col-md-3">
                    
                    <!-- 切换按钮组 (保持原样) -->
                    <div class="border rounded bg-light d-flex align-items-center mb-2" style="height: 31px;">
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <button type="button" class="btn btn-light date-type-btn" data-value="operation" 
                                :class="{ 'active btn-primary text-white': dateType === 'operation' }"
                                @click="updateDateType('operation')"
                                style="font-size: 0.75rem; padding: 0.25rem 0;"
                            >運行日</button>
                            <button type="button" class="btn btn-light date-type-btn" data-value="billing"
                                :class="{ 'active btn-primary text-white': dateType === 'billing' }"
                                @click="updateDateType('billing')"
                                style="font-size: 0.75rem; padding: 0.25rem 0;"
                            >請求日</button>
                            <button type="button" class="btn btn-light date-type-btn" data-value="payment"
                                :class="{ 'active btn-primary text-white': dateType === 'payment' }"
                                @click="updateDateType('payment')"
                                style="font-size: 0.75rem; padding: 0.25rem 0;"
                            >入金日</button>
                        </div>
                    </div>

                    <!-- 日期和天数并排显示 -->
                    <div class="input-group input-group-sm">
                        <!-- 日期输入框 -->
                        <input 
                            type="text" 
                            name="target_date" 
                            class="form-control datepicker-3months" 
                            value="{{ request('target_date') }}" 
                            placeholder=""
                        >
                        
                        <!-- 天数输入框 (移到了这里) -->
                        <input type="number" name="days" class="form-control" 
                            list="days_list" value="{{ request('days') }}" placeholder="日数" style="max-width: 100px;">
                        
                        <!-- 可选：加一个单位标签 -->
                        <span class="input-group-text">日間</span>

                        <datalist id="days_list">
                            <option value="1">
                            <option value="3">
                            <option value="7">
                            <option value="14">
                            <option value="21">
                            <option value="31">
                        </datalist>
                    </div>
                </div>

                <!-- 3. 请求先 (下拉框) -->
                <div class="col-md-2">
                    <label class="form-label mb-1 text-muted small">請求先</label>
                    <select name="agency_id" class="form-select form-select-sm">
                        <option value="0">請求先選択</option>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                {{ $agency->agency_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. 担当 (下拉框) -->
                <div class="col-md-2">
                    <label class="form-label mb-1 text-muted small">担当</label>
                    <select name="staff_id" class="form-select form-select-sm">
                        <option value="0">担当選択</option>
                        @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 5. 入金状況 (多选下拉框) -->
                <div class="col-md-1">
                    <label class="form-label mb-1 text-muted small">入金状況</label>
                    <div class="dropdown payment-status-dropdown">
                        <!-- 下拉按钮 (修复点：移除了 form-select，改用手动样式匹配左边) -->
                        <button class="form-select form-select-sm dropdown-toggle payment-status-btn" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false"
                                style="background-color: #fff; border-color: #ced4da; color: #495057; width: 100%; text-align: left; position: relative; padding-right: 2.5rem;">
                            
                            <!-- 左侧：固定显示的文字 -->
                            <span class="dropdown-label">入金状況</span>
                            
                            <!-- 右侧：徽章 -->
                            <span class="selected-count badge bg-primary rounded-pill" 
                                style="position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%); display: none;">
                                0
                            </span>
                        </button>

                        <!-- 下拉菜单内容 (保持不变) -->
                        <div class="dropdown-menu p-0" style="min-width: 200px;">
                            <div class="dropdown-header border-bottom px-3 py-2 bg-light">
                                <label class="d-flex align-items-center w-100" style="cursor: pointer;">
                                    <input type="checkbox" class="form-check-input me-2 select-all-status">
                                    <strong>全て選択</strong>
                                </label>
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                    <input type="checkbox" 
                                        class="form-check-input me-2 status-checkbox" 
                                        value="1"
                                        @if(is_array(request('payment_status')) && in_array('1', request('payment_status'))) checked @endif> 未入金 
                                </label>
                                <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                    <input type="checkbox" 
                                        class="form-check-input me-2 status-checkbox" 
                                        value="2"
                                        @if(is_array(request('payment_status')) && in_array('2', request('payment_status'))) checked @endif> 部分入金 
                                </label>
                                <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                    <input type="checkbox" 
                                        class="form-check-input me-2 status-checkbox" 
                                        value="3"
                                        @if(is_array(request('payment_status')) && in_array('3', request('payment_status'))) checked @endif> 入金済 
                                </label>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- 6. 搜索按钮 -->
                <div class="col-md-1 ms-auto">
                    <label class="form-label mb-1 d-block" style="font-size: 0.75rem; opacity: 0;">Action</label>
                    <button type="submit" class="btn btn-primary btn-sm w-100" style="height: 31px; padding-top: 0; padding-bottom: 0;">
                        検索
                    </button>
                </div>

                <!-- 7. 清除按钮 (可选) -->
                <div class="col-md-1">
                     <label class="form-label mb-1 d-block" style="font-size: 0.75rem; opacity: 0;">Action</label>
                     @if(request()->hasAny(['start_date', 'days', 'agency_id', 'staff_id', 'payment_status']))
                        <a href="{{ route('masters.invoices.sum',['group_id'=>request('group_id') ]) }}" style="height: 30px; line-height: 30px;padding-top: 0; padding-bottom: 0;" class="btn btn-outline-secondary btn-sm w-100" >
                            <i class="bi bi-x-circle"></i>クリア
                        </a>
                     @endif
                </div>

            </form>
        </div>
    </div>
</div>
    
    <!-- 搜索结果提示：根据筛选条件动态显示 -->
    <!-- 搜索结果提示 -->
    @if(request()->hasAny(['start_date', 'target_date', 'date_type', 'agency_id', 'staff_id', 'payment_status', 'search', 'status']))
        <div class="alert alert-info mb-2 d-flex align-items-center py-2" style="font-size: 0.875rem;">
            <i class="bi bi-info-circle me-2 fs-6"></i>
            <div class="flex-grow-1">
                <!-- 期间 -->
                @if(request('start_date'))
                    <strong>期間：</strong>{{ request('start_date') }}から
                    @if(request('days')){{ request('days') }}日間 @endif
                    <span class="text-muted mx-1">|</span>
                @endif

                <!-- 目标日期 -->
                @if(request('target_date'))
                    <strong>対象日：</strong>
                    @php
                        $dateTypeText = match(request('date_type', 'operation')) {
                            'billing' => '請求日',
                            'payment' => '入金日',
                            default => '運行日'
                        };
                    @endphp
                    {{ $dateTypeText }}: {{ request('target_date') }}
                    <span class="text-muted mx-1">|</span>
                @endif

                <!-- 请求先 -->
                @if(request('agency_id') && request('agency_id') != 0)
                    <strong>請求先：</strong>
                    {{ $agencies->firstWhere('id', request('agency_id'))->agency_name ?? '不明' }}
                    <span class="text-muted mx-1">|</span>
                @endif

                <!-- 担当 -->
                @if(request('staff_id') && request('staff_id') != 0)
                    <strong>担当：</strong>
                    {{ $staffs->firstWhere('id', request('staff_id'))->name ?? '不明' }}
                    <span class="text-muted mx-1">|</span>
                @endif

                <!-- 入金状况 -->
                @if(request('payment_status') !== null && request('payment_status') !== '')
                    <strong>入金状況：</strong>
                    @if(request('payment_status') == 'unpaid') 未入金
                    @elseif(request('payment_status') == 'paid') 入金済
                    @endif
                    <span class="text-muted mx-1">|</span>
                @endif

                <!-- 搜索关键词 (保留原有的) -->
                @if(request('search'))
                    <strong>検索：</strong>「{{ request('search') }}」
                    <span class="text-muted mx-1">|</span>
                @endif

                 <!-- 结果数量 -->
                 @if(isset($invoices) && $invoices->count() > 0)
                    {{ count($invoices) }}件の結果が見つかりました
                @else
                    該当するものが見つかりませんでした
                @endif
            </div>

            <!-- 关闭按钮：关键修改在这里 -->
            <button type="button"
                    class="btn-close position-static ms-auto"
                    style="font-size: 0.75rem;"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>
        </div>
    @endif


    <!-- 表格区域 -->
    <div class="card shadow-sm"> 
        <div class="table-responsive"> 
            <!-- 修改点：font-size 0.875rem -->
            <table class="table table-bordered mb-0 align-middle"> 
                <thead class="table-secondary"> 
                    <tr> 
                        <th class="text-center py-1" style="width: 60px; font-size: 0.75rem;">予約ID</th> 
                        <th class="text-center py-1" style="width: 120px; font-size: 0.75rem;">請求先</th> 
                        <th class="text-center py-1" style="width: 120px; font-size: 0.75rem;">タイトル</th> 
                        <th class="text-center py-1" style="width: 100px; font-size: 0.75rem;">運行日</th> 
                        <th class="text-center py-1" style="width: 100px; font-size: 0.75rem;">請求日</th> 
                        <th class="text-center py-1" style="width: 80px; font-size: 0.75rem;">通貨</th> 
                        <th class="text-center py-1" style="width: 100px; font-size: 0.75rem;">合計金額</th> 
                        <th class="text-center py-1" style="width: 100px; font-size: 0.75rem;">未入金</th> 
                        <th class="text-center py-1" style="width: 80px; font-size: 0.75rem;">タイプ</th> 
                        <th class="text-center py-1" style="width: 80px; font-size: 0.75rem;">請求担当</th>
                        
                        <th class="text-center py-1" style="width: 60px; font-size: 0.75rem;" title="データロック状態">ロック</th> 
                    </tr> 
                </thead> 
                <tbody> 
                    @php
                        $currentAgencyId = null;
                        $agencyTotalAmount = 0;
                        $agencyPaidAmount = 0;
                        $grandTotalAmount = 0; // 总合计
                        $grandPaidAmount = 0;  // 总合计
                    @endphp
                    @forelse($invoices as $invoice) 
                    @php
                        // 如果 Agency 切换，或者这是第一条数据
                        $isFirstRow = $currentAgencyId === null;
                        $isAgencyChanged = $currentAgencyId !== $invoice->agency_id;

                        if ($isFirstRow || $isAgencyChanged) {
                            // 如果不是第一行，先输出上一个 Agency 的小计
                            if (!$isFirstRow) {
                                echo '<tr class="table-secondary font-weight-bold">';
                                echo '<td colspan="6" class="text-end">【'.$currentAgencyName.'】 小計：</td>';
                                echo '<td class="text-end">'.number_format($agencyTotalAmount).'</td>';
                                echo '<td class="text-end">'.number_format($agencyTotalAmount - $agencyPaidAmount).'</td>';
                                echo '<td colspan="5" class="text-end"></td>';
                                echo '</tr>';
                            }

                            // 重置当前 Agency 的统计
                            $currentAgencyId = $invoice->agency_id;
                            $currentAgencyName = $invoice->agency->agency_name ?? '無名請求先';
                            $agencyTotalAmount = 0;
                            $agencyPaidAmount = 0;
                        }

                        // 累加金额
                        $agencyTotalAmount += $invoice->total_amount;
                        $agencyPaidAmount += $invoice->paid_amount;

                        // 累加全局合计
                        $grandTotalAmount += $invoice->total_amount;
                        $grandPaidAmount += $invoice->paid_amount;
                    @endphp
                    <tr> 
                        <td class="text-center text-muted small py-1" style="font-size: 0.75rem;">{{ $invoice->id }}</td> 
                        <td class="text-center py-1" style="font-size: 0.875rem;">{{ $invoice->agency->agency_name ?? ''}}</td> 
                        <td class="text-center py-1" style="font-size: 0.875rem;">{{ $invoice->billing_title }}</td> 
                        <td class="text-center py-1" style="font-size: 0.875rem;">
                            {{ $invoice->operation_date?->format('Y/m/d') }}
                        </td> 
                        <td class="text-center py-1" style="font-size: 0.875rem;">
                            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') }}
                        </td> 
                        <td class="text-center py-1" style="font-size: 0.875rem;">{{ $invoice->currency_code }}</td> 
                        <td class="text-center font-monospace py-1" style="font-size: 0.875rem;"> 
                            {{ number_format($invoice->total_amount, 0) }} 
                        </td> 
                        <td class="text-center font-monospace py-1" style="font-size: 0.875rem;"> 
                            {{ number_format($invoice->total_amount - $invoice->paid_amount, 0) }} 
                        </td> 
                        <td class="text-center font-monospace py-1" style="font-size: 0.875rem;"> 
                            {{ $invoice->type == 1 ? '正式' : '臨時' }} 
                        </td> 
                        <td class="text-center py-1" style="font-size: 0.875rem;">{{ $invoice->staff->name ?? '' }}</td> 
                        <td class="text-center py-1"> 
                            <button type="button" class="btn btn-sm border-0 toggle-lock-btn" 
                                data-id="{{ $invoice->id }}" data-locked="{{ $invoice->is_locked ? 1 : 0 }}" 
                                title="{{ $invoice->is_locked ? 'ロックを解除' : 'ロックを掛ける' }}" 
                                style="width: 30px; height: 30px; border-radius: 50%; transition: all 0.2s; display:inline-flex; align-items:center; justify-content:center; padding: 0;">
                                @if($invoice->is_locked) 
                                    <i class="bi bi-lock-fill text-danger" style="font-size: 0.9rem;"></i> 
                                @else 
                                    <i class="bi bi-unlock-fill text-success" style="font-size: 0.9rem;"></i> 
                                @endif 
                            </button> 
                        </td>  
                    </tr> 
                    @empty 
                    <tr> 
                        <td colspan="14" class="text-center py-4"> 
                            @if(request()->hasAny(['search', 'status'])) 
                                <div class="text-muted"> 
                                    <i class="bi bi-search display-6 mb-2 d-block"></i> 
                                    <p class="mb-0 fw-bold" style="font-size: 0.9rem;">検索条件に一致する請求書が見つかりませんでした</p> 
                                    <p class="small">検索条件を変更してお試しください</p> 
                                </div> 
                            @else 
                                <div class="text-muted"> 
                                    <i class="bi bi-file-text display-6 mb-2 d-block"></i> 
                                    <p class="mb-0 fw-bold" style="font-size: 0.9rem;">請求書が登録されていません</p> 
                                    <p class="small">「新規追加」ボタンから最初の請求書を作成してください</p> 
                                </div> 
                            @endif 
                        </td> 
                    </tr> 
                    @endforelse 

                    @if($currentAgencyId !== null)
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="6" class="text-end">【{{ $currentAgencyName }}】 小計：</td>
                            <td class="text-end">{{ number_format($agencyTotalAmount) }}</td>
                            <td class="text-center">{{ number_format($agencyTotalAmount - $agencyPaidAmount) }}</td>
                            <td colspan="5" class="text-end"></td>
                        </tr>
                    @endif

                    <tr class="table-primary font-weight-bold">
                        <td colspan="6" class="text-end">合計：</td>
                        <td class="text-end">{{ number_format($grandTotalAmount) }}</td>
                        <td class="text-center">{{ number_format($grandTotalAmount - $grandPaidAmount) }}</td>
                        <td colspan="5" class="text-end"></td>
                    </tr>
                </tbody> 
            </table> 

        </div> 
    </div>
        
</div>



<!-- 【新增/修改】AJAX 脚本处理锁状态切换及批量操作 -->
<script>

document.addEventListener('DOMContentLoaded', function () {
    initPaymentStatusDropdown(); 
    flatpickr('.datepicker-3months', {
        locale: "ja",
        dateFormat: "Y-m-d",
        showMonths: 3, // 显示3个月
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
        }
    });


});

function initPaymentStatusDropdown() {
    const checkboxes = document.querySelectorAll('.status-checkbox');
    const selectAllCheckbox = document.querySelector('.select-all-status');
    const selectedCountSpan = document.querySelector('.selected-count');
    
    // 【修复点 1】获取搜索表单 (请确保你的 form 标签上加了 id="searchForm")
    const searchForm = document.getElementById('searchForm'); 
    
    // 防御性检查：如果没找到表单，打印错误并退出
    if (!searchForm) {
        console.error('Search form with id "searchForm" not found!');
        return;
    }

    function updateDisplay() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        const count = selected.length;

        // 更新徽章数字
        if (count === 0) {
            selectedCountSpan.style.display = 'none';
        } else {
            selectedCountSpan.textContent = count;
            selectedCountSpan.style.display = 'inline-block';
        }

        // 更新全选状态
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = count > 0 && count === checkboxes.length;
        }

        // 【修复点 2】清理旧的隐藏 Input
        // 注意：这里必须在表单内部查找，而不是全局查找
        searchForm.querySelectorAll('.status-hidden-input').forEach(input => input.remove());

        // 【修复点 3】创建新的 Input 并直接添加到表单中
        selected.forEach(cb => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'payment_status[]'; // PHP/Laravel 接收数组的标准写法
            hiddenInput.value = cb.value;
            hiddenInput.className = 'status-hidden-input';
            // 【关键】直接 append 到表单
            searchForm.appendChild(hiddenInput);
        });
    }


    // 为每个 Checkbox 添加事件
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation(); // 防止触发 label 的点击
            updateDisplay();
        });
        
        // 防止点击 Checkbox 时触发 label 的 toggle (避免重复触发)
        checkbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // 全选逻辑
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function(e) {
            e.stopPropagation();
            const isChecked = this.checked;
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
            });
            updateDisplay();
        });
    }

    // 点击下拉项时触发 Checkbox
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // 如果点击的是 Checkbox 本身，上面的逻辑已经处理了，这里直接返回
            if (e.target.type === 'checkbox') {
                return;
            }
            e.preventDefault(); // 防止下拉框关闭（如果有的话）
            e.stopPropagation();
            
            const checkbox = this.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                updateDisplay();
            }
        });
    });

    // 初始化：检查 URL 参数并预选中 (页面刷新保持状态)
    // 这里简单处理：如果有隐藏域存在，说明后端传过来了，保持 UI 一致
    // 更复杂的逻辑需要解析 URL，这里依赖后端在 Blade 渲染时处理 checked 状态（如你原来的 Blade 代码所示）
    updateDisplay();

}

const currentParams = new URLSearchParams(window.location.search);

const baseUrl = "{{ route('masters.invoices.pdf') }}";

// 构建最终 URL
let finalUrl = baseUrl;

if (currentParams.toString()) {
    // 判断 baseUrl 是否已包含参数
    finalUrl += baseUrl.includes('?') ? '&' : '?';
    finalUrl += currentParams.toString();
}

// 赋值给按钮
document.getElementById('downloadPdf').href = finalUrl;
document.getElementById('downloadPdfSum').href = finalUrl + "?sum=1";


</script>
<style>
.table tbody tr {
    background-color: transparent !important; /* 确保基础背景透明 */
}

.table thead th,
.table tbody td {
    padding-top: 0.1rem !important;    /* 强制覆盖 */
    padding-bottom: 0.1rem !important; /* 强制覆盖 */
    vertical-align: middle;
}
/* 2. 使用最顶级的 !important 强制覆盖 */
/* 我们甚至要针对 .table-striped 的特定类进行覆盖 */
.table-striped tbody tr:hover td,
.table-striped tbody tr:hover th,
.table tbody tr:hover td,
.table tbody tr:hover th {
    background-color: #e9ecef  !important; /* 鲜明的浅蓝色背景 */
    cursor: pointer !important;
    /* 强制提升层级，防止被其他阴影盖住 */
    position: relative !important;
    z-index: 1 !important;
    /* 字体加粗，增加反馈感 */
    font-weight: 500 !important;
}

/* 3. 针对锁定按钮行的特殊处理（如果有） */
/* 如果你的行里有绝对定位的元素，可能需要这个 */
.table tbody tr:hover .toggle-lock-btn {
    transform: scale(1.1) !important; /* 锁定图标稍微放大 */
}

</style>
@endsection