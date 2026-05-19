<style>
    /* === 以下是 sty.txt 中的样式 === */
    .nav-tabs .nav-link.active {
        background-color: #0d6efd !important;
        color: #fff !important;
        border-color: #0d6efd !important;
        border-bottom-color: #0d6efd !important;
    }

    .nav-tabs .nav-link {
        background-color: #fff;
        color: #212529;
        border-color: #dee2e6;
    }

    .nav-tabs .nav-link:hover {
        border-color: #dee2e6;
        color: #495057;
    }

    .nav-tabs .nav-link.active i {
        color: #fff !important;
    }

    .form-control-datalist {
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        background-repeat: no-repeat;
        cursor: pointer;
    }

    .modal-dialog {
        margin: 2rem auto;
    }

    /* 新增：必填字段的错误样式 */
    .is-invalid-custom {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    .invalid-feedback-custom {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .was-validated .invalid-feedback-custom {
        display: block;
    }
</style>

<!-- === 第一步模态框：选择売掛金或普通預金 === -->
<div class="modal fade" id="journalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">仕訳選択</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- 假设这是选中的发票ID，实际使用时从页面获取 -->
                <input type="hidden" id="selected-invoice-ids" value="1001,1002,1003">
                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-outline-primary btn-lg py-3" onclick="openDetailModal('receivable')">
                        <i class="bi bi-credit-card me-2"></i> 売掛金
                    </button>
                    <button type="button" class="btn btn-outline-success btn-lg py-3" onclick="openDetailModal('deposit')">
                        <i class="bi bi-bank me-2"></i> 普通預金
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- === 第二步模态框：复式记账表单 (含动态数据填充) === -->
<div class="modal fade" id="cashEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <!-- === 1. 顶部公共区域 === -->
            <div class="modal-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                <div class="container-fluid p-0">
                    <div class="row g-2 align-items-end">
                        <!-- 日期 -->
                        <div class="col-auto">
                            <label class="form-label small fw-bold mb-1">仕訳日</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" id="entry-date" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <!-- 部门 -->
                        <div class="col-auto flex-grow-1">
                            <label class="form-label small fw-bold mb-1">部門</label>
                            <select id="post-dept" class="form-select form-select-sm">
                                <option value="">部門選択</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- 传票类别 -->
                        <div class="col-auto">
                            <label class="form-label small fw-bold mb-1">伝票種別</label>
                            <input type="text" class="form-control form-control-sm" value="" id="source-type">
                        </div>
                        <!-- 摘要 -->
                        <div class="col-auto flex-grow-1">
                            <label class="form-label small fw-bold mb-1">摘要</label>
                            <input type="text" class="form-control form-control-sm" placeholder="" id="summary-input">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close ms-3 mb-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2 px-4">
                <!-- 隐藏域：用于存储选中的发票ID 和 交易类型 -->
                <input type="hidden" id="detail-invoice-ids" value="">
                <input type="hidden" id="detail-transaction-type" value="">
                <div class="row g-3">
                    <!-- === 2. 左侧：借方 (自动合计) === -->
                    <div class="col-6">
                        <div class="border rounded-3 h-100" style="border-top: 4px solid #dc3545;">
                            <div class="bg-danger text-white px-3 py-2 d-flex justify-content-between align-items-center rounded-top-2">
                                <span class="fw-bold"><i class="bi bi-arrow-right me-2"></i> 借方</span>
                            </div>
                            <div class="p-3">
                                <div class="row g-2 align-items-center">
                                    <!-- 科目 (Datalist) -->
                                    <div class="col-4">
                                        <!-- 隐藏的 ID 字段 -->
                                        <input type="hidden" id="debit-account-id" name="debit_account_id">
                                        <!-- 显示的输入框 -->
                                        <input type="text" id="debit-account-input" class="form-control form-control-sm form-control-datalist" list="account-list" placeholder="科目検索">
                                    </div>
                                    <!-- 税率 (Select) -->
                                    <div class="col-3">
                                        <select class="form-select form-select-sm" id="debit-tax-select">
                                            @foreach($taxes as $tax)
                                                <option value="{{ $tax->id }}">{{ $tax->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- 金额 -->
                                    <div class="col-4">
                                        <input type="number" class="form-control form-control-sm text-end fw-bold" id="total-amount" value="0" readonly style="background-color: #e9ecef;">
                                    </div>
                                </div>
                                <!-- 辅助信息 -->
                                <div class="mt-2 p-2 bg-light border rounded-1">
                                    <div class="row g-2">
                                        <!-- 辅助科目 (Datalist) -->
                                        <div class="col-4">
                                            <input type="hidden" id="debit-subject-id">
                                            <input type="text" id="debit-subject-input" class="form-control form-control-sm form-control-datalist" list="subSubjectSuggestionList" placeholder="補助科目" readonly>
                                        </div>
                                        <!-- 取引先 -->
                                        <div class="col-4">
                                            <input type="text" id="partner-input" class="form-control form-control-sm" list="partner-list" placeholder="取引先名を入力" autocomplete="off" name="partner_name">
                                            <input type="hidden" id="debit-partner-id" value="">
                                            <datalist id="partner-list">
                                                @foreach($partners as $partner)
                                                    <option value="{{ $partner->name }}" data-id="{{ $partner->id }}"></option>
                                                @endforeach
                                            </datalist>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-group input-group-sm">
                                                <input type="date" class="form-control deal-date-input" id="deal-date-input" value="" placeholder="取引日">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- === 3. 右侧：贷方 (动态生成明细) === -->
                    <div class="col-6">
                        <div class="border rounded-3 h-100" style="border-top: 4px solid #212529;">
                            <div class="bg-dark text-white px-3 py-2 d-flex justify-content-between align-items-center rounded-top-2">
                                <span class="fw-bold"><i class="bi bi-arrow-left me-2"></i> 貸方</span>
                            </div>
                            <div class="p-3" id="credit-rows-container">
                                <!-- JS将动态生成行并插入到这里 -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center border-top-0 pt-0 pb-3">
                <button type="button" class="btn btn-primary rounded-3 px-4" onclick="submitForm()">登録</button>
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">取消</button>
            </div>
        </div>
    </div>
</div>

<!-- Datalist 定义 (通常放在 body 底部或 head 中) -->
<datalist id="account-list">
    @foreach($accounts as $account)
        <option value="{{ $account->code }} - {{ $account->name }}" data-tax-id="{{ $account->tax_id }}"></option>
    @endforeach
</datalist>
<datalist id="subSubjectSuggestionList"></datalist>

<script>
    window.AccountSettings = {
        receiveAccountTaxId: "{{ $receiveAccount->tax_id ?? '' }}",
        receiveAccountCode: "{{ $receiveAccount->code ?? '' }}",
        receiveAccountName: "{{ $receiveAccount->name ?? '' }}",
        depositAccountTaxId: "{{ $depositAccount->tax_id ?? '' }}",
        depositAccountCode: "{{ $depositAccount->code ?? '' }}",
        depositAccountName: "{{ $depositAccount->name ?? '' }}"
    };

    document.addEventListener('DOMContentLoaded', function () {
        // === 1. 定义 URL 模板 ===
        let getSubsUrlTemplate = "{{ route('masters.account.account-subs', ['accountId' => '__ID__']) }}";
        const submitUrl = "{{ route('masters.invoices.journal.store') }}";

        // --- 借方 Partner 逻辑 ---
        const partnerInput = document.getElementById('partner-input');
        const partnerIdHidden = document.getElementById('debit-partner-id');
        if (partnerInput && partnerIdHidden) {
            partnerInput.addEventListener('input', function () {
                const selectedName = this.value;
                const dataList = document.getElementById('partner-list');
                const options = dataList.querySelectorAll('option');
                let foundId = '';
                options.forEach(option => {
                    if (option.value === selectedName) {
                        foundId = option.getAttribute('data-id') || '';
                    }
                });
                partnerIdHidden.value = foundId;
            });
        }

        // === 2. 首页按钮点击逻辑 ===
        const journalBtn = document.getElementById('btn-journal-entry');
        if (journalBtn) {
            journalBtn.addEventListener('click', function () {
                const selectedIds = Array.from(document.querySelectorAll('.invoice-checkbox:checked'))
                    .map(cb => cb.value);
                if (selectedIds.length === 0) {
                    alert('请先选择要分录的发票！');
                    return;
                }
                const hiddenInput = document.getElementById('selected-invoice-ids');
                if (hiddenInput) {
                    hiddenInput.value = selectedIds.join(',');
                }
                const step1Modal = new bootstrap.Modal(document.getElementById('journalModal'));
                step1Modal.show();
            });
        }

        // === 3. 模态框交互逻辑 ===
        window.openDetailModal = function (type) {
            const step1Input = document.getElementById('selected-invoice-ids');
            const invoiceIds = step1Input ? step1Input.value : '';
            if (!invoiceIds) {
                alert('没有获取到选中的数据！');
                return;
            }
            const step1ModalEl = document.getElementById('journalModal');
            const step1Modal = bootstrap.Modal.getInstance(step1ModalEl);
            if (step1Modal) {
                step1Modal.hide();
            }
            const step2ModalEl = document.getElementById('cashEntryModal');
            const step2Modal = new bootstrap.Modal(step2ModalEl);
            step2ModalEl.addEventListener('shown.bs.modal', function handler() {
                step2ModalEl.removeEventListener('shown.bs.modal', handler);
                fillAndShowSecondModal(invoiceIds, type);
            }, { once: true });
            step2Modal.show();
        };

        function fillAndShowSecondModal(selectedIdsStr, type) {
            const selectedIds = selectedIdsStr.split(',').map(id => id.trim()).filter(id => id);
            let totalAmount = 0;
            const container = document.getElementById('credit-rows-container');
            if (!container) return;
            container.innerHTML = '';

            selectedIds.forEach((id, index) => {
                const checkbox = document.querySelector(`input.invoice-checkbox[value="${id}"]`);
                let amountVal = 0;
                let dealDate = '';
                if (checkbox) {
                    const amountStr = checkbox.getAttribute('data-request-amount');
                    amountVal = parseFloat(amountStr?.replace(/,/g, '') || '0') || 0;
                    dealDate = checkbox.getAttribute('data-deal-date');
                    if (dealDate) {
                        dealDate = dealDate.substring(0, 10); 
                    }
                }
                totalAmount += amountVal;

                const defaultAccountId = "{{ $spmsgAccount->id }}";
                const defaultAccountName = "{{ $spmsgAccount->code }} - {{ $spmsgAccount->name }}";
                const defaultTaxId = "{{ $spmsgAccount->tax_id }}";

                const newRowHtml = `
                <div class="row g-2 align-items-center mb-2 credit-row">
                    <div class="col-4"> 
                        <input type="hidden" class="credit-account-id" name="credit_lines[${index}][account_id]" value="${defaultAccountId}"> 
                        <input type="text" class="form-control form-control-sm form-control-datalist credit-account-input" 
                            list="account-list" placeholder="勘定科目" 
                            value="${defaultAccountName}" 
                            > 
                    </div> 
                    <div class="col-3"> 
                        <select class="form-select form-select-sm tax-select" name="credit_lines[${index}][tax_type]">
                            @foreach($taxes as $tax) 
                                <option value="{{ $tax->id }}" ${'{{ $tax->id }}' == defaultTaxId ? 'selected' : ''}>{{ $tax->name }}</option> 
                            @endforeach 
                        </select> 
                    </div> 
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm text-end row-amount" value="${amountVal}" readonly>
                    </div>
                    <div class="col-12 mt-1">
                        <div class="p-2 bg-light border rounded-1">
                            <div class="row g-2">
                                <!-- 辅助科目 -->
                                <div class="col-4">
                                    <input type="hidden" class="credit-subject-id" name="credit_lines[${index}][sub_subject_id]">
                                    <input type="text" class="form-control form-control-sm form-control-datalist credit-subject-input" list="subSubjectSuggestionList" placeholder="補助科目" readonly style="background: transparent;">
                                </div>
                                <!-- 交易方 -->
                                <div class="col-4">
                                    <input type="text" class="form-control form-control-sm partner-input-field" list="partner-list" placeholder="取引先名を入力" autocomplete="off">
                                    <input type="hidden" class="credit-partner-id" name="credit_lines[${index}][partner_id]" value="">
                                </div>
                                <div class="col-4">
                                    <input type="date" class="form-control form-control-sm deal-date-input" value="${dealDate}" placeholder="取引日">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
                container.insertAdjacentHTML('beforeend', newRowHtml);
            });

            const totalInput = document.getElementById('total-amount');
            if (totalInput) totalInput.value = totalAmount;
            document.getElementById('detail-invoice-ids').value = selectedIdsStr;
            document.getElementById('detail-transaction-type').value = type === 'deposit' ? 'income' : 'expense';

            // 锁定借方科目和税率
            const debitAccountInput = document.getElementById('debit-account-input');
            const debitTaxSelect = document.getElementById('debit-tax-select');
            const debitSubjectInput = document.getElementById('debit-subject-input'); // 获取辅助科目输入框
            const debitSubjectIdInput = document.getElementById('debit-subject-id'); // 获取辅助科目ID隐藏框

            if (debitAccountInput && debitTaxSelect) {
                let targetCode = '';
                let targetTaxId = '';
                if (type === 'receivable') {
                    targetCode = window.AccountSettings.receiveAccountCode + ' - ' + window.AccountSettings.receiveAccountName;
                    targetTaxId = window.AccountSettings.receiveAccountTaxId;
                } else if (type === 'deposit') {
                    targetCode = window.AccountSettings.depositAccountCode + ' - ' + window.AccountSettings.depositAccountName;
                    targetTaxId = window.AccountSettings.depositAccountTaxId;
                }
                if (targetCode && targetTaxId) {
                    debitAccountInput.value = targetCode;
                    debitTaxSelect.value = targetTaxId;

                    // --- 关键修复：手动调用处理函数 ---
                    // 这会自动去请求辅助科目并填充
                    handleAccountChange(debitAccountInput, debitSubjectInput, debitSubjectIdInput);
                    
                    // --- 原有逻辑：锁定输入框 ---
                    // 注意：这里要放在 handleAccountChange 之后，否则 handle 里的 removeAttribute 会被覆盖
                    debitAccountInput.setAttribute('readonly', 'readonly');
                    debitAccountInput.style.backgroundColor = '#e9ecef';
                    debitTaxSelect.setAttribute('disabled', 'disabled');
                }
            }


            // 动态行内 Partner 和 SubSubject 的事件绑定
            container.addEventListener('input', function (e) {
                if (e.target.classList.contains('partner-input-field')) {
                    const selectedName = e.target.value;
                    const dataList = document.getElementById('partner-list');
                    const options = dataList.querySelectorAll('option');
                    let foundId = '';
                    options.forEach(option => {
                        if (option.value === selectedName) {
                            foundId = option.getAttribute('data-id') || '';
                        }
                    });
                    const row = e.target.closest('.credit-row');
                    const hiddenIdField = row.querySelector('.credit-partner-id');
                    if (hiddenIdField) hiddenIdField.value = foundId;
                }
            });

            // 【修复重点】将辅助科目的监听事件从 input 改为 change
            container.addEventListener('change', function (e) {
                if (e.target.classList.contains('credit-subject-input')) {
                    const selectedName = e.target.value;
                    const row = e.target.closest('.credit-row');
                    const hiddenIdField = row.querySelector('.credit-subject-id');
                    
                    if (hiddenIdField && selectedName) {
                        // 从 datalist 中查找对应的 ID
                        const dataList = document.getElementById('subSubjectSuggestionList');
                        const options = dataList.querySelectorAll('option');
                        let foundId = '';
                        options.forEach(option => {
                            if (option.value === selectedName) {
                                foundId = option.getAttribute('data-id') || '';
                            }
                        });
                        hiddenIdField.value = foundId;
                    } else if (hiddenIdField) {
                        hiddenIdField.value = '';
                    }
                }
            });
        }

        // --- 科目选择联动逻辑 ---
        function handleAccountChange(accountInput, subSubjectInput, subSubjectIdInput) {
            const inputValue = accountInput.value.trim();
            subSubjectInput.value = '';
            subSubjectIdInput.value = '';
            if (inputValue) {
                const parts = inputValue.split(' - ');
                const potentialId = parts[0]; // 修复：取数组的第一项作为ID
                if (potentialId && getSubsUrlTemplate.includes('__ID__')) {
                    const url = getSubsUrlTemplate.replace('__ID__', potentialId);
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            const dataList = document.getElementById('subSubjectSuggestionList');
                            dataList.innerHTML = '';
                            data.forEach(sub => {
                                const option = document.createElement('option');
                                option.value = sub.name;
                                option.setAttribute('data-id', sub.id);
                                dataList.appendChild(option);
                            });
                            subSubjectInput.disabled = false;
                            subSubjectInput.removeAttribute('readonly');
                        })
                        .catch(err => {
                            console.error('获取辅助科目失败:', err);
                            subSubjectInput.disabled = true;
                        });
                }
            } else {
                subSubjectInput.disabled = true;
                subSubjectInput.setAttribute('readonly', 'readonly');
            }

            // 税率联动
            const row = accountInput.closest('.credit-row') || accountInput.closest('.debit-row');
            const taxSelect = row ? row.querySelector('.tax-select') : null;
            if (inputValue && taxSelect) {
                const dataList = document.getElementById('account-list');
                if (dataList) {
                    const options = dataList.querySelectorAll('option');
                    let matchedTaxId = null;
                    options.forEach(option => {
                        if (option.value === inputValue) {
                            matchedTaxId = option.getAttribute('data-tax-id');
                        }
                    });
                    if (matchedTaxId) {
                        accountInput.setAttribute('data-tax-id', matchedTaxId);
                        taxSelect.value = matchedTaxId;
                        taxSelect.dispatchEvent(new Event('change'));
                    }
                }
            }
        }

        // --- 借方科目绑定 ---
        const debitAccountInput = document.getElementById('debit-account-input');
        const debitSubjectInput = document.getElementById('debit-subject-input');
        const debitSubjectIdInput = document.getElementById('debit-subject-id');
        if (debitAccountInput && debitSubjectInput) {
            debitAccountInput.addEventListener('input', function () {
                handleAccountChange(this, debitSubjectInput, debitSubjectIdInput);
            });
        }
        if (debitSubjectInput && debitSubjectIdInput) {
            debitSubjectInput.addEventListener('change', function() {
                const selectedName = this.value;
                const dataList = document.getElementById('subSubjectSuggestionList');
                const options = dataList.querySelectorAll('option');
                let foundId = '';
                options.forEach(option => {
                    if (option.value === selectedName) {
                        foundId = option.getAttribute('data-id') || '';
                    }
                });
                // 修复点 B：把找到的 ID 赋值给隐藏域
                debitSubjectIdInput.value = foundId; 
            });
        }

        // --- 贷方科目绑定 ---
        const creditContainer = document.getElementById('credit-rows-container');
        if (creditContainer) {
            creditContainer.addEventListener('input', function (e) {
                if (e.target.classList.contains('credit-account-input')) {
                    const row = e.target.closest('.credit-row');
                    const subInput = row.querySelector('.credit-subject-input');
                    const subIdInput = row.querySelector('.credit-subject-id');
                    handleAccountChange(e.target, subInput, subIdInput);
                }
            });
        }

        // === 4. 表单提交逻辑 ===
        window.submitForm = function () {
            if (!validateForm()) {
                return; // 如果验证失败，直接中断，不执行后续提交
            }
            const invoiceIdsStr = document.getElementById('detail-invoice-ids').value;
            const transactionType = document.getElementById('detail-transaction-type').value;
            const entryDate = document.getElementById('entry-date').value;
            const summary = document.getElementById('summary-input').value;
            const sourceType = document.getElementById('source-type').value;
            const department = document.getElementById('post-dept').value;

            const creditRows = [];
            const rowElements = document.querySelectorAll('.credit-row');
            const selectedInvoiceIds = document.getElementById('detail-invoice-ids').value.split(',');
            let accountFlag = 1;
            rowElements.forEach((row, index) => {
                const accountInput = row.querySelector('.credit-account-input');
                const accountName = accountInput ? accountInput.value : '';
                const accountId = accountName ? accountName.split(' - ')[0] : '';

                const taxSelect = row.querySelector('.tax-select');
                const taxId = taxSelect ? taxSelect.value : '';

                const amountInput = row.querySelector('.row-amount');
                const amount = amountInput ? parseFloat(amountInput.value) || 0 : 0;
                const dealDate = row.querySelector('.deal-date-input').value;

                const subSubjectIdInput = row.querySelector('.credit-subject-id');
                let subSubjectId = '';
                if (subSubjectIdInput) {
                    subSubjectId = subSubjectIdInput.value;
                }
                // 兜底：如果隐藏域依然为空，尝试最后从 datalist 匹配一次
                if (!subSubjectId) {
                    const subInput = row.querySelector('.credit-subject-input');
                    const subName = subInput?.value || '';
                    if (subName) {
                        const dataList = document.getElementById('subSubjectSuggestionList');
                        if (dataList) {
                            const option = dataList.querySelector(`option[value="${subName}"]`);
                            if (option) {
                                subSubjectId = option.getAttribute('data-id') || '';
                            }
                        }
                    }
                }

                const partnerIdInput = row.querySelector('.credit-partner-id');
                const partnerNameInput = row.querySelector('.partner-input-field');
                let partnerId = '';
                if (partnerIdInput) {
                    partnerId = partnerIdInput.value;
                }
                if (!partnerId && partnerNameInput) {
                    const selectedName = partnerNameInput.value;
                    const dataList = document.getElementById('partner-list');
                    if (dataList && selectedName) {
                        const option = dataList.querySelector(`option[value="${selectedName}"]`);
                        if (option) {
                            partnerId = option.getAttribute('data-id') || '';
                        }
                    }
                }
                const currentInvoiceId = selectedInvoiceIds[index] || '';
                if(accountId == ''){
                    accountFlag = 0;
                }
                creditRows.push({
                    line_number: index + 1,
                    account_id: accountId,
                    account_name: accountName,
                    tax_id: taxId,
                    amount: amount,
                    sub_subject_id: subSubjectId,
                    partner_id: partnerId,
                    deal_date:dealDate,
                    invoice_id: currentInvoiceId
                });
            });

            if(entryDate == ''){
                alert('请选择仕訳日');
                return;
            }
            if(accountFlag == 0){
                alert('请选择勘定科目');
                return;
            }

            const payload = {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                date: entryDate,
                department: department,
                summary: summary,
                source_type: sourceType,
                type: transactionType,
                source_invoice_ids: invoiceIdsStr.split(','),
                total_amount: document.getElementById('total-amount').value,
                debit: {
                    account_id: getDebitAccountId(),
                    account_name: document.getElementById('debit-account-input').value,
                    tax_id: document.getElementById('debit-tax-select').value,
                    amount: document.getElementById('total-amount').value,
                    partner_id: document.getElementById('debit-partner-id').value,
                    sub_subject_id: document.getElementById('debit-subject-id').value,
                    deal_date: document.getElementById('deal-date-input').value
                },
                credit_lines: creditRows,
            };

            console.log('发送的数据:', payload);

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('网络错误，状态码：' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('记账成功！');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('cashEntryModal'));
                        if (modal) modal.hide();
                        location.reload();
                    } else {
                        alert('错误：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('提交失败:', error);
                    alert('提交失败：' + error.message);
                });
        };

        function getDebitAccountId() {
            const accountInput = document.getElementById('debit-account-input');
            const accountName = accountInput.value.trim();
            if (!accountName) return '';
            const dataList = document.getElementById('account-list');
            const options = dataList.querySelectorAll('option');
            for (let option of options) {
                if (option.value === accountName) {
                    return accountName.split(' - ')[0];
                }
            }
            return '';
        }
    });

    function validateForm() {
        // 2. 验证贷方 (Credit) - 检查所有行
        const creditRows = document.querySelectorAll('.credit-row');
        if (creditRows.length === 0) {
            alert('未生成任何【贷方】分录行，请检查发票数据！');
            return false;
        }

        for (let i = 0; i < creditRows.length; i++) {
            const row = creditRows[i];
            const accountIdInput = row.querySelector('.credit-account-id');
            const accountNameInput = row.querySelector('.credit-account-input');
            
            // 检查隐藏域 ID 或 显示的文本域
            if (!accountIdInput?.value && !accountNameInput?.value) {
                alert(`第 ${i + 1} 行【贷方】勘定科目为空，请补全信息！`);
                return false;
            }
        }

        return true; // 验证通过
    }
</script>