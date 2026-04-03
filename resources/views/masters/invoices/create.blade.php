@extends('layouts.app')

@section('title', '請求書作成')

@section('content')
<!-- 修改点：减少容器左右内边距 px-2 -->
<div class="container-fluid px-2">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb: 减小底部间距 mb-2，字体变小 -->
            <nav aria-label="breadcrumb" class="mb-2" style="font-size: 0.875rem;">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}">請求書管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">請求書作成</li>
                </ol>
            </nav>

            <!-- Flash Messages: 减小内边距 py-2，字体变小 -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 mb-2" role="alert" style="font-size: 0.875rem;">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show py-2 mb-2" role="alert" style="font-size: 0.875rem;">
                <h5 class="alert-heading fs-6 mb-1"><i class="bi bi-exclamation-triangle"></i> 入力エラーがあります</h5>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('masters.invoices.store') }}" method="POST" id="invoiceForm">
                @csrf
                <input type="hidden" name="group_id" value="{{ $groupId }}">

                <!-- ================= 第一部分：基本情報 ================= -->
                <!-- 修改点：mb-3 (减小间距), 移除 shadow-sm (可选，这里保留原意只改大小) -->
                <div class="card shadow-sm mb-3">
                    <!-- 修改点：添加 py-1 减小高度，h5 添加 style 强制缩小字体 -->
                    <div class="card-header bg-primary text-white py-1">
                        <h5 class="mb-0 fs-6" style="font-size: 0.9rem !important; line-height: 1.2;">
                            <i class="bi bi-info-circle"></i> 請求書基本情報
                        </h5>
                    </div>
                    <!-- 修改点：p-2 (减小内边距) -->
                    <div class="card-body p-2">
                        
                        <!-- Row 1: 修改点 g-2 (减小列间距), mb-2 (减小底间距) -->
                        <div class="row g-2 mb-2 align-items-start">
                            
                            <!-- 左侧：代理店 & Textarea -->
                            <div class="col-lg-6">
                                <!-- 修改点：mb-1 -->
                                <div class="d-flex align-items-center mb-1">
                                    <!-- 修改点：字体变小 me-2 -> me-1 -->
                                    <label for="agency_id" class="fw-bold me-1 mb-0" style="font-size: 0.875rem;">
                                        <i class="bi bi-building"></i> 代理店
                                    </label>
                                    <select class="form-select form-select-sm" id="agency_id" name="agency_id" style="width: auto; min-width: 100px; font-size: 0.875rem;">
                                        <option value="0">-- 代理店を選択してください --</option>
                                        @foreach($agencies ?? [] as $agency)
                                            <option value="{{ $agency->id }}" {{ old('agency_id') == $agency->id ? 'selected' : '' }}
                                                    data-agency-name="{{ $agency->agency_name ?? '' }}"
                                                    data-agency-code="{{ $agency->agency_code ?? '' }}">
                                                {{ $agency->agency_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Textarea: 字体变小 -->
                                <textarea required class="form-control form-control-sm" id="agency_detail" name="agency_detail" rows="3" placeholder="代理店を選択すると自動入力されます..." style="font-size: 0.875rem;">{{ old('agency_detail') }}</textarea>
                            </div>

                            <!-- 右侧：8 个字段 -->
                            <div class="col-lg-6">
                                <!-- 修改点：g-1 (极小间距) -->
                                <div class="row g-1">
                                    <!-- 第一行 -->
                                    <div class="col-md-3 col-6">
                                        <label for="billing_contact" class="form-label mb-0" style="font-size: 0.75rem;">請求担当</label>
                                        <select class="form-select form-select-sm" id="staff_id" name="staff_id" style="font-size: 0.875rem;">
                                            @foreach($staffs ?? [] as $staff)
                                                <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                                    {{ $staff->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label mb-0" style="font-size: 0.75rem;">内税/外税</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="tax_mode" id="tax_mode_1" value="1" {{ old('tax_mode', '1') == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="tax_mode_1" style="font-size: 0.75rem;">内税</label>
                                            <input type="radio" class="btn-check" name="tax_mode" id="tax_mode_2" value="2" {{ old('tax_mode', '1') == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="tax_mode_2" style="font-size: 0.75rem;">外税</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label mb-0" style="font-size: 0.75rem;">言語</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="language" id="lang_1" value="1" {{ old('language', '1') == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="lang_1" style="font-size: 0.75rem;">日本語</label>
                                            <input type="radio" class="btn-check" name="language" id="lang_2" value="2" {{ old('language', '1') == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="lang_2" style="font-size: 0.75rem;">英語</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label mb-0" style="font-size: 0.75rem;">タイプ</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="type" id="type_1" value="1" {{ old('type', '1') == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="type_1" style="font-size: 0.75rem;">正式</label>
                                            <input type="radio" class="btn-check" name="type" id="type_2" value="2" {{ old('type', '1') == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="type_2" style="font-size: 0.75rem;">臨時</label>
                                        </div>
                                    </div>

                                    <!-- 第二行 -->
                                    <div class="col-md-3 col-6">
                                        <label for="currency_code" class="form-label mb-0" style="font-size: 0.75rem;">通貨</label>
                                        <select class="form-select form-select-sm" id="currency_code" name="currency_code" style="font-size: 0.875rem;">
                                            @foreach($currencies ?? [] as $currency)
                                                <option value="{{ $currency->currency_code }}" {{ old('currency_code') == $currency->currency_code ? 'selected' : '' }}>
                                                    {{ $currency->currency_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="invoice_date" class="form-label mb-0" style="font-size: 0.75rem;">請求日</label>
                                        <input type="date" class="form-control form-control-sm" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" style="font-size: 0.875rem;">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="operation_date" class="form-label mb-0" style="font-size: 0.75rem;">運行日</label>
                                        <input type="date" class="form-control form-control-sm" id="operation_date" name="operation_date" value="{{ old('operation_date') }}" style="font-size: 0.875rem;">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="reservation_id" class="form-label mb-0" style="font-size: 0.75rem;">予約 ID</label>
                                        <input type="text" class="form-control form-control-sm" id="reservation_id" name="reservation_id" value="{{ old('reservation_id') }}" style="font-size: 0.875rem;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 修改点：my-2 (减小分割线间距) -->
                        <hr class="my-2 text-muted">

                        <!-- Row 2: 标题、支付日、银行、锁 -->
                        <!-- 修改点：g-2, mb-2 -->
                        <div class="row g-2 align-items-end mb-2">
                            <div class="col-md-4">
                                <label for="billing_title" class="form-label fw-bold mb-0" style="font-size: 0.875rem;">タイトル</label>
                                <input type="text" class="form-control form-control-sm @error('billing_title') is-invalid @enderror"
                                    id="billing_title" name="billing_title"
                                    value="{{ old('billing_title') }}" placeholder="例：2024 年 3 月分請求書" style="font-size: 0.875rem;">
                            </div>
                            <div class="col-md-3">
                                <label for="due_date" class="form-label fw-bold mb-0" style="font-size: 0.875rem;">支払指定日</label>
                                <input type="date" required class="form-control form-control-sm @error('due_date') is-invalid @enderror"
                                    id="due_date" name="due_date" value="{{ old('due_date') }}" style="font-size: 0.875rem;">
                            </div>
                            <div class="col-md-3">
                                <label for="bank_id" class="form-label fw-bold mb-0" style="font-size: 0.875rem;">入金銀行</label>
                                <select class="form-select form-select-sm @error('bank_id') is-invalid @enderror" id="bank_id" name="bank_id" style="font-size: 0.875rem;">
                                    @foreach($banks ?? [] as $bank)
                                        <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->bank_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold d-block text-center mb-0" style="font-size: 0.875rem;">ロック</label>
                                <input class="form-check-input" type="checkbox" role="switch" id="lock_switch" 
                                    {{ old('is_locked') ? 'checked' : '' }} style="display: none;">
                                
                                <!-- 修改点：mt-1, font-size 1.5rem (原 2rem) -->
                                <div class="text-center mt-1" id="lock_icon_container" style="cursor: pointer; font-size: 1.5rem;">
                                    <i class="bi bi-unlock-fill text-success"></i>
                                </div>
                                
                                <input type="hidden" name="is_locked" id="lock_hidden_input" value="{{ old('is_locked') ? '1' : '0' }}">
                            </div>
                        </div>

                        <!-- Row 3: 備考 -->
                        <div class="row">
                            <div class="col-12">
                                <label for="notes" class="form-label fw-bold mb-0" style="font-size: 0.875rem;">備考</label>
                                <textarea class="form-control form-control-sm" id="notes" name="notes" rows="2" placeholder="請求書の下に表示する備考を入力してください。" style="font-size: 0.875rem;">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ================= 第二部分：請求明細 ================= -->
                <!-- 修改点：mb-3 -->
                <div class="card shadow-sm mb-3">
                    <!-- 修改点：py-1, h5 字体缩小 -->
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center py-1">
                        <h5 class="mb-0 fs-6" style="font-size: 0.9rem !important; line-height: 1.2;">
                            <i class="bi bi-list-task"></i> 請求明細
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" id="addItemRowBtn" style="font-size: 0.75rem; padding: 0.1rem 0.4rem;">
                            <i class="bi bi-plus-lg"></i> 行を追加
                        </button>
                    </div>
                    <!-- 修改点：p-2 -->
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0" id="itemsTable" style="font-size: 0.875rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;" class="py-1">No.</th>
                                        <th style="width: 500px;" class="py-1">内容</th>
                                        <th style="width: 100px;" class="py-1">単価</th>
                                        <th style="width: 100px;" class="py-1">数量</th>
                                        <th style="width: 100px;" class="py-1">税率 (%)</th>
                                        <th style="width: 120px;" class="py-1">小計</th> 
                                        <th style="width: 100px;" class="py-1">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @php
                                        $oldItems = old('items', []);
                                        $displayItems = count($oldItems) > 0 ? $oldItems : array_fill(0, 10, []);
                                    @endphp
                                    @foreach($displayItems as $index => $item)
                                        @php $index = (int)$index; $orderNumber = $index + 1; @endphp
                                        <tr data-index="{{ $index }}" draggable="true">
                                            <td class="text-center align-middle display-order py-1" style="font-size: 0.8rem;">{{ $orderNumber }}</td>
                                            <td class="py-1">
                                                <input type="text" class="form-control form-control-sm description" 
                                                    name="items[{{ $index }}][description]" list="product-list-{{ $index }}" 
                                                    value="{{ $item['description'] ?? old('items.'.$index.'.description') }}" placeholder="入力または選択">
                                                <datalist id="product-list-{{ $index }}">
                                                    @foreach($products ?? [] as $product) <option value="{{ $product->name }}"> @endforeach
                                                </datalist>
                                            </td>
                                            <td class="py-1"><input type="number" class="form-control form-control-sm unit-price" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" min="0" step="0.01"></td>
                                            <td class="py-1"><input type="number" class="form-control form-control-sm quantity" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? '' }}" min="1" step="1"></td>
                                            <td class="py-1">
                                                <select class="form-control form-control-sm tax-rate" name="items[{{ $index }}][tax_rate]">
                                                    <option value="10" {{ (isset($item['tax_rate']) && $item['tax_rate'] == 10) ? 'selected' : '' }}>10</option>
                                                    <option value="8"  {{ (isset($item['tax_rate']) && $item['tax_rate'] == 8)  ? 'selected' : '' }}>8</option>
                                                    <option value="-1" {{ (isset($item['tax_rate']) && $item['tax_rate'] == -1) ? 'selected' : '' }}>免税</option>
                                                    <option value="-2" {{ (isset($item['tax_rate']) && $item['tax_rate'] == -2) ? 'selected' : '' }}>非課税</option>
                                                </select>
                                            </td>
                                            <td class="align-middle py-1"><input type="text" tabindex="-1" class="form-control form-control-sm line-total-input" value="0.00" readonly style="background-color: #f8f9fa; text-align: left;"></td>
                                            <td class="text-center align-middle py-1">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動" tabindex="-1" style="padding: 0.1rem 0.3rem;"><i class="bi bi-arrow-up"></i></button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動" tabindex="-1" style="padding: 0.1rem 0.3rem;"><i class="bi bi-arrow-down"></i></button>
                                                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加" tabindex="-1" style="padding: 0.1rem 0.3rem;"><i class="bi bi-plus-lg"></i></button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除" tabindex="-1" style="padding: 0.1rem 0.3rem;"><i class="bi bi-dash-lg"></i></button>
                                                </div>
                                                <input type="hidden" name="items[{{ $index }}][display_order]" value="{{ $orderNumber }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Template (保持逻辑，样式由 CSS 控制) -->
                <template id="newRowTemplate">
                    <tr draggable="true">
                        <td class="text-center align-middle display-order py-1" style="font-size: 0.8rem;"></td>
                        <td class="py-1">
                            <input type="text" class="form-control form-control-sm description" name="items[__index__][description]" list="product-list-__index__" value="" placeholder="入力または選択">
                            <datalist id="product-list-__index__">@foreach($products ?? [] as $product) <option value="{{ $product->name }}"> @endforeach</datalist>
                        </td>
                        <td class="py-1"><input type="number" class="form-control form-control-sm unit-price" name="items[__index__][unit_price]" min="0" step="0.01" value=""></td>
                        <td class="py-1"><input type="number" class="form-control form-control-sm quantity" name="items[__index__][quantity]" min="1" step="1" value=""></td>
                        <td class="py-1">
                            <select class="form-control form-control-sm tax-rate" name="items[__index__][tax_rate]">
                                <option value="10" selected>10</option><option value="8">8</option><option value="-1">免税</option><option value="-2">非課税</option>
                            </select>
                        </td>
                        <td class="align-middle py-1"><input type="text" tabindex="-1" class="form-control form-control-sm line-total-input" value="0.00" readonly style="background-color: #f8f9fa; text-align: left;"></td>
                        <td class="text-center align-middle py-1">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" tabindex="-1" style="padding: 0.1rem 0.3rem;"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" tabindex="-1" style="padding: 0.1rem 0.3rem;"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" class="btn btn-outline-success btn-sm add-row-btn" tabindex="-1" style="padding: 0.1rem 0.3rem;"><i class="bi bi-plus-lg"></i></button>
                                <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" tabindex="-1" style="padding: 0.1rem 0.3rem;"><i class="bi bi-dash-lg"></i></button>
                            </div>
                            <input type="hidden" name="items[__index__][display_order]" value="">
                        </td>
                    </tr>
                </template>

                <!-- 操作ボタン：减小间距 mb-3 -->
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm" style="font-size: 0.875rem;"><i class="bi bi-check-circle"></i> 登録する</button>
                        <a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}" class="btn btn-secondary btn-sm ms-2" style="font-size: 0.875rem;"><i class="bi bi-x-circle"></i> キャンセル</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    // 1. 代理店联动
    const agencySelect = document.getElementById('agency_id');
    const clientDetailsTextarea = document.getElementById('agency_detail');
    if (agencySelect && clientDetailsTextarea) {
        agencySelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            if (this.value === "") { clientDetailsTextarea.value = ""; return; }
            const agencyName = selectedOption.getAttribute('data-agency-name') || '';
            const agencyCode = selectedOption.getAttribute('data-agency-code') || '';
            const name = selectedOption.text;
            let detailsText = `会社名:${name}\n`;
            if (agencyCode) detailsText += `コード:${agencyCode}\n`;
            clientDetailsTextarea.value = detailsText;
        });
    }

    // 2. 锁开关
    const lockIconContainer = document.getElementById('lock_icon_container');
    const lockSwitch = document.getElementById('lock_switch');
    const lockHiddenInput = document.getElementById('lock_hidden_input');

    if (lockIconContainer && lockSwitch && lockHiddenInput) {
        updateLockIcon();
        lockIconContainer.addEventListener('click', function () {
            lockSwitch.checked = !lockSwitch.checked;
            updateLockIcon();
        });

        function updateLockIcon() {
            const iconElement = lockIconContainer.querySelector('i');
            if (lockSwitch.checked) {
                iconElement.className = 'bi bi-lock-fill text-danger';
                lockHiddenInput.value = '1';
            } else {
                iconElement.className = 'bi bi-unlock-fill text-success';
                lockHiddenInput.value = '0';
            }
        }
    }

    // 3. 表格逻辑
    const existingRows = document.querySelectorAll('#itemsBody tr[data-index]');
    let globalRowIndex = existingRows.length;
    function updateDisplayOrder() {
        document.querySelectorAll('#itemsBody tr[data-index]').forEach((row, idx) => {
            const order = idx + 1;
            row.querySelector('.display-order').textContent = order;
            const hiddenInput = row.querySelector('input[name$="[display_order]"]');
            if (hiddenInput) hiddenInput.value = order;
        });
    }
    function addNewRow(afterIndex = null) {
        const template = document.getElementById('newRowTemplate');
        const newRow = template.content.firstElementChild.cloneNode(true);
        const newIndex = globalRowIndex++;
        newRow.dataset.index = newIndex;
        newRow.innerHTML = newRow.innerHTML.replace(/__index__/g, newIndex);
        const tbody = document.getElementById('itemsBody');
        if (afterIndex !== null) {
            const targetRow = tbody.querySelector(`tr[data-index="${afterIndex}"]`);
            if (targetRow && targetRow.nextElementSibling) tbody.insertBefore(newRow, targetRow.nextElementSibling);
            else tbody.appendChild(newRow);
        } else { tbody.appendChild(newRow); }
        updateDisplayOrder();
        calculateRowTotal(newRow);
        initDraggableRows();
    }
    document.getElementById('addItemRowBtn').addEventListener('click', () => addNewRow());
    document.getElementById('itemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.add-row-btn, .delete-row-btn, .move-up-btn, .move-down-btn');
        if (!btn) return;
        const row = btn.closest('tr');
        const currentIndex = row.dataset.index;
        if (btn.classList.contains('add-row-btn')) addNewRow(currentIndex);
        if (btn.classList.contains('delete-row-btn')) {
            if (document.querySelectorAll('#itemsBody tr[data-index]').length > 1) { row.remove(); updateDisplayOrder(); }
            else alert('明細は最低 1 行必要です。');
        }
        if (btn.classList.contains('move-up-btn')) {
            const prevRow = row.previousElementSibling;
            if (prevRow && prevRow.dataset.index !== undefined) { row.parentNode.insertBefore(row, prevRow); updateDisplayOrder(); }
        }
        if (btn.classList.contains('move-down-btn')) {
            const nextRow = row.nextElementSibling;
            if (nextRow && nextRow.dataset.index !== undefined) { row.parentNode.insertBefore(nextRow, row); updateDisplayOrder(); }
        }
    });
    updateDisplayOrder();
    function calculateRowTotal(row) {
        const unitPriceInput = row.querySelector('.unit-price');
        const quantityInput = row.querySelector('.quantity');
        const totalInput = row.querySelector('.line-total-input');
        if (!unitPriceInput || !quantityInput || !totalInput) return;
        
        const price = parseFloat(unitPriceInput.value) || 0;
        const qty = parseFloat(quantityInput.value) || 0;
        const total = Math.round(price * qty);
        totalInput.value = total.toLocaleString('ja-JP');
    }

    // ===== 拖拽排序逻辑 =====
    let dragSrcEl = null;

    function handleDragStart(e) {
        dragSrcEl = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.outerHTML);
        this.classList.add('dragging');
    }

    function handleDragOver(e) {
        if (e.preventDefault) e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        this.classList.add('over');
    }

    function handleDragLeave() {
        this.classList.remove('over');
    }

    function handleDrop(e) {
        if (e.stopPropagation) e.stopPropagation();
        if (dragSrcEl !== this) {
            const tbody = document.getElementById('itemsBody');
            const allRows = Array.from(tbody.querySelectorAll('tr[data-index]'));
            const srcIndex = allRows.indexOf(dragSrcEl);
            const destIndex = allRows.indexOf(this);
            if (srcIndex === -1 || destIndex === -1) return;
            dragSrcEl.parentNode.removeChild(dragSrcEl);
            if (destIndex < srcIndex) {
                this.parentNode.insertBefore(dragSrcEl, this);
            } else {
                this.parentNode.insertBefore(dragSrcEl, this.nextSibling);
            }
            updateDisplayOrder();
        }
        this.classList.remove('over');
        return false;
    }

    function handleDragEnd() {
        const rows = document.querySelectorAll('#itemsBody tr[data-index]');
        rows.forEach(row => row.classList.remove('dragging', 'over'));
    }

    function initDraggableRows() {
        const rows = document.querySelectorAll('#itemsBody tr[data-index]');
        rows.forEach(row => {
            row.addEventListener('dragstart', handleDragStart, false);
            row.addEventListener('dragover', handleDragOver, false);
            row.addEventListener('dragenter', handleDragEnter, false);
            row.addEventListener('dragleave', handleDragLeave, false);
            row.addEventListener('drop', handleDrop, false);
            row.addEventListener('dragend', handleDragEnd, false);
        });
    }

    initDraggableRows();

    document.getElementById('itemsBody').addEventListener('input', function (e) {
        if (e.target.classList.contains('unit-price') || e.target.classList.contains('quantity')) {
            calculateRowTotal(e.target.closest('tr'));
        }
    });
    document.querySelectorAll('#itemsBody tr[data-index]').forEach(row => calculateRowTotal(row));
})();
</script>

<style>
/* 全局紧凑样式覆盖 */
body {
    font-size: 0.875rem !important;
}
.required::after { content: " *"; color: #dc3545; }

/* 表单控件统一变小 */
.form-control, .form-select {
    font-size: 0.875rem !important;
    padding: 0.25rem 0.5rem !important; /* 更小的内边距 */
    height: auto;
}

/* 标签统一变小 */
.form-label {
    font-size: 0.75rem !important;
    margin-bottom: 0.15rem !important;
}

/* 表格样式压缩 */
.table-bordered td { border: 1px solid #dee2e6; }
#itemsTable tbody tr:hover { background-color: #f8f9fa; }
#itemsTable tbody tr td { vertical-align: middle; }
.gap-1 { gap: 0.25rem; }

#itemsTable .form-control-sm { 
    padding: 0.1rem 0.25rem !important; 
    font-size: 0.8rem !important; 
    height: auto !important; 
}

#itemsTable tbody tr { 
    height: 36px !important; /* 进一步压缩行高 */
}

#itemsTable td { 
    padding: 0.2rem !important; 
    vertical-align: middle; 
}

#itemsTable .btn-sm { 
    padding: 0.1rem 0.2rem !important; 
    font-size: 0.7rem !important; 
    line-height: 1;
}

/* 拖拽效果 */
#itemsBody tr.dragging {
    opacity: 0.5;
    background-color: #e9ecef !important;
}
#itemsBody tr.over {
    border-top: 2px solid #0d6efd;
}

/* 卡片头部字体修正 */
.card-header h5 {
    font-size: 0.9rem !important;
    line-height: 1.2;
}
</style>
@endsection