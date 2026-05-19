<!-- resources/views/modals/journal_view_modal.blade.php -->
<div class="modal fade" id="viewJournalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <!-- 顶部标题与关闭按钮 -->
            <div class="modal-header bg-white border-bottom-0 pb-0 pt-3 px-4">
                <div class="container-fluid p-0">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <h5 class="modal-title">仕訳詳細</h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-info text-dark fs-6" id="view-journal-id">Loading...</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close ms-3 mb-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2 px-4">

                <div class="row g-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <!-- 日期 -->
                        <div class="col-auto">
                            <label class="form-label small fw-bold mb-1">仕訳日</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-plaintext" id="view-entry-date" readonly>
                            </div>
                        </div>
                        <!--  -->
                        <div class="col-auto">
                            <label class="form-label small fw-bold mb-1">部门</label>
                            <input type="text" class="form-control form-control-sm" value="" id="department_name" readonly>
                        </div>

                        <!-- 传票类别 -->
                        <div class="col-auto">
                            <label class="form-label small fw-bold mb-1">伝票種別</label>
                            <input type="text" class="form-control form-control-sm" value="" id="source_type" readonly>
                        </div>
                        <div class="col-auto flex-grow-1">
                            <label class="form-label small fw-bold mb-1">摘要</label>
                            <input type="text" class="form-control form-control-sm" id="view-summary" readonly>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- 左侧：借方 -->
                    <div class="col-6">
                        <div class="border rounded-3 h-100" style="border-top: 4px solid #dc3545;">
                            <div class="bg-danger text-white px-3 py-2 d-flex justify-content-between align-items-center rounded-top-2">
                                <span class="fw-bold"><i class="bi bi-arrow-right me-2"></i> 借方</span>
                            </div>
                            <div class="p-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-4">
                                        <input type="text" id="view-debit-account" class="form-control form-control-sm form-control-plaintext" readonly>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="view-debit-tax" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control form-control-sm text-end fw-bold" id="view-total-amount" readonly style="background-color: #e9ecef;">
                                    </div>
                                </div>
                                <div class="mt-2 p-2 bg-light border rounded-1">
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <input type="text" id="view-debit-subject" class="form-control form-control-sm form-control-plaintext" readonly>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" id="view-debit-partner" class="form-control form-control-sm form-control-plaintext" readonly>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control form-control-sm form-control-plaintext" id="view-debit-deal-date" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 右侧：贷方 -->
                    <div class="col-6">
                        <div class="border rounded-3 h-100" style="border-top: 4px solid #212529;">
                            <div class="bg-dark text-white px-3 py-2 d-flex justify-content-between align-items-center rounded-top-2">
                                <span class="fw-bold"><i class="bi bi-arrow-left me-2"></i> 貸方</span>
                            </div>
                            <div class="p-3" id="view-credit-rows-container">
                                <!-- 动态生成的贷方行将插入这里 -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// 确保 DOM 加载完成后执行
document.addEventListener('DOMContentLoaded', function () {
    // === 1. 修改点击事件监听器 ===
    // 使用事件委托，监听所有带有 data-journal-id 属性的元素
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-journal-id]');
        if (target) {
            const journalId = target.getAttribute('data-journal-id');
            if (journalId && journalId !== 'N/A') {
                openViewModal(journalId);
            }
        }
    });

    // === 2. 定义打开模态框的函数 ===
    function openViewModal(id) {
        // 1. 显示 Loading 状态
        const modalIdSpan = document.getElementById('view-journal-id');
        const modalTitle = document.querySelector('#viewJournalModal .modal-title');
        if (modalIdSpan) modalIdSpan.textContent = `Loading... (ID: ${id})`;
        if (modalTitle) modalTitle.textContent = 'データ読み込み中...';

        // 显示模态框
        const modal = new bootstrap.Modal(document.getElementById('viewJournalModal'));
        modal.show();
        let getUrl = "{{ route('masters.invoices.journal.get', ['invoice_id' => 'PLACEHOLDER_ID']) }}";
        getUrl = getUrl.replace('PLACEHOLDER_ID', id); 

        fetch(getUrl) 
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    populateViewModal(data.data);
                } else {
                    alert('データ取得失敗: ' + data.message);
                    modal.hide();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('通信エラー: ' + error.message);
                modal.hide();
            });
    }

    // === 3. 定义填充模态框的函数 ===
    function populateViewModal(data) {
        // 填充顶部公共信息
        document.getElementById('view-journal-id').textContent = data.id;
        document.getElementById('view-entry-date').value = data.date;
        document.getElementById('view-summary').value = data.summary || '-';
        document.getElementById('source_type').value = data.source_type || '-';
        document.getElementById('department_name').value = data.department_name || '-';

        // 填充借方 (Debit)
        document.getElementById('view-debit-account').value = data.debit.account_name || '-';
        document.getElementById('view-debit-tax').value = data.debit.tax_name || '-';
        document.getElementById('view-total-amount').value = Number(data.debit.amount).toLocaleString();
        document.getElementById('view-debit-subject').value = data.debit.sub_subject_name || '-';
        document.getElementById('view-debit-partner').value = data.debit.partner_name || '-';
        document.getElementById('view-debit-deal-date').value = data.debit.deal_date || '-';
        document.getElementById('post-dept').value = data.department_id || '';

        // 填充贷方 (Credit) - 动态生成行
        const container = document.getElementById('view-credit-rows-container');
        container.innerHTML = ''; // 清空旧数据

        data.credit_lines.forEach(line => {
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2';
            row.innerHTML = `
                <div class="col-4">
                    <input type="text" class="form-control form-control-sm" value="${line.account_name}" readonly>
                </div>
                <div class="col-3">
                    <input type="text" class="form-control form-control-sm" value="${line.tax_name}" readonly>
                </div>
                <div class="col-4">
                    <input type="text" class="form-control form-control-sm text-end" value="${Number(line.amount).toLocaleString()}" readonly>
                </div>
                <div class="col-12 mt-1">
                    <div class="p-2 bg-light border rounded-1">
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" value="${line.sub_subject_name || '-'}" readonly>
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" value="${line.partner_name || '-'}" readonly>
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" value="${line.deal_date || '-'}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(row);
        });

        // 更新标题
        document.querySelector('#viewJournalModal .modal-title').textContent = '仕訳詳細';
    }
});
</script>