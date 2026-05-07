<!-- ========================================== -->
<!-- 1. 初始操作选择模态框 (Step 1) -->
<!-- ========================================== -->
<div class="modal fade" id="initialActionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>入金操作選択</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 fs-5 fw-bold">以下の操作を選んでください。</p>
                <div class="alert alert-light border mb-4 p-3">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-3 text-primary fw-bold">「詳細入金」</dt>
                        <dd class="col-sm-9 mb-2">入金画面に切り替える。<br>入金額・銀行・備考などを個別設定可能。</dd>
                        <dt class="col-sm-3 text-primary fw-bold">「取消」</dt>
                        <dd class="col-sm-9">操作を中止する。</dd>
                    </dl>
                </div>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <button type="button" class="btn btn-primary btn-lg px-5 flex-grow-1" style="min-width: 180px;" id="btn-action-detail">
                        <i class="bi bi-pencil-square me-2"></i>入金
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg px-5 flex-grow-1" style="min-width: 180px;" data-bs-dismiss="modal">
                        取消
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. 详细编辑模态框 (Step 2 - 核心表单) -->
<!-- ========================================== -->
<div class="modal fade" id="bulkReconcileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl"> 
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fs-6"><i class="bi bi-list-check me-2"></i>個別消し込み処理 (詳細)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('masters.payments.store') }}" method="POST" id="detail-form">
                @csrf
                <input type="hidden" name="return_url" id="form-return-url" value="">
                <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                <input type="hidden" name="mode" value="detail">
                
                <!-- 【关键】顶层公共隐藏域 (提交时由 JS 填充) -->
                <input type="hidden" name="payment_date" id="form-payment-date">
                <input type="hidden" name="remark" id="form-remark">

                <div class="modal-body bg-light pb-1">
                    
                    <!-- 【公共设置区】 -->
                    <div class="bg-white border rounded p-2 mb-2 shadow-sm d-flex align-items-end gap-3">
                        <!-- 1. 日期 -->
                        <div class="flex-grow-1" style="max-width: 160px;">
                            <label class="form-label mb-0 small fw-bold text-primary">入金日</label>
                            <input type="date" id="common-payment-date" class="form-control form-control-sm fw-bold" 
                                   value="{{ date('Y-m-d') }}" style="height: 31px;">
                        </div>
                        <div class="flex-grow-1" style="max-width: 200px;">
                            <label class="form-label mb-0 small fw-bold text-primary">振込銀行</label>
                            <select id="common-bank-id" name="bank_id" class="form-select form-select-sm" style="height: 31px;">
                                <option value="0">銀行を選択</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- 2. 备注 -->
                        <div class="flex-grow-1">
                            <label class="form-label mb-0 small fw-bold text-primary">備考</label>
                            <input type="text" id="common-remark" class="form-control form-control-sm" 
                                   placeholder="" style="height: 31px;">
                        </div>
                        <div class="text-muted small mb-1 d-none d-md-block">
                            <i class="bi bi-info-circle"></i> 自動反映
                        </div>
                    </div>

                    <!-- 统计提示 -->
                    <div class="alert alert-warning d-flex align-items-center py-2 mb-2 small shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
                        <div>
                            選択された <strong id="reconcile-count" class="fw-bold">0</strong> 件。
                            <span class="text-dark">入金額のみ個別入力してください。</span>
                        </div>
                    </div>

                    <!-- 动态列表容器 -->
                    <div id="reconcile-items-container" class="space-y-2" style="max-height: 60vh; overflow-y: auto; padding-right: 5px;">
                        <!-- JS 将在此处插入卡片 -->
                    </div>
                </div>

                <!-- ✅ 底部统计栏：分两行布局，确保完美对齐 -->
                <div class="modal-footer bg-white border-top py-3">
                    <!-- 【第一行】统计信息：独占一行，宽度 100% -->
                    <div class="w-100 mb-2">
                        <div class="d-flex align-items-center w-100">
                            <!-- 1. 合計标签 (对应上方: 請求書番号 col-md-2) -->
                            <div class="col-md-2 col-lg-2 pe-2" style="padding-left: 0.75rem; padding-right: 0.75rem;">
                                <div class="text-muted fw-bold small text-nowrap" style="font-size: 0.875rem; line-height: 1.5;">
                                    合計 (<span id="reconcile-count-footer">0</span>件):
                                </div>
                            </div>

                            <!-- 2. 請求金額合計 (对应上方: 請求金額 col-md-2) -->
                            <div class="col-md-2 col-lg-2 text-center" style="padding-left: 0.75rem; padding-right: 0.75rem; border-right: 1px solid #dee2e6;">
                                <div class="fw-bold font-monospace text-muted" style="font-size: 0.875rem; line-height: 1.5;">
                                    <span id="footer-request-total">0</span>
                                </div>
                            </div>

                            <!-- 3. 未入金残高合計 (对应上方: 未入金残高 col-md-2) -->
                            <div class="col-md-2 col-lg-2 text-center" style="padding-left: 0.75rem; padding-right: 0.75rem; border-right: 1px solid #dee2e6;">
                                <div class="fw-bold font-monospace text-danger" style="font-size: 0.875rem; line-height: 1.5;">
                                    <span id="footer-balance-total">0</span>
                                </div>
                            </div>

                            <!-- 4. 入金額合計 (对应上方: 入金額 col-md-3) -->
                            <div class="col-md-3 col-lg-3" style="padding-left: 0.75rem; padding-right: 0.75rem;">
                                <div class="fw-bold font-monospace text-primary" style="font-size: 0.875rem; line-height: 1.5;">
                                    <span id="footer-payment-total">0</span> 
                                    <span id="footer-currency-label" class="small text-muted" style="font-size: 0.75rem;"></span>
                                </div>
                            </div>

                            <!-- 5. 占位列 (对应上方: 取引先 col-md-2) -->
                            <div class="col-md-2 col-lg-2 d-none d-md-block" style="padding-left: 0.75rem; padding-right: 0.75rem;"></div>
                        </div>
                    </div>

                    <!-- 【第二行】按钮组：独立一行，右对齐 -->
                    <div class="w-100 d-flex justify-content-end gap-2 mt-1">
                        <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">
                            キャンセル
                        </button>
                        <button type="submit" id="btn-submit-reconcile" class="btn btn-sm btn-success px-4 fw-bold">
                            <i class="bi bi-check-circle-fill"></i> 登録実行
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. 隐藏表单：用于“全额入金”模式 (如有需要) -->
<!-- ========================================== -->
<form id="full-payment-form" action="{{ route('masters.payments.store') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="return_url" id="form-return-url" value="">
    <input type="hidden" name="group_id" value="{{ request('group_id') }}">
    <input type="hidden" name="mode" value="full">
    <input type="hidden" name="payment_date" id="full-form-payment-date">
    <input type="hidden" name="remark" id="full-form-remark">
    <div id="full-payment-inputs"></div>
