@extends('layouts.app')

@section('title', '运行日报編集')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 page-title">运行日报編集</h5>
        <div>
            <a href="{{ route('masters.daily-reports.export-pdf', $report->id) }}" class="btn btn-sm btn-outline-danger me-2" target="_blank">
                <i class="bi bi-file-pdf"></i> PDF导出
            </a>
            <a href="{{ route('masters.daily-reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3 error-alert" role="alert">
        <h6 class="alert-heading mb-1">
            <i class="bi bi-exclamation-triangle"></i> 入力エラーがあります
        </h6>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('masters.daily-reports.update', $report->id) }}">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">日付</label>
                        <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($report->date)->format('Y年m月d日') }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">運転手</label>
                        <input type="text" class="form-control bg-light" value="{{ $report->driver->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">車両</label>
                        <input type="text" class="form-control bg-light" value="{{ $report->vehicle->registration_number ?? '-' }}" readonly>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="start_time" class="form-label">出庫時間</label>
                        <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                               value="{{ old('start_time', $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '') }}">
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="start_mileage" class="form-label">出庫時メーター</label>
                        <div class="input-group">
                            <input type="number" name="start_mileage" id="start_mileage" class="form-control @error('start_mileage') is-invalid @enderror" 
                                   value="{{ old('start_mileage', $report->start_mileage) }}" min="0" step="1">
                            <span class="input-group-text">km</span>
                        </div>
                        @error('start_mileage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="end_time" class="form-label">帰庫時間</label>
                        <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                               value="{{ old('end_time', $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '') }}">
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="end_mileage" class="form-label">帰庫時メーター</label>
                        <div class="input-group">
                            <input type="number" name="end_mileage" id="end_mileage" class="form-control @error('end_mileage') is-invalid @enderror" 
                                   value="{{ old('end_mileage', $report->end_mileage) }}" min="0" step="1">
                            <span class="input-group-text">km</span>
                        </div>
                        @error('end_mileage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">走行距離</label>
                        <div class="input-group">
                            <input type="text" id="distance" class="form-control bg-light" readonly>
                            <span class="input-group-text">km</span>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   name="allow_edit" 
                                   id="allowEditSwitch" 
                                   value="1" 
                                   style="width: 40px; height: 20px; cursor: pointer;"
                                   {{ old('allow_edit', $report->allow_edit ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="allowEditSwitch" style="font-weight: 500;">
                                ドライバー編集を許可する
                            </label>
                        </div>
                    </div>
                </div>
                
                @foreach($itineraries as $itineraryIndex => $itinerary)
                <div class="card mb-3" style="background-color: #f8f9fa;">
                    <div class="card-header" style="background-color: #e9ecef;">
                        <strong>行程 {{ $itineraryIndex + 1 }}</strong>
                        <span class="ms-3 text-muted">
                            {{ $itinerary->start_location ?? '?' }} → {{ $itinerary->end_location ?? '?' }}
                        </span>
                        <span class="ms-3 text-muted">
                            {{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%;">時間</th>
                                        <th style="width: 10%;">走行距離</th>
                                        <th style="width: 45%;">住所</th>
                                        <th style="width: 15%;">操作</th>
                                        <th style="width: 15%;">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($itinerary->operationLogs as $log)
                                    <tr class="log-row" data-log-id="{{ $log->id }}">
                                        <td>
                                            <input type="time" class="form-control form-control-sm log-time-input" value="{{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}" style="font-size: 0.75rem;">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm log-mileage-input" value="{{ $log->mileage }}" min="0" style="font-size: 0.75rem;">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm log-address-input" value="{{ $log->address ?? '' }}" style="font-size: 0.75rem;">
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm log-action-select" style="font-size: 0.75rem;">
                                                @foreach($operationTypes as $type)
                                                <option value="{{ $type->name }}" {{ $log->action == $type->name ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary save-log-btn" data-log-id="{{ $log->id }}">
                                                <i class="bi bi-save"></i> 保存
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            操作ログはありません
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="info-bar">
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-item">
                                        <i class="bi bi-person-circle info-icon"></i>
                                        <div>
                                            <div class="info-label">作成者</div>
                                            <div class="info-value">{{ $report->creator ? $report->creator->name : '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-item">
                                        <i class="bi bi-calendar-plus info-icon"></i>
                                        <div>
                                            <div class="info-label">作成日時</div>
                                            <div class="info-value">{{ $report->created_at ? \Carbon\Carbon::parse($report->created_at)->format('Y/m/d H:i:s') : '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-item">
                                        <i class="bi bi-person-check info-icon"></i>
                                        <div>
                                            <div class="info-label">更新者</div>
                                            <div class="info-value">{{ $report->updater ? $report->updater->name : '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-item">
                                        <i class="bi bi-calendar-check info-icon"></i>
                                        <div>
                                            <div class="info-label">更新日時</div>
                                            <div class="info-value">{{ $report->updated_at ? \Carbon\Carbon::parse($report->updated_at)->format('Y/m/d H:i:s') : '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-check-circle"></i> 保存
                        </button>
                        <a href="{{ route('masters.daily-reports.index') }}" class="btn btn-outline-secondary btn-sm px-4 ms-2">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const startMileageInput = document.getElementById('start_mileage');
    const endMileageInput = document.getElementById('end_mileage');
    const distanceInput = document.getElementById('distance');
    
    function calculateDistance() {
        const start = parseInt(startMileageInput.value) || 0;
        const end = parseInt(endMileageInput.value) || 0;
        
        if (start > 0 && end > 0 && end >= start) {
            distanceInput.value = end - start;
        } else {
            distanceInput.value = '';
        }
    }
    
    startMileageInput.addEventListener('input', calculateDistance);
    endMileageInput.addEventListener('input', calculateDistance);
    calculateDistance();
    
    document.querySelectorAll('.save-log-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const logId = this.getAttribute('data-log-id');
            const dailyReportId = {{ $report->id }};
            const row = this.closest('tr');
            const timeInput = row.querySelector('.log-time-input');
            const mileageInput = row.querySelector('.log-mileage-input');
            const addressInput = row.querySelector('.log-address-input');
            const actionSelect = row.querySelector('.log-action-select');
            
            let loggedAt = null;
            if (timeInput && timeInput.value) {
                const dateStr = @json($report->date);
                let dateOnly = dateStr;
                if (dateStr.includes('T')) {
                    dateOnly = dateStr.split('T')[0];
                } else if (dateStr.includes(' ')) {
                    dateOnly = dateStr.split(' ')[0];
                }
                let timeValue = timeInput.value;
                if (timeValue.includes(' ')) {
                    const parts = timeValue.split(' ');
                    timeValue = parts[parts.length - 1];
                }
                loggedAt = `${dateOnly} ${timeValue}:00`;
            }
            
            const mileage = mileageInput ? parseInt(mileageInput.value) : null;
            const address = addressInput ? addressInput.value : '';
            const action = actionSelect ? actionSelect.value : '';
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            btn.disabled = true;
            
            fetch(`/masters/daily-reports/operation-log/${logId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    daily_report_id: dailyReportId,
                    logged_at: loggedAt,
                    mileage: mileage,
                    address: address,
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('保存しました');
                    location.reload(); 
                    // btn.innerHTML = '<i class="bi bi-check-circle"></i> 保存';
                    // setTimeout(() => {
                    //     btn.innerHTML = '<i class="bi bi-save"></i> 保存';
                    //     btn.disabled = false;
                    // }, 1000);
                } else {
                    alert('更新に失敗しました: ' + (data.message || '不明なエラー'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('エラーが発生しました: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    });
</script>
@endsection

@push('styles')
<style>
.page-title {
    color: #374151;
    font-size: 1rem;
}
.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}
.error-alert {
    font-size: 0.875rem;
}
.bg-light {
    background-color: #f8f9fa !important;
}

.info-bar {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px 20px;
    border: 1px solid #e9ecef;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-icon {
    font-size: 1.25rem;
    color: #6c757d;
}

.info-label {
    font-size: 0.7rem;
    color: #6c757d;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 0.85rem;
    font-weight: 600;
    color: #2c3e50;
}

@media (max-width: 768px) {
    .info-item {
        margin-bottom: 12px;
    }
}

.table td, .table th {
    vertical-align: middle;
}
</style>
@endpush