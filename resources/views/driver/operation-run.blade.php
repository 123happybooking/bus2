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

        <div class="vehicle-selector" style="display:none;">
            <span class="vehicle-label">車両</span>
            <select id="vehicleSelect" class="vehicle-select">
                @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" {{ $defaultVehicleId == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->registration_number }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="action-buttons">
            @foreach($operationButtons as $button)
            <button class="action-btn" 
                    data-action="{{ $button->name }}" 
                    data-description="{{ $button->description }}">
                {{ $button->name }}
            </button>
            @endforeach
        </div>

        <div class="logs-container">
            <div class="logs-header">
                <span class="col-time">時間</span>
                <span class="col-mileage">走行距離</span>
                <span class="col-action">操作</span>
            </div>
            <div class="logs-list" id="logsList">
                @foreach($logs as $log)
                <div class="log-item" data-id="{{ $log->id }}" data-action="{{ $log->action }}" data-mileage="{{ $log->mileage }}" data-status="{{ $log->status }}" data-address="{{ $log->address }}" data-date="{{ \Carbon\Carbon::parse($log->logged_at)->format('Y/m/d') }}" data-time="{{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}">
                    <span class="col-time">
                        {{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}
                    </span>
                    <span class="col-mileage">
                        @if($log->mileage)
                            {{ $log->mileage }} KM<br>
                        @else
                            {{ $log->mileage ?? '' }}
                        @endif
                        {{ $log->address ?? '' }}
                    </span>
                    <span class="col-action">{{ $log->action }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="action-input-modal" id="actionInputModal">
    <div class="modal-content">
        <h4 id="actionModalTitle">情報入力</h4>
        <div class="edit-field">
            <label>住所</label>
            <input type="text" id="actionAddressInput" placeholder="住所" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 12px; font-size: 14px; background-color: var(--bg-color); color: var(--text-primary); margin-bottom: 16px;">
        </div>
        <div class="edit-field">
            <label>走行距離 (km)</label>
            <input type="number" id="actionMileageInput" placeholder="走行距離" min="0" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 12px; font-size: 14px; background-color: var(--bg-color); color: var(--text-primary); margin-bottom: 16px;">
        </div>
        <div class="modal-buttons">
            <button class="modal-confirm" id="actionConfirmBtn">確認</button>
            <button class="modal-cancel" id="actionCancelBtn">キャンセル</button>
        </div>
    </div>
</div>

<div class="edit-log-modal" id="editLogModal">
    <div class="modal-content">
        <h4>ログを編集</h4>
        <div class="edit-field">
            <label>時間</label>
            <input type="time" id="editTimeInput" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 12px; font-size: 14px; background-color: var(--bg-color); color: var(--text-primary); margin-bottom: 16px;">
        </div>
        <div class="edit-field">
            <label>住所</label>
            <input type="text" id="editAddressInput" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 12px; font-size: 14px; background-color: var(--bg-color); color: var(--text-primary); margin-bottom: 16px;">
        </div>
        <div class="edit-field">
            <label>走行距離 (km)</label>
            <input type="number" id="editMileageInput" placeholder="走行距離" min="0" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 12px; font-size: 14px; background-color: var(--bg-color); color: var(--text-primary); margin-bottom: 16px;">
        </div>
        <div class="edit-field">
            <label>操作</label>
            <select id="editActionSelect" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 12px; font-size: 14px; background-color: var(--bg-color); color: var(--text-primary); margin-bottom: 16px;">
                @foreach($operationButtons as $button)
                <option value="{{ $button->name }}">{{ $button->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="modal-buttons" style="display: flex; gap: 12px; justify-content: space-between;">
            <button class="modal-delete" id="editDeleteBtn" style="flex: 1; padding: 10px; border: none; border-radius: 12px; font-size: 14px; cursor: pointer; background-color: #dc2626; color: white;">削除</button>
            <button class="modal-confirm" id="editConfirmBtn" style="flex: 1; padding: 10px; border: none; border-radius: 12px; font-size: 14px; cursor: pointer; background-color: var(--accent-color); color: var(--accent-text);">更新</button>
            <button class="modal-cancel" id="cancelEditModalBtn" style="flex: 1; padding: 10px; border: none; border-radius: 12px; font-size: 14px; cursor: pointer; background-color: var(--bg-color); color: var(--text-secondary); border: 1px solid var(--border-color);">キャンセル</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
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

.vehicle-selector {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 12px 16px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.vehicle-label {
    font-size: 14px;
    color: var(--text-secondary);
    font-weight: 500;
}

.vehicle-select {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    font-size: 14px;
    background-color: var(--bg-color);
    color: var(--text-primary);
    cursor: pointer;
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

.log-item {
    display: flex;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    font-size: 12px;
    color: var(--text-primary);
    cursor: pointer;
    transition: background-color 0.2s;
}

.log-item:hover {
    background-color: var(--bg-color);
}

.log-item:last-child {
    border-bottom: none;
}

.col-time {
    width: 140px;
    flex-shrink: 0;
}

.col-mileage {
    width: 80px;
    flex-shrink: 0;
    text-align: center;
}

.col-action {
    flex: 1;
    text-align: right;
}

.action-input-modal, .edit-log-modal {
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

.action-input-modal.show, .edit-log-modal.show {
    visibility: visible;
    opacity: 1;
}

.modal-content {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 20px;
    width: 320px;
    text-align: center;
}

.modal-content h4 {
    font-size: 16px;
    margin-bottom: 16px;
    color: var(--text-primary);
}

.edit-field {
    margin-bottom: 16px;
    text-align: left;
}

.edit-field label {
    display: block;
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 8px;
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

.modal-delete {
    background-color: #dc2626;
    color: white;
}
</style>
@endpush

@push('scripts')
<script>
let googleMapsReady = false;
let pendingLocationResolvers = [];
const allowEdit = {{ $allowEdit ? 'true' : 'false' }};

window.onGoogleMapsReady = function() {
    googleMapsReady = true;
    console.log('✅ Google Maps API 加载完成');
    pendingLocationResolvers.forEach(resolver => resolver());
    pendingLocationResolvers = [];
};

function waitForGoogleMaps() {
    return new Promise((resolve) => {
        if (googleMapsReady && typeof google !== 'undefined' && google.maps) {
            resolve();
        } else {
            pendingLocationResolvers.push(resolve);
        }
    });
}

function cleanAddress(address) {
    if (!address) return '';
    let cleaned = address;
    cleaned = cleaned.replace(/[ ]*邮政编码[：:]\s*\d+/g, '');
    cleaned = cleaned.replace(/\s*[A-Z0-9]{3,7}\+[A-Z0-9]{3,5}\s*/gi, '');
    cleaned = cleaned.replace(/[\s,，]+$/, '');
    cleaned = cleaned.replace(/\s+/g, ' ');
    return cleaned.trim();
}

let currentPendingAction = null;
let currentItineraryId = {{ $itinerary->id }};
let currentEditingLogId = null;
let completedActions = new Set();

function getSelectedVehicleId() {
    return document.getElementById('vehicleSelect').value;
}

function markButtonCompleted(action) {
    const buttons = document.querySelectorAll('.action-btn');
    buttons.forEach(btn => {
        if (btn.getAttribute('data-action') === action) {
            btn.classList.add('completed');
        }
    });
}

function bindLogItemClickEvent(logItem) {
    logItem.style.cursor = 'pointer';
    logItem.addEventListener('click', function(e) {
        e.stopPropagation();
        openEditModal(this);
    });
}

async function getCurrentLocation() {
    await waitForGoogleMaps();
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject('お使いのブラウザは Geolocation に対応していません。');
            return;
        }
        const options = {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        };
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const geocoder = new google.maps.Geocoder();
                const latlng = { lat: lat, lng: lng };
                geocoder.geocode({ location: latlng }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        let rawAddress = results[0].formatted_address;
                        let cleanedAddress = cleanAddress(rawAddress);
                        resolve({
                            latitude: lat,
                            longitude: lng,
                            address: cleanedAddress
                        });
                    } else {
                        reject('住所の取得に失敗しました。');
                    }
                });
            },
            (error) => {
                let msg = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        msg = '位置情報の利用が許可されていません。';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        msg = '位置情報を取得できませんでした。';
                        break;
                    case error.TIMEOUT:
                        msg = '位置情報の取得がタイムアウトしました。';
                        break;
                    default:
                        msg = '不明なエラーが発生しました。';
                }
                reject(msg);
            },
            options
        );
    });
}