</form>

<!-- ========================================== -->
<!-- 4. 模板：带余额显示的明细行 -->
<!-- ========================================== -->
<template id="invoice-item-template">
    <div class="card mb-1 border-start border-3 border-primary shadow-sm invoice-item-card position-relative">
        <!-- 删除按钮 -->
        <button type="button" 
                class="btn-close position-absolute top-0 end-0 m-0 z-index-1 bg-danger bg-opacity-10" 
                aria-label="Remove"
                title="この行を削除"
                style="z-index: 10;">
        </button>

        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <!-- 隐藏域 -->
                <input type="hidden" name="items[{index}][invoice_id]" class="item-invoice-id">
                <input type="hidden" name="items[{index}][original_amount]" class="item-original-amount">
                
                <!-- 1. 发票号 (col-md-2) -->
                <div class="col-md-2 col-lg-2">
                    <div class="small text-muted mb-0">請求書番号</div>
                    <div class="fw-bold text-dark item-invoice-no text-truncate" title=""></div>
                </div>
                
                <!-- 3. 请求金额 (col-md-2) -->
                <div class="col-md-2 col-lg-2 text-center">
                    <div class="small text-muted mb-0">請求金額</div>
                    <div class="fw-bold font-monospace item-request-amount"></div>
                </div>

                <!-- 4. 余额列 (col-md-2) -->
                <div class="col-md-2 col-lg-2 text-center">
                    <div class="small text-muted mb-0">未入金残高</div>
                    <div class="fw-bold font-monospace text-danger item-balance-amount"></div>
                </div>
                
                <!-- 5. 入金额 (col-md-3) -->
                <div class="col-md-3 col-lg-3">
                    <div class="small text-primary fw-bold mb-0">入金額</div>
                    <div class="input-group input-group-sm"> 
                        <input type="number" name="items[{index}][payment_amount]" 
                               class="form-control form-control-sm item-payment-amount fw-bold" 
                               step="0.01" min="0" required>
                        <span class="input-group-text bg-light item-currency-display"></span>
                    </div>
                </div>

                <!-- 2. 客户名 (col-md-2) - 调整顺序以匹配视觉习惯，或保持原样 -->
                <div class="col-md-2 col-lg-2  text-center">
                    <div class="small text-muted mb-0">請求先</div>
                    <div class="small text-muted item-customer-name text-truncate" title=""></div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- ========================================== -->
