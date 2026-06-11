@extends('layouts.driver')

@section('title', '立替計算')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">立替計算 - {{ $formattedDate }}</div>
        <div class="header-right">
            <div style="width: 32px;"></div>
        </div>
    </div>

    <div class="expense-container">
        <div class="expense-list" id="expenseList">
            @foreach($expenses as $expense)
            <div class="expense-item" data-id="{{ $expense->id }}" 
                 data-expense_date="{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}"
                 data-amount="{{ $expense->amount }}"
                 data-type_id="{{ $expense->type_id }}"
                 data-payment_method_id="{{ $expense->payment_method_id }}"
                 data-agency_flag="{{ $expense->agency_flag ? '1' : '0' }}"
                 data-remark="{{ $expense->remark ?? '' }}">
                <div class="expense-row">
                    <div class="expense-left">
                        <div class="expense-date">{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y/m/d') }}</div>
                        <div class="expense-type">
                            {{ $expense->expenseType->type_name ?? '' }}
                            @if($expense->agency_flag)
                            <div class="expense-badge">代理店負担</div>
                            @endif
                        </div>
                    </div>
                    <div class="expense-right">
                        <div class="expense-amount">¥ {{ number_format($expense->amount) }}</div>
                        <div class="expense-payment">{{ $expense->paymentMethod->method_name ?? '' }}</div>
                    </div>
                </div>
                @if($expense->remark)
                <div class="expense-remark">{{ $expense->remark }}</div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="button-container">
            <button class="add-expense-btn" id="addExpenseBtn">+ 立替追加</button>
            <button class="back-btn" id="cancelBtn">戻る</button>
        </div>
    </div>
</div>

<div class="expense-modal" id="expenseModal">
    <div class="modal-content">
        <h4 id="modalTitle">立替登録</h4>
        <div class="form-grid">
            <div class="edit-field">
                <label>経費日付</label>
                <input type="date" id="expenseDateInput" class="modal-input">
            </div>
            <div class="edit-field">
                <label>経費種別</label>
                <select id="expenseTypeSelect" class="modal-select">
                    <option value="">選択してください</option>
                    @foreach($expenseTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="edit-field">
                <label>支払方法</label>
                <select id="paymentMethodSelect" class="modal-select">
                    <option value="">選択してください</option>
                    @foreach($paymentMethods as $method)
                    <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="edit-field">
                <label>金額 (円)</label>
                <input type="number" id="amountInput" class="modal-input" min="0" step="1" placeholder="0">
            </div>
            <div class="edit-field">
                <label>備考</label>
                <textarea id="remarkInput" class="modal-textarea" rows="3" placeholder="備考を入力..."></textarea>
            </div>
            <div class="edit-field">
                <label class="checkbox-label">
                    <input type="checkbox" id="agencyFlagCheckbox"> 代理店負担
                </label>
            </div>
        </div>
        
        <div class="receipts-area" style="margin-top: 16px; border-top: 1px solid var(--border-color); padding-top: 16px;">
            <div class="receipts-label" style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
                📷 領収書
            </div>
            <div class="preview-receipts-list" id="previewReceiptsList" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
            </div>
        </div>

        <div class="modal-buttons" style="display: flex; gap: 12px; justify-content: space-between;">
            <button class="modal-delete" id="deleteExpenseBtn" style="display: none;">削除</button>
            <button class="modal-confirm" id="confirmBtn">確認</button>
            <button class="modal-cancel" id="cancelModalBtn">キャンセル</button>
        </div>
    </div>
</div>

<div class="image-modal" id="imageViewModal">
    <div class="image-modal-content" style="text-align: center;">
        <button class="image-modal-close" onclick="closeImageViewModal()">×</button>
        <img id="imageViewImg" src="" style="max-width: 90%; max-height: 90%;">
    </div>
</div>
@endsection

@push('styles')
<style>
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.form-grid .edit-field:last-child,
.form-grid .edit-field:nth-last-child(2) {
    grid-column: span 2;
}

.form-grid .edit-field:has(textarea) {
    grid-column: span 2;
}

.expense-container {
    padding: 12px;
}

.expense-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.expense-item {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}

.expense-item:active {
    transform: scale(0.98);
}

.expense-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.expense-left {
    flex: 1;
}

.expense-right {
    text-align: right;
}

.expense-date {
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 4px;
}

.expense-type {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
}

.expense-amount {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
}

.expense-payment {
    font-size: 11px;
    color: var(--text-secondary);
    margin-top: 4px;
}

.expense-remark {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
}

.expense-badge {
    font-size: 10px;
    padding: 1px 8px;
    margin: 0 0 0 10px;
    background-color: #f59e0b;
    color: white;
    border-radius: 20px;
    display: inline-block;
}

.button-container {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 16px;
}

.add-expense-btn {
    flex: 1;
    padding: 12px 16px;
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.back-btn {
    flex: 1;
    padding: 12px 16px;
    background-color: var(--card-bg);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.expense-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    visibility: hidden;
    opacity: 0;
    transition: all 0.3s;
}

.expense-modal.show {
    visibility: visible;
    opacity: 1;
}

.modal-content {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 20px;
    width: 320px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content h4 {
    font-size: 16px;
    margin-bottom: 16px;
    color: var(--text-primary);
    text-align: center;
}

.edit-field {
    text-align: left;
}

.edit-field label {
    display: block;
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 2px;
}

.modal-input, .modal-select, .modal-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 14px;
    background-color: var(--bg-color);
    color: var(--text-primary);
}

.modal-textarea {
    resize: vertical;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.modal-buttons {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.modal-buttons button {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    cursor: pointer;
}

.modal-confirm {
    background-color: var(--accent-color);
    color: var(--accent-text);
}

.modal-cancel {
    background-color: var(--bg-color);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.modal-delete {
    background-color: #dc2626;
    color: white;
}

.expense-type-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.expense-badge-inline {
    font-size: 10px;
    padding: 2px 8px;
    background-color: #f59e0b;
    color: white;
    border-radius: 20px;
}

.upload-receipt-btn {
    flex: 1;
    padding: 12px 16px;
    background-color: #10b981;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.upload-receipt-btn:active {
    transform: scale(0.98);
}

.image-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    visibility: hidden;
    opacity: 0;
    transition: all 0.3s;
}

.image-modal.show {
    visibility: visible;
    opacity: 1;
}

.image-modal img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

.image-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
}

.preview-receipts-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.preview-receipt-item {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 6px;
    background-color: #f3f4f6;
    border: 1px solid var(--border-color);
    flex-shrink: 0;
}

.preview-receipt-delete {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background-color: #dc2626;
    color: white;
    border: none;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-receipt-btn-square {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 10px;
    text-align: center;
    flex-shrink: 0;
}

.upload-receipt-btn-square:active {
    transform: scale(0.98);
}
</style>
@endpush

@push('scripts')
<script>
let currentItineraryId = {{ $itinerary->id }};
let currentBusAssignmentId = {{ $itinerary->bus_assignment_id ?? 'null' }};
let currentEditingId = null;
let currentDriverId = {{ session('driver_id') }};
let pendingFiles = [];
let deletedReceiptIds = [];

function bindExpenseItemClickEvents() {
    document.querySelectorAll('.expense-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            openEditModal(this);
        });
    });
}

function openEditModal(element) {
    currentEditingId = element.getAttribute('data-id');
    const expenseDate = element.getAttribute('data-expense_date');
    const amount = element.getAttribute('data-amount');
    const typeId = element.getAttribute('data-type_id');
    const paymentMethodId = element.getAttribute('data-payment_method_id');
    const agencyFlag = element.getAttribute('data-agency_flag') === '1';
    const remark = element.getAttribute('data-remark') || '';
    
    document.getElementById('modalTitle').textContent = '立替編集';
    document.getElementById('expenseDateInput').value = expenseDate;
    document.getElementById('amountInput').value = amount;
    document.getElementById('expenseTypeSelect').value = typeId;
    document.getElementById('paymentMethodSelect').value = paymentMethodId;
    document.getElementById('agencyFlagCheckbox').checked = agencyFlag;
    document.getElementById('remarkInput').value = remark;
    
    const deleteBtn = document.getElementById('deleteExpenseBtn');
    if (deleteBtn) {
        deleteBtn.style.display = 'block';
    }
    
    pendingFiles = [];
    deletedReceiptIds = [];
    
    const previewContainer = document.getElementById('previewReceiptsList');
    if (previewContainer) {
        const existingItems = previewContainer.querySelectorAll('.preview-receipt-item');
        existingItems.forEach(item => item.remove());
        
        let uploadBtn = document.getElementById('uploadReceiptBtnSquare');
        if (uploadBtn) {
            uploadBtn.remove();
        }
        
        uploadBtn = document.createElement('label');
        uploadBtn.className = 'upload-receipt-btn-square';
        uploadBtn.id = 'uploadReceiptBtnSquare';
        uploadBtn.style.display = 'flex';
        uploadBtn.style.flexDirection = 'column';
        uploadBtn.style.alignItems = 'center';
        uploadBtn.style.justifyContent = 'center';
        uploadBtn.style.width = '60px';
        uploadBtn.style.height = '60px';
        uploadBtn.style.backgroundColor = '#10b981';
        uploadBtn.style.color = 'white';
        uploadBtn.style.border = 'none';
        uploadBtn.style.borderRadius = '6px';
        uploadBtn.style.cursor = 'pointer';
        uploadBtn.style.fontSize = '10px';
        uploadBtn.style.textAlign = 'center';
        uploadBtn.innerHTML = '📷<br>選択';
        
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.id = 'receiptFileInput';
        fileInput.accept = 'image/jpeg,image/png,image/jpg';
        fileInput.multiple = true;
        fileInput.style.display = 'none';
        
        fileInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach(file => {
                if (file.size > 5 * 1024 * 1024) {
                    alert(`ファイル ${file.name} は5MB以下にしてください`);
                    return;
                }
                
                pendingFiles.push(file);
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'preview-receipt-item';
                    previewDiv.setAttribute('data-filename', file.name);
                    previewDiv.style.position = 'relative';
                    previewDiv.style.width = '60px';
                    previewDiv.style.height = '60px';
                    previewDiv.style.borderRadius = '6px';
                    previewDiv.style.border = '1px solid var(--border-color)';
                    previewDiv.style.flexShrink = '0';
                    
                    previewDiv.innerHTML = `
                        <img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" class="preview-receipt-delete" data-filename="${file.name}" 
                                style="position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; border-radius: 50%; background-color: #dc2626; color: white; border: none; font-size: 12px; cursor: pointer;">
                            ×
                        </button>
                    `;
                    
                    const currentUploadBtn = document.getElementById('uploadReceiptBtnSquare');
                    if (currentUploadBtn) {
                        previewContainer.insertBefore(previewDiv, currentUploadBtn);
                    } else {
                        previewContainer.appendChild(previewDiv);
                    }
                };
                reader.readAsDataURL(file);
            });
            
            e.target.value = '';
        });
        
        uploadBtn.appendChild(fileInput);
        previewContainer.appendChild(uploadBtn);
    }
    
    loadExistingReceipts(currentEditingId);
    
    document.getElementById('expenseModal').classList.add('show');
}

