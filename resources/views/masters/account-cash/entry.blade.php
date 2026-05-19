
    <style>
        /* 样式保持不变 */
        .nav-tabs .nav-link.active { background-color: #0d6efd !important; color: #fff !important; border-color: #0d6efd !important; border-bottom-color: #0d6efd !important; }
        .nav-tabs .nav-link { background-color: #fff; color: #212529; border-color: #dee2e6; }
        .nav-tabs .nav-link:hover { border-color: #dee2e6; color: #495057; }
        .nav-tabs .nav-link.active i { color: #fff !important; }
        .form-control-datalist { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4,5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e"); background-position: right 0.75rem center; background-size: 16px 12px; background-repeat: no-repeat; cursor: pointer; }
        .modal-dialog { margin: 2rem auto; }
        
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



    <div class="modal fade" id="cashEntryModal" tabindex="-1" aria-hidden="true">
        <!-- 1. 表单地址 -->
        <form id="cashEntryForm" action="{{ route('masters.account-cash.journal.store') }}" method="POST" novalidate>
            @csrf
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-light py-2 border-bottom">
                        <ul class="nav nav-tabs border-0 w-100" role="tablist">
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link active fw-bold py-1 px-2" data-bs-toggle="tab" href="#tabExpense" data-value="expense" style="font-size: 0.9rem;">
                                    <i class="bi bi-arrow-up-circle"></i> 支出
                                </a>
                            </li>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link fw-bold py-1 px-2" data-bs-toggle="tab" href="#tabIncome" data-value="income" style="font-size: 0.9rem;">
                                    <i class="bi bi-arrow-down-circle"></i> 収入
                                </a>
                            </li>
                        </ul>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body pt-4 pb-3">
                        <!-- 隐藏字段：交易类型 -->
                        <input type="hidden" name="transaction_type" id="transactionTypeInput" value="expense">
                        
                        <div class="tab-content mb-2">
                            <div class="tab-pane fade show active" id="tabExpense"></div>
                            <div class="tab-pane fade" id="tabIncome"></div>
                        </div>
                        <div class="row g-2 align-items-center">
                            <!-- 取引日 -->
                            <label class="col-3 col-form-label text-end fw-bold small">仕訳日</label>
                            <div class="col-9">
                                <input type="date" class="form-control form-control-sm" name="transaction_date" value="{{ date('Y-m-d') }}">
                            </div>

                            <!-- 勘定科目 (必填) -->
                            <label class="col-3 col-form-label text-end fw-bold small">勘定科目 <span class="text-danger">*</span></label>
                            <div class="col-9 position-relative">
                                <input type="text" style="display: none;" tabindex="-1">
                                <!-- 1. 添加 required 属性 -->
                                <input type="text" id="search-account-input" class="form-control form-control-sm form-control-datalist" 
                                       list="account-list-search" placeholder="科目コードまたは名前を入力" autocomplete="off" required>
                                <input type="hidden" id="search-account-id" name="account_id" required>

                                <datalist id="account-list-search">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->code }} - {{ $account->name }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <!-- 辅助科目 -->
                            <label class="col-3 col-form-label text-end fw-bold small">補助科目</label>
                            <div class="col-9 position-relative">
                                <input type="text" class="form-control form-control-sm form-control-datalist" 
                                       id="subSubjectInput" placeholder="先に勘定科目を選択してください" list="subSubjectSuggestionList" readonly>
                                <input type="hidden" name="sub_subject_id" id="subSubjectIdInput" value="">
                                <datalist id="subSubjectSuggestionList"></datalist>
                            </div>

                            <!-- 摘要 -->
                            <label class="col-3 col-form-label text-end fw-bold small">摘要</label>
                            <div class="col-9">
                                <input type="text" class="form-control form-control-sm" name="description" placeholder="摘要・取引内容など">
                            </div>

                            <!-- 取引先 -->
                            <label class="col-3 col-form-label text-end fw-bold small">取引先</label>
                            <div class="col-9 position-relative">
                                <input type="text" id="partner-input" class="form-control form-control-sm" list="partner-list" placeholder="取引先名を入力" autocomplete="off" name="partner_name">
                                <input type="hidden" id="partner-id-input" name="partner_id" value="">
                                <datalist id="partner-list">
                                    @foreach($partners as $partner)
                                        <option value="{{ $partner->name }}" data-id="{{ $partner->id }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <!-- 取引日 -->
                            <label class="col-3 col-form-label text-end fw-bold small">取引日</label>
                            <div class="col-9">
                                <input type="date" class="form-control form-control-sm" name="deal_date" value="">
                            </div>

                            <!-- 金额 (必填) -->
                            <label class="col-3 col-form-label text-end fw-bold small">金額 <span class="text-danger">*</span></label>
                            <div class="col-9">
                                <div class="input-group input-group-sm">
                                    <!-- 2. 添加 required 属性 -->
                                    <input type="number" class="form-control text-end" name="amount" placeholder="0" required>

                                    <select class="form-select" name="tax_type" style="max-width: 150px;">
                                        @foreach($taxes as $tax)
                                            <option value="{{ $tax->id }}">{{ $tax->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-center border-top-0 pt-0 pb-3">
                        <button type="submit" class="btn btn-primary rounded-3 px-4" style="width: 100px;">登録</button>
                        <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal" style="width: 100px;">取消</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. 定义 URL 模板 (保持不变)
    let getSubsUrlTemplate = "{{ route('masters.account.account-subs', ['accountId' => '__ID__']) }}";

    // 2. 获取 DOM 元素 (新增获取 partner 相关元素)
    const accountInput = document.getElementById('search-account-input');
    const subSubjectInput = document.getElementById('subSubjectInput');
    const subSubjectList = document.getElementById('subSubjectSuggestionList');
    const subSubjectIdInput = document.getElementById('subSubjectIdInput');
    const accountIdInput = document.getElementById('search-account-id');
    
    // --- 新增：获取交易方相关元素 ---
    const partnerInput = document.getElementById('partner-input');
    const partnerIdInput = document.getElementById('partner-id-input');
    const partnerList = document.getElementById('partner-list');

    // 3. Tab 相关 (保持不变)
    const tabElements = document.querySelectorAll('.nav-tabs a');
    const typeInput = document.getElementById('transactionTypeInput');
    tabElements.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            const selectedType = this.getAttribute('data-value');
            typeInput.value = selectedType;
        });
    });

    // 4. 主科目联动逻辑 (保持不变)
    accountInput.addEventListener('input', function() {
        const inputValue = this.value.trim();
        subSubjectList.innerHTML = '';
        subSubjectInput.value = '';
        subSubjectIdInput.value = '';
        subSubjectInput.disabled = true;
        subSubjectInput.setAttribute('readonly', 'readonly');
        if (inputValue) {
            const potentialId = inputValue.split(' - ')[0];
            accountIdInput.value = potentialId;
            if (potentialId) {
                const url = getSubsUrlTemplate.replace('__ID__', potentialId);
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.length > 0) {
                            data.forEach(item => {
                                const text = typeof item === 'object' ? `${item.id} - ${item.name}` : item;
                                const option = document.createElement('option');
                                option.value = text;
                                subSubjectList.appendChild(option);
                            });
                            subSubjectInput.disabled = false;
                            subSubjectInput.removeAttribute('readonly');
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        subSubjectInput.disabled = true;
                        subSubjectInput.setAttribute('readonly', 'readonly');
                    });
            }
        } else {
            accountIdInput.value = '';
        }
    });

    subSubjectInput.addEventListener('input', function() {
        const value = this.value.trim();
        if (!value) {
            subSubjectIdInput.value = '';
            return;
        }
        const id = value.split(' - ')[0];
        subSubjectIdInput.value = id;
    });

    // --- 新增：交易方联动逻辑 ---
    // 当用户输入或选择交易方时触发
    partnerInput.addEventListener('input', function() {
        const selectedName = this.value.trim();
        partnerIdInput.value = ''; // 清空之前的 ID

        if (selectedName) {
            // 遍历 datalist 中的选项
            Array.from(partnerList.options).forEach(option => {
                if (option.value === selectedName) {
                    // 假设后端在 option 上设置了 data-id 属性
                    // 如果后端没有设置 data-id，这里需要修改逻辑，比如通过 API 查询 ID
                    // 或者在 Blade 模板中直接把 ID 存在 value 里 (例如: value="ID - Name")
                    const partnerId = option.getAttribute('data-id');
                    if (partnerId) {
                        partnerIdInput.value = partnerId;
                    }
                    return;
                }
            });
        }
    });

    // 5. 表单提交前的自定义校验逻辑 (保持不变)
    const form = document.getElementById('cashEntryForm');
    form.addEventListener('submit', function (event) {
        form.classList.remove('was-validated');
        document.querySelectorAll('.is-invalid-custom').forEach(el => el.classList.remove('is-invalid-custom'));
        
        const accountValue = accountInput.value.trim();
        const amountValue = form.querySelector('input[name="amount"]').value.trim();
        let isValid = true;

        if (!accountValue) {
            accountInput.classList.add('is-invalid-custom');
            isValid = false;
        }
        if (!amountValue) {
            const amountInput = form.querySelector('input[name="amount"]');
            amountInput.classList.add('is-invalid-custom');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
        }
    });
</script>
