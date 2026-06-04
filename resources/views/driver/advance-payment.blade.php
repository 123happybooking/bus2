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
        
        <div class="receipts-section" id="receiptsSection">
            <div class="receipts-header">
                <span class="receipts-title">📷 領収書</span>
            </div>
            <div class="receipts-list" id="receiptsList">
                <div class="no-receipts">読み込み中...</div>
            </div>
        </div>

        <div class="button-container">
            <button class="add-expense-btn" id="addExpenseBtn">+ 立替追加</button>
            <button class="upload-receipt-btn" id="uploadReceiptBtn">📷 領収書アップロード</button>
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
        <div class="modal-buttons" style="display: flex; gap: 12px; justify-content: space-between;">
            <button class="modal-delete" id="deleteExpenseBtn" style="display: none;">削除</button>
            <button class="modal-confirm" id="confirmBtn">確認</button>
            <button class="modal-cancel" id="cancelModalBtn">キャンセル</button>
        </div>
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

/* 收据区域样式 */
.receipts-section {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 16px;
}

.receipts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.receipts-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
}

.receipts-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.receipt-item {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    background-color: #f3f4f6;
    border: 1px solid var(--border-color);
}

.receipt-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    border-radius: 8px;
}

.receipt-delete {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background-color: #dc2626;
    color: white;
    border: none;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.no-receipts {
    color: var(--text-secondary);
    font-size: 12px;
    padding: 20px;
    text-align: center;
}

/* 图片预览模态框 */
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


</style>
@endpush

@push('scripts')
<script>
let currentItineraryId = {{ $itinerary->id }};
let currentBusAssignmentId = {{ $itinerary->bus_assignment_id ?? 'null' }};
let currentEditingId = null;
let currentDriverId = {{ session('driver_id') }};

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
    
    document.getElementById('expenseModal').classList.add('show');
}

function closeModal() {
    document.getElementById('expenseModal').classList.remove('show');
    currentEditingId = null;
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
    
    const btn = document.getElementById('confirmBtn');
    const originalText = btn.textContent;
    btn.textContent = '送信中...';
    btn.disabled = true;
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            itinerary_id: currentItineraryId,
            bus_assignment_id: currentBusAssignmentId,
            expense_date: expenseDate,
            amount: parseFloat(amount),
            type_id: parseInt(typeId),
            payment_method_id: parseInt(paymentMethodId),
            agency_flag: agencyFlag ? 1 : 0,
            remark: remark
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
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

function deleteExpense() {
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
            'Content-Type': 'application/json',
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
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
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
    deleteExpense();
});

document.getElementById('expenseModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    bindExpenseItemClickEvents();
});






function loadReceipts() {
    const receiptsList = document.getElementById('receiptsList');
    
    receiptsList.innerHTML = '<div class="no-receipts">読み込み中...</div>';
    
    fetch(`/driver/advance-payment/receipts/${currentItineraryId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.receipts && data.receipts.length > 0) {
            let html = '';
            data.receipts.forEach((receipt) => {
                html += `
                    <div class="receipt-item" data-receipt-id="${receipt.id}">
                        <img src="${receipt.url}" class="receipt-image" onclick="viewReceiptImage('${receipt.url}')">
                        <button class="receipt-delete" onclick="deleteReceipt(${receipt.id}, event)">×</button>
                    </div>
                `;
            });
            receiptsList.innerHTML = html;
        } else {
            receiptsList.innerHTML = '<div class="no-receipts">📷 領収書はありません</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        receiptsList.innerHTML = '<div class="no-receipts">読み込みエラー</div>';
    });
}

function uploadReceipt() {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/jpeg,image/png,image/jpg';
    fileInput.multiple = true;
    
    fileInput.onchange = function(e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;
        
        const uploadBtn = document.getElementById('uploadReceiptBtn');
        const originalText = uploadBtn.innerHTML;
        let uploadedCount = 0;
        const totalFiles = files.length;
        
        uploadBtn.innerHTML = `アップロード中... (0/${totalFiles})`;
        uploadBtn.disabled = true;
        
        Array.from(files).forEach((file) => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`ファイル ${file.name} は5MB以下にしてください`);
                uploadedCount++;
                if (uploadedCount === totalFiles) {
                    uploadBtn.innerHTML = originalText;
                    uploadBtn.disabled = false;
                }
                return;
            }
            
            const formData = new FormData();
            formData.append('receipt_image', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('itinerary_id', currentItineraryId);
            
            fetch('/driver/advance-payment/receipts', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                uploadedCount++;
                uploadBtn.innerHTML = `アップロード中... (${uploadedCount}/${totalFiles})`;
                
                if (uploadedCount === totalFiles) {
                    uploadBtn.innerHTML = originalText;
                    uploadBtn.disabled = false;
                    loadReceipts();
                }
                
                if (!data.success) {
                    alert(`${file.name}: ${data.message || 'アップロードに失敗しました'}`);
                }
            })
            .catch(error => {
                uploadedCount++;
                console.error('Error:', error);
                alert(`${file.name}: アップロードに失敗しました`);
                
                if (uploadedCount === totalFiles) {
                    uploadBtn.innerHTML = originalText;
                    uploadBtn.disabled = false;
                }
            });
        });
    };
    
    fileInput.click();
}

function deleteReceipt(receiptId, event) {
    event.stopPropagation();
    
    if (!confirm('この領収書を削除してもよろしいですか？')) {
        return;
    }
    
    fetch(`/driver/advance-payment/receipts/${receiptId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadReceipts();
        } else {
            alert(data.message || '削除に失敗しました');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('削除に失敗しました');
    });
}

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

document.addEventListener('click', function(e) {
    const modal = document.getElementById('imageViewModal');
    if (modal && modal.classList.contains('show') && e.target === modal) {
        closeImageViewModal();
    }
});

document.getElementById('uploadReceiptBtn')?.addEventListener('click', function() {
    uploadReceipt();
});

document.addEventListener('DOMContentLoaded', function() {
    loadReceipts();
});

</script>
@endpush