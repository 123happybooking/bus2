@extends('layouts.win')

@section('title', '運行割当編集')

@php
    $isDriverLedger = request()->input('source') === 'driver-ledger';
    $isLocked = $busAssignment->lock_arrangement == 1;
    
    $isRestricted = !$isDriverLedger && $isLocked;
    
    if ($isDriverLedger) {
        $readonlyAttr = 'readonly';
        $disabledAttr = 'disabled';
        $searchableReadonly = 'readonly';
        $searchableBgColor = 'background-color: #f9fafb;';
        $vehicleReadonly = 'readonly';
        $vehicleBgColor = 'background-color: #f9fafb;';
        $driverReadonly = '';
        $driverBgColor = '';
        $numberReadonly = 'readonly';
        $remarksReadonly = 'readonly';
        $checkboxDisabled = 'disabled';
    } elseif ($isRestricted) {
        $readonlyAttr = 'readonly';
        $disabledAttr = 'disabled';
        $searchableReadonly = 'readonly';
        $searchableBgColor = 'background-color: #f9fafb;';
        $vehicleReadonly = 'readonly';
        $vehicleBgColor = 'background-color: #f9fafb;';
        $driverReadonly = 'readonly';
        $driverBgColor = 'background-color: #f9fafb;';
        $numberReadonly = '';
        $remarksReadonly = '';
        $checkboxDisabled = '';
    } else {
        $readonlyAttr = '';
        $disabledAttr = '';
        $searchableReadonly = '';
        $searchableBgColor = '';
        $vehicleReadonly = '';
        $vehicleBgColor = '';
        $driverReadonly = 'readonly';
        $driverBgColor = 'background-color: #f9fafb;';
        $numberReadonly = '';
        $remarksReadonly = '';
        $checkboxDisabled = '';
    }
