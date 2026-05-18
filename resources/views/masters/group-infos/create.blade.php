@extends('layouts.win')

@section('title', '新規予約作成')

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ route('masters.group-infos.store') }}" id="createForm">
        @csrf
        <input type="hidden" name="iframe" value="1" id="isIframe">

        <div class="card mt-2 mb-2">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center flex-shrink-0">
                    <label for="status" class="label-text mr-2">車両指定</label>
                    <input type="checkbox" id="status" class="checkbox mr-5" name="vehicle_selection" {{ old('vehicle_selection') ? 'checked' : '' }}>
                </div>
                
                <div class="d-flex align-items-center flex-shrink-0">
                    <label for="vehicle_grade_id" class="label-text mr-2">車輛等級</label>
                    <select name="vehicle_grade_id" id="vehicle_grade_id" class="form-input-small" style="width:80px;">
                        @foreach($vehicleGrades ?? [] as $grade)
                            <option value="{{ $grade->id }}" 
                                {{ (old('vehicle_grade_id') == $grade->id) || (empty(old('vehicle_grade_id')) && $grade->code == 'ST') ? 'selected' : '' }}>
                                {{ $grade->description }} ({{ $grade->grade_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="d-flex align-items-center flex-shrink-0">
                    <label for="yoyaku" class="label-text mr-2">予約状況</label>
                    <select id="yoyaku" class="form-input-small" name="reservation_status" style="width:80px;">
                        <option value="予約" style="background-color: #ccf5ff; color: black;" {{ old('reservation_status') == '予約' ? 'selected' : '' }} selected>予約</option>
                        <option value="仮押さえ" style="background-color: #ffff99; color: black;" {{ old('reservation_status') == '仮押さえ' ? 'selected' : '' }}>仮押さえ</option>
                        <option value="見積" style="background-color: #ccffcc; color: black;" {{ old('reservation_status') == '見積' ? 'selected' : '' }}>見積</option>
                        <option value="危ない" style="background-color: #ffcccc; color: black;" {{ old('reservation_status') == '危ない' ? 'selected' : '' }}>危ない</option>
                        <option value="確定待ち" style="background-color: #ffd9b3; color: black;" {{ old('reservation_status') == '確定待ち' ? 'selected' : '' }}>確定待ち</option>
                        <option value="確定" style="background-color: #cbb87c; color: black;" {{ old('reservation_status') == '確定' ? 'selected' : '' }}>確定</option>
                        <option value="送信済" style="background-color: #e6e6fa; color: black;" {{ old('reservation_status') == '送信済' ? 'selected' : '' }}>送信済</option>
                        <option value="実績待ち" style="background-color: #e0b0ff; color: black;" {{ old('reservation_status') == '実績待ち' ? 'selected' : '' }}>実績待ち</option>
                        <option value="運行済" style="background-color: #c0c0c0; color: black;" {{ old('reservation_status') == '運行済' ? 'selected' : '' }}>運行済</option>
                        <option value="請求済" style="background-color: #b0e0e6; color: black;" {{ old('reservation_status') == '請求済' ? 'selected' : '' }}>請求済</option>
                        <option value="キャンセル" style="background-color: #d3d3d3; color: black;" {{ old('reservation_status') == 'キャンセル' ? 'selected' : '' }}>キャンセル</option>
                        <option value="稼働不可" style="background-color: #2c2c2c; color: white;" {{ old('reservation_status') == '稼働不可' ? 'selected' : '' }}>稼働不可</option>
                    </select>
                </div>
                
                <div class="d-flex align-items-center flex-shrink-0">
                    <label for="category" class="label-text mr-2">業務分類</label>
                    <select id="category" name="reservation_categories_id" class="form-input" style="width: 80px;">
                        <option value="">-- 選択 --</option>
                        @foreach($reservationCategories ?? [] as $category)
                            <option value="{{ $category->id }}" 
                                    data-category-name="{{ $category->category_name }}"
                                    data-category-code="{{ $category->category_code }}"
                                    data-color-code="{{ $category->color_code }}"
                                    {{ old('reservation_categories_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="d-flex align-items-center mb-1 position-relative">
                <div class="label-width text-gray">車両名</div>
                <div class="flex-1 position-relative">
                    <input type="text" name="vehicle_name_input" class="form-input search-input" id="vehicle_search" 
                           value="{{ old('vehicle_name_input') }}" placeholder="車両名を入力" autocomplete="off">
                    <input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ old('vehicle_id') }}">
                    <div class="suggestions-container" id="vehicle_suggestions" style="display: none;"></div>
                </div>
            </div>

            <div class="d-flex mb-1">
                <div class="label-width text-gray">開始日</div>
                <div class="d-flex align-items-center flex-nowrap date-range-responsive" style="flex: 1; gap: 6px;">
                    <div class="flex-grow-1">
                        <input type="text" name="start_date" value="" class="form-input-small datepicker-3months w-100" id="start_date" placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>
                    <div class="flex-grow-1">
                        <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}" class="form-input-small w-100" step="60">
                    </div>
                    <div class="text-center">
                        <span class="label-text">~</span>
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="end_date" value="" class="form-input-small datepicker-3months w-100" id="end_date" placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>
                    <div class="flex-grow-1">
                        <input type="time" name="end_time" value="{{ old('end_time', '18:00') }}" class="form-input-small w-100" step="60">
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center mb-1">
                <div class="label-width text-gray">号車</div>
                <div class="input-width-100 mr-4">
                    <input type="text" name="vehicle_number" value="{{ old('vehicle_number', '1/1') }}" class="form-input" id="vehicle_number" placeholder="号車">
                </div>
                
                <div class="label-width text-gray text-center">ガイド</div>
                <div class="flex-1 position-relative">
                    <input type="text" name="guide_name_input" class="form-input search-input" id="guide_search" 
                           value="{{ old('guide_name_input') }}" placeholder="ガイド名を入力" autocomplete="off">
                    <input type="hidden" name="guide_id" id="guide_id" value="{{ old('guide_id') }}">
                    <div class="suggestions-container" id="guide_suggestions" style="display: none;"></div>
                </div>
            </div>

            <div class="d-flex align-items-center mb-1">
                <div class="label-width text-gray">運転手</div>
                <div class="flex-1 position-relative">
                    <input type="text" name="driver_name_input" class="form-input search-input" id="driver_search" 
                           value="{{ old('driver_name_input', $selectedDriverName ?? '') }}" placeholder="運転手名を入力" autocomplete="off">
                    <input type="hidden" name="driver_id" id="driver_id" value="{{ old('driver_id', $selectedDriverId ?? '') }}">
                    <div class="suggestions-container" id="driver_suggestions" style="display: none;"></div>
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
                            <input type="text" name="agency_name_input" class="form-input search-input" id="agency_search" 
                                   value="{{ old('agency_name_input') }}" placeholder="代理店名を入力" autocomplete="off">
                            <input type="hidden" name="agency_id" id="agency_id" value="{{ old('agency_id') }}">
                            <input type="hidden" name="agency_code" id="agency_code" value="{{ old('agency_code') }}">
                            <input type="hidden" name="agency_branch" id="agency_branch" value="{{ old('agency_branch') }}">
                            <input type="hidden" name="agency_phone" id="agency_phone" value="{{ old('agency_phone') }}">
                            <div class="suggestions-container" id="agency_suggestions" style="display: none;"></div>
                        </div>
                        
                        <div class="label-width text-gray text-center">团体名</div>
                        <div class="flex-1">
                            <input type="text" name="group_name" value="{{ old('group_name') }}" class="form-input" placeholder="团体名を入力">
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-1">
                        <div class="label-width text-gray">大人</div>
                        <div class="input-width-number mr-4" style="width: 30px;">
                            <input type="text" name="adult_count" id="adult_count" value="{{ old('adult_count', '') }}" class="form-input">
                        </div>
                        <div class="label-width text-gray mr-2 text-center" style="width: 30px;">小人</div>
                        <div class="input-width-number mr-4" style="width: 30px;">
                            <input type="text" name="child_count" value="{{ old('child_count', '') }}" class="form-input" min="0">
                        </div>
                        <div class="label-width text-gray mr-2 text-center" style="width: 30px;">荷物</div>
                        <div class="input-width-number flex-1">
                            <input type="text" name="luggage" value="{{ old('luggage') }}" class="form-input">
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <div class="label-width text-gray">備考</div>
                        <div class="flex-1">
                            <textarea name="remarks" rows="3" class="form-input" style="resize: vertical; height: auto;">{{ old('remarks') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <div class="label-width text-gray">オプション</div>
                        <div class="flex-1">
                            <div class="options-container" style="display: flex; flex-wrap: wrap; gap: 12px;">
                                @foreach($options ?? [] as $option)
                                <label class="d-flex align-items-center" style="cursor: pointer;">
                                    <input type="checkbox" name="options[]" value="{{ $option->id }}" 
                                           class="mr-1" style="margin-right: 4px;"
                                           {{ in_array($option->id, old('options', [])) ? 'checked' : '' }}>
                                    <span style="font-size: 11px;">{{ $option->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="customer-tab" style="display: none;">
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">担当</div>
                        <div class="flex-1">
                            <input type="text" name="agency_contact_name" value="{{ old('agency_contact_name') }}" class="form-input" id="agency_contact_name">
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">国籍</div>
                        <div class="flex-1">
                            <input type="text" name="agency_country" value="{{ old('agency_country') }}" class="form-input" id="agency_country">
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">AGT予約ID</div>
                        <div class="flex-1">
                            <input type="text" name="agt_tour_id" class="form-input" value="{{ old('agt_tour_id') }}">
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">荷物数</div>
                        <div class="flex-1">
                            <input type="number" name="luggage_count" value="{{ old('luggage_count', 0) }}" class="form-input" min="0">
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="vehicle-tab" style="display: none;">
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">車両分類</div>
                        <div class="flex-1">
                            <input type="text" name="vehicle_type" class="form-input" id="vehicle_type" readonly>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">車種</div>
                        <div class="flex-1">
                            <input type="text" name="vehicle_model" class="form-input" id="vehicle_model" readonly>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="label-width-large text-gray">車両営業所</div>
                        <div class="flex-1">
                            <input type="text" name="vehicle_branch" class="form-input" id="vehicle_branch" readonly>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="label-width-large text-gray">定員</div>
                        <div class="flex-1">
                            <input type="text" name="seating_capacity" class="form-input" id="seating_capacity" readonly>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="history-tab" style="display: none;">
                    <div class="dashed-box">
                        履歴はありません
                    </div>
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
                    <input type="checkbox" id="ignore_operation" class="checkbox-large" name="ignore_operation" {{ old('ignore_operation') ? 'checked' : '' }}>
                    <label for="ignore_operation" class="label-text">運行無視</label>
                </div>
                <div class="d-flex align-items-center">
                    <input type="checkbox" id="ignore_attendance" class="checkbox-large" name="ignore_attendance" {{ old('ignore_attendance') ? 'checked' : '' }}>
                    <label for="ignore_attendance" class="label-text">勤怠無視</label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-1 mb-2">
            <div class="d-flex gap-2">
                <button type="submit" class="btn-primary" id="saveBtn">作成</button>
                <button type="button" class="btn-secondary" id="detailBtn" style="display: none;">詳細</button>
            </div>
            <button type="button" id="cancelBtn" class="btn-danger">取消</button>
        </div>

        <input type="hidden" name="reservation_status" value="" id="bookingStatus">
        <input type="hidden" name="vehicle_type_selection" value="" id="vehicleSpec">
        <input type="hidden" name="itinerary_id" value="0">
        <input type="hidden" name="saved_id" id="saved_id" value="">
    </form>
</div>
@endsection


@push('styles')
<style>
    body,.container-fluid { background-color: #ddd !important;}
    .text-small { color: #374151; font-size: 11px; }
    .text-gray { color: #6b7280; font-size: 11px; }
    .card { background-color: white; border: 1px solid #E5E7EB; border-radius: 6px; padding: 4px 8px; margin-bottom: 8px; }
    .form-input { width: 100%; border: 1px solid #bbb; border-radius: 4px; font-size: 11px; padding: 4px 6px; height: 28px; }
    .form-input-small { border: 1px solid #bbb; border-radius: 4px; padding: 4px; height: 28px; font-size: 11px; }
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
    .btn-secondary { background-color: #186718; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
    .btn-secondary:hover { background-color: #125112; }
    .btn-danger { background-color: #dc2626; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
    .btn-danger:hover { background-color: #b91c1c; }
    .dashed-box { color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db; }
    .label-width { width: 50px; }
    .label-width-large { width: 60px; }
    .label-width-copy { width: 90px; }
    .input-width-date { width: 120px; }
    .input-width-time { width: 90px; }
    .input-width-number { width: 60px; }
    .input-width-100 { width: 100px; }
    .input-width-40 { width: 40px; }
    .mr-2 { margin-right: 8px; }
    .mr-4 { margin-right: 4px; }
    .mr-5 { margin-right: 8px; }
    .ml-4 { margin-left: 4px; }
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
    .date-container { display: flex; align-items: center; flex-wrap: wrap; gap: 2px; }
    .position-relative { position: relative; }
    
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
    
    .vehicle-selected { border-color: #2563eb; background-color: #f0f7ff; }
    .warning-message { color: #f59e0b; font-size: 10px; margin-top: 2px; animation: fadeIn 0.3s ease; }

    
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
    
    
@media (max-width: 500px) {
    .date-range-responsive {
        flex-wrap: wrap !important;
    }
    
    .date-range-responsive > div {
        flex: 0 0 calc(50% - 3px) !important;
        margin-bottom: 6px;
    }
    
    .date-range-responsive > div:nth-child(3) {
        flex: 0 0 100% !important;
        text-align: center;
        order: 2;
        margin-bottom: 6px;
    }
    
    .date-range-responsive > div:nth-child(1),
    .date-range-responsive > div:nth-child(2) {
        order: 1;
    }
    
    .date-range-responsive > div:nth-child(4),
    .date-range-responsive > div:nth-child(5) {
        order: 3;
    }
    
    .date-range-responsive .text-center {
        line-height: 0px;
        padding: 0;
        margin: -5px 0 6px 0;
    }
    
    
    .date-range-responsive input[type="text"],
    .date-range-responsive input[type="time"] {
        height: 32px !important;
        padding: 4px 8px !important;
        font-size: 14px !important;
        border: 1px solid #bbb !important;
        border-radius: 4px !important;
        background-color: #fff !important;
        -webkit-appearance: none !important;
        appearance: none !important;
    }
    
    .date-range-responsive input[type="time"] {
        min-height: 32px !important;
    }
    
    
    
    .card.mt-2.mb-2 .d-flex.align-items-center.justify-content-between {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 12px !important;
    }
    
    .card.mt-2.mb-2 .d-flex.align-items-center.flex-shrink-0 {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        width: 100% !important;
        flex-shrink: 1 !important;
        gap: 8px !important;
        overflow: hidden !important;
    }
    
    .card.mt-2.mb-2 .d-flex.align-items-center.flex-shrink-0 .label-text {
        font-size: 0.7rem !important;
        white-space: nowrap !important;
        color: #374151 !important;
        flex-shrink: 0 !important;
    }
    
    .card.mt-2.mb-2 .d-flex.align-items-center.flex-shrink-0 select,
    .card.mt-2.mb-2 .d-flex.align-items-center.flex-shrink-0 .form-input,
    .card.mt-2.mb-2 .d-flex.align-items-center.flex-shrink-0 .form-input-small {
        width: auto !important;
        flex: 1 !important;
        min-width: 0 !important;
        background-color: #fff !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        color: #333 !important;
        font-size: 0.7rem !important;
        padding: 4px 6px !important;
        height: 28px !important;
    }
    
    .card.mt-2.mb-2 .d-flex.align-items-center.flex-shrink-0 input[type="checkbox"] {
        width: 14px !important;
        height: 14px !important;
        margin: 0 !important;
        flex-shrink: 0 !important;
    }
}
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.datepicker-3months', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        mode: 'single',
        disableMobile: true,
        wrap: false,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
        },
        onReady: function(selectedDates, dateStr, instance) {
            const daysContainer = instance.daysContainer;
            if (daysContainer) {
                const dayContainers = daysContainer.querySelectorAll('.dayContainer');
                dayContainers.forEach(function(dayContainer) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'month-wrapper';
                    dayContainer.parentNode.insertBefore(wrapper, dayContainer);
                    wrapper.appendChild(dayContainer);
                });
            }
        }
    });

    const isInIframe = window.self !== window.top;
    if (isInIframe) {
        document.getElementById('isIframe').value = '1';
    }

    const cancelBtn = document.getElementById('cancelBtn');
    const detailBtn = document.getElementById('detailBtn');
    const savedIdInput = document.getElementById('saved_id');
    
    cancelBtn.addEventListener('click', function() {
        if (isInIframe) {
            window.parent.postMessage({
                action: 'close-iframe-and-reload'
            }, '*');
        } else {
            window.location.href = '{{ route('masters.group-infos.index') }}';
        }
    });
    
    detailBtn.addEventListener('click', function() {
        const id = savedIdInput.value;
        if (id) {
            const editUrl = '{{ route("masters.group-infos.edit", ":id") }}'.replace(':id', id);
            if (isInIframe) {
                console.log('詳細ボタンクリック: 親ページにopen-editメッセージを送信します:', editUrl);
                window.parent.postMessage({
                    action: 'open-edit',
                    url: editUrl
                }, '*');
            } else {
                window.location.href = editUrl;
            }
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

    document.getElementById('yoyaku').addEventListener('change', function() {
        document.getElementById('bookingStatus').value = this.value;
    });
    
    document.getElementById('status').addEventListener('change', function() {
        document.getElementById('vehicleSpec').value = this.checked ? '指定' : '';
    });

    function validateDateRange() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!startDate || !endDate) {
            return true;
        }
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (start > end) {
            alert('終了日は開始日以降の日付を入力してください');
            return false;
        }
        return true;
    }

    const vehicles = @json($vehicles ?? []);
    const guides = @json($guides ?? []);
    const drivers = @json($drivers ?? []);
    const agencies = @json($agencies ?? []);

    function setupSearch(type, items, formatter, autoFillVehicleNumber = true) {
        const searchInput = document.getElementById(`${type}_search`);
        const suggestionsDiv = document.getElementById(`${type}_suggestions`);
        const hiddenId = document.getElementById(`${type}_id`);
        
        if (!searchInput) return;

        const vehicleTypeInput = document.getElementById('vehicle_type');
        const vehicleModelInput = document.getElementById('vehicle_model');
        const vehicleBranchInput = document.getElementById('vehicle_branch');
        const seatingCapacityInput = document.getElementById('seating_capacity');

        const agencyCodeInput = document.getElementById('agency_code');
        const agencyBranchInput = document.getElementById('agency_branch');
        const agencyPhoneInput = document.getElementById('agency_phone');
        const agencyContactNameInput = document.getElementById('agency_contact_name');
        const agencyCountryInput = document.getElementById('agency_country');

        function showSuggestions(query = '') {
            const filtered = items.filter(item => {
                const searchable = formatter(item).display.toLowerCase();
                return searchable.includes(query.toLowerCase());
            }).slice(0, 10);

            if (filtered.length === 0) {
                suggestionsDiv.style.display = 'none';
                return;
            }

            let html = '';
            filtered.forEach(item => {
                const formatted = formatter(item);
                html += `<div class="suggestion-item" data-id="${formatted.id}" data-data='${JSON.stringify(formatted)}'>${formatted.display}</div>`;
            });

            suggestionsDiv.innerHTML = html;
            suggestionsDiv.style.display = 'block';
        }

        searchInput.addEventListener('focus', function() {
            showSuggestions('');
        });

        searchInput.addEventListener('input', function() {
            showSuggestions(this.value);
        });

        suggestionsDiv.addEventListener('click', function(e) {
            const suggestion = e.target.closest('.suggestion-item');
            if (!suggestion) return;

            const id = suggestion.dataset.id;
            const data = JSON.parse(suggestion.dataset.data);
            
            searchInput.value = data.display;
            hiddenId.value = id;
            suggestionsDiv.style.display = 'none';

            if (type === 'vehicle') {
                if (vehicleTypeInput) vehicleTypeInput.value = data.type || '';
                if (vehicleModelInput) vehicleModelInput.value = data.model || '';
                if (vehicleBranchInput) vehicleBranchInput.value = data.branch || '';
                if (seatingCapacityInput) seatingCapacityInput.value = data.seating || '';
            } else if (type === 'agency') {
                if (agencyCodeInput) agencyCodeInput.value = data.agency_code || '';
                if (agencyBranchInput) agencyBranchInput.value = data.branch_name || '';
                if (agencyPhoneInput) agencyPhoneInput.value = data.phone || '';
                if (agencyContactNameInput && data.manager && !agencyContactNameInput.value) {
                    agencyContactNameInput.value = data.manager || '';
                }
                if (agencyCountryInput && data.country && !agencyCountryInput.value) {
                    agencyCountryInput.value = data.country || '';
                }
            }
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

    setupSearch('vehicle', vehicles, (item) => {
        return {
            display: `${item.registration_number} ${item.vehicle_model?.model_name ? '(' + item.vehicle_model.model_name + ')' : ''}`,
            id: item.id,
            registration: item.registration_number,
            type: item.vehicleType?.type_name || item.vehicle_type?.type_name || '',
            model: item.vehicleModel?.model_name || item.vehicle_model?.model_name || '',
            branch: item.branch?.branch_name || '',
            seating: item.seating_capacity || ''
        };
    }, true);

    setupSearch('guide', guides, (item) => {
        return {
            display: `${item.name} ${item.guide_code ? '(' + item.guide_code + ')' : ''}`,
            id: item.id,
            code: item.guide_code,
            phone: item.phone_number,
            branch: item.branch?.branch_name || '',
            employment_type: item.employment_type
        };
    });

    setupSearch('driver', drivers, (item) => {
        return {
            display: `${item.name} ${item.driver_code ? '(' + item.driver_code + ')' : ''}`,
            id: item.id,
            code: item.driver_code,
            phone: item.phone_number
        };
    });

    setupSearch('agency', agencies, (item) => {
        return {
            display: `${item.agency_name} ${item.branch_name ? '(' + item.branch_name + ')' : ''}`,
            id: item.id,
            agency_code: item.agency_code,
            branch_name: item.branch_name,
            phone: item.phone_number,
            manager: item.manager_name,
            country: item.country,
            email: item.email
        };
    });

    function checkSeatingCapacity() {
        const adultCount = parseInt(document.getElementById('adult_count')?.value) || 0;
        const childCount = parseInt(document.querySelector('input[name="child_count"]')?.value) || 0;
        const guideCount = parseInt(document.querySelector('input[name="guide_count"]')?.value) || 0;
        const otherCount = parseInt(document.querySelector('input[name="other_count"]')?.value) || 0;
        const totalPeople = adultCount + childCount + guideCount + otherCount;
        const seatingCapacity = parseInt(document.getElementById('seating_capacity')?.value) || 0;
        
        const existingWarning = document.querySelector('.warning-message');
        if (existingWarning) existingWarning.remove();
        
        if (seatingCapacity > 0 && totalPeople > seatingCapacity) {
            const warningDiv = document.createElement('div');
            warningDiv.className = 'warning-message';
            warningDiv.innerText = `定員(${seatingCapacity}名)を超えています`;
            
            const adultCountParent = document.getElementById('adult_count')?.parentNode?.parentNode;
            if (adultCountParent) {
                adultCountParent.appendChild(warningDiv);
            }
        }
    }

    const adultCountInput = document.getElementById('adult_count');
    if (adultCountInput) {
        adultCountInput.addEventListener('input', checkSeatingCapacity);
    }
    
    const childCountInput = document.querySelector('input[name="child_count"]');
    if (childCountInput) {
        childCountInput.addEventListener('input', checkSeatingCapacity);
    }
    
    const guideCountInput = document.querySelector('input[name="guide_count"]');
    if (guideCountInput) {
        guideCountInput.addEventListener('input', checkSeatingCapacity);
    }
    
    const otherCountInput = document.querySelector('input[name="other_count"]');
    if (otherCountInput) {
        otherCountInput.addEventListener('input', checkSeatingCapacity);
    }

    function showErrors(errors) {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        for (const field in errors) {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                
                if (field === 'start_date' || field === 'end_date') {
                    document.querySelector('[data-tab="basic"]').click();
                }
            }
        }
    }

    document.getElementById('createForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateDateRange()) {
            return;
        }
    
        const adultCount = parseInt(document.getElementById('adult_count')?.value) || 0;
        const childCount = parseInt(document.querySelector('input[name="child_count"]')?.value) || 0;
        const guideCount = parseInt(document.querySelector('input[name="guide_count"]')?.value) || 0;
        const otherCount = parseInt(document.querySelector('input[name="other_count"]')?.value) || 0;
        const totalPeople = adultCount + childCount + guideCount + otherCount;
        const seatingCapacity = parseInt(document.getElementById('seating_capacity')?.value) || 0;
        
        if (seatingCapacity > 0 && totalPeople > seatingCapacity) {
            if (!confirm(`定員(${seatingCapacity}名)を超えています。このまま保存しますか？`)) {
                return;
            }
        }
    
        const formData = new FormData(this);
        
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
                if (data.id) {
                    savedIdInput.value = data.id;
                }
                
                const editUrl = '{{ route("masters.group-infos.edit", ":id") }}'.replace(':id', data.id);
                
                const newWindow = window.open(editUrl, '_blank');
                
                if (window.parent && window.parent !== window) {
                    window.parent.location.reload();
                } else {
                    window.location.reload();
                }
                
                setTimeout(() => {
                    if (window.parent && window.parent !== window) {
                        window.parent.postMessage('close-iframe', '*');
                    } else {
                        window.close();
                    }
                }, 1000);
                
                submitBtn.textContent = '作成';
                
            } else {
                alert(data.message || '保存に失敗しました');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            if (error.errors) {
                showErrors(error.errors);
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

document.addEventListener('DOMContentLoaded', function() {
    initDateRangePicker('input[name="start_date"]', 'input[name="end_date"]');
});


document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const vehicleId = urlParams.get('vehicle_id');
    const vehicleName = urlParams.get('vehicle_name');
    const startDate = urlParams.get('start_date');
    const endDate = urlParams.get('end_date');
    
    if (startDate) {
        document.getElementById('start_date').value = startDate;
    }
    if (endDate) {
        document.getElementById('end_date').value = endDate;
    }
    
    if (vehicleId && vehicleName) {
        const vehicleSearchInput = document.getElementById('vehicle_search');
        const vehicleIdInput = document.getElementById('vehicle_id');
        
        if (vehicleSearchInput && vehicleIdInput) {
            vehicleSearchInput.value = vehicleName;
            vehicleIdInput.value = vehicleId;
            
            const event = new Event('change', { bubbles: true });
            vehicleSearchInput.dispatchEvent(event);
        }
    }
});
</script>
@endpush