function openCreateModal() {
    currentEditingId = null;
    document.getElementById('modalTitle').textContent = '立替登録';
    document.getElementById('expenseDateInput').value = new Date().toISOString().split('T')[0];
    document.getElementById('amountInput').value = '';
    document.getElementById('expenseTypeSelect').value = '';
    document.getElementById('paymentMethodSelect').value = '';
    document.getElementById('agencyFlagCheckbox').checked = false;
    document.getElementById('remarkInput').value = '';
    
    const deleteBtn = document.getElementById('deleteExpenseBtn');
    if (deleteBtn) {
        deleteBtn.style.display = 'none';
    }
    
    pendingFiles = [];
    deletedReceiptIds = [];
    
    const previewContainer = document.getElementById('previewReceiptsList');
    if (previewContainer) {
        const existingItems = previewContainer.querySelectorAll('.preview-receipt-item');
        existingItems.forEach(item => item.remove());
        
        let uploadBtn = document.getElementById('uploadReceiptBtnSquare');
        if (uploadBtn) {
            uploadBtn.remove();
        }
        
        uploadBtn = document.createElement('label');
        uploadBtn.className = 'upload-receipt-btn-square';
        uploadBtn.id = 'uploadReceiptBtnSquare';
        uploadBtn.style.display = 'flex';
        uploadBtn.style.flexDirection = 'column';
        uploadBtn.style.alignItems = 'center';
        uploadBtn.style.justifyContent = 'center';
        uploadBtn.style.width = '60px';
        uploadBtn.style.height = '60px';
        uploadBtn.style.backgroundColor = '#10b981';
        uploadBtn.style.color = 'white';
        uploadBtn.style.border = 'none';
        uploadBtn.style.borderRadius = '6px';
        uploadBtn.style.cursor = 'pointer';
        uploadBtn.style.fontSize = '10px';
        uploadBtn.style.textAlign = 'center';
        uploadBtn.innerHTML = '📷<br>選択';
        
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.id = 'receiptFileInput';
        fileInput.accept = 'image/jpeg,image/png,image/jpg';
        fileInput.multiple = true;
        fileInput.style.display = 'none';
        
        fileInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach(file => {
                if (file.size > 5 * 1024 * 1024) {
                    alert(`ファイル ${file.name} は5MB以下にしてください`);
                    return;
                }
                
                pendingFiles.push(file);
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'preview-receipt-item';
                    previewDiv.setAttribute('data-filename', file.name);
                    previewDiv.style.position = 'relative';
                    previewDiv.style.width = '60px';
                    previewDiv.style.height = '60px';
                    previewDiv.style.borderRadius = '6px';
                    previewDiv.style.border = '1px solid var(--border-color)';
                    previewDiv.style.flexShrink = '0';
                    
                    previewDiv.innerHTML = `
                        <img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" class="preview-receipt-delete" data-filename="${file.name}" 
                                style="position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; border-radius: 50%; background-color: #dc2626; color: white; border: none; font-size: 12px; cursor: pointer;">
                            ×
                        </button>
                    `;
                    
                    const currentUploadBtn = document.getElementById('uploadReceiptBtnSquare');
                    if (currentUploadBtn) {
                        previewContainer.insertBefore(previewDiv, currentUploadBtn);
                    } else {
                        previewContainer.appendChild(previewDiv);
                    }
                };
                reader.readAsDataURL(file);
            });
            
            e.target.value = '';
        });
        
        uploadBtn.appendChild(fileInput);
        previewContainer.appendChild(uploadBtn);
    }
    
    document.getElementById('expenseModal').classList.add('show');
}