function submitLog(action, mileage, address) {
    const vehicleId = getSelectedVehicleId();
    const requestBody = {
        action: action,
        mileage: mileage,
        vehicle_id: vehicleId,
        address: address
    };
    
    return fetch(`/driver/operation/log/${currentItineraryId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(requestBody)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addLogToList(data.log);
            if (!completedActions.has(data.log.action)) {
                completedActions.add(data.log.action);
                markButtonCompleted(data.log.action);
            }
            const buttons = document.querySelectorAll('.action-btn');
            const isLastButton = buttons.length > 0 && document.querySelector(`.action-btn[data-action="${action}"]`) === buttons[buttons.length - 1];
            if (isLastButton) {
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
    logItem.setAttribute('data-id', log.id);
    logItem.setAttribute('data-action', log.action);
    logItem.setAttribute('data-mileage', log.mileage || '');
    logItem.setAttribute('data-status', log.status);
    logItem.setAttribute('data-address', log.address || '');
    logItem.setAttribute('data-time', log.time);
    
    let mileageHtml = '';
    if (log.mileage) {
        mileageHtml = `${log.mileage} KM<br>`;
    }
    const addressHtml = log.address || '';
    
    logItem.innerHTML = `
        <span class="col-time">
            ${log.time}
        </span>
        <span class="col-mileage">
            ${mileageHtml}${addressHtml}
        </span>
        <span class="col-action">${log.action}</span>
    `;
    
    bindLogItemClickEvent(logItem);
    logsList.insertBefore(logItem, logsList.firstChild);
    
    sortLogsList();
}

function updateLogInList(logId, newAction, newMileage, newAddress, newTime) {
    const logItem = document.querySelector(`.log-item[data-id="${logId}"]`);
    if (logItem) {
        logItem.setAttribute('data-action', newAction);
        logItem.setAttribute('data-mileage', newMileage || '');
        logItem.setAttribute('data-address', newAddress || '');
        logItem.setAttribute('data-time', newTime);
        
        const timeSpan = logItem.querySelector('.col-time');
        if (timeSpan) {
            timeSpan.innerHTML = newTime;
        }
        
        const mileageSpan = logItem.querySelector('.col-mileage');
        const actionSpan = logItem.querySelector('.col-action');
        
        if (mileageSpan) {
            const newMileageHtml = newMileage ? `${newMileage} KM<br>` : '';
            mileageSpan.innerHTML = newMileageHtml + (newAddress || '');
        }
        if (actionSpan) {
            actionSpan.textContent = newAction;
        }
        
        sortLogsList();
    }
}

function sortLogsList() {
    const logsList = document.getElementById('logsList');
    const items = Array.from(logsList.children);
    
    items.sort((a, b) => {
        const timeA = a.getAttribute('data-time') || '';
        const timeB = b.getAttribute('data-time') || '';
        return timeB.localeCompare(timeA);
    });
    
    items.forEach(item => logsList.appendChild(item));
}

function deleteLogFromList(logId, action) {
    const logItem = document.querySelector(`.log-item[data-id="${logId}"]`);
    if (logItem) {
        logItem.remove();
    }
    
    const remainingLogsWithSameAction = document.querySelectorAll(`.log-item[data-action="${action}"]`);
    if (remainingLogsWithSameAction.length === 0) {
        const button = document.querySelector(`.action-btn[data-action="${action}"]`);
        if (button) {
            button.classList.remove('completed');
        }
        completedActions.delete(action);
    }
    
    sortLogsList();
}

function openEditModal(logItem) {
    if (!allowEdit) {
        alert('この日報は編集できません。管理者にお問い合わせください。');
        return false;
    }
    
    currentEditingLogId = logItem.getAttribute('data-id');
    const logAction = logItem.getAttribute('data-action');
    const currentMileage = logItem.getAttribute('data-mileage');
    const currentAddress = logItem.getAttribute('data-address') || '';
    const currentTime = logItem.getAttribute('data-time') || '';
    
    document.getElementById('editActionSelect').value = logAction;
    document.getElementById('editMileageInput').value = currentMileage;
    document.getElementById('editAddressInput').value = currentAddress;
    document.getElementById('editTimeInput').value = currentTime;
    
    document.getElementById('editLogModal').classList.add('show');
}

document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!allowEdit) {
            alert('この日報は編集できません。管理者に連絡してください。');
            return;
        }
        
        const action = this.getAttribute('data-action');
        if (completedActions.has(action)) {
            return;
        }
        
        currentPendingAction = action;
        const modal = document.getElementById('actionInputModal');
        const modalTitle = document.getElementById('actionModalTitle');
        modalTitle.textContent = `${action} - 情報入力`;
        
        document.getElementById('actionMileageInput').value = '';
        document.getElementById('actionAddressInput').value = '';
        
        modal.classList.add('show');
        
        try {
            const location = await getCurrentLocation();
            if (location && location.address) {
                document.getElementById('actionAddressInput').value = location.address;
            }
        } catch (error) {
            console.error('位置情報取得エラー:', error);
        }
    });
});

document.getElementById('actionConfirmBtn').addEventListener('click', function() {
    const mileage = document.getElementById('actionMileageInput').value;
    const address = document.getElementById('actionAddressInput').value;
    
    if (!mileage) {
        alert('走行距離を入力してください。');
        return;
    }
    
    if (!address) {
        alert('住所を入力してください。');
        return;
    }
    
    if (currentPendingAction) {
        const btn = this;
        const originalText = btn.textContent;
        btn.textContent = '送信中...';
        btn.disabled = true;
        
        submitLog(currentPendingAction, parseInt(mileage), address)
            .then(() => {
                document.getElementById('actionInputModal').classList.remove('show');
                currentPendingAction = null;
            })
            .finally(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            });
    }
});

document.getElementById('actionCancelBtn').addEventListener('click', function() {
    document.getElementById('actionInputModal').classList.remove('show');
    currentPendingAction = null;
});

document.getElementById('editConfirmBtn').addEventListener('click', function() {
    const newAction = document.getElementById('editActionSelect').value;
    const newMileage = document.getElementById('editMileageInput').value;
    const newAddress = document.getElementById('editAddressInput').value;
    const newTime = document.getElementById('editTimeInput').value;
    
    if (!newMileage) {
        alert('走行距離を入力してください。');
        return;
    }
    
    if (!newAddress) {
        alert('住所を入力してください。');
        return;
    }
    
    const vehicleId = getSelectedVehicleId();
    const btn = this;
    const originalText = btn.textContent;
    btn.textContent = '更新中...';
    btn.disabled = true;
    
    fetch(`/driver/operation/log/${currentEditingLogId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: newAction,
            mileage: parseInt(newMileage),
            address: newAddress,
            time: newTime,
            vehicle_id: vehicleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateLogInList(currentEditingLogId, newAction, parseInt(newMileage), newAddress, data.log.time);
            document.getElementById('editLogModal').classList.remove('show');
            currentEditingLogId = null;
        } else {
            alert('更新に失敗しました');
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

document.getElementById('editDeleteBtn').addEventListener('click', function() {
    if (!confirm('このログを削除してもよろしいですか？')) {
        return;
    }
    
    const action = document.getElementById('editActionSelect').value;
    const btn = this;
    const originalText = btn.textContent;
    btn.textContent = '削除中...';
    btn.disabled = true;
    
    fetch(`/driver/operation/log/${currentEditingLogId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            deleteLogFromList(currentEditingLogId, action);
            document.getElementById('editLogModal').classList.remove('show');
            currentEditingLogId = null;
        } else {
            alert('削除に失敗しました');
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

document.getElementById('cancelEditModalBtn').addEventListener('click', function() {
    document.getElementById('editLogModal').classList.remove('show');
    currentEditingLogId = null;
});

document.getElementById('backBtn').addEventListener('click', function() {
    window.history.back();
});

document.addEventListener('DOMContentLoaded', function() {
    const existingLogItems = document.querySelectorAll('.log-item');
    existingLogItems.forEach(logItem => {
        bindLogItemClickEvent(logItem);
    });
    
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
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=geocoding&callback=onGoogleMapsReady" async defer></script>
@endpush