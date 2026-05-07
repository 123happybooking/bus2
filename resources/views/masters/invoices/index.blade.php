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
        <!-- 修改点：btn-sm, 字体变小 -->
        <a href="{{ route('masters.invoices.create', ['group_id' => request('group_id')]) }}" class="btn btn-primary btn-sm" style="font-size: 0.875rem;">
            <i class="bi bi-plus-lg"></i> 新規追加
        </a>
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
            <form method="GET" id="searchForm" action="{{ route('masters.invoices.index') }}" class="row g-3 align-items-end">
                
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
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle payment-status-btn" 
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
                        <a href="{{ route('masters.invoices.index',['group_id'=>request('group_id') ]) }}" class="btn btn-outline-secondary btn-sm w-100" >
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
                    {{ $invoices->total() }}件の結果が見つかりました
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

    <!-- 【新增】批量操作工具栏 (默认隐藏) -->
    <!-- 修改点：mb-2, py-1 -->
    <div id="bulk-action-bar" class="card border-primary mb-2 shadow-sm d-none" style="background-color: #f8fbff;">
        <!-- 修改点：py-1 -->
        <div class="card-body py-1 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2" id="selected-count">0</span>
                <span class="text-primary fw-bold small" style="font-size: 0.75rem;">件選択中</span>
            </div>
            <div class="d-flex gap-1 align-items-center"> <!-- gap-2 -> gap-1 -->
                <!-- 【新增】批量销账按钮 -->
                <button type="button" class="btn btn-sm btn-primary shadow-sm" id="btn-bulk-reconcile" title="選択した請求書を一括で消し込み" style="font-size: 0.75rem; padding: 0.15rem 0.3rem;">
                    <i class="bi bi-cash-coin"></i> <span class="d-none d-sm-inline">一括消し込み</span>
                </button>
                <div class="vr mx-1"></div>
                <!-- 批量锁定 -->
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-bulk-lock" title="選択した項目をロック" style="font-size: 0.75rem; padding: 0.15rem 0.3rem;">
                    <i class="bi bi-lock-fill"></i> <span class="d-none d-sm-inline">一括ロック</span>
                </button>
                <!-- 批量解锁 -->
                <button type="button" class="btn btn-sm btn-outline-success" id="btn-bulk-unlock" title="選択した項目のロックを解除" style="font-size: 0.75rem; padding: 0.15rem 0.3rem;">
                    <i class="bi bi-unlock-fill"></i> <span class="d-none d-sm-inline">一括解除</span>
                </button>

                <div class="vr mx-1"></div>
                <!-- 批量下载 PDF -->
                <form action="{{ route('masters.invoices.bulk-pdf') }}" method="POST" target="_blank" id="form-bulk-pdf" class="d-inline">
                    @csrf
                    <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                    <div id="bulk-pdf-inputs"></div>
                    <button type="submit" class="btn btn-sm btn-outline-dark" title="選択した項目の PDF をダウンロード" style="font-size: 0.75rem; padding: 0.15rem 0.3rem;">
                        <i class="bi bi-file-earmark-pdf"></i> <span class="d-none d-sm-inline">一括 PDF</span>
                    </button>
                </form>
                
                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="btn-clear-selection" style="font-size: 0.75rem;">
                    クリア
                </button>
            </div>
        </div>
    </div>

    <!-- 表格区域 -->