<!-- 5. JavaScript 逻辑 (数据对象化版本) -->
<!-- ========================================== -->
<script>
(function() {
    'use strict';

    // 等待 DOM 完全加载后再执行，确保能获取到表格中的按钮
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReconcileModal);
    } else {
        initReconcileModal();
    }

    function initReconcileModal() {
        const btnBulkReconcile = document.getElementById('btn-bulk-reconcile');
        const initialModalEl = document.getElementById('initialActionModal');
        const detailModalEl = document.getElementById('bulkReconcileModal');
        
        if (!initialModalEl || !window.bootstrap) return;
        
        const initialModal = new bootstrap.Modal(initialModalEl);
        const detailModal = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;

        const detailContainer = document.getElementById('reconcile-items-container');
        const detailTemplate = document.getElementById('invoice-item-template');
        const fullPaymentFormInputs = document.getElementById('full-payment-inputs');
        const commonDateInput = document.getElementById('common-payment-date');
        const commonRemarkInput = document.getElementById('common-remark');

        let selectedInvoiceData = []; 

        // --- 【全局函数】更新底部统计 ---
        window.updateFooterStats = function() {
            if (!detailContainer) return;
            let totalRequest = 0, totalBalance = 0, totalPayment = 0, count = 0, currentCurrency = 'JPY';
            
            document.querySelectorAll('.invoice-item-card').forEach((card, index) => {
                count++;
                if (index === 0) {
                    const firstCurrencySpan = card.querySelector('.item-currency-display');
                    if (firstCurrencySpan && firstCurrencySpan.textContent) currentCurrency = firstCurrencySpan.textContent;
                }
                const requestVal = parseFloat(card.querySelector('.item-original-amount')?.value || '0');
                totalRequest += requestVal;
                
                const balanceEl = card.querySelector('.item-balance-amount');
                const balanceText = balanceEl ? balanceEl.textContent.replace(/,/g, '') : '0';
                totalBalance += parseFloat(balanceText) || 0;
                
                const paymentInput = card.querySelector('.item-payment-amount');
                if (paymentInput && !paymentInput.disabled) {
                    const payVal = parseFloat(paymentInput.value) || 0;
                    totalPayment += payVal;
                    const max = parseFloat(paymentInput.getAttribute('data-max-amount')) || 0;
                    if (max > 0 && payVal > max + 0.001) paymentInput.classList.add('is-invalid');
                    else paymentInput.classList.remove('is-invalid');
                }
            });

            ['reconcile-count', 'reconcile-count-footer'].forEach(id => { 
                const el = document.getElementById(id); 
                if(el) el.textContent = count; 
            });

            const fmt = (num) => num.toLocaleString('ja-JP');
            const setTxt = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = fmt(val); };
            
            setTxt('footer-request-total', totalRequest);
            setTxt('footer-balance-total', totalBalance);
            setTxt('footer-payment-total', totalPayment);
            
            const footerCurrencyEl = document.getElementById('footer-currency-label');
            if (footerCurrencyEl) footerCurrencyEl.textContent = currentCurrency;
            
            const submitBtn = document.getElementById('btn-submit-reconcile');
            if (submitBtn) submitBtn.disabled = (count === 0);
        };

        if (!btnBulkReconcile) return;

        // --- 1. 批量按钮点击 ---
        btnBulkReconcile.addEventListener('click', function () {
            const returnUrl = this.getAttribute('data-return-url') || '';
            window.sourceReturnUrl = returnUrl; // 存入全局变量供后面使用
            // 场景 A: Index 页 (通过复选框选择)
            const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
            
            let dataToProcess = [];

            if (checkedBoxes.length > 0) {
                // Index 页逻辑
                dataToProcess = Array.from(checkedBoxes).map(cb => ({
                    id: cb.value,
                    invoice_no: cb.dataset.invoiceNo || '',
                    customer_name: cb.dataset.customerName || '-',
                    currency_code: cb.dataset.currencyCode || '', 
                    request_amount: parseFloat(cb.dataset.requestAmount) || 0,
                    balance_amount: parseFloat(cb.dataset.balanceAmount) || 0
                }));
            } else {
                // 场景 B: Edit 页 (当前按钮本身就是数据源)
                // 检查当前点击的按钮是否带有数据属性
                const singleId = btnBulkReconcile.getAttribute('value') || btnBulkReconcile.dataset.invoiceId;
                
                if (singleId) {
                    dataToProcess = [{
                        id: singleId,
                        invoice_no: btnBulkReconcile.dataset.invoiceNo || '',
                        customer_name: btnBulkReconcile.dataset.customerName || '-',
                        currency_code: btnBulkReconcile.dataset.currencyCode || 'JPY',
                        request_amount: parseFloat(btnBulkReconcile.dataset.requestAmount) || 0,
                        balance_amount: parseFloat(btnBulkReconcile.dataset.balanceAmount) || 0
                    }];
                }
            }

            if (dataToProcess.length === 0) { 
                alert('対象の請求書を選択してください。'); 
                return; 
            }
            
            selectedInvoiceData = dataToProcess;
            
            // ✅ 关键：如果是单条模式 (Edit 页)，直接跳过“初始选择模态框”，进入“详细编辑模态框”
            if (dataToProcess.length === 1 && btnBulkReconcile.classList.contains('invoice-checkbox-simulator')) {
                // 模拟点击“详细入金”按钮的逻辑
                if(btnDetail) btnDetail.click();
            } else {
                // 多条模式，显示初始选择框
                initialModal.show();
            }
        });

        // --- 2. 全额入金 ---
        const btnFull = document.getElementById('btn-action-full');
        if(btnFull) {
            btnFull.addEventListener('click', function() {
                if(selectedInvoiceData.length === 0) return;
                fullPaymentFormInputs.innerHTML = '';
                if(commonDateInput) document.getElementById('full-form-payment-date').value = commonDateInput.value;
                if(commonRemarkInput) document.getElementById('full-form-remark').value = commonRemarkInput.value || '';
                
                selectedInvoiceData.forEach((item, index) => {
                    const createInput = (name, val) => { 
                        const inp = document.createElement('input'); 
                        inp.type = 'hidden'; inp.name = name; inp.value = val; 
                        fullPaymentFormInputs.appendChild(inp); 
                    };
                    createInput(`items[${index}][invoice_id]`, item.id);
                    createInput(`items[${index}][payment_amount]`, item.balance_amount.toFixed(2));
                });
                initialModal.hide();
                document.getElementById('full-payment-form').submit();
            });
        }

        // --- 3. 详细入金渲染 ---
        const btnDetail = document.getElementById('btn-action-detail');
        if(btnDetail && detailContainer && detailTemplate) {
            btnDetail.addEventListener('click', function() {
                if(selectedInvoiceData.length === 0) return;
                initialModal.hide();
                detailContainer.innerHTML = '';
                
                selectedInvoiceData.forEach((item, index) => {
                    const clone = document.importNode(detailTemplate.content, true);
                    clone.querySelector('.item-invoice-id').value = item.id;
                    clone.querySelector('.item-original-amount').value = item.request_amount;
                    clone.querySelector('.item-invoice-no').textContent = item.invoice_no;
                    clone.querySelector('.item-customer-name').textContent = item.customer_name;
                    clone.querySelector('.item-request-amount').textContent = item.request_amount.toLocaleString('ja-JP');
                    
                    const currencySpan = clone.querySelector('.item-currency-display');
                    if(currencySpan) currencySpan.textContent = item.currency_code;
                    
                    const balanceEl = clone.querySelector('.item-balance-amount');
                    const amountInput = clone.querySelector('.item-payment-amount');
                    
                    if (item.balance_amount < 0.005) {
                        balanceEl.textContent = "0"; 
                        balanceEl.className = 'fw-bold font-monospace text-secondary item-balance-amount';
                        amountInput.value = "0.00"; amountInput.disabled = true; 
                        amountInput.classList.add('bg-light', 'text-muted'); 
                        amountInput.setAttribute('data-max-amount', "0"); 
                    } else if (item.balance_amount > 0.005) {
                        balanceEl.textContent = item.balance_amount.toLocaleString('ja-JP');
                        amountInput.value = item.balance_amount.toFixed(2); 
                        amountInput.disabled = false; 
                        amountInput.setAttribute('data-max-amount', item.balance_amount);
                        amountInput.addEventListener('input', function() { 
                            if (typeof window.updateFooterStats === 'function') window.updateFooterStats(); 
                        });
                    } else {
                        balanceEl.textContent = "0"; 
                        balanceEl.className = 'fw-bold font-monospace text-secondary item-balance-amount';
                        amountInput.value = "0.00"; amountInput.disabled = true; 
                        amountInput.classList.add('bg-light', 'text-muted'); 
                        amountInput.setAttribute('data-max-amount', "0");
                    }
                    
                    clone.querySelectorAll('input').forEach(el => { 
                        const name = el.getAttribute('name'); 
                        if(name) el.setAttribute('name', name.replace('{index}', index)); 
                    });
                    
                    clone.querySelector('.btn-close').addEventListener('click', function() { 
                        this.closest('.invoice-item-card').remove(); 
                        if (typeof window.updateFooterStats === 'function') window.updateFooterStats(); 
                    });
                    
                    detailContainer.appendChild(clone);
                });
                
                if (typeof window.updateFooterStats === 'function') window.updateFooterStats();
                const returnUrlInput = document.getElementById('form-return-url');
                if (returnUrlInput && window.sourceReturnUrl) {
                    returnUrlInput.value = window.sourceReturnUrl;
                }
                detailModal.show();
            });
        }

        // --- 4. 提交校验 ---
        const btnSubmit = document.getElementById('btn-submit-reconcile');
        const detailForm = document.getElementById('detail-form');
        if (btnSubmit && detailForm) {
            btnSubmit.addEventListener('click', function(e) {
                e.preventDefault();
                if(commonDateInput) document.getElementById('form-payment-date').value = commonDateInput.value;
                if(commonRemarkInput) document.getElementById('form-remark').value = commonRemarkInput.value || '';
                
                const paymentInputs = detailContainer.querySelectorAll('.item-payment-amount');
                let hasError = false, firstErrorInput = null, errorMsg = '';
                
                paymentInputs.forEach(input => {
                    if(input.disabled) return;
                    const max = parseFloat(input.getAttribute('data-max-amount')) || 0;
                    const val = parseFloat(input.value) || 0;
                    
                    if (max <= 0.005) { hasError = true; firstErrorInput = input; errorMsg = '残高 0 の項目が含まれています。'; } 
                    else if (val > max + 0.001) { hasError = true; if(!firstErrorInput) firstErrorInput = input; errorMsg = '入金額が残高を超えています。'; } 
                    else if (val < 0.005) { hasError = true; if(!firstErrorInput) firstErrorInput = input; errorMsg = '残高があるのに入力が 0 です。'; }
                    
                    if(hasError && firstErrorInput) { 
                        firstErrorInput.classList.add('is-invalid', 'border-danger'); 
                        firstErrorInput.style.borderColor = 'red'; 
                    } else { 
                        input.classList.remove('is-invalid', 'border-danger'); 
                        input.style.borderColor = ''; 
                    }
                });
                
                if (hasError) {
                    if(firstErrorInput) { 
                        firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' }); 
                        firstErrorInput.focus(); 
                    }
                    alert('⚠️ エラー：\n' + errorMsg); 
                    return;
                }
                detailForm.submit();
            });
        }

        // --- 5. ✅ 单行销账兼容逻辑 (确保此时 DOM 已完全加载) ---
        const singleButtons = document.querySelectorAll('.btn-single-reconcile');
        if (singleButtons.length > 0) {
            singleButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetId = this.dataset.id;
                    let targetCheckbox = document.querySelector(`.invoice-checkbox[value="${targetId}"]`);
                    
                    if (!targetCheckbox) {
                        // 容错：尝试遍历查找
                        const allCheckboxes = document.querySelectorAll('.invoice-checkbox');
                        for(let cb of allCheckboxes) {
                            if(cb.value == targetId) { targetCheckbox = cb; break; }
                        }
                        if(!targetCheckbox) {
                            alert('エラー：該当するチェックボックスが見つかりません。');
                            return;
                        }
                    }

                    // 清空其他选择
                    document.querySelectorAll('.invoice-checkbox:checked').forEach(cb => cb.checked = false);
                    targetCheckbox.checked = true;
                    // 触发 change 事件以更新批量操作栏
                    targetCheckbox.dispatchEvent(new Event('change'));

                    // 延迟触发主按钮
                    setTimeout(() => {
                        if(btnBulkReconcile) btnBulkReconcile.click();
                    }, 50);
                });
            });
        }
    }
})();
</script>