@endphp

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ route('masters.bus-assignments.update', $busAssignment->id) }}" id="editForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="iframe" value="1" id="isIframe">
        <input type="hidden" name="group_info_id" id="group_info_id" value="{{ $busAssignment->group_info_id }}">

        <div class="m-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <label for="status" class="label-text mr-2">車種指定</label>
                    <input type="checkbox" id="status" class="checkbox mr-5" name="vehicle_type_spec_check" value="1" {{ $busAssignment->vehicle_type_spec_check ? 'checked' : '' }} {{ $checkboxDisabled }}>
                </div>
                
                <div class="d-flex align-items-center mx-3">
                    <label for="reservation_status" class="label-text mr-2">予約状況</label>
                        <select id="reservation_status" class="form-input-small" name="reservation_status" {{ $disabledAttr }}>
                            <option value="予約" style="background-color: #ccf5ff; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '予約' ? 'selected' : '' }}>予約</option>
                            <option value="仮押さえ" style="background-color: #ffff99; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '仮押さえ' ? 'selected' : '' }}>仮押さえ</option>
                            <option value="見積" style="background-color: #ccffcc; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '見積' ? 'selected' : '' }}>見積</option>
                            <option value="危ない" style="background-color: #ffcccc; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '危ない' ? 'selected' : '' }}>危ない</option>
                            <option value="確定待ち" style="background-color: #ffd9b3; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '確定待ち' ? 'selected' : '' }}>確定待ち</option>
                            <option value="確定" style="background-color: #cbb87c; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '確定' ? 'selected' : '' }}>確定</option>
                            <option value="送信済" style="background-color: #e6e6fa; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '送信済' ? 'selected' : '' }}>送信済</option>
                            <option value="実績待ち" style="background-color: #e0b0ff; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '実績待ち' ? 'selected' : '' }}>実績待ち</option>
                            <option value="運行済" style="background-color: #c0c0c0; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '運行済' ? 'selected' : '' }}>運行済</option>
                            <option value="請求済" style="background-color: #b0e0e6; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '請求済' ? 'selected' : '' }}>請求済</option>
                            <option value="キャンセル" style="background-color: #d3d3d3; color: black;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == 'キャンセル' ? 'selected' : '' }}>キャンセル</option>
                            <option value="稼働不可" style="background-color: #2c2c2c; color: white;" {{ ($busAssignment->groupInfo->reservation_status ?? '') == '稼働不可' ? 'selected' : '' }}>稼働不可</option>
                        </select>
                </div>
                
                <div class="d-flex align-items-center">
                    <label for="category" class="label-text mr-2">業務分類</label>
                    <select id="category" name="reservation_categories_id" class="form-input" style="width: 100px;" {{ $disabledAttr }}>
                        <option value="">-- 選択 --</option>
                        @foreach($reservationCategories ?? [] as $category)
                            <option value="{{ $category->id }}" 
                                    {{ ($busAssignment->groupInfo->reservation_categories_id ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="info-cards d-flex gap-2 mb-2">
            <div class="info-card w-100" style="background-color: #e6f3ff; border: 1px solid #b8d9ff; border-radius: 6px; padding: 6px 12px;">
                <span class="info-label" style="font-size: 11px; color: #1e40af;">予約ID</span>
                <span class="info-value" style="font-size: 13px; font-weight: bold; margin-left: 8px;">
                    {{ $busAssignment->group_info_id }} 
                    @php
                        $busCount = \App\Models\Masters\BusAssignment::where('group_info_id', $busAssignment->group_info_id)->count();
                    @endphp
                    @if($busCount > 1)
                        [{{ $busCount }}]
                    @endif
                </span>
            </div>
        </div>

        <div class="card">
            <div class="d-flex align-items-center mb-1 position-relative">
                <div class="label-width text-gray">車両名</div>
                <div class="flex-1 position-relative">
                    <input type="text" name="vehicle_name_input" class="form-input" id="vehicle_search" 
                           value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->registration_number . ($busAssignment->vehicle->vehicleModel ? ' (' . $busAssignment->vehicle->vehicleModel->model_name . ')' : '') : '' }}" 
                           placeholder="車両名を入力" autocomplete="off"
                           {{ $vehicleReadonly }} style="{{ $vehicleBgColor }}">
                    <input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ $busAssignment->vehicle_id }}">
                    <div class="suggestions-container" id="vehicle_suggestions" style="display: none;"></div>
                </div>
            </div>

            <div class="d-flex mb-1">
                <div class="label-width text-gray">開始日</div>
                <div class="d-flex align-items-center" style="flex: 1;">
                    <input type="text" name="start_date" value="{{ $busAssignment->start_date ? \Carbon\Carbon::parse($busAssignment->start_date)->format('Y-m-d') : '' }}" class="form-input-small input-width-date" id="start_date" style="flex: 1; min-width: 0;" placeholder="YYYY-MM-DD" autocomplete="off" {{ $readonlyAttr }}>
                    <span class="mx-2">
                        <input type="time" name="start_time" value="{{ $busAssignment->start_time ? \Carbon\Carbon::parse($busAssignment->start_time)->format('H:i') : '08:00' }}" class="form-input-small input-width-time" step="60" style="width: 90px;" {{ $readonlyAttr }}>
                    </span>
                    <span class="label-text mx-2" style="margin-left:0 !important;">~</span>
                    <input type="text" name="end_date" value="{{ $busAssignment->end_date ? \Carbon\Carbon::parse($busAssignment->end_date)->format('Y-m-d') : '' }}" class="form-input-small input-width-date" id="end_date" style="flex: 1; min-width: 0;" placeholder="YYYY-MM-DD" autocomplete="off" {{ $readonlyAttr }}>
                    <span class="ms-2">
                        <input type="time" name="end_time" value="{{ $busAssignment->end_time ? \Carbon\Carbon::parse($busAssignment->end_time)->format('H:i') : '18:00' }}" class="form-input-small input-width-time" step="60" style="width: 90px;" {{ $readonlyAttr }}>
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center mb-1">
                <div class="label-width text-gray">号車</div>
                <div class="input-width-100 mr-4">
                    <input type="text" name="vehicle_number" value="{{ $busAssignment->vehicle_number ?? '' }}" class="form-input" id="vehicle_number" placeholder="号車" {{ $readonlyAttr }}>
                </div>
                
                <div class="label-width text-gray">ガイド</div>
                <div class="flex-1 position-relative">
                    <input type="text" name="guide_name_input" class="form-input" id="guide_search" 
                           value="{{ $busAssignment->guide ? $busAssignment->guide->name . ($busAssignment->guide->guide_code ? ' (' . $busAssignment->guide->guide_code . ')' : '') : '' }}" 
                           placeholder="ガイド名を入力" autocomplete="off"
                           {{ $vehicleReadonly }} style="{{ $vehicleBgColor }}">
                    <input type="hidden" name="guide_id" id="guide_id" value="{{ $busAssignment->guide_id }}">
                    <div class="suggestions-container" id="guide_suggestions" style="display: none;"></div>
                </div>
            </div>

            <div class="d-flex align-items-center mb-1">
                <div class="label-width text-gray">運転手</div>
                <div class="flex-1 position-relative">
                    <input type="text" name="driver_name_input" class="form-input search-input" id="driver_search" 
                           value="{{ $busAssignment->driver ? $busAssignment->driver->name . ($busAssignment->driver->driver_code ? ' (' . $busAssignment->driver->driver_code . ')' : '') : '' }}" 
                           placeholder="運転手名を入力" autocomplete="off"
                           {{ $driverReadonly }} style="{{ $driverBgColor }}">
                    <input type="hidden" name="driver_id" id="driver_id" value="{{ $busAssignment->driver_id }}">
                    <div class="suggestions-container" id="driver_suggestions" style="display: none;"></div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="info-card" style="background-color: #e6f3ff; border: 1px solid #b8d9ff; border-radius: 6px; padding: 6px 12px;">
                <span class="info-label" style="font-size: 11px; color: #1e40af;">運行ID</span>
                <span class="info-value" style="font-size: 13px; font-weight: bold; margin-left: 8px;">{{ $busAssignment->id }}</span>
            </div>
            <div class="d-flex gap-3 text-gray">
                <div class="d-flex align-items-center gap-1">
                    <input type="checkbox" class="label-text mr-1" id="lock_arrangement" name="lock_arrangement" value="1" 
                           {{ $busAssignment->lock_arrangement ? 'checked' : '' }} {{ $checkboxDisabled }}>
                    <label for="lock_arrangement" style="color: #9ca3af;">操作ロック</label>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <input type="checkbox" class="label-text mr-1" id="status_finalized" name="status_finalized"  value="1" 
                           {{ $busAssignment->status_finalized ? 'checked' : '' }} {{ $checkboxDisabled }}>
                    <label for="status_finalized" style="color: #9ca3af;">最終確認</label>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <input type="checkbox" class="label-text mr-1" id="status_sent" name="status_sent"  value="1" 
                           {{ $busAssignment->status_sent ? 'checked' : '' }} {{ $checkboxDisabled }}>
                    <label for="status_sent" style="color: #9ca3af;">送信</label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="tab-container">
                <div class="tab-wrapper">
                    <span class="tab-item active" data-tab="basic">基本</span>
                    <span class="tab-item inactive" data-tab="customer" style="margin-left: -1px;">顧客</span>
                    <span class="tab-item inactive" data-tab="vehicle" style="margin-left: -1px;">車両</span>
                    <span class="tab-item inactive" data-tab="history" style="margin-left: -1px;">履歴</span>
                    <span class="tab-item inactive" data-tab="copy" style="margin-left: -1px;">複製</span>
                </div>
                <div class="tab-line"></div>
            </div>

            <div id="tabContent" class="tab-content">
                <div class="tab-pane active" id="basic-tab">
                    <div class="d-flex align-items-center mb-1 position-relative">
                        <div class="label-width text-gray">代理店</div>
                        <div class="flex-1 position-relative">
                            <input type="text" name="agency" class="form-input" id="agency_search" 
                                   value="{{ $busAssignment->groupInfo->agency ?? '' }}" placeholder="代理店名を入力" autocomplete="off"
                                   {{ $vehicleReadonly }} style="{{ $vehicleBgColor }}">
                            <input type="hidden" name="agency_id" id="agency_id" value="{{ $busAssignment->groupInfo->agency_id ?? '' }}">
                            <input type="hidden" name="agency_code" id="agency_code" value="{{ $busAssignment->groupInfo->agency_code ?? '' }}">
                            <input type="hidden" name="agency_branch" id="agency_branch" value="{{ $busAssignment->groupInfo->agency_branch ?? '' }}">
                            <input type="hidden" name="agency_phone" id="agency_phone" value="{{ $busAssignment->groupInfo->agency_phone ?? '' }}">
                            <div class="suggestions-container" id="agency_suggestions" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-1">
                        <div class="label-width text-gray">大人</div>
                        <div class="input-width-number mr-4">
                            <input type="number" name="adult_count" id="adult_count" value="{{ $busAssignment->adult_count ?? 0 }}" class="form-input" min="0" {{ $numberReadonly }}>
                        </div>
                        <div class="label-width text-gray mr-2">小人</div>
                        <div class="input-width-number mr-4">
                            <input type="number" name="child_count" value="{{ $busAssignment->child_count ?? 0 }}" class="form-input" min="0" {{ $numberReadonly }}>
                        </div>
                        <div class="label-width text-gray mr-2">ガイド</div>
                        <div class="input-width-number mr-4">
                            <input type="number" name="guide_count" value="{{ $busAssignment->guide_count ?? 0 }}" class="form-input" min="0" {{ $numberReadonly }}>
                        </div>
                        <div class="label-width text-gray mr-2">その他</div>
                        <div class="input-width-number">
                            <input type="number" name="other_count" value="{{ $busAssignment->other_count ?? 0 }}" class="form-input" min="0" {{ $numberReadonly }}>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <div class="label-width text-gray">備考</div>
                        <div class="flex-1">
                            <textarea name="operation_remarks" rows="5" class="form-input" style="resize: vertical; height: auto;" {{ $remarksReadonly }}>{{ $busAssignment->operation_remarks ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="customer-tab" style="display: none;">
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">担当</div>
                        <div class="flex-1">
                            <input type="text" name="representative" value="{{ $busAssignment->representative ?? '' }}" class="form-input" id="representative" {{ $readonlyAttr }}>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">電話</div>
                        <div class="flex-1">
                            <input type="text" name="representative_phone" value="{{ $busAssignment->representative_phone ?? '' }}" class="form-input" id="representative_phone" {{ $readonlyAttr }}>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">AGT予約ID</div>
                        <div class="flex-1">
                            <input type="text" name="agt_tour_id" class="form-input" value="{{ $busAssignment->groupInfo->agt_tour_id ?? '' }}" {{ $readonlyAttr }}>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">荷物数</div>
                        <div class="flex-1">
                            <input type="number" name="luggage_count" value="{{ $busAssignment->luggage_count ?? 0 }}" class="form-input" min="0" {{ $readonlyAttr }}>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="vehicle-tab" style="display: none;">
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">車両分類</div>
                        <div class="flex-1">
                            <input type="text" name="vehicle_type" class="form-input" id="vehicle_type" value="{{ $busAssignment->vehicle->vehicleType->type_name ?? '' }}" {{ $readonlyAttr }} readonly>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">車種</div>
                        <div class="flex-1">
                            <input type="text" name="vehicle_model" class="form-input" id="vehicle_model" value="{{ $busAssignment->vehicle->vehicleModel->model_name ?? '' }}" {{ $readonlyAttr }} readonly>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">車両営業所</div>
                        <div class="flex-1">
                            <input type="text" name="vehicle_branch" class="form-input" id="vehicle_branch" value="{{ $busAssignment->vehicle->branch->branch_name ?? '' }}" {{ $readonlyAttr }} readonly>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="label-width-large text-gray">定員</div>
                        <div class="flex-1">
                            <input type="text" name="seating_capacity" class="form-input" id="seating_capacity" value="{{ $busAssignment->vehicle->seating_capacity ?? '' }}" {{ $readonlyAttr }} readonly>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="history-tab" style="display: none;">
                    @if(isset($logs) && $logs->count() > 0)
                        <div style="max-height: 150px; overflow-y: auto; font-size: 11px;">
                            <table class="table table-sm table-bordered" style="font-size: 11px; margin-bottom: 0;">
                                <thead style="background-color: #f3f4f6;">
                                    <tr>
                                        <th style="width: 8%; text-align: center;">#</th>
                                        <th>操作内容</th>
                                        <th style="width: 15%;">ユーザー</th>
                                        <th style="width: 25%;">操作日時</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $index => $log)
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle;">{{ $index + 1 }}</td>
                                            <td>{{ $log->action_description }}</td>
                                            <td>{{ $log->username ?? $log->user_id ?? 'system' }}</td>
                                            <td style="white-space: nowrap;">{{ $log->created_at ? $log->created_at->format('Y/m/d H:i:s') : '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="dashed-box" style="color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db;">
                            履歴はありません
                        </div>
                    @endif
                </div>

                <div class="tab-pane" id="copy-tab" style="display: none;">
                    <div class="dashed-box">
                        複製機能はこちら
                    </div>
                </div>
            </div>
        </div>

        <div class="m-2">
            <div class="d-flex gap-4">
                <div class="d-flex align-items-center">
                    <input type="checkbox" id="ignore_operation" class="checkbox-large" name="ignore_operation" value="1" {{ $busAssignment->ignore_operation ? 'checked' : '' }} {{ $disabledAttr }}>
                    <label for="ignore_operation" class="label-text" style="color: #9ca3af;">運行無視</label>
                </div>
                <div class="d-flex align-items-center">
                    <input type="checkbox" id="ignore_driver" class="checkbox-large" name="ignore_driver" value="1" {{ $busAssignment->ignore_driver ? 'checked' : '' }} {{ $disabledAttr }}>
                    <label for="ignore_driver" class="label-text" style="color: #9ca3af;">勤怠無視</label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex gap-2">
                <button type="button" class="btn-primary" id="detailBtn">運行詳細</button>
                <button type="submit" class="btn-primary" id="saveBtn">変更</button>
                <button type="button" class="btn-secondary" id="closeBtn">閉じる</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .text-small { color: #374151; font-size: 11px; }
    .text-gray { color: #6b7280; font-size: 11px; }
    .card { background-color: white; border: 1px solid #E5E7EB; border-radius: 6px; padding: 4px 8px; margin-bottom: 8px; }
    .form-input { width: 100%; border: 1px solid #E5E7EB; border-radius: 4px; font-size: 11px; padding: 4px 6px; height: 28px; }
    .form-input-small { border: 1px solid #E5E7EB; border-radius: 4px; padding: 4px; height: 28px; font-size: 11px; }
    .checkbox { width: 12px; height: 12px; margin-right: 2px; }
    .checkbox-large { width: 14px; height: 14px; margin-right: 4px; }
    .label-text { color: #374151; font-size: 11px; }
    .label-text-gray { color: #6b7280; font-size: 11px; }
    .tab-container { position: relative; }
    .tab-wrapper { display: flex; margin-bottom: -1px; }
    .tab-item { font-size: 11px; padding: 6px 16px; border-radius: 4px 4px 0 0; cursor: pointer; }
    .tab-item.active { background-color: white; color: #374151; border-top: 1px solid #d1d5db; border-left: 1px solid #d1d5db; border-right: 1px solid #d1d5db; border-bottom: none; z-index: 2; }
    .tab-item.inactive { background-color: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; border-bottom: 1px solid #d1d5db; }
    .tab-line { height: 1px; background-color: #d1d5db; width: 100%; margin-top: -1px; z-index: 1; }
    .tab-content { padding-top: 4px; }
    .btn-primary { background-color: #2563eb; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
    .btn-primary:hover { background-color: #1d4ed8; }
    .btn-primary:disabled { background-color: #93c5fd; cursor: not-allowed; }
    .btn-secondary { background-color: #6b7280; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
    .btn-secondary:hover { background-color: #4b5563; }
    .dashed-box { color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db; }
    .label-width { width: 50px; }
    .label-width-large { width: 60px; }
    .input-width-date { width: 120px; }
    .input-width-time { width: 90px; }
    .input-width-number { width: 60px; }
    .input-width-100 { width: 100px; }
    .mr-2 { margin-right: 8px; }
    .mr-4 { margin-right: 4px; }
    .mr-5 { margin-right: 8px; }
    .mx-2 { margin: 0 2px; }
    .mx-3 { margin: 0 4px; }
    .mb-1 { margin-bottom: 4px; }
    .mb-2 { margin-bottom: 8px; }
    .mt-2 { margin-top: 8px; }
    .mt-3 { margin-top: 12px; }
    .gap-2 { gap: 8px; }
    .gap-4 { gap: 16px; }
    .flex-1 { flex: 1; }
    .d-flex { display: flex; }
    .flex-wrap { flex-wrap: wrap; }
    .align-items-center { align-items: center; }
    .align-items-start { align-items: flex-start; }
    .justify-content-between { justify-content: space-between; }
    .position-relative { position: relative; }
    .info-cards { margin-bottom: 8px; }
    .info-card { display: inline-flex; align-items: center; }
    
    .suggestions-container {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .suggestion-item {
        padding: 6px 8px;
        cursor: pointer;
        font-size: 11px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .suggestion-item:hover {
        background-color: #f3f4f6;
    }
    
    .suggestion-item:last-child {
        border-bottom: none;
    }
    
    .warning-message { color: #f59e0b; font-size: 10px; margin-top: 2px; animation: fadeIn 0.3s ease; }
    
    #history-tab .table th,
    #history-tab .table td {
        padding: 6px 8px;
        vertical-align: middle;
    }
    
    #history-tab .table tbody tr:hover {
        background-color: #f3f4f6;
    }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    
    .is-invalid { border-color: #dc3545 !important; background-color: #fff8f8; }
    .is-invalid:focus { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25); }
    .error-message { color: #dc3545; font-size: 10px; margin-top: 2px; }
    .is-invalid { animation: shake 0.2s ease-in-out; }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-3px); } 75% { transform: translateX(3px); } }
    
    input[readonly] { background-color: #f9fafb; cursor: default; }
    input[readonly]:focus { outline: none; border-color: #E5E7EB; }

    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: 0.6;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator:hover,
    input[type="time"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isInIframe = window.self !== window.top;
    if (isInIframe) {
        document.getElementById('isIframe').value = '1';
    }

    const closeBtn = document.getElementById('closeBtn');
    const detailBtn = document.getElementById('detailBtn');
    const groupInfoId = document.getElementById('group_info_id').value;

    closeBtn.addEventListener('click', function() {
        if (isInIframe) {
            window.parent.postMessage('close-iframe', '*');
            
            setTimeout(function() {
                try {
                    window.parent.document.getElementById('iframeModal').style.display = 'none';
                } catch(e) {
                    window.close();
                }
            }, 100);
        } else {
            window.close();
            
            setTimeout(function() {
                window.location.href = '{{ route("masters.bus-assignments.index") }}';
            }, 100);
        }
    });

    detailBtn.addEventListener('click', function() {
        const editUrl = '{{ route("masters.group-infos.edit", ":id") }}'.replace(':id', groupInfoId);
        
        if (isInIframe) {
            window.open(editUrl, '_blank');
            
            setTimeout(function() {
                try {
                    window.parent.document.getElementById('iframeModal').style.display = 'none';
                } catch(e) {
                    window.parent.postMessage('close-iframe', '*');
                }
            }, 100);
        } else {
            window.location.href = editUrl;
        }
    });

    const tabs = document.querySelectorAll('.tab-item');
    const panes = document.querySelectorAll('.tab-pane');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => {
                t.classList.remove('active');
                t.classList.add('inactive');
            });
            
            this.classList.add('active');
            this.classList.remove('inactive');
            
            panes.forEach(pane => pane.style.display = 'none');
            document.getElementById(this.getAttribute('data-tab') + '-tab').style.display = 'block';
        });
    });

    const vehicles = @json($vehicles ?? []);
    const drivers = @json($drivers ?? []);
    const guides = @json($guides ?? []);
    const agencies = @json($agencies ?? []);
    const isDriverLedger = '{{ $isDriverLedger }}' === '1';
    const isRestricted = '{{ $isRestricted }}' === '1';

    function setupVehicleSearch() {
        const searchInput = document.getElementById('vehicle_search');
        const suggestionsDiv = document.getElementById('vehicle_suggestions');
        const hiddenId = document.getElementById('vehicle_id');
        
        if (!searchInput) return;
    
        function fetchVehicleDetails(vehicleId) {
            fetch(`/masters/vehicles/${vehicleId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.vehicle_type) document.getElementById('vehicle_type').value = data.vehicle_type;
                if (data.vehicle_model) document.getElementById('vehicle_model').value = data.vehicle_model;
                if (data.vehicle_branch) document.getElementById('vehicle_branch').value = data.vehicle_branch;
                if (data.seating_capacity) document.getElementById('seating_capacity').value = data.seating_capacity;
            })
            .catch(error => console.error('Error fetching vehicle details:', error));
        }
    
        function showSuggestions(query = '') {
            const filtered = vehicles.filter(item => {
                const display = `${item.registration_number} ${item.vehicle_model ? '(' + item.vehicle_model.model_name + ')' : ''}`;
                return display.toLowerCase().includes(query.toLowerCase());
            }).slice(0, 10);
    
            if (filtered.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }
    
            let html = '';
            filtered.forEach(item => {
                const display = `${item.registration_number} ${item.vehicle_model ? '(' + item.vehicle_model.model_name + ')' : ''}`;
                html += `<div class="suggestion-item" data-id="${item.id}" data-name="${item.registration_number}" data-model="${item.vehicle_model ? item.vehicle_model.model_name : ''}">${display}</div>`;
            });
    
            suggestionsDiv.innerHTML = html;
            suggestionsDiv.style.display = 'block';
        }
    
        searchInput.addEventListener('focus', function() {
            if (!searchInput.readOnly) {
                showSuggestions('');
            }
        });
    
        searchInput.addEventListener('input', function() {
            if (!searchInput.readOnly) {
                showSuggestions(this.value);
            }
        });
    
        suggestionsDiv.addEventListener('click', function(e) {
            const suggestion = e.target.closest('.suggestion-item');
            if (!suggestion) return;
    
            const id = suggestion.dataset.id;
            const name = suggestion.dataset.name;
            const model = suggestion.dataset.model;
            
            searchInput.value = `${name} ${model ? '(' + model + ')' : ''}`;
            hiddenId.value = id;
            suggestionsDiv.style.display = 'none';
            
            fetchVehicleDetails(id);
        });
    
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });
    
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                suggestionsDiv.style.display = 'none';
            }
        });
    }

    function setupGuideSearch() {
        const searchInput = document.getElementById('guide_search');
        const suggestionsDiv = document.getElementById('guide_suggestions');
        const hiddenId = document.getElementById('guide_id');
        
        if (!searchInput) return;

        function showSuggestions(query = '') {
            const filtered = guides.filter(item => {
                const display = `${item.name} ${item.guide_code ? '(' + item.guide_code + ')' : ''}`;
                return display.toLowerCase().includes(query.toLowerCase());
            }).slice(0, 10);

            if (filtered.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }

            let html = '';
            filtered.forEach(item => {
                const display = `${item.name} ${item.guide_code ? '(' + item.guide_code + ')' : ''}`;
                html += `<div class="suggestion-item" data-id="${item.id}" data-name="${item.name}" data-code="${item.guide_code || ''}">${display}</div>`;
            });

            suggestionsDiv.innerHTML = html;
            suggestionsDiv.style.display = 'block';
        }

        searchInput.addEventListener('focus', function() {
            if (!searchInput.readOnly) {
                showSuggestions('');
            }
        });

        searchInput.addEventListener('input', function() {
            if (!searchInput.readOnly) {
                showSuggestions(this.value);
            }
        });

        suggestionsDiv.addEventListener('click', function(e) {
            const suggestion = e.target.closest('.suggestion-item');
            if (!suggestion) return;

            const id = suggestion.dataset.id;
            const name = suggestion.dataset.name;
            const code = suggestion.dataset.code;
            
            searchInput.value = `${name} ${code ? '(' + code + ')' : ''}`;
            hiddenId.value = id;
            suggestionsDiv.style.display = 'none';
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                suggestionsDiv.style.display = 'none';
            }
        });
    }

    function setupAgencySearch() {
        const searchInput = document.getElementById('agency_search');
        const suggestionsDiv = document.getElementById('agency_suggestions');
        const hiddenId = document.getElementById('agency_id');
        const hiddenCode = document.getElementById('agency_code');
        const hiddenBranch = document.getElementById('agency_branch');
        const hiddenPhone = document.getElementById('agency_phone');
        
        if (!searchInput) return;
    
        function showSuggestions(query = '') {
            const filtered = agencies.filter(item => {
                const display = `${item.agency_name} ${item.branch_name ? '(' + item.branch_name + ')' : ''}`;
                return display.toLowerCase().includes(query.toLowerCase());
            }).slice(0, 10);
    
            if (filtered.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }
    
            let html = '';
            filtered.forEach(item => {
                const display = `${item.agency_name} ${item.branch_name ? '(' + item.branch_name + ')' : ''}`;
                html += `<div class="suggestion-item" data-id="${item.id}" data-name="${item.agency_name}" data-branch="${item.branch_name || ''}" data-code="${item.agency_code || ''}" data-phone="${item.phone_number || ''}">${display}</div>`;
            });
    
            suggestionsDiv.innerHTML = html;
            suggestionsDiv.style.display = 'block';
        }
    
        searchInput.addEventListener('focus', function() {
            if (!searchInput.readOnly) {
                showSuggestions('');
            }
        });
    
        searchInput.addEventListener('input', function() {
            if (!searchInput.readOnly) {
                showSuggestions(this.value);
            }
        });
    
        suggestionsDiv.addEventListener('click', function(e) {
            const suggestion = e.target.closest('.suggestion-item');
            if (!suggestion) return;
    
            const id = suggestion.dataset.id;
            const name = suggestion.dataset.name;
            const branch = suggestion.dataset.branch;
            const code = suggestion.dataset.code;
            const phone = suggestion.dataset.phone;
            
            searchInput.value = `${name} ${branch ? '(' + branch + ')' : ''}`;
            hiddenId.value = id;
            if (hiddenCode) hiddenCode.value = code;
            if (hiddenBranch) hiddenBranch.value = branch;
            if (hiddenPhone) hiddenPhone.value = phone;
            suggestionsDiv.style.display = 'none';
        });
    
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });
    
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                suggestionsDiv.style.display = 'none';
            }
        });
    }

    function setupDriverSearch() {
        const searchInput = document.getElementById('driver_search');
        const suggestionsDiv = document.getElementById('driver_suggestions');
        const hiddenId = document.getElementById('driver_id');
        
        if (!searchInput) return;

        function showSuggestions(query = '') {
            const filtered = drivers.filter(item => {
                const display = `${item.name} ${item.driver_code ? '(' + item.driver_code + ')' : ''}`;
                return display.toLowerCase().includes(query.toLowerCase());
            }).slice(0, 10);

            if (filtered.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }

            let html = '';
            filtered.forEach(item => {
                const display = `${item.name} ${item.driver_code ? '(' + item.driver_code + ')' : ''}`;
                html += `<div class="suggestion-item" data-id="${item.id}" data-name="${item.name}" data-code="${item.driver_code || ''}">${display}</div>`;
            });

            suggestionsDiv.innerHTML = html;
            suggestionsDiv.style.display = 'block';
        }

        searchInput.addEventListener('focus', function() {
            if (!searchInput.readOnly) {
                showSuggestions('');
            }
        });

        searchInput.addEventListener('input', function() {
            if (!searchInput.readOnly) {
                showSuggestions(this.value);
            }
        });

        suggestionsDiv.addEventListener('click', function(e) {
            const suggestion = e.target.closest('.suggestion-item');
            if (!suggestion) return;

            const id = suggestion.dataset.id;
            const name = suggestion.dataset.name;
            const code = suggestion.dataset.code;
            
            searchInput.value = `${name} ${code ? '(' + code + ')' : ''}`;
            hiddenId.value = id;
            suggestionsDiv.style.display = 'none';
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                suggestionsDiv.style.display = 'none';
            }
        });
    }

    if (isDriverLedger) {
        setupDriverSearch();
    } else if (isRestricted) {
    } else {
        setupVehicleSearch();
        setupGuideSearch();
        setupAgencySearch();
        setupDriverSearch();
        
        if (typeof initDateRangePicker === 'function') {
            initDateRangePicker('input[name="start_date"]', 'input[name="end_date"]');
        }
    }

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
    
        const formData = new FormData(this);
        formData.append('_method', 'PUT');
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = '保存中...';
        submitBtn.disabled = true;
    
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (window.parent) {
                    window.parent.postMessage({
                        action: 'close-iframe-and-reload'
                    }, '*');
                } else {
                    window.close();
                    window.location.href = '{{ route('masters.operation-ledger.index') }}';
                }
            } else {
                alert(data.message || '更新に失敗しました');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            if (error.errors) {
                const errorMessages = Object.values(error.errors).flat().join('\n');
                alert('入力内容に誤りがあります:\n' + errorMessages);
            } else {
                alert(error.message || 'エラーが発生しました');
            }
            
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>
@endpush