<div class="card shadow-sm"> 
    <div class="table-responsive"> 
        <!-- 修改点：font-size 0.875rem -->
        <table class="table table-bordered mb-0 align-middle"> 
            <thead class="table-secondary"> 
                <tr> 
                    <!-- 【新增】全选复选框 -->
                    <th class="text-center py-1" style="width: 40px; font-size: 0.75rem;"> 
                        <input type="checkbox" class="form-check-input" id="select-all" title="全選択"> 
                    </th> 
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
                    <th class="text-center py-1" style="width: 140px; font-size: 0.75rem;">操作</th> 
                </tr> 
            </thead> 
            <tbody> 
                @forelse($invoices as $invoice) 
                <tr> 
                    <td class="text-center py-1"> 
                        <input type="checkbox" class="form-check-input invoice-checkbox" value="{{ $invoice->id }}" 
                            data-locked="{{ $invoice->is_locked ? 1 : 0 }}" 
                            data-customer-id="{{ $invoice->agency_id }}" 
                            data-invoice-no="{{ $invoice->invoice_number }}" 
                            data-currency-code="{{ $invoice->currency_code }}" 
                            data-customer-name="{{ $invoice->agency->agency_name ?? ''}}"
                            data-return-url="{{ url()->current() }}" 
                            data-request-amount="{{ number_format($invoice->total_amount, 2, '.', '') }}" 
                            data-balance-amount="{{ number_format($invoice->total_amount - $invoice->paid_amount, 2, '.', '') }}"> 
                    </td> 
                    <td class="text-center text-muted small py-1" style="font-size: 0.75rem;">{{ $invoice->id }}</td> 
                    <td class="text-center py-1" style="font-size: 0.875rem;">{{ $invoice->agency->agency_name ?? ''}}</td> 
                    <td class="text-center fw-bold text-primary py-1" style="font-size: 0.875rem;">{{ $invoice->billing_title }}</td> 
                    <td class="text-center py-1" style="font-size: 0.875rem;">
                        {{ \Carbon\Carbon::parse($invoice->operation_date)->format('Y/m/d') }}
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
                    <td class="py-1"> 
                        <div class="d-flex gap-1 justify-content-center"> 
                            @if($invoice->total_amount > $invoice->paid_amount) 
                                <button type="button" class="btn btn-sm btn-outline-warning btn-single-reconcile" 
                                    data-id="{{ $invoice->id }}" title="消し込み (入金登録)" style="padding: 0.1rem 0.3rem;"> 
                                    <i class="bi bi-cash-coin" style="font-size: 0.8rem;"></i> 
                                </button> 
                            @else 
                                <button type="button" class="btn btn-sm btn-light text-muted" disabled title="全額入金済み" style="padding: 0.1rem 0.3rem;"> 
                                    <i class="bi bi-check-circle-fill" style="font-size: 0.8rem;"></i> 
                                </button> 
                            @endif 
                            <a href="{{ route('masters.invoices.pdf', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                               class="btn btn-sm btn-outline-success" title="PDF ダウンロード" target="_blank" style="padding: 0.1rem 0.3rem;"> 
                                <i class="bi bi-file-earmark-pdf" style="font-size: 0.8rem;"></i> 
                            </a> 
                            <a href="{{ route('masters.invoices.show', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                               class="btn btn-sm btn-outline-info" title="詳細" style="padding: 0.1rem 0.3rem;"> 
                                <i class="bi bi-eye" style="font-size: 0.8rem;"></i> 
                            </a> 
                            @if(!$invoice->is_locked)
                                <a href="{{ route('masters.invoices.edit', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                   class="btn btn-sm btn-outline-primary {{ $invoice->is_locked ? 'disabled' : '' }}" 
                                   title="{{ $invoice->is_locked ? 'ロック中です' : '編集' }}" style="padding: 0.1rem 0.3rem;"> 
                                    <i class="bi bi-pencil" style="font-size: 0.8rem;"></i> 
                                </a>
                            @endif 
                            @if( !$invoice->is_locked && $invoice->total_amount == $invoice->paid_amount ) 
                                <form action="{{ route('masters.invoices.destroy', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                      method="POST" class="d-inline" onsubmit="return checkAndDelete({{ $invoice->type }}, '{{ $invoice->invoice_number }}')">
                                    @csrf @method('DELETE') 
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="削除" style="padding: 0.1rem 0.3rem;"> 
                                        <i class="bi bi-trash" style="font-size: 0.8rem;"></i> 
                                    </button> 
                                </form> 
                            @endif 
                        </div> 
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
            </tbody> 
        </table> 
        <div class="border-top border-2 d-flex justify-content-end py-2 px-3 bg-light"> 
            <div class="text-end"> 
                <span class="fw-bold text-dark me-4" style="font-size: 0.9rem;"> 
                    合計金額: <span class="text-primary">{{ number_format($totalAmount) }}</span> 
                </span> 
                <span class="fw-bold text-dark" style="font-size: 0.9rem;"> 
                    入金残高: <span class="text-danger">{{ number_format($totalAmount - $paidAmount) }}</span> 
                </span> 
            </div> 
        </div> 
    </div> 