function closeModal() {
    document.getElementById('expenseModal').classList.remove('show');
    currentEditingId = null;
    pendingFiles = [];
    deletedReceiptIds = [];
}

function loadExistingReceipts(expenseId) {
    const previewContainer = document.getElementById('previewReceiptsList');
    if (!previewContainer) return;
    
    const uploadBtn = document.getElementById('uploadReceiptBtnSquare');
    if (!uploadBtn) return;
    
    fetch(`/driver/advance-payment/receipts-by-expense?expense_id=${expenseId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.receipts && data.receipts.length > 0) {
            data.receipts.forEach((receipt) => {
                const receiptDiv = document.createElement('div');
                receiptDiv.className = 'preview-receipt-item';
                receiptDiv.setAttribute('data-receipt-id', receipt.id);
                receiptDiv.style.position = 'relative';
                receiptDiv.style.width = '60px';
                receiptDiv.style.height = '60px';
                receiptDiv.style.borderRadius = '6px';
                receiptDiv.style.border = '1px solid var(--border-color)';
                receiptDiv.style.flexShrink = '0';
                
                receiptDiv.innerHTML = `
                    <img src="${receipt.url}" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" 
                         onclick="viewReceiptImage('${receipt.url}')">
                    <button type="button" class="existing-receipt-delete" data-receipt-id="${receipt.id}" 
                            style="position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; border-radius: 50%; background-color: #dc2626; color: white; border: none; font-size: 12px; cursor: pointer;">
                        ×
                    </button>
                `;
                
                previewContainer.insertBefore(receiptDiv, uploadBtn);
            });
            
            document.querySelectorAll('.existing-receipt-delete').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const receiptId = this.getAttribute('data-receipt-id');
                    deleteExistingReceipt(receiptId);
                    this.closest('.preview-receipt-item').remove();
                });
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function deleteExistingReceipt(receiptId) {
    if (!confirm('この領収書を削除してもよろしいですか？')) {
        return;
    }
    
    deletedReceiptIds.push(receiptId);
}

function submitExpense() {
    const expenseDate = document.getElementById('expenseDateInput').value;
    const amount = document.getElementById('amountInput').value;
    const typeId = document.getElementById('expenseTypeSelect').value;
    const paymentMethodId = document.getElementById('paymentMethodSelect').value;
    const agencyFlag = document.getElementById('agencyFlagCheckbox').checked;
    const remark = document.getElementById('remarkInput').value;
    
    if (!expenseDate) {
        alert('経費日付を入力してください');
        return;
    }
    if (!typeId) {
        alert('経費種別を選択してください');
        return;
    }
    if (!paymentMethodId) {
        alert('支払方法を選択してください');
        return;
    }
    if (!amount || parseFloat(amount) <= 0) {
        alert('金額を入力してください');
        return;
    }
    
    const url = currentEditingId 
        ? `/driver/advance-payment/${currentEditingId}`
        : '/driver/advance-payment';
    const method = currentEditingId ? 'PUT' : 'POST';
    
    const formData = new FormData();
    formData.append('itinerary_id', currentItineraryId);
    formData.append('bus_assignment_id', currentBusAssignmentId);
    formData.append('expense_date', expenseDate);
    formData.append('amount', parseFloat(amount));
    formData.append('type_id', parseInt(typeId));
    formData.append('payment_method_id', parseInt(paymentMethodId));
    formData.append('agency_flag', agencyFlag ? 1 : 0);
    formData.append('remark', remark);
    formData.append('_method', method === 'PUT' ? 'PUT' : 'POST');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    pendingFiles.forEach((file, index) => {
        formData.append(`receipts[${index}]`, file);
    });
    
    deletedReceiptIds.forEach((id, index) => {
        formData.append(`deleted_receipt_ids[${index}]`, id);
    });
    
    const btn = document.getElementById('confirmBtn');
    const originalText = btn.textContent;
    btn.textContent = '送信中...';
    btn.disabled = true;
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert(data.message || 'エラーが発生しました');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました');
    })
    .finally(() => {
        btn.textContent = originalText;
        btn.disabled = false;
    });
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('preview-receipt-delete')) {
        const filename = e.target.getAttribute('data-filename');
        pendingFiles = pendingFiles.filter(f => f.name !== filename);
        e.target.closest('.preview-receipt-item')?.remove();
    }
});

function viewReceiptImage(imageUrl) {
    let modal = document.getElementById('imageViewModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'imageViewModal';
        modal.className = 'image-modal';
        modal.innerHTML = `
            <button class="image-modal-close" onclick="closeImageViewModal()">×</button>
            <img id="imageViewImg" src="">
        `;
        document.body.appendChild(modal);
    }
    const img = document.getElementById('imageViewImg');
    img.src = imageUrl;
    modal.classList.add('show');
}

function closeImageViewModal() {
    const modal = document.getElementById('imageViewModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

document.getElementById('backBtn')?.addEventListener('click', function() {
    window.history.back();
});

document.getElementById('cancelBtn')?.addEventListener('click', function() {
    window.history.back();
});

document.getElementById('addExpenseBtn')?.addEventListener('click', function() {
    openCreateModal();
});

document.getElementById('confirmBtn')?.addEventListener('click', function() {
    submitExpense();
});

document.getElementById('cancelModalBtn')?.addEventListener('click', function() {
    closeModal();
});

document.getElementById('deleteExpenseBtn')?.addEventListener('click', function() {
    if (!currentEditingId) return;
    if (!confirm('この立替を削除してもよろしいですか？')) {
        return;
    }
    
    const btn = document.getElementById('deleteExpenseBtn');
    const originalText = btn.textContent;
    btn.textContent = '削除中...';
    btn.disabled = true;
    
    fetch(`/driver/advance-payment/${currentEditingId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
        } else {
            alert(data.message || '削除に失敗しました');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました');
    })
    .finally(() => {
        btn.textContent = originalText;
        btn.disabled = false;
    });
});

document.getElementById('expenseModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.addEventListener('click', function(e) {
    const modal = document.getElementById('imageViewModal');
    if (modal && modal.classList.contains('show') && e.target === modal) {
        closeImageViewModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    bindExpenseItemClickEvents();
});
</script>
@endpush