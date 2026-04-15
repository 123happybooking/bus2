@extends('layouts.driver')

@section('title', '運行操作')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">運行操作</div>
        <div class="header-right">
            <div style="width: 32px;"></div>
        </div>
    </div>

    <div class="operation-container">
        <div class="itinerary-info">
            <div class="info-row">
                <span class="info-label">予約ID</span>
                <span class="info-value">{{ $itinerary->busAssignment->groupInfo->id ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">予約名</span>
                <span class="info-value">{{ $itinerary->busAssignment->groupInfo->group_name ?? '' }}</span>
            </div>
        </div>

        <div class="action-buttons">
            <button class="action-btn" data-action="迎車">迎車</button>
            <button class="action-btn" data-action="到着">到着</button>
            <button class="action-btn" data-action="空車">空車</button>
            <button class="action-btn" data-action="下車">下車</button>
            <button class="action-btn" data-action="終了">終了</button>
        </div>

        <div class="logs-container">
            <div class="logs-header">
                <span class="col-time">時間</span>
                <span class="col-action">操作</span>
                <span class="col-mileage">走行距離</span>
                <span class="col-status">状態</span>
            </div>
            <div class="logs-list" id="logsList">
                @foreach($logs as $log)
                <div class="log-item">
                    <span class="col-time">{{ \Carbon\Carbon::parse($log->logged_at)->format('Y/m/d H:i:s') }}</span>
                    <span class="col-action">{{ $log->action }}</span>
                    <span class="col-mileage">{{ $log->mileage ?? '' }}</span>
                    <span class="col-status">{{ $log->status }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.operation-container {
    padding: 12px;
}

.itinerary-info {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 16px;
}

.info-row {
    display: flex;
    margin-bottom: 8px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    width: 80px;
    font-size: 14px;
    color: var(--text-secondary);
}

.info-value {
    flex: 1;
    font-size: 14px;
    color: var(--text-primary);
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.action-btn {
    flex: 1;
    min-width: 60px;
    padding: 12px 0;
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn.completed {
    background-color: var(--card-bg);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.action-btn:active {
    transform: scale(0.98);
}

.logs-container {
    background-color: var(--card-bg);
    border-radius: 16px;
    overflow: hidden;
}

.logs-header {
    display: flex;
    padding: 12px 16px;
    background-color: var(--card-bg);
    border-bottom: 1px solid var(--border-color);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
}

.logs-list {
    max-height: 400px;
    overflow-y: auto;
}

.log-item {
    display: flex;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    font-size: 12px;
    color: var(--text-primary);
}

.log-item:last-child {
    border-bottom: none;
}

.col-time {
    width: 140px;
    flex-shrink: 0;
}

.col-action {
    width: 60px;
    flex-shrink: 0;
}

.col-mileage {
    width: 60px;
    flex-shrink: 0;
    text-align: right;
}

.col-status {
    flex: 1;
    text-align: right;
}

.mileage-modal {
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

.mileage-modal.show {
    visibility: visible;
    opacity: 1;
}

.modal-content {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 20px;
    width: 280px;
    text-align: center;
}

.modal-content h4 {
    font-size: 16px;
    margin-bottom: 16px;
    color: var(--text-primary);
}

.modal-content input {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 14px;
    background-color: var(--bg-color);
    color: var(--text-primary);
    margin-bottom: 16px;
}

.modal-buttons {
    display: flex;
    gap: 12px;
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
</style>

<div class="mileage-modal" id="mileageModal">
    <div class="modal-content">
        <h4 id="modalTitle">走行距離を入力</h4>
        <input type="number" id="mileageInput" placeholder="走行距離 (km)" min="0">
        <div class="modal-buttons">
            <button class="modal-confirm" id="confirmBtn">確認</button>
            <button class="modal-cancel" id="cancelModalBtn">キャンセル</button>
        </div>
    </div>
</div>

<script>
let currentAction = null;
let currentItineraryId = {{ $itinerary->id }};
let completedActions = new Set();

function markButtonCompleted(action) {
    const buttons = document.querySelectorAll('.action-btn');
    buttons.forEach(btn => {
        if (btn.getAttribute('data-action') === action) {
            btn.classList.add('completed');
        }
    });
}

function addLog(action, mileage = null) {
    fetch(`/driver/operation/log/${currentItineraryId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: action,
            mileage: mileage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addLogToList(data.log);
            if (!completedActions.has(action)) {
                completedActions.add(action);
                markButtonCompleted(action);
            }
            if (action === '終了') {
                const date = '{{ \Carbon\Carbon::parse($itinerary->date)->format('Y-m-d') }}';
                setTimeout(() => {
                    window.location.href = `/driver/daily-itineraries/${date}`;
                }, 500);
            }
        } else {
            alert('エラーが発生しました');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました');
    });
}

function addLogToList(log) {
    const logsList = document.getElementById('logsList');
    const logItem = document.createElement('div');
    logItem.className = 'log-item';
    logItem.innerHTML = `
        <span class="col-time">${log.logged_at}</span>
        <span class="col-action">${log.action}</span>
        <span class="col-mileage">${log.mileage || ''}</span>
        <span class="col-status">${log.status}</span>
    `;
    logsList.insertBefore(logItem, logsList.firstChild);
}

document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.getAttribute('data-action');
        
        if (completedActions.has(action)) {
            return;
        }
        
        if (action === '到着' || action === '下車') {
            currentAction = action;
            const modal = document.getElementById('mileageModal');
            const modalTitle = document.getElementById('modalTitle');
            modalTitle.textContent = action === '到着' ? '到着時の走行距離を入力' : '下車時の走行距離を入力';
            document.getElementById('mileageInput').value = '';
            modal.classList.add('show');
        } else {
            addLog(action);
        }
    });
});

document.getElementById('confirmBtn').addEventListener('click', function() {
    const mileage = document.getElementById('mileageInput').value;
    if (currentAction && mileage) {
        addLog(currentAction, parseInt(mileage));
        document.getElementById('mileageModal').classList.remove('show');
        currentAction = null;
    }
});

document.getElementById('cancelModalBtn').addEventListener('click', function() {
    document.getElementById('mileageModal').classList.remove('show');
    currentAction = null;
});

document.getElementById('backBtn').addEventListener('click', function() {
    window.history.back();
});

document.addEventListener('DOMContentLoaded', function() {
    const existingLogs = document.querySelectorAll('.log-item .col-action');
    const uniqueActions = new Set();
    existingLogs.forEach(log => {
        const action = log.textContent;
        if (!uniqueActions.has(action)) {
            uniqueActions.add(action);
            completedActions.add(action);
            markButtonCompleted(action);
        }
    });
});
</script>
@endsection