</div>
    
    <!-- 分页区域 -->
    @if($invoices->hasPages() || $invoices->total() > 0)
        <div class="mt-3">
            <!-- 使用 flex 容器实现整体居中 -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                
                <!-- 1. 左侧：行数选择器 -->
                <div class="d-flex align-items-center">
                    <label for="per_page_select" 
                        class="form-label small text-muted mb-0 me-2" 
                        style="white-space: nowrap;"> <!-- 添加这行 -->
                        表示件数:
                    </label>
                    <select id="per_page_select" class="form-select form-select-sm" style="font-size: 0.75rem;min-width: 80px;">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                    </select>
                </div>

                <!-- 2. 中间：分页链接 -->
                <nav aria-label="Page navigation">
                    <!-- 关键：添加 pagination-sm -->
                    <ul class="pagination pagination-sm mb-0">
                        <!-- 上一页 -->
                        <li class="page-item {{ $invoices->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $invoices->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        @php
                            $current = $invoices->currentPage();
                            $last = $invoices->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $invoices->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $invoices->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $invoices->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                            </li>
                        @endif

                        <!-- 下一页 -->
                        <li class="page-item {{ !$invoices->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $invoices->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- 3. 底部：统计信息 -->
            <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                表示中：{{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }} / 全 {{ $invoices->total() }} 件
            </div>
        </div>
    @endif
</div>

<!-- Toast 容器 -->
<div class="position-fixed bottom-0 end-0 p-2" style="z-index: 11">
    <!-- 修改点：p-2, 字体变小 -->
    <div id="lockToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true" style="font-size: 0.875rem;">
        <div class="d-flex">
            <div class="toast-body" id="lockToastMessage">
                <!-- 消息内容 -->
            </div>
            <!-- 修改点：移除 btn-sm，使用 style 3控制大小 -->
            <button type="button" class="btn-close btn-close-white me-2 m-auto" style="font-size: 0.875rem;" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- 引入批量销账模态框组件 --}}
@include('masters.invoices.components.bulk-reconcile-modal')

<!-- 【新增/修改】AJAX 脚本处理锁状态切换及批量操作 -->
<script>
function checkAndDelete(type, number) {
    if (type != 2) {
        alert('請求書タイプを臨時にしてから削除してください。');
        return false;
    }
    const message = '本当に請求書「' + number + '」を削除しますか？\nこの操作は元に戻せません。';
    return confirm(message);
}

document.addEventListener('DOMContentLoaded', function () {
    // 1. 分页行数切换逻辑
    const perPageSelect = document.getElementById('per_page_select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const newPerPage = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', newPerPage);
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        });
    }

    // 2. 复选框联动与批量操作栏逻辑
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.invoice-checkbox');
    const bulkActionBar = document.getElementById('bulk-action-bar');
    const selectedCountBadge = document.getElementById('selected-count');
    
    if (!selectAllCheckbox) {
        console.error('Error: "select-all" checkbox not found!');
        return;
    }

    function updateBulkActionBar() {
        const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (selectedCountBadge) selectedCountBadge.textContent = count;

        if (count > 0) {
            if(bulkActionBar) bulkActionBar.classList.remove('d-none');
            
            const pdfContainer = document.getElementById('bulk-pdf-inputs');
            if(pdfContainer) {
                pdfContainer.innerHTML = '';
                checkedBoxes.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'invoice_ids[]';
                    input.value = cb.value;
                    if (pdfContainer) {
                        pdfContainer.appendChild(input);
                    }
                });
            }
        } else {
            if(bulkActionBar) bulkActionBar.classList.add('d-none');
        }
    }

    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        rowCheckboxes.forEach(cb => {
            cb.checked = isChecked;
            cb.dispatchEvent(new Event('change')); 
        });
        updateBulkActionBar();
    });

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            if (selectAllCheckbox) {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                selectAllCheckbox.checked = allChecked;
            }
            updateBulkActionBar();
        });
    });

    const btnClearSelection = document.getElementById('btn-clear-selection');
    if(btnClearSelection) {
        btnClearSelection.addEventListener('click', function() {
            rowCheckboxes.forEach(cb => cb.checked = false);
            if(selectAllCheckbox) selectAllCheckbox.checked = false;
            updateBulkActionBar();
        });
    }

    // 3. 批量锁定/解锁 AJAX 逻辑
    const btnBulkLock = document.getElementById('btn-bulk-lock');
    const btnBulkUnlock = document.getElementById('btn-bulk-unlock');
    const toastEl = document.getElementById('lockToast');
    let toast = null;
    
    if(toastEl && window.bootstrap) {
        toast = new bootstrap.Toast(toastEl, { delay: 800 });
    }

    async function processBulkAction(ids, lockState) {
        if(btnBulkLock) btnBulkLock.disabled = true;
        if(btnBulkUnlock) btnBulkUnlock.disabled = true;

        try {
            const response = await fetch('/masters/invoices/bulk-toggle-lock', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ invoice_ids: ids, locked: lockState ? 1 : 0 })
            });
            const data = await response.json();
            if(data.success) {
                if(toast) {
                    document.getElementById('lockToastMessage').innerHTML = lockState ? 'ロック完了' : 'ロック解除完了';
                    toast.show();
                }
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                resetButtons();
            }
        } catch(e) {
            console.error(e);
            alert('Network error');
            resetButtons();
        }

        function resetButtons() {
            if(btnBulkLock) btnBulkLock.disabled = false;
            if(btnBulkUnlock) btnBulkUnlock.disabled = false;
        }
    }

    if(btnBulkLock) {
        btnBulkLock.addEventListener('click', async function() {
            const ids = Array.from(document.querySelectorAll('.invoice-checkbox:checked')).map(cb => cb.value);
            if(ids.length === 0) return;
            if(!confirm(`選択した ${ids.length} 件をロックしますか？`)) return;
            await processBulkAction(ids, true);
        });
    }
    
    if(btnBulkUnlock) {
        btnBulkUnlock.addEventListener('click', async function() {
            const ids = Array.from(document.querySelectorAll('.invoice-checkbox:checked')).map(cb => cb.value);
            if(ids.length === 0) return;
            if(!confirm(`選択した ${ids.length} 件のロックを解除しますか？`)) return;
            await processBulkAction(ids, false);
        });
    }
    
    // 4. 单行锁定逻辑
    document.querySelectorAll('.toggle-lock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const current = parseInt(this.dataset.locked);
            if(!confirm('操作しますか？')) return;
            
            fetch(`/masters/invoices/${id}/toggle-lock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ locked: current ? 0 : 1 })
            }).then(() => window.location.reload());
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // 1. 日期类型切换逻辑
    const dateTypeInput = document.getElementById('date_type');
    const dateTypeBtns = document.querySelectorAll('.date-type-btn');

    dateTypeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // 移除所有按钮的激活状态
            dateTypeBtns.forEach(b => {
                b.classList.remove('active', 'btn-primary', 'text-white');
                b.classList.add('btn-light');
            });
            // 激活当前按钮
            this.classList.remove('btn-light');
            this.classList.add('active', 'btn-primary', 'text-white');
            // 更新隐藏域的值
            dateTypeInput.value = this.dataset.value;
        });
    });

    // 初始化状态（如果页面加载时有默认值）
    const currentType = dateTypeInput.value;
    const activeBtn = document.querySelector(`.date-type-btn[data-value="${currentType}"]`);
    if(activeBtn) {
        activeBtn.click(); // 触发一次点击以设置样式
    }

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

// 在 DOM 加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    // ... (其他初始化代码)
    
    // 初始化入金状況功能
    // 注意：确保这行代码在 initBranchSelect() 之后，或者确保 ID 不冲突
    initPaymentStatusDropdown(); 
});

document.addEventListener('DOMContentLoaded', function () {
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