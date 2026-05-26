@extends('layouts.app')

@section('title', '運行日報編集')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 page-title">運行日報編集</h5>
        <div>
            <a href="{{ route('masters.daily-reports.export-pdf', $report->id) }}" class="btn btn-sm btn-outline-danger me-2" target="_blank">
                <i class="bi bi-file-pdf"></i> PDF导出
            </a>
            <a href="{{ route('masters.daily-reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>
            
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

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

    <div class="card shadow-sm card-edit">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('masters.daily-reports.update', $report->id) }}" id="dailyReportForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">日付</label>
                        <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($report->date)->format('Y年m月d日') }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">天気</label>
                        <select name="weather" class="form-select">
                            <option value="">-- 選択 --</option>
                            <option value="晴れ" {{ old('weather', $report->weather) == '晴れ' ? 'selected' : '' }}>晴れ</option>
                            <option value="曇り" {{ old('weather', $report->weather) == '曇り' ? 'selected' : '' }}>曇り</option>
                            <option value="雨" {{ old('weather', $report->weather) == '雨' ? 'selected' : '' }}>雨</option>
                            <option value="雪" {{ old('weather', $report->weather) == '雪' ? 'selected' : '' }}>雪</option>
                            <option value="霧" {{ old('weather', $report->weather) == '霧' ? 'selected' : '' }}>霧</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">運転手</label>
                        <input type="text" class="form-control bg-light" value="{{ $report->driver->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">車両</label>
                        <input type="text" class="form-control bg-light" value="{{ $report->vehicle->registration_number ?? '-' }}" readonly>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="start_work_time" class="form-label">始業時刻</label>
                        <input type="time" name="start_work_time" id="start_work_time" class="form-control @error('start_work_time') is-invalid @enderror" 
                               value="{{ old('start_work_time', $report->start_work_time ? \Carbon\Carbon::parse($report->start_work_time)->format('H:i') : '') }}">
                        @error('start_work_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="end_work_time" class="form-label">終業時刻</label>
                        <input type="time" name="end_work_time" id="end_work_time" class="form-control @error('end_work_time') is-invalid @enderror" 
                               value="{{ old('end_work_time', $report->end_work_time ? \Carbon\Carbon::parse($report->end_work_time)->format('H:i') : '') }}">
                        @error('end_work_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <div class="col-md-3">
                        <label for="start_time" class="form-label">出庫時間</label>
                        <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                               value="{{ old('start_time', $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '') }}">
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
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
                
                    <div class="col-md-3">
                        <label for="end_time" class="form-label">帰庫時間</label>
                        <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                               value="{{ old('end_time', $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '') }}">
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label class="form-label">走行距離</label>
                        <div class="input-group">
                            <input type="text" id="distance" class="form-control bg-light" readonly>
                            <span class="input-group-text">km</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="actual_distance" class="form-label">実車距離</label>
                        <div class="input-group">
                            <input type="number" name="actual_distance" id="actual_distance" class="form-control @error('actual_distance') is-invalid @enderror" 
                                   value="{{ old('actual_distance', $report->actual_distance) }}" min="0" step="1">
                            <span class="input-group-text">km</span>
                        </div>
                        @error('actual_distance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="empty_distance" class="form-label">空車距離</label>
                        <div class="input-group">
                            <input type="number" name="empty_distance" id="empty_distance" class="form-control @error('empty_distance') is-invalid @enderror" 
                                   value="{{ old('empty_distance', $report->empty_distance) }}" min="0" step="1">
                            <span class="input-group-text">km</span>
                        </div>
                        @error('empty_distance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-12">
                        <label for="remark" class="form-label">備考</label>
                        <textarea name="remark" id="remark" class="form-control @error('remark') is-invalid @enderror" rows="3">{{ old('remark', $report->remark) }}</textarea>
                        @error('remark')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                
                
                <ul class="nav nav-tabs mb-3" id="reportTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="operation-tab" data-bs-toggle="tab" data-bs-target="#operation" type="button" role="tab" aria-controls="operation" aria-selected="true">
                            運行
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inspection-tab" data-bs-toggle="tab" data-bs-target="#inspection" type="button" role="tab" aria-controls="inspection" aria-selected="false">
                            点検
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="reportTabContent">
                    <div class="tab-pane fade show active" id="operation" role="tabpanel">
                        @foreach($itineraries as $itineraryIndex => $itinerary)
                        <div class="card mb-3" style="background-color: #f8f9fa;">
                            <div class="card-header" style="background-color: #e9ecef;">
                                <strong>行程 {{ $itineraryIndex + 1 }}</strong>
                                <span class="ms-3 text-muted">
                                    {{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }} {{ $itinerary->start_location ?? '?' }} - {{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }} {{ $itinerary->end_location ?? '?' }}
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="expense-section">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0 logs-table" style="font-size: 0.8rem;" data-itinerary-index="{{ $itineraryIndex }}">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 15%;">時間</th>
                                                    <th style="width: 10%;">走行距離</th>
                                                    <th style="width: 40%;">住所</th>
                                                    <th style="width: 15%;">操作</th>
                                                    <th style="width: 10%; text-align: center;">行操作</th>
                                                </tr>
                                            </thead>
                                            <tbody class="logs-tbody">
                                                @php
                                                    $logIndex = 0;
                                                @endphp
                                                @foreach($itinerary->operationLogs as $log)
                                                <tr class="log-row" data-log-id="{{ $log->id }}" data-index="{{ $logIndex }}">
                                                    <td>
                                                        <input type="time" class="form-control form-control-sm log-time-input" name="logs[{{ $itineraryIndex }}][{{ $logIndex }}][logged_at]" value="{{ \Carbon\Carbon::parse($log->logged_at)->format('H:i') }}" style="font-size: 0.75rem;">
                                                        <input type="hidden" name="logs[{{ $itineraryIndex }}][{{ $logIndex }}][id]" value="{{ $log->id }}">
                                                        <input type="hidden" name="logs[{{ $itineraryIndex }}][{{ $logIndex }}][display_order]" value="{{ $logIndex }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm log-mileage-input" name="logs[{{ $itineraryIndex }}][{{ $logIndex }}][mileage]" value="{{ $log->mileage }}" min="0" style="font-size: 0.75rem;">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm log-address-input" name="logs[{{ $itineraryIndex }}][{{ $logIndex }}][address]" value="{{ $log->address ?? '' }}" style="font-size: 0.75rem;">
                                                    </td>
                                                    <td>
                                                        <select class="form-select form-select-sm log-action-select" name="logs[{{ $itineraryIndex }}][{{ $logIndex }}][action]" style="font-size: 0.75rem;">
                                                            @foreach($operationTypes as $type)
                                                            <option value="{{ $type->name }}" {{ $log->action == $type->name ? 'selected' : '' }}>
                                                                {{ $type->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <button type="button" class="btn btn-outline-success btn-sm add-log-btn" title="行を追加">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm delete-log-btn" title="行を削除">
                                                                <i class="bi bi-dash-lg"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @php $logIndex++; @endphp
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                @php
                                    $itineraryExpenses = $expensesByItinerary[$itinerary->id] ?? [];
                                    if($itineraryExpenses) {
                                @endphp
                                <div class="expense-section pt-0">
                                    <div class="table-responsive">
                                        <b>立替</b>
                                        <table class="table table-sm table-bordered expense-table" style="font-size: 0.75rem; background-color: #fff; margin-bottom: 0;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 12%;">日付</th>
                                                    <th style="width: 15%;">種別</th>
                                                    <th style="width: 10%;">金額</th>
                                                    <th style="width: 12%;">支払方法</th>
                                                    <th style="width: 8%;">代理店</th>
                                                    <th style="width: 33%;">備考</th>
                                                    <th style="width: 10%; text-align: center;">行操作</th>
                                                </tr>
                                            </thead>
                                            <tbody class="expense-tbody" data-itinerary-id="{{ $itinerary->id }}" data-itinerary-index="{{ $itineraryIndex }}" data-expense-date="{{ \Carbon\Carbon::parse($report->date)->format('Y-m-d') }}">
                                                @forelse($itineraryExpenses as $expenseIndex => $expense)
                                                <tr class="expense-row" data-expense-id="{{ $expense->id }}" data-expense-index="{{ $expenseIndex }}">
                                                    <td>
                                                        <input type="date" name="expenses[{{ $itinerary->id }}][{{ $expenseIndex }}][expense_date]" 
                                                               value="{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}" 
                                                               class="form-control form-control-sm expense-date-input" style="font-size: 0.7rem;">
                                                    </td>
                                                    <td>
                                                        <select name="expenses[{{ $itinerary->id }}][{{ $expenseIndex }}][type_id]" class="form-select form-select-sm expense-type-select" style="font-size: 0.7rem;">
                                                            <option value="">-- 選択 --</option>
                                                            @foreach($expenseTypes ?? [] as $type)
                                                            <option value="{{ $type->id }}" {{ $expense->type_id == $type->id ? 'selected' : '' }}>
                                                                {{ $type->type_name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="expenses[{{ $itinerary->id }}][{{ $expenseIndex }}][amount]" 
                                                               value="{{ $expense->amount }}" step="1" min="0"
                                                               class="form-control form-control-sm expense-amount-input" style="font-size: 0.7rem; text-align: right;">
                                                    </td>
                                                    <td>
                                                        <select name="expenses[{{ $itinerary->id }}][{{ $expenseIndex }}][payment_method_id]" class="form-select form-select-sm expense-payment-select" style="font-size: 0.7rem;">
                                                            <option value="">-- 選択 --</option>
                                                            @foreach($paymentMethods ?? [] as $method)
                                                            <option value="{{ $method->id }}" {{ $expense->payment_method_id == $method->id ? 'selected' : '' }}>
                                                                {{ $method->method_name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center" style="margin: 0;">
                                                            <input type="checkbox" name="expenses[{{ $itinerary->id }}][{{ $expenseIndex }}][agency_flag]" value="1" 
                                                                   class="form-check-input expense-agency-checkbox" id="agency_flag_{{ $expense->id }}"
                                                                   {{ $expense->agency_flag ? 'checked' : '' }}
                                                                   style="cursor: pointer;">
                                                            <label class="form-check-label ms-1 expense-agency-label" for="agency_flag_{{ $expense->id }}" style="font-size: 0.7rem; cursor: pointer;">代理店</label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="expenses[{{ $itinerary->id }}][{{ $expenseIndex }}][remark]" 
                                                               value="{{ $expense->remark }}" placeholder="備考"
                                                               class="form-control form-control-sm expense-remark-input" style="font-size: 0.7rem;">
                                                    </td>
                                                    <input type="hidden" name="expenses[{{ $itinerary->id }}][{{ $expenseIndex }}][id]" value="{{ $expense->id }}">
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <button type="button" class="btn btn-outline-success btn-sm add-expense-row-btn" title="行を追加" style="padding: 2px 6px;">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm delete-expense-row-btn" title="行を削除" style="padding: 2px 6px;">
                                                                <i class="bi bi-dash-lg"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr class="expense-row expense-empty-row">
                                                    <td colspan="7" class="text-center text-muted" style="padding: 20px;">
                                                        立替金データがありません。「+」ボタンをクリックして追加してください。
                                                     </td>
                                                 </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @php
                                    }
                                @endphp
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="tab-pane fade" id="inspection" role="tabpanel">
                        <div class="card mb-3" style="background-color: #fff;">
                            <div class="card-header" style="background-color: #e9ecef;">
                                <strong>車両点検</strong>
                                <span class="ms-3 text-muted">
                                    {{ \Carbon\Carbon::parse($report->date)->format('Y年m月d日') }}
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="check-items-container">
                                    @php
                                        $vehicleId = $report->vehicle_id;
                                        $date = $report->date;
                                        $checkItems = \App\Models\Driver\DriverVehicleCheckItems::where('is_active', true)
                                            ->orderBy('display_order')
                                            ->get()
                                            ->groupBy('category');
                                        $savedChecks = \App\Models\Driver\DriverVehicleCheck::where('driver_id', $report->driver_id)
                                            ->where('vehicle_id', $vehicleId)
                                            ->where('date', $date)
                                            ->get()
                                            ->keyBy('driver_vehicle_check_items_id');
                                    @endphp
                                    @foreach($checkItems as $category => $items)
                                    <div class="check-category">
                                        <div class="category-title">{{ $category }}</div>
                                        <div class="category-items">
                                            @foreach($items as $item)
                                            <div class="check-item" data-item-id="{{ $item->id }}">
                                                <div class="item-name">{{ $item->item_name }}</div>
                                                <div class="item-options">
                                                    <label class="radio-label">
                                                        <input type="radio" name="checks[{{ $item->id }}]" value="1" 
                                                            class="radio-input check-radio" 
                                                            data-item-id="{{ $item->id }}"
                                                            {{ ($savedChecks[$item->id]->is_ok ?? '') == 1 ? 'checked' : '' }}>
                                                        <span class="radio-text">正常</span>
                                                    </label>
                                                    <label class="radio-label">
                                                        <input type="radio" name="checks[{{ $item->id }}]" value="0" 
                                                            class="radio-input check-radio" 
                                                            data-item-id="{{ $item->id }}"
                                                            {{ ($savedChecks[$item->id]->is_ok ?? '') == 0 ? 'checked' : '' }}>
                                                        <span class="radio-text">異常</span>
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                    
                                    <div class="check-category">
                                        <div class="category-title">備考</div>
                                        <textarea name="check_remark" class="form-control mt-3" rows="4" style="font-size: 12px;">{{ $report->checkRemark->remark ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                
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
@endsection

@push('scripts')
<script>
const startMileageInput = document.getElementById('start_mileage');
const endMileageInput = document.getElementById('end_mileage');
const distanceInput = document.getElementById('distance');

function calculateDistance() {
    const start = parseInt(startMileageInput.value) || 0;
    const end = parseInt(endMileageInput.value) || 0;
    
    if (end >= start) {
        distanceInput.value = end - start;
    } else {
        distanceInput.value = '';
    }
}

startMileageInput.addEventListener('input', calculateDistance);
endMileageInput.addEventListener('input', calculateDistance);
calculateDistance();

function reindexRows(table) {
    const tbody = table.querySelector('.logs-tbody');
    const rows = tbody.querySelectorAll('.log-row');
    const itineraryIndex = table.getAttribute('data-itinerary-index');
    
    rows.forEach((row, newIndex) => {
        row.setAttribute('data-index', newIndex);
        
        const timeInput = row.querySelector('.log-time-input');
        const idInput = row.querySelector('input[name*="[id]"]');
        const mileageInput = row.querySelector('.log-mileage-input');
        const addressInput = row.querySelector('.log-address-input');
        const actionSelect = row.querySelector('.log-action-select');
        const displayOrderInput = row.querySelector('input[name*="[display_order]"]');
        
        if (timeInput) {
            timeInput.setAttribute('name', `logs[${itineraryIndex}][${newIndex}][logged_at]`);
        }
        if (idInput) {
            idInput.setAttribute('name', `logs[${itineraryIndex}][${newIndex}][id]`);
        }
        if (mileageInput) {
            mileageInput.setAttribute('name', `logs[${itineraryIndex}][${newIndex}][mileage]`);
        }
        if (addressInput) {
            addressInput.setAttribute('name', `logs[${itineraryIndex}][${newIndex}][address]`);
        }
        if (actionSelect) {
            actionSelect.setAttribute('name', `logs[${itineraryIndex}][${newIndex}][action]`);
        }
        if (displayOrderInput) {
            displayOrderInput.setAttribute('name', `logs[${itineraryIndex}][${newIndex}][display_order]`);
            displayOrderInput.value = newIndex;
        } else {
            const newDisplayOrder = document.createElement('input');
            newDisplayOrder.type = 'hidden';
            newDisplayOrder.name = `logs[${itineraryIndex}][${newIndex}][display_order]`;
            newDisplayOrder.value = newIndex;
            row.cells[0].appendChild(newDisplayOrder);
        }
    });
}

function addLogRow(button) {
    const row = button.closest('.log-row');
    const tbody = row.parentNode;
    const table = row.closest('.logs-table');
    const itineraryIndex = table.getAttribute('data-itinerary-index');
    
    const newRow = document.createElement('tr');
    newRow.className = 'log-row';
    newRow.setAttribute('data-log-id', '');
    newRow.setAttribute('data-index', '');
    
    const currentRowCount = tbody.querySelectorAll('.log-row').length;
    const newIndex = currentRowCount;
    
    newRow.innerHTML = `
        <tr>
            <input type="time" class="form-control form-control-sm log-time-input" name="logs[${itineraryIndex}][${newIndex}][logged_at]" style="font-size: 0.75rem;">
            <input type="hidden" name="logs[${itineraryIndex}][${newIndex}][id]" value="">
            <input type="hidden" name="logs[${itineraryIndex}][${newIndex}][display_order]" value="${newIndex}">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm log-mileage-input" name="logs[${itineraryIndex}][${newIndex}][mileage]" min="0" style="font-size: 0.75rem;">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm log-address-input" name="logs[${itineraryIndex}][${newIndex}][address]" style="font-size: 0.75rem;">
        </td>
        <td>
            <select class="form-select form-select-sm log-action-select" name="logs[${itineraryIndex}][${newIndex}][action]" style="font-size: 0.75rem;">
                @foreach($operationTypes as $type)
                <option value="{{ $type->name }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center">
            <div class="d-flex justify-content-center gap-1">
                <button type="button" class="btn btn-outline-success btn-sm add-log-btn" title="行を追加">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm delete-log-btn" title="行を削除">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </div>
        </td>
    `;
    
    const referenceRow = button.closest('.log-row');
    referenceRow.parentNode.insertBefore(newRow, referenceRow.nextSibling);
    
    const newAddBtn = newRow.querySelector('.add-log-btn');
    const newDeleteBtn = newRow.querySelector('.delete-log-btn');
    
    newAddBtn.addEventListener('click', function() { addLogRow(this); });
    newDeleteBtn.addEventListener('click', function() { deleteLogRow(this); });
    
    reindexRows(table);
}

function deleteLogRow(button) {
    const row = button.closest('.log-row');
    const tbody = row.parentNode;
    const table = row.closest('.logs-table');
    
    if (tbody.querySelectorAll('.log-row').length <= 1) {
        if (confirm('最後の行を削除しますか？')) {
            row.remove();
            reindexRows(table);
        }
    } else {
        row.remove();
        reindexRows(table);
    }
}

document.querySelectorAll('.logs-table').forEach(table => {
    const tbody = table.querySelector('.logs-tbody');
    const addButtons = tbody.querySelectorAll('.add-log-btn');
    const deleteButtons = tbody.querySelectorAll('.delete-log-btn');
    
    addButtons.forEach(btn => {
        btn.removeEventListener('click', btn._addHandler);
        btn._addHandler = function() { addLogRow(this); };
        btn.addEventListener('click', btn._addHandler);
    });
    
    deleteButtons.forEach(btn => {
        btn.removeEventListener('click', btn._deleteHandler);
        btn._deleteHandler = function() { deleteLogRow(this); };
        btn.addEventListener('click', btn._deleteHandler);
    });
});

function getDefaultExpenseDate(tbody) {
    return tbody.getAttribute('data-expense-date') || new Date().toISOString().split('T')[0];
}

function generateExpenseIndex() {
    return Date.now() + '_' + Math.random().toString(36).substr(2, 8);
}

function addExpenseRow(button) {
    const currentRow = button.closest('.expense-row');
    const tbody = currentRow.closest('.expense-tbody');
    const itineraryId = tbody.getAttribute('data-itinerary-id');
    const itineraryIndex = tbody.getAttribute('data-itinerary-index');
    const defaultDate = getDefaultExpenseDate(tbody);
    const newIndex = generateExpenseIndex();
    
    const emptyRow = tbody.querySelector('.expense-empty-row');
    if (emptyRow) {
        emptyRow.remove();
    }
    
    const newRowHtml = `
        <tr class="expense-row" data-expense-id="" data-expense-index="${newIndex}">
            <td>
                <input type="date" name="expenses[${itineraryId}][${newIndex}][expense_date]" 
                       value="${defaultDate}"
                       class="form-control form-control-sm expense-date-input" style="font-size: 0.7rem;">
            </td>
            <td>
                <select name="expenses[${itineraryId}][${newIndex}][type_id]" class="form-select form-select-sm expense-type-select" style="font-size: 0.7rem;">
                    <option value="">-- 選択 --</option>
                    @foreach($expenseTypes ?? [] as $type)
                    <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="expenses[${itineraryId}][${newIndex}][amount]" value="0" step="1" min="0"
                       class="form-control form-control-sm expense-amount-input" style="font-size: 0.7rem; text-align: right;">
            </td>
            <td>
                <select name="expenses[${itineraryId}][${newIndex}][payment_method_id]" class="form-select form-select-sm expense-payment-select" style="font-size: 0.7rem;">
                    <option value="">-- 選択 --</option>
                    @foreach($paymentMethods ?? [] as $method)
                    <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center">
                <div class="form-check d-flex justify-content-center" style="margin: 0;">
                    <input type="checkbox" name="expenses[${itineraryId}][${newIndex}][agency_flag]" value="1" 
                           class="form-check-input expense-agency-checkbox" id="agency_flag_${newIndex}"
                           style="cursor: pointer;">
                    <label class="form-check-label ms-1 expense-agency-label" for="agency_flag_${newIndex}" style="font-size: 0.7rem; cursor: pointer;">代理店</label>
                </div>
            </td>
            <td>
                <input type="text" name="expenses[${itineraryId}][${newIndex}][remark]" placeholder="備考"
                       class="form-control form-control-sm expense-remark-input" style="font-size: 0.7rem;">
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-outline-success btn-sm add-expense-row-btn" title="行を追加" style="padding: 2px 6px;">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm delete-expense-row-btn" title="行を削除" style="padding: 2px 6px;">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </td>
        </table>
    `;
    
    currentRow.insertAdjacentHTML('afterend', newRowHtml);
    const newRow = currentRow.nextElementSibling;
    bindExpenseRowEvents(newRow);
}

function deleteExpenseRow(button) {
    const row = button.closest('.expense-row');
    const tbody = row.closest('.expense-tbody');
    
    const expenseId = row.getAttribute('data-expense-id');
    if (expenseId && expenseId !== '') {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'deleted_expense_ids[]';
        input.value = expenseId;
        document.getElementById('dailyReportForm').appendChild(input);
    }
    
    row.remove();
    
    const remainingRows = tbody.querySelectorAll('.expense-row:not(.expense-empty-row)');
    if (remainingRows.length === 0) {
        const itineraryId = tbody.getAttribute('data-itinerary-id');
        const itineraryIndex = tbody.getAttribute('data-itinerary-index');
        const defaultDate = getDefaultExpenseDate(tbody);
        
        tbody.innerHTML = `
            <tr class="expense-row expense-empty-row">
                <td colspan="7" class="text-center text-muted" style="padding: 20px;">
                    立替金データがありません。「+」ボタンをクリックして追加してください。
                 </td>
             </tr>
        `;
        
        const emptyRow = tbody.querySelector('.expense-empty-row');
        const addBtnInEmptyRow = emptyRow.querySelector('.add-expense-row-btn');
        if (addBtnInEmptyRow) {
            const newAddBtn = addBtnInEmptyRow.cloneNode(true);
            addBtnInEmptyRow.parentNode.replaceChild(newAddBtn, addBtnInEmptyRow);
            newAddBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                tbody.innerHTML = '';
                const newIndex = generateExpenseIndex();
                const newRowHtml = `
                    <tr class="expense-row" data-expense-id="" data-expense-index="${newIndex}">
                        <td>
                            <input type="date" name="expenses[${itineraryId}][${newIndex}][expense_date]" 
                                   value="${defaultDate}"
                                   class="form-control form-control-sm expense-date-input" style="font-size: 0.7rem;">
                        </td>
                        <td>
                            <select name="expenses[${itineraryId}][${newIndex}][type_id]" class="form-select form-select-sm expense-type-select" style="font-size: 0.7rem;">
                                <option value="">-- 選択 --</option>
                                @foreach($expenseTypes ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="expenses[${itineraryId}][${newIndex}][amount]" value="0" step="1" min="0"
                                   class="form-control form-control-sm expense-amount-input" style="font-size: 0.7rem; text-align: right;">
                        </td>
                        <td>
                            <select name="expenses[${itineraryId}][${newIndex}][payment_method_id]" class="form-select form-select-sm expense-payment-select" style="font-size: 0.7rem;">
                                <option value="">-- 選択 --</option>
                                @foreach($paymentMethods ?? [] as $method)
                                <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center" style="margin: 0;">
                                <input type="checkbox" name="expenses[${itineraryId}][${newIndex}][agency_flag]" value="1" 
                                       class="form-check-input expense-agency-checkbox" id="agency_flag_${newIndex}"
                                       style="cursor: pointer;">
                                <label class="form-check-label ms-1 expense-agency-label" for="agency_flag_${newIndex}" style="font-size: 0.7rem; cursor: pointer;">代理店</label>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="expenses[${itineraryId}][${newIndex}][remark]" placeholder="備考"
                                   class="form-control form-control-sm expense-remark-input" style="font-size: 0.7rem;">
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-outline-success btn-sm add-expense-row-btn" title="行を追加" style="padding: 2px 6px;">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm delete-expense-row-btn" title="行を削除" style="padding: 2px 6px;">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', newRowHtml);
                bindExpenseRowEvents(tbody.querySelector('.expense-row'));
            });
        }
    }
}

function bindExpenseRowEvents(row) {
    if (row.classList && row.classList.contains('expense-empty-row')) {
        const addBtn = row.querySelector('.add-expense-row-btn');
        if (addBtn) {
            const newAddBtn = addBtn.cloneNode(true);
            addBtn.parentNode.replaceChild(newAddBtn, addBtn);
            newAddBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const tbody = row.closest('.expense-tbody');
                const itineraryId = tbody.getAttribute('data-itinerary-id');
                const itineraryIndex = tbody.getAttribute('data-itinerary-index');
                const defaultDate = getDefaultExpenseDate(tbody);
                const newIndex = generateExpenseIndex();
                
                tbody.innerHTML = '';
                const newRowHtml = `
                    <tr class="expense-row" data-expense-id="" data-expense-index="${newIndex}">
                        <td>
                            <input type="date" name="expenses[${itineraryId}][${newIndex}][expense_date]" 
                                   value="${defaultDate}"
                                   class="form-control form-control-sm expense-date-input" style="font-size: 0.7rem;">
                        </td>
                        <td>
                            <select name="expenses[${itineraryId}][${newIndex}][type_id]" class="form-select form-select-sm expense-type-select" style="font-size: 0.7rem;">
                                <option value="">-- 選択 --</option>
                                @foreach($expenseTypes ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="expenses[${itineraryId}][${newIndex}][amount]" value="0" step="1" min="0"
                                   class="form-control form-control-sm expense-amount-input" style="font-size: 0.7rem; text-align: right;">
                        </td>
                        <td>
                            <select name="expenses[${itineraryId}][${newIndex}][payment_method_id]" class="form-select form-select-sm expense-payment-select" style="font-size: 0.7rem;">
                                <option value="">-- 選択 --</option>
                                @foreach($paymentMethods ?? [] as $method)
                                <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center" style="margin: 0;">
                                <input type="checkbox" name="expenses[${itineraryId}][${newIndex}][agency_flag]" value="1" 
                                       class="form-check-input expense-agency-checkbox" id="agency_flag_${newIndex}"
                                       style="cursor: pointer;">
                                <label class="form-check-label ms-1 expense-agency-label" for="agency_flag_${newIndex}" style="font-size: 0.7rem; cursor: pointer;">代理店</label>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="expenses[${itineraryId}][${newIndex}][remark]" placeholder="備考"
                                   class="form-control form-control-sm expense-remark-input" style="font-size: 0.7rem;">
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-outline-success btn-sm add-expense-row-btn" title="行を追加" style="padding: 2px 6px;">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm delete-expense-row-btn" title="行を削除" style="padding: 2px 6px;">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', newRowHtml);
                bindExpenseRowEvents(tbody.querySelector('.expense-row'));
            });
        }
        return;
    }
    
    const addBtn = row.querySelector('.add-expense-row-btn');
    const deleteBtn = row.querySelector('.delete-expense-row-btn');
    
    if (addBtn) {
        const newAddBtn = addBtn.cloneNode(true);
        addBtn.parentNode.replaceChild(newAddBtn, addBtn);
        newAddBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            addExpenseRow(this);
        });
    }
    
    if (deleteBtn) {
        const newDeleteBtn = deleteBtn.cloneNode(true);
        deleteBtn.parentNode.replaceChild(newDeleteBtn, deleteBtn);
        newDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            deleteExpenseRow(this);
        });
    }
    
    const agencyCheckbox = row.querySelector('.expense-agency-checkbox');
    const agencyLabel = row.querySelector('.expense-agency-label');
    if (agencyCheckbox && agencyLabel) {
        const newAgencyCheckbox = agencyCheckbox.cloneNode(true);
        agencyCheckbox.parentNode.replaceChild(newAgencyCheckbox, agencyCheckbox);
        
        const newAgencyLabel = agencyLabel.cloneNode(true);
        agencyLabel.parentNode.replaceChild(newAgencyLabel, agencyLabel);
        
        newAgencyLabel.addEventListener('click', function(e) {
            e.preventDefault();
            const cb = document.getElementById(this.getAttribute('for'));
            if (cb) {
                cb.checked = !cb.checked;
            }
        });
    }
}

document.querySelectorAll('.expense-tbody').forEach(tbody => {
    if (!tbody.getAttribute('data-expense-date')) {
        const defaultDate = new Date().toISOString().split('T')[0];
        tbody.setAttribute('data-expense-date', defaultDate);
    }
    
    const rows = tbody.querySelectorAll('.expense-row');
    rows.forEach(row => {
        bindExpenseRowEvents(row);
    });
});
</script>
@endpush

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

.table td, .table th {
    vertical-align: middle;
}

.expense-section {
    padding: 12px 16px;
    background-color: #fff;
}

.card.mb-3 {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

.card-header {
    background-color: #e9ecef;
    padding: 10px 16px;
    border-bottom: 1px solid #dee2e6;
}

.card-header strong {
    font-size: 14px;
    color: #2c3e50;
}

.card-header .text-muted {
    font-size: 12px;
}

.card-header .nav-tabs {
    margin-bottom: -1px;
    border-bottom: none;
}

.card-header .nav-link {
    padding: 0.25rem 0.75rem;
    color: #6c757d;
    border: none;
}

.card-header .nav-link.active {
    color: #2563eb;
    background-color: transparent;
    border-bottom: 2px solid #2563eb;
}

.nav-item {
    margin-bottom: 0;
}

.check-items-container {
    padding: 8px 12px;
}

.check-category {
    margin-bottom: 12px;
    background-color: #f8f9fa;
    border-radius: 6px;
    overflow: hidden;
}

.category-title {
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    background-color: #e9ecef;
    border-bottom: 1px solid #dee2e6;
}

.category-items {
    padding: 4px 0;
}

.check-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 12px;
    border-bottom: 1px solid #e9ecef;
}

.check-item:last-child {
    border-bottom: none;
}

.item-name {
    font-size: 12px;
    color: #2c3e50;
}

.item-options {
    display: flex;
    gap: 12px;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}

.radio-input {
    width: 14px;
    height: 14px;
    cursor: pointer;
    margin: 0;
}

.radio-text {
    font-size: 11px;
    color: #495057;
}

.logs-table .log-row td {
    padding: 4px;
}

.logs-table input,
.logs-table select {
    font-size: 0.75rem;
    padding: 4px 6px;
}

@media (max-width: 768px) {
    .info-item {
        margin-bottom: 12px;
    }
    
    .check-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .item-options {
        width: 100%;
        justify-content: flex-start;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .logs-table {
        font-size: 0.7rem;
    }
    
    .logs-table input,
    .logs-table select {
        font-size: 0.65rem;
        padding: 2px 4px;
    }
}
</style>
@endpush