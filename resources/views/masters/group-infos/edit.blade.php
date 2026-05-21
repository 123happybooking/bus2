@extends('layouts.app')

@section('title', 'グループ情報編集')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 page-title">グループ情報編集</h5>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="newGroupBtn" class="btn btn-primary btn-sm px-3" 
                    style="background-color: #2563eb; border-color: #2563eb; font-size: 0.875rem;">
                <i class="bi bi-plus-lg"></i> 新規予約
            </button>
            <a href="{{ route('masters.group-infos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>
    
    <div id="alert-container"></div>
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-3 success-alert" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3 error-alert" role="alert">
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
    
    <form method="POST" action="{{ route('masters.group-infos.update', $groupInfo->id) }}" id="editForm">
        @csrf
        @method('PUT')
        
        <div class="card shadow-sm mb-1">
            <div class="card-header py-2 px-3" style="background-color: #141c28; border-bottom: 1px solid #aaa;">
                <h6 class="mb-0" style="color: #fff; font-size: 0.875rem; font-weight: 500;">基本情報</h6>
            </div>
            <div class="card-body p-2">
                <div class="row" style="margin-right: -5px; margin-left: -5px;">
                    <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex w-100 gap-2">
                                    <div class="d-flex w-50 gap-2">
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="span-label" style="min-width: 50px;">予約ID</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center w-100" style="color: #2563eb;">{{ $groupInfo->id ?? '0' }}</span>
                                        </div>
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="span-label" style="min-width: 50px;">状態</span>
                                            <select class="form-select form-select-sm border w-100" name="reservation_status" id="reservation_status">
                                                <option value="仮押さえ" style="background-color: #ffff99; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '仮押さえ' ? 'selected' : '' }}>仮押さえ</option>
                                                <option value="見積" style="background-color: #ccffcc; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '見積' ? 'selected' : '' }}>見積</option>
                                                <option value="予約" style="background-color: #ccf5ff; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '予約' ? 'selected' : '' }}>予約</option>
                                                <option value="危ない" style="background-color: #ffcccc; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '危ない' ? 'selected' : '' }}>危ない</option>
                                                <option value="確定待ち" style="background-color: #ffd9b3; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '確定待ち' ? 'selected' : '' }}>確定待ち</option>
                                                <option value="確定" style="background-color: #cbb87c; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '確定' ? 'selected' : '' }}>確定</option>
                                                <option value="送信済" style="background-color: #e6e6fa; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '送信済' ? 'selected' : '' }}>送信済</option>
                                                <option value="実績待ち" style="background-color: #e0b0ff; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '実績待ち' ? 'selected' : '' }}>実績待ち</option>
                                                <option value="運行済" style="background-color: #c0c0c0; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '運行済' ? 'selected' : '' }}>運行済</option>
                                                <option value="請求済" style="background-color: #b0e0e6; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == '請求済' ? 'selected' : '' }}>請求済</option>
                                                <option value="キャンセル" style="background-color: #d3d3d3; color: black;" {{ old('reservation_status', $groupInfo->reservation_status) == 'キャンセル' ? 'selected' : '' }}>キャンセル</option>
                                                <option value="稼働不可" style="background-color: #2c2c2c; color: white;" {{ old('reservation_status', $groupInfo->reservation_status) == '稼働不可' ? 'selected' : '' }}>稼働不可</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex w-50 gap-2">
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="span-label" style="min-width: 50px;">担当</span>
                                            <div class="flex-1 position-relative w-100">
                                                <input type="text" class="form-control form-control-sm border search-input w-100" id="staff_search" value="{{ old('agency_contact_name', $groupInfo->agency_contact_name ?? $defaultStaffName) }}" autocomplete="off">
                                                <input type="hidden" name="agency_contact_name" value="{{ old('agency_contact_name', $groupInfo->agency_contact_name ?? $defaultStaffName) }}">
                                                <div class="suggestions-container" id="staff_suggestions" style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="span-label" style="min-width: 50px;">営業所</span>
                                            <div class="flex-1 position-relative w-100">
                                                <input type="text" class="form-control form-control-sm border search-input w-100" id="branch_search" value="{{ old('vehicle_branch', $groupInfo->vehicle_branch ?? $defaultBranchName) }}" autocomplete="off">
                                                <input type="hidden" name="vehicle_branch" id="vehicle_branch" value="{{ old('vehicle_branch', $groupInfo->vehicle_branch ?? $defaultBranchName) }}">
                                                <div class="suggestions-container" id="branch_suggestions" style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex w-100 gap-2">
                                    <div class="d-flex align-items-center w-50">
                                        <span class="span-label" style="min-width: 50px;">代理店</span>
                                        <div class="flex-1 position-relative w-100">
                                            <input type="text" class="form-control form-control-sm border search-input w-100" id="agency_search" value="{{ old('agency', $groupInfo->agency) }}" autocomplete="off">
                                            <input type="hidden" name="agency" id="agency" value="{{ old('agency', $groupInfo->agency) }}">
                                            <input type="hidden" name="agency_code" id="agency_code" value="{{ old('agency_code', $groupInfo->agency_code) }}">
                                            <input type="hidden" name="agency_branch" id="agency_branch" value="{{ old('agency_branch', $groupInfo->agency_branch) }}">
                                            <input type="hidden" name="agency_phone" id="agency_phone" value="{{ old('agency_phone', $groupInfo->agency_phone) }}">
                                            <input type="hidden" name="agency_id" id="agency_id" value="{{ old('agency_id', $groupInfo->agency_id) }}">
                                            <div class="suggestions-container" id="agency_suggestions" style="display: none;"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex w-50 gap-2">
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="span-label" style="min-width: 50px; white-space: nowrap;">業務</span>
                                            <div class="position-relative w-100">
                                                <input type="text" class="form-control form-control-sm border search-input w-100" 
                                                       id="category_search" 
                                                       value="{{ old('business_category', $groupInfo->reservationCategory ? $groupInfo->reservationCategory->category_name : '') }}" autocomplete="off">
                                                <input type="hidden" name="reservation_categories_id" id="business_category_id" 
                                                       value="{{ old('reservation_categories_id', $groupInfo->reservation_categories_id) }}">
                                                <div class="suggestions-container" id="category_suggestions" style="display: none;"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="span-label" style="min-width: 50px; white-space: nowrap;">行程</span>
                                            <input type="text" class="form-control form-control-sm border w-100" name="itinerary_name" value="{{ old('itinerary_name', $groupInfo->itinerary_name ?? '') }}">
                                        </div>
                                    </div>
                                    
                                    <input type="text" class="form-control form-control-sm border datepicker-3months" name="start_date" id="start_date" value="{{ old('start_date', $groupInfo->start_date ? $groupInfo->start_date->format('Y-m-d') : '') }}" style="display: none;" readonly>
                                    <input type="text" class="form-control form-control-sm border datepicker-3months" name="end_date" id="end_date" value="{{ old('end_date', $groupInfo->end_date ? $groupInfo->end_date->format('Y-m-d') : '') }}" style="display: none;" readonly>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex w-100 gap-2">
                                    <div class="d-flex align-items-center w-50">
                                        <span class="span-label" style="min-width: 50px; white-space: nowrap;">団体名</span>
                                        <input type="text" class="form-control form-control-sm border w-100" name="group_name" value="{{ old('group_name', $groupInfo->group_name ?? '') }}">
                                    </div>
                                    
                                    <div class="d-flex w-50 gap-2">
                                        <div class="d-flex align-items-center w-50">
                                            <span class="span-label" style="min-width: 50px; white-space: nowrap;">人数</span>
                                            <div class="d-flex gap-1 flex-fill">
                                                <input type="number" class="form-control form-control-sm border flex-fill" name="adult_count" id="adult_count" value="{{ old('adult_count', $groupInfo->adult_count == 0 ? '' : $groupInfo->adult_count) }}" placeholder="大人" min="0">
                                                <input type="number" class="form-control form-control-sm border flex-fill" name="child_count" id="child_count" value="{{ old('child_count', $groupInfo->child_count == 0 ? '' : $groupInfo->child_count) }}" placeholder="小人" min="0">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center w-50">
                                            <span class="span-label" style="min-width: 50px; white-space: nowrap;">荷物数</span>
                                            <input type="text" class="form-control form-control-sm border w-100" name="luggage" id="luggage" value="{{ old('luggage', $groupInfo->luggage ?? '') }}" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex w-100 gap-2">
                                    <div class="d-flex w-50 gap-2">
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="span-label" style="min-width: 50px; white-space: normal; word-break: keep-all;">AGT予約ID</span>
                                            <input type="text" class="form-control form-control-sm border w-100" name="agt_tour_id" value="{{ old('agt_tour_id', $groupInfo->agt_tour_id ?? '') }}">
                                        </div>
                                        <div class="d-flex align-items-center flex-fill">
                                            <span class="span-label" style="min-width: 50px;">国籍</span>
                                            <div class="flex-1 position-relative w-100">
                                                <input type="text" class="form-control form-control-sm border search-input w-100" id="country_search" value="{{ old('agency_country', $groupInfo->agency_country ?? '') }}" autocomplete="off">
                                                <input type="hidden" name="agency_country" id="agency_country" value="{{ old('agency_country', $groupInfo->agency_country ?? '') }}">
                                                <div class="suggestions-container" id="country_suggestions" style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center w-50">
                                        <span class="span-label" style="min-width: 50px; white-space: normal; word-break: keep-all;">オプション</span>
                                        <div class="flex-1 w-100">
                                            <div class="options-container" style="display: flex; flex-wrap: wrap; gap: 12px; border: 1px solid #aaa; border-radius: 4px; padding: 6px 10px; min-height: 32px; background-color: white;">
                                                @foreach($options ?? [] as $option)
                                                <label class="d-flex align-items-center" style="cursor: pointer;">
                                                    <input type="checkbox" name="options[]" value="{{ $option->id }}" 
                                                           class="mr-1" style="margin-right: 4px;"
                                                           {{ in_array($option->id, $selectedOptions ?? []) ? 'checked' : '' }}>
                                                    <span style="font-size: 11px;">{{ $option->name }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex align-items-start w-100">
                                    <span class="span-label">備考</span>
                                    <textarea name="remarks" rows="4" class="form-control form-control-sm border w-100">{{ old('remarks', $groupInfo->remarks) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                        <div class="tab-container">
                            <div class="d-flex w-100" style="border-bottom: 1px solid #aaa;">
                                <span class="tab-item active flex-fill text-center px-2 py-1" data-tab="basic" style="background-color: white; border: 1px solid #aaa; border-bottom-color: white; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #374151; font-size: 0.8rem; cursor: pointer;">基本</span>
                                <span class="tab-item inactive flex-fill text-center px-2 py-1" data-tab="customer" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">顧客</span>
                                <span class="tab-item inactive flex-fill text-center px-2 py-1" data-tab="history" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">履歴</span>
                            </div>
                            <div class="tab-line" style="display: none;"></div>
                        </div>

                        <div id="tabContent" class="tab-content" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 193px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">
                            
                            <div class="tab-pane active" id="basic-tab">
                                <div class="file-manager" style="display: flex; flex-direction: column; height: 100%;">
                                    <div class="file-list" id="file-list" style="flex: 1; overflow-y: auto; margin-bottom: 10px; min-height: 0;">
                                        @if($groupInfo->files && $groupInfo->files->count() > 0)
                                            @foreach($groupInfo->files as $file)
                                            <div class="file-item" data-file-id="{{ $file->id }}" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-radius: 4px; margin-bottom: 4px;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="bi {{ $file->icon }}" style="color: #2563eb;"></i>
                                                    <span class="file-name" style="font-size: 12px;">{{ $file->file_name }}</span>
                                                    <span class="file-size" style="font-size: 11px; color: #6b7280;">({{ $file->size_for_humans }})</span>
                                                </div>
                                                <div class="file-actions" style="display: flex; gap: 8px;">
                                                    <a href="{{ route('masters.group-files.download', $file->id) }}" class="btn-download" style="color: #2563eb; text-decoration: none;">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <button type="button" class="btn-delete-file" data-file-id="{{ $file->id }}" style="background: none; border: none; color: #dc2626; cursor: pointer;">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="file-empty" style="text-align: center; padding: 60px 0 0 0; color: #9ca3af; font-size: 12px;">
                                                <i class="bi bi-folder2-open"></i> ファイルはありません
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="file-upload" style="display: flex; justify-content: center; flex-shrink: 0;">
                                        <button type="button" id="btn-upload-file" class="btn btn-sm btn-primary" style="background-color: #2563eb; border: none; padding: 6px 16px; font-size: 12px;">
                                            <i class="bi bi-cloud-upload"></i> アップロード
                                        </button>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="tab-pane" id="customer-tab" style="display: none;">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="span-label" style="white-space: nowrap; min-width: 70px;">担当</span>
                                    <input type="text" class="form-control form-control-sm border" name="agency_contact_name_tab" value="{{ old('agency_contact_name', $groupInfo->agency_contact_name) }}" style="flex: 1;">
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="span-label" style="white-space: nowrap; min-width: 70px;">国籍</span>
                                    <input type="text" class="form-control form-control-sm border" name="agency_country_tab" value="{{ old('agency_country', $groupInfo->agency_country ?? '') }}" style="flex: 1;">
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="span-label" style="white-space: nowrap; min-width: 70px;">予約ID</span>
                                    <input type="text" class="form-control form-control-sm border" name="id_display" value="{{ $groupInfo->id }}" readonly style="flex: 1; background-color: #f9fafb;">
                                </div>
                            </div>
                        
                            <div class="tab-pane" id="history-tab" style="display: none;">
                                <div class="dashed-box" style="color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db;">
                                    履歴はありません
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="operation-details-container">
            @foreach($groupedItineraries as $vehicleKey => $group)
                @php 
                    $busAssignment = $group['bus_assignment'] ?? null;
                    
                    if (!$busAssignment) {
                        continue;
                    }
                    
                    $vehicleIndex = $loop->iteration;
                    $busId = $busAssignment->id ?? $vehicleKey;
                    $vehicleName = $group['vehicle_name'] ?? '';
                    $vehicleId = $group['vehicle_id'] ?? '';
                    $driverName = $group['driver_name'] ?? ($busAssignment->driver_name ?? '');
                    $driverId = $busAssignment->driver_id ?? '';
                    
                    
                    $vehicleInfo = null;
                    if ($vehicleId) {
                        $vehicleInfo = $vehicles->firstWhere('id', $vehicleId);
                    }
                    
                    $driverInfo = null;
                    if ($driverId) {
                        $driverInfo = $drivers->firstWhere('id', $driverId);
                    }
                    
                    $guideInfo = null;
                    if ($busAssignment && $busAssignment->guide_id) {
                        $guideInfo = $guides->firstWhere('id', $busAssignment->guide_id);
                    }
                @endphp
                <div class="card shadow-sm mb-1 vehicle-detail-card" data-vehicle-id="{{ $vehicleId }}" data-vehicle-index="{{ $vehicleIndex }}" data-bus-id="{{ $busId }}">
                    <div class="card-header py-1 px-3 d-flex align-items-center" style="background-color: #141c28; border-bottom: 1px solid #aaa;">
                        <h6 class="mb-0 me-3" style="color: #fff; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                            <span>運行詳細-{{ sprintf('%02d', $vehicleIndex) }}</span>
                            <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; background-color: {{ $group['completion_status'] == '完成' ? '#10b981' : '#f59e0b' }}; color: white;">
                                {{ $group['completion_status'] }}
                            </span>
                            <button type="button" class="btn-pdf-export btn btn-sm" 
                                    data-bus-id="{{ $busId }}" 
                                    data-vehicle-index="{{ $vehicleIndex }}"
                                    style="background-color: #dc2626; border: none; color: white; padding: 2px 10px; font-size: 0.7rem; border-radius: 4px; display: flex; align-items: center; gap: 4px;">
                                <i class="bi bi-file-pdf"></i> 運行指示書PDF
                            </button>
                        </h6>
                        <div class="d-flex align-items-center ms-auto card-header-actions" style="gap: 15px;">
                            <div class="form-check d-flex align-items-center">
                                <label class="form-check-label me-2" for="bus_assignments_{{ $vehicleIndex }}" style="font-size: 0.8rem; color: #fff;">最終確認</label>
                                <input type="checkbox" class="form-check-input" id="bus_assignments_{{ $vehicleIndex }}" name="bus_assignments[{{ $vehicleIndex }}][status_finalized]" value="1" {{ $busAssignment && $busAssignment->status_finalized ? 'checked' : '' }} style="margin: 0;">
                            </div>
                                
                            <div class="form-check d-flex align-items-center">
                                <label class="form-check-label me-2" for="status_sent_{{ $vehicleIndex }}" style="font-size: 0.8rem; color: #fff;">送信済</label>
                                <input type="checkbox" class="form-check-input" id="status_sent_{{ $vehicleIndex }}" name="bus_assignments[{{ $vehicleIndex }}][status_sent]" value="1" {{ $busAssignment && $busAssignment->status_sent ? 'checked' : '' }} style="margin: 0;">
                            </div>
                                
                            <div class="form-check d-flex align-items-center">
                                <label class="form-check-label me-2" for="lock_arrangement_{{ $vehicleIndex }}" style="font-size: 0.8rem; color: #fff;">操作ロック</label>
                                <input type="checkbox" class="form-check-input" id="lock_arrangement_{{ $vehicleIndex }}" name="bus_assignments[{{ $vehicleIndex }}][lock_arrangement]" value="1" {{ $busAssignment && $busAssignment->lock_arrangement ? 'checked' : '' }} style="margin: 0;">
                            </div>
                            <div class="d-flex align-items-center card-header-btns" style="gap: 5px;">
                                <input type="text" class="form-control form-control-sm border merge-operation-id" placeholder="運行ID" style="width: 80px;">
                                <button type="button" class="btn btn-sm btn-primary merge-btn" style="font-size: 0.75rem; padding: 4px 8px;">統合</button>
                                <button type="button" class="btn btn-sm btn-secondary split-btn" style="font-size: 0.75rem; padding: 4px 8px;">分割</button>
                                <button type="button" class="btn btn-sm btn-info copy-btn" style="font-size: 0.75rem; padding: 4px 8px; background-color: #17a2b8; border-color: #17a2b8; color: white;">Copy</button>
                                <button type="button" class="btn btn-sm btn-success update-btn" style="font-size: 0.75rem; padding: 4px 8px; background-color: #28a745; border-color: #28a745; color: white;">更新</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <input type="hidden" name="bus_assignments[{{ $vehicleIndex }}][id]" value="{{ $busId }}">
                        <input type="hidden" name="bus_assignments[{{ $vehicleIndex }}][vehicle_index]" value="{{ $vehicleIndex }}">
                        
                        <div class="row" style="margin-right: -5px; margin-left: -5px;">
                            <div class="col-md-6 yxxx-l" style="width:60%; padding-right: 5px; padding-left: 5px;">
                                <div class="row mb-1">
                                    <div class="col-md-12">
                                        <div class="d-flex w-100">
                                            <div class="d-flex align-items-center" style="width: 50%; gap: 8px;">
                                                <div class="d-flex align-items-center" style="width: 70%;">
                                                    <span class="span-label">運行ID</span>
                                                    <span class="border px-2 py-1 bg-white rounded w-100" style="color: #2563eb;">
                                                        {{ $busAssignment->id ?? '' }}
                                                    </span>
                                                </div>
                                                
                                                <div class="d-flex align-items-center vehicle_type_spec_check" style="width: 30%;">
                                                    <span class="span-label" style="width: auto !important;">車種指定</span>
                                                    <input type="checkbox" class="form-check-input" name="bus_assignments[{{ $vehicleIndex }}][vehicle_type_spec_check]" value="1" {{ $busAssignment && $busAssignment->vehicle_type_spec_check ? 'checked' : '' }} style="margin: 0;">
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex" style="width: 50%; gap: 8px;">
                                                <div class="d-flex align-items-center" style="width: 50%;">
                                                    <span class="span-label">車両等級</span>
                                                    <select name="bus_assignments[{{ $vehicleIndex }}][vehicle_grade_id]" class="form-select form-select-sm border w-100">
                                                        <option value="">-- 選択 --</option>
                                                        @foreach($vehicleGrades ?? [] as $grade)
                                                            <option value="{{ $grade->id }}" {{ ($busAssignment->vehicle_grade_id ?? '') == $grade->id ? 'selected' : '' }}>
                                                                {{ $grade->description ?? $grade->grade_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div class="d-flex align-items-center" style="width: 50%;">
                                                    <span class="span-label">号車</span>
                                                    <input type="text" class="form-control form-control-sm border w-100" name="bus_assignments[{{ $vehicleIndex }}][vehicle_number]" value="{{ $busAssignment->vehicle_number ?? sprintf('%02d', $vehicleIndex) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="row mb-1">
                                    <div class="col-md-12">
                                        <div class="d-flex w-100">
                                            <div class="d-flex align-items-center row-step_car" style="width: 50%;">
                                                <span class="span-label" style="white-space: normal; word-break: break-all; line-height: 1.2; min-width: 70px;">ステップカー</span>
                                                <input type="text" class="form-control form-control-sm border" name="bus_assignments[{{ $vehicleIndex }}][step_car]" value="{{ $busAssignment->step_car ?? '' }}" style="flex: 1;" id="step_car_{{ $vehicleIndex }}">
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1 copy-stepcar-btn border" 
                                                        data-vehicle-index="{{ $vehicleIndex }}" 
                                                        style="padding: 4px 8px; font-size: 0.7rem; white-space: nowrap;"
                                                        title="団体名をコピー">
                                                    <i class="bi bi-files"></i> Copy
                                                </button>
                                            </div>
                                            
                                            <div class="d-flex" style="width: 50%; gap: 8px;">
                                                <div class="d-flex align-items-center adult-child" style="width: 50%;">
                                                    <span class="span-label">人数</span>
                                                    <div class="d-flex gap-1 flex-fill">
                                                        <input type="number" class="form-control form-control-sm border flex-fill" name="bus_assignments[{{ $vehicleIndex }}][adult_count]" value="{{ $busAssignment->adult_count ?: '' }}" placeholder="大" min="0">
                                                        <input type="number" class="form-control form-control-sm border flex-fill" name="bus_assignments[{{ $vehicleIndex }}][child_count]" value="{{ $busAssignment->child_count ?: '' }}" placeholder="小" min="0">
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex align-items-center" style="width: 50%;">
                                                    <span class="span-label">荷物</span>
                                                    <input type="text" class="form-control form-control-sm border w-100" name="bus_assignments[{{ $vehicleIndex }}][luggage]" value="{{ $busAssignment->luggage ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="row mb-1">
                                    <div class="col-md-12">
                                        <div class="d-flex w-100 vehicle-representative">
                                            <div class="d-flex align-items-center" style="width: 50%;">
                                                <span class="span-label">車両</span>
                                                <div class="flex-1 position-relative w-100">
                                                    <input type="text" class="form-control form-control-sm border search-input w-100" 
                                                           id="vehicle_search_{{ $vehicleIndex }}" 
                                                           value="{{ $vehicleInfo ? $vehicleInfo->registration_number . ($vehicleInfo->vehicleModel ? '(' . $vehicleInfo->vehicleModel->model_name . ')' : '') : '' }}" 
                                                           autocomplete="off"
                                                           placeholder="-- 車両を選択 --">
                                                    <input type="hidden" name="bus_assignments[{{ $vehicleIndex }}][vehicle_id]" id="vehicle_id_{{ $vehicleIndex }}" value="{{ $busAssignment->vehicle_id ?? $vehicleId ?? '' }}">
                                                    <div class="suggestions-container" id="vehicle_suggestions_{{ $vehicleIndex }}" style="display: none;"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex align-items-center representative-1" style="width: 50%;">
                                                <span class="span-label">代表</span>
                                                <input type="text" class="form-control form-control-sm border" name="bus_assignments[{{ $vehicleIndex }}][representative]" value="{{ $busAssignment->representative ?? '' }}" placeholder="Name">
                                                
                                                <input type="text" class="form-control form-control-sm border ms-2" name="bus_assignments[{{ $vehicleIndex }}][representative_phone]" value="{{ $busAssignment->representative_phone ?? '' }}" placeholder="Tel/Cell">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="row mb-1">
                                    <div class="col-md-12">
                                        <div class="d-flex w-100">
                                            <div class="d-flex align-items-center" style="width: 50%;">
                                                <span class="span-label">運転手</span>
                                                <div class="flex-1 position-relative w-100">
                                                    <input type="text" class="form-control form-control-sm border search-input w-100" 
                                                           id="driver_search_{{ $vehicleIndex }}" 
                                                           value="{{ $driverInfo ? $driverInfo->name . ($driverInfo->driver_code ? '(' . $driverInfo->driver_code . ')' : '') : '' }}" 
                                                           autocomplete="off"
                                                           placeholder="-- 選択 --">
                                                    <input type="hidden" name="bus_assignments[{{ $vehicleIndex }}][driver_id]" id="driver_id_{{ $vehicleIndex }}" value="{{ $busAssignment->driver_id ?? $driverId ?? '' }}">
                                                    <div class="suggestions-container" id="driver_suggestions_{{ $vehicleIndex }}" style="display: none;"></div>
                                                </div>
                                                <label class="span-label" style="min-width: 30px;">仮</label>
                                                <input type="checkbox" class="form-check-input" name="bus_assignments[{{ $vehicleIndex }}][temporary_driver]" value="1" {{ $busAssignment && $busAssignment->temporary_driver ? 'checked' : '' }} style="margin: 0;">
                                            </div>
                                            
                                            <div class="d-flex align-items-center" style="width: 50%;">
                                                <span class="span-label">添乗</span>
                                                <div class="flex-1 position-relative w-100">
                                                    <input type="text" class="form-control form-control-sm border search-input w-100" 
                                                           id="guide_search_{{ $vehicleIndex }}" 
                                                           value="{{ $guideInfo ? $guideInfo->name . ($guideInfo->guide_code ? '(' . $guideInfo->guide_code . ')' : '') : '' }}" 
                                                           autocomplete="off"
                                                           placeholder="-- 選択 --">
                                                    <input type="hidden" name="bus_assignments[{{ $vehicleIndex }}][guide_id]" id="guide_id_{{ $vehicleIndex }}" value="{{ $busAssignment->guide_id ?? '' }}">
                                                    <div class="suggestions-container" id="guide_suggestions_{{ $vehicleIndex }}" style="display: none;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                                <div class="tab-container-{{ $vehicleIndex }}">
                                    <div class="d-flex w-100" style="border-bottom: 1px solid #aaa;">
                                        <span class="tab-button2 active flex-fill text-center px-2 py-1" data-container="{{ $vehicleIndex }}" data-tab2="basic2-{{ $vehicleIndex }}" style="background-color: white; border: 1px solid #aaa; border-bottom-color: white; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #374151; font-size: 0.8rem; cursor: pointer;">基本</span>
                                        <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $vehicleIndex }}" data-tab2="doc-{{ $vehicleIndex }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">給与</span>
                                        <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $vehicleIndex }}" data-tab2="expense-{{ $vehicleIndex }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">立替</span>
                                    </div>

                                    <div id="basic2-{{ $vehicleIndex }}" class="tab-content2" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: auto; height: 102px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; overflow: auto;">
                                        <div class="file-manager-{{ $vehicleIndex }}" style="display: flex; flex-direction: column; height: 100%;">
                                            <div class="file-list file-list-bus" id="file-list-bus-{{ $busId }}" data-bus-id="{{ $busId }}" style="flex: 1; margin-bottom: 10px;">
                                                @if(isset($group['files']) && $group['files']->count() > 0)
                                                    @foreach($group['files'] as $file)
                                                    <div class="file-item" data-file-id="{{ $file->id }}" style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-radius: 4px; margin-bottom: 4px;">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <i class="bi {{ $file->icon }}" style="color: #2563eb;"></i>
                                                            <span class="file-name" style="font-size: 11px;">{{ $file->file_name }}</span>
                                                            <span class="file-size" style="font-size: 10px; color: #6b7280;">({{ $file->size_for_humans }})</span>
                                                        </div>
                                                        <div class="file-actions" style="display: flex; gap: 6px;">
                                                            <a href="{{ route('masters.group-files.download', $file->id) }}" class="btn-download" style="color: #2563eb; text-decoration: none;">
                                                                <i class="bi bi-download" style="font-size: 12px;"></i>
                                                            </a>
                                                            <button type="button" class="btn-delete-file-bus" data-file-id="{{ $file->id }}" data-bus-id="{{ $busId }}" style="background: none; border: none; color: #dc2626; cursor: pointer;">
                                                                <i class="bi bi-trash" style="font-size: 12px;"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <div class="file-empty-bus" style="text-align: center; padding: 10px 0 0 0; color: #9ca3af; font-size: 11px;">
                                                        <i class="bi bi-folder2-open"></i> ファイルはありません
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="file-upload-bus" style="display: flex; justify-content: center; flex-shrink: 0;">
                                                <button type="button" class="btn-upload-file-bus btn btn-sm btn-primary mb-2" data-bus-id="{{ $busId }}" style="background-color: #2563eb; border: none; padding: 4px 12px; font-size: 11px;">
                                                    <i class="bi bi-cloud-upload"></i> アップロード
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="doc-{{ $vehicleIndex }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; overflow: auto;">
                                        <div class="compensation-container" data-bus-id="{{ $busId }}" data-vehicle-index="{{ $vehicleIndex }}">
                                            @php
                                                $busCompensations = $compensationsByBus[$busId] ?? [];
                                            @endphp
                                            
                                            @if(count($busCompensations) > 0)
                                            <table class="table table-sm table-bordered compensation-table" style="font-size: 11px; margin-bottom: 5px;">
                                                <thead style="text-align: center;">
                                                    <tr>
                                                        <th style="width: 20%; background-color: #f8f9fa;">対象日</th>
                                                        <th style="width: 25%; background-color: #f8f9fa;">報酬種別</th>
                                                        <th style="width: 15%; background-color: #f8f9fa;">単価</th>
                                                        <th style="width: 10%; background-color: #f8f9fa;">数量</th>
                                                        <th style="width: 15%; background-color: #f8f9fa;">金額</th>
                                                        <th style="width: 15%; background-color: #f8f9fa;">操作</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="compensation-tbody">
                                                    @php $totalAmount = 0; @endphp
                                                    @foreach($busCompensations as $compIndex => $comp)
                                                    <tr class="compensation-row" data-comp-index="{{ $compIndex }}">
                                                        <td>
                                                            <input type="hidden" name="bus_assignments[{{ $vehicleIndex }}][compensations][{{ $compIndex }}][id]" value="{{ $comp->id }}">
                                                            <input type="date" class="form-control form-control-sm" 
                                                                   name="bus_assignments[{{ $vehicleIndex }}][compensations][{{ $compIndex }}][target_date]" 
                                                                   value="{{ $comp->target_date }}">
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm compensation-type" 
                                                                    name="bus_assignments[{{ $vehicleIndex }}][compensations][{{ $compIndex }}][comp_id]">
                                                                <option value="">-- 選択 --</option>
                                                                @foreach($compensationTypes ?? [] as $type)
                                                                    <option value="{{ $type->id }}" {{ ($comp->comp_id ?? '') == $type->id ? 'selected' : '' }}>
                                                                        {{ $type->comp_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm compensation-price" 
                                                                   name="bus_assignments[{{ $vehicleIndex }}][compensations][{{ $compIndex }}][price]" 
                                                                   value="{{ intval($comp->price) }}" step="1" min="0">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm compensation-qty" 
                                                                   name="bus_assignments[{{ $vehicleIndex }}][compensations][{{ $compIndex }}][qty]" 
                                                                   value="{{ intval($comp->qty) }}" step="1" min="0">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm compensation-amount" 
                                                                   name="bus_assignments[{{ $vehicleIndex }}][compensations][{{ $compIndex }}][amount]" 
                                                                   value="{{ intval($comp->amount) }}" readonly style="background-color: #f3f4f6;">
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-success add-compensation-row" style="padding: 2px 6px; font-size: 10px;">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger remove-compensation-row" style="padding: 2px 6px; font-size: 10px;">
                                                                <i class="bi bi-dash-lg"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @php $totalAmount += $comp->amount; @endphp
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                                                        <td colspan="4" class="text-end">合計</td>
                                                        <td class="text-end"><span class="total-amount-display">¥ {{ number_format($totalAmount) }}</span></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                            @else
                                            <div class="text-center py-3">
                                                <button type="button" class="btn btn-sm btn-outline-primary add-first-compensation-row" 
                                                        data-bus-id="{{ $busId }}" data-vehicle-index="{{ $vehicleIndex }}"
                                                        style="font-size: 11px; padding: 4px 12px;">
                                                    <i class="bi bi-plus-lg"></i> 手当を追加
                                                </button>
                                            </div>
                                            @endif
                                            
                                            <input type="hidden" class="total-amount-hidden" name="bus_assignments[{{ $vehicleIndex }}][compensations_total]" value="{{ $totalAmount ?? 0 }}">
                                        </div>
                                    </div>

                                    <div id="expense-{{ $vehicleIndex }}" class="tab-content2 expense-tab" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; overflow: auto;">
                                        <div class="expense-container" data-bus-id="{{ $busId }}" data-vehicle-index="{{ $vehicleIndex }}">
                                            @php
                                                $busExpenses = $expensesByBus[$busId] ?? [];
                                            @endphp
                                            
                                            @if(count($busExpenses) > 0)
                                            <table class="table table-sm table-bordered expense-table" style="font-size: 11px; margin-bottom: 5px;">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 15%; background-color: #f8f9fa; text-align: center;">日付</th>
                                                        <th style="width: 20%; background-color: #f8f9fa; text-align: center;">種別</th>
                                                        <th style="width: 12%; background-color: #f8f9fa; text-align: center;">金額</th>
                                                        <th style="width: 15%; background-color: #f8f9fa; text-align: center;">支払方法</th>
                                                        <th style="width: 8%; background-color: #f8f9fa; text-align: center;">代理店</th>
                                                        <th style="width: 10%; background-color: #f8f9fa; text-align: center;">操作</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="expense-tbody">
                                                    @php $totalExpenseAmount = 0; @endphp
                                                    @foreach($busExpenses as $expIndex => $expense)
                                                    <tr class="expense-row" data-expense-index="{{ $expIndex }}">
                                                        <td>
                                                            <input type="hidden" name="bus_assignments[{{ $vehicleIndex }}][expenses][{{ $expIndex }}][id]" value="{{ $expense->id }}">
                                                            <input type="date" class="form-control form-control-sm expense-date" 
                                                                   name="bus_assignments[{{ $vehicleIndex }}][expenses][{{ $expIndex }}][expense_date]" 
                                                                   value="{{ $expense->expense_date }}">
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm expense-type" 
                                                                    name="bus_assignments[{{ $vehicleIndex }}][expenses][{{ $expIndex }}][type_id]">
                                                                <option value="">-- 選択 --</option>
                                                                @foreach($expenseTypes ?? [] as $type)
                                                                    <option value="{{ $type->id }}" {{ $expense->type_id == $type->id ? 'selected' : '' }}>
                                                                        {{ $type->type_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm expense-amount" 
                                                                   name="bus_assignments[{{ $vehicleIndex }}][expenses][{{ $expIndex }}][amount]" 
                                                                   value="{{ intval($expense->amount) }}" step="1" min="0">
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm expense-payment" 
                                                                    name="bus_assignments[{{ $vehicleIndex }}][expenses][{{ $expIndex }}][payment_method_id]">
                                                                <option value="">-- 選択 --</option>
                                                                @foreach($paymentMethods ?? [] as $method)
                                                                    <option value="{{ $method->id }}" {{ $expense->payment_method_id == $method->id ? 'selected' : '' }}>
                                                                        {{ $method->method_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="form-check d-flex justify-content-center" style="margin: 0; min-height: 1rem;">
                                                                <input type="checkbox" class="form-check-input expense-agency" 
                                                                       name="bus_assignments[{{ $vehicleIndex }}][expenses][{{ $expIndex }}][agency_flag]" 
                                                                       value="1" {{ $expense->agency_flag ? 'checked' : '' }}
                                                                       id="expense_agency_{{ $vehicleIndex }}_{{ $expIndex }}">
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center gap-1">
                                                                <button type="button" class="btn btn-sm btn-outline-success add-expense-row">
                                                                    <i class="bi bi-plus-lg"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-danger remove-expense-row">
                                                                    <i class="bi bi-dash-lg"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @php $totalExpenseAmount += $expense->amount; @endphp
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                                                        <td colspan="4" class="text-end">合計</td>
                                                        <td class="text-end"><span class="total-expense-display">¥ {{ number_format($totalExpenseAmount) }}</span></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                            @else
                                            <div class="text-center py-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary add-first-expense-row" 
                                                        data-bus-id="{{ $busId }}" data-vehicle-index="{{ $vehicleIndex }}">
                                                    <i class="bi bi-plus-lg"></i> 立替を追加
                                                </button>
                                            </div>
                                            @endif
                                            
                                            <input type="hidden" class="total-expense-hidden" name="bus_assignments[{{ $vehicleIndex }}][expenses_total]" value="{{ $totalExpenseAmount ?? 0 }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12">
                                <table class="table table-bordered table-sm vehicle-itinerary-table" style="font-size: 0.8rem; background-color: white;" data-vehicle-table="{{ $vehicleIndex }}">
                                    <thead style="background-color: #f3f4f6; text-align: center;">
                                        <tr>
                                            <th style="width: 10%; text-align: center; background-color: #f3f4f6;">運行日</th>
                                            <th style="width: 10%; text-align: center; background-color: #f3f4f6;">開始時刻/場所</th>
                                            <th style="width: 10%; text-align: center; background-color: #f3f4f6;">終了時刻/場所</th>
                                            <th style="text-align: center; background-color: #f3f4f6;">行程</th>
                                            <th style="width: 5%; text-align: center; background-color: #f3f4f6;">選択</th>
                                            <th style="width: 180px; text-align: center; background-color: #f3f4f6;">操作</th>
                                         </thead>
                                    <tbody>
                                        @foreach($group['itineraries'] as $index => $itinerary)
                                        @php 
                                            $globalIndex = ($vehicleIndex - 1) * 100 + $index;
                                            $displayNumber = $index + 1;
                                            $itineraryBusId = $itinerary->bus_assignment_id ?? '';
                                        @endphp
                                        <tr class="itinerary-row" data-vehicle="{{ $vehicleIndex }}" data-index="{{ $globalIndex }}" data-bus-id="{{ $itineraryBusId }}" data-itinerary-id="{{ $itinerary->id }}">
                                            <td style="vertical-align: middle; text-align: center; background-color: #f9f9f9; position: relative;">
                                                <span class="row-number" style="position: absolute; top: 2px; left: 2px; color: #2563eb; font-size: 10px; font-weight: bold;">{{ $displayNumber }}</span>
                                                <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][id]" value="{{ $itinerary->id }}">
                                                <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][display_order]" value="{{ $globalIndex + 1 }}">
                                                <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][bus_assignment_id]" value="{{ $itineraryBusId }}" class="itinerary-bus-id">
                                                <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][vehicle_id]" value="{{ $itinerary->vehicle_id ?? '' }}" class="itinerary-vehicle-id">
                                                <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][driver_id]" value="{{ $itinerary->driver_id ?? $driverId }}" class="itinerary-driver-id">
                                                <input type="text" class="form-control form-control-sm border datepicker-3months" name="daily_itineraries[{{ $globalIndex }}][date]" value="{{ $itinerary->date ? \Carbon\Carbon::parse($itinerary->date)->format('Y-m-d') : '' }}" style="width: 100%; text-align: center;" placeholder="YYYY-MM-DD">
                                                <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][vehicle_group]" value="{{ $vehicleIndex }}">
                                             </td>
                                            <td style="padding: 2px;">
                                                <div class="d-flex flex-column" style="gap: 2px;">
                                                    <input type="time" class="form-control form-control-sm border" 
                                                           name="daily_itineraries[{{ $globalIndex }}][time_start]" 
                                                           value="{{ $itinerary->time_start ? \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') : '08:00' }}" 
                                                           style="width: 100%;" step="60">
                                                    <input type="text" class="form-control form-control-sm border" 
                                                           name="daily_itineraries[{{ $globalIndex }}][start_location]" 
                                                           value="{{ $itinerary->start_location ?? '' }}" 
                                                           placeholder="開始場所" style="width: 100%;">
                                                </div>
                                             </td>
                                            <td style="padding: 2px;">
                                                <div class="d-flex flex-column" style="gap: 2px;">
                                                    <input type="time" class="form-control form-control-sm border" 
                                                           name="daily_itineraries[{{ $globalIndex }}][time_end]" 
                                                           value="{{ $itinerary->time_end ? \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') : '18:00' }}" 
                                                           style="width: 100%;" step="60">
                                                    <input type="text" class="form-control form-control-sm border" 
                                                           name="daily_itineraries[{{ $globalIndex }}][end_location]" 
                                                           value="{{ $itinerary->end_location ?? '' }}" 
                                                           placeholder="終了場所" style="width: 100%;">
                                                </div>
                                             </td>
                                            <td style="vertical-align: middle; padding: 2px;">
                                                <textarea name="daily_itineraries[{{ $globalIndex }}][itinerary]" rows="2" 
                                                          class="form-control form-control-sm border" 
                                                          style="width: 100%; height: 100%; min-height: 60px;">{{ $itinerary->itinerary ?? '' }}</textarea>
                                             </td>
                                            <td style="padding: 2px; text-align: center; vertical-align: middle;">
                                                <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                                                    <input type="checkbox" class="form-check-input itinerary-select" 
                                                           id="select_itinerary_{{ $globalIndex }}" 
                                                           style="margin: 0; width: 18px; height: 18px; cursor: pointer;">
                                                </div>
                                             </td>
                                            <td style="padding: 2px; text-align: center; vertical-align: middle;">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動">
                                                        <i class="bi bi-arrow-up"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動">
                                                        <i class="bi bi-arrow-down"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除">
                                                        <i class="bi bi-dash-lg"></i>
                                                    </button>
                                                </div>
                                             </td>
                                         </tr>
                                        @endforeach
                                    </tbody>
                                 </table>
                            </div>
                        </div>
                        
                        <div class="row mt-2" style="margin-right: -5px; margin-left: -5px;">
                            <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
                                <div class="d-flex w-100 mb-1">
                                    <span class="span-label" style="min-width: 30px;">オプション</span>
                                    <div class="options-container w-100" style="display: flex; flex-wrap: wrap; gap: 8px; border: 1px solid #aaa; border-radius: 4px; padding: 4px 8px; min-height: 28px; background-color: white;">
                                        @foreach($options ?? [] as $option)
                                        <label class="d-flex align-items-center" style="cursor: pointer; font-size: 11px;">
                                            <input type="checkbox" name="bus_assignments[{{ $vehicleIndex }}][options][]" value="{{ $option->id }}" 
                                                   style="margin-right: 2px;"
                                                   {{ in_array($option->id, explode(',', $busAssignment->options ?? '')) ? 'checked' : '' }}>
                                            {{ $option->name }}
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                    
                                <div class="d-flex w-100">
                                    <span class="span-label" style="min-width: 30px;">備考</span>
                                    <textarea name="bus_assignments[{{ $vehicleIndex }}][operation_remarks]" rows="1" class="form-control form-control-sm border" placeholder="指示書に表示">{{ $busAssignment->operation_remarks ?? '' }}</textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                                <textarea name="bus_assignments[{{ $vehicleIndex }}][operation_memo]" rows="2" class="form-control form-control-sm border" style="height: 62px;" placeholder="手配メモ一">{{ $busAssignment->operation_memo ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="d-flex align-items-center gap-4 my-2">
            <div class="form-check d-flex align-items-center">
                <input type="checkbox" class="form-check-input me-2" name="ignore_operation" value="1" id="global_ignore_operation" {{ $groupInfo->ignore_operation ? 'checked' : '' }} style="width: 18px; height: 18px;">
                <label class="form-check-label" for="global_ignore_operation" style="font-size: 0.9rem;">運行無視</label>
            </div>
            <div class="form-check d-flex align-items-center">
                <input type="checkbox" class="form-check-input me-2" name="ignore_attendance" value="1" id="global_ignore_attendance" {{ $groupInfo->ignore_attendance ? 'checked' : '' }} style="width: 18px; height: 18px;">
                <label class="form-check-label" for="global_ignore_attendance" style="font-size: 0.9rem;">勤怠無視</label>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3" id="saveBtn">
                    <i class="bi bi-check-circle"></i> 確認
                </button>
                <a href="{{ route('masters.group-infos.index') }}" class="btn btn-sm btn-outline-secondary px-3 ms-2">
                    <i class="bi bi-x-circle"></i> キャンセル
                </a>
            </div>
            <div>
                <button type="button" class="btn btn-outline-danger btn-sm" 
                        onclick="if(confirm('本当にこのグループを削除しますか？\nこの操作は元に戻せません。')) { document.getElementById('deleteForm').submit(); }">
                    <i class="bi bi-trash"></i> グループ削除
                </button>
            </div>
        </div>
    </form>
    
    <form id="deleteForm" action="{{ route('masters.group-infos.destroy', $groupInfo->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
</div>


<div id="iframeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; overflow: auto;">
    <div style="position: relative; width: 100%; min-height: 100%; display: flex; justify-content: center; align-items: center; padding: 20px;">
        <div id="modalContent" style="background-color: #f3f4f6; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); width: 90%; max-width: 550px; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 16px; color: #fff; font-size: 14px; font-weight: 500; background-color: #374151;">
                <span id="modalTitle">新規グループ作成</span>
                <button onclick="closeIframeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #fff;">&times;</button>
            </div>
            <iframe id="modalIframe" src="" style="width: 100%; height: 480px; border: none; display: block;"></iframe>
        </div>
    </div>
</div>
@endsection


@push('styles')
<style>
.text-gray { color: #6b7280; font-size: 11px; }
.form-input { width: 100%; border: 1px solid #aaa; border-radius: 4px; font-size: 11px; padding: 4px 6px; height: 28px; }
.form-input-small { border: 1px solid #aaa; border-radius: 4px; padding: 4px; height: 28px; font-size: 11px; }
.checkbox { width: 12px; height: 12px; margin-right: 2px; }
.checkbox-large { width: 14px; height: 14px; margin-right: 4px; }
.label-text { color: #374151; font-size: 11px; }
.label-text-gray { color: #6b7280; font-size: 11px; }
.tab-container { position: relative; }
.tab-wrapper { display: flex; margin-bottom: -1px; }
.tab-item { font-size: 11px; padding: 6px 16px; border-radius: 4px 4px 0 0; cursor: pointer; }
.tab-item.active { background-color: white; color: #374151; border-top: 1px solid #d1d5db; border-left: 1px solid #d1d5db; border-right: 1px solid #d1d5db; border-bottom: none; z-index: 2; }
.tab-item.inactive { background-color: #f3f4f6; color: #374151; border: 1px solid #aaa; border-bottom: 1px solid #d1d5db; }
.tab-line { height: 1px; background-color: #d1d5db; width: 100%; margin-top: -1px; z-index: 1; }
.tab-content { padding-top: 4px; overflow: auto;}
.btn-primary { background-color: #2563eb; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
.btn-primary:hover { background-color: #1d4ed8; }
.btn-primary:disabled { background-color: #93c5fd; cursor: not-allowed; }
.btn-secondary { background-color: #186718; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
.btn-secondary:hover { background-color: #125112; }
.btn-danger { background-color: #dc2626; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
.btn-danger:hover { background-color: #b91c1c; }
.btn-info { background-color: #17a2b8; border: none; color: white; font-size: 12px; padding: 6px 8px; border-radius: 4px; cursor: pointer; }
.btn-info:hover { background-color: #138496; }
.btn-success { background-color: #28a745; border: none; color: white; font-size: 12px; padding: 6px 8px; border-radius: 4px; cursor: pointer; }
.btn-success:hover { background-color: #218838; }
.dashed-box { color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db; }
.label-width { width: 50px; }
.label-width-large { width: 60px; }
.label-width-copy { width: 90px; }
.input-width-date { width: 110px; }
.input-width-time { width: 80px; }
.input-width-number { width: 60px; }
.input-width-100 { width: 100px; }
.input-width-40 { width: 40px; }
.mr-2 { margin-right: 8px; }
.mr-4 { margin-right: 4px; }
.mr-5 { margin-right: 8px; }
.ml-4 { margin-left: 4px; }
.mx-1 { margin: 0 2px; }
.mx-2 { margin: 0 4px; }
.mx-3 { margin: 0 4px; }
.mb-1 { margin-bottom: 4px; }
.mb-2 { margin-bottom: 8px; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 12px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 1rem;}
.gap-4 { gap: 16px; }
.flex-1 { flex: 1; }
.d-flex { display: flex; }
.flex-wrap { flex-wrap: wrap; }
.align-items-center { align-items: center; }
.align-items-start { align-items: flex-start; }
.justify-content-between { justify-content: space-between; }
.date-container { display: flex; align-items: center; flex-wrap: wrap; gap: 2px; }
.position-relative { position: relative; }

.success-alert {
    font-size: 0.875rem;
    background-color: #d1e7dd;
    border-color: #badbcc;
    color: #0f5132;
}

.success-alert .btn-close {
    padding: 0.75rem;
}

.error-alert {
    font-size: 0.875rem;
}

.error-alert .btn-close {
    padding: 0.75rem;
}

.suggestions-container {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #aaa;
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

.card {
    border: 1px solid #999;
}
.card-body {
    color: #aaa !important;
    border-radius: var(--bs-card-border-radius);
}
.card-body input[type="checkbox"] {
    min-width: 14px;
    min-height: 14px;
}

input[type="text"],
input[type="checkbox"],
input[type="number"],
.border{
    border: 1px solid #aaa !important;
}


.locked-field {
    background-color: #f3f4f6 !important;
    cursor: not-allowed !important;
    opacity: 0.7;
}

input.locked-field, 
select.locked-field {
    pointer-events: none;
}

button.locked-field {
    opacity: 0.5;
    pointer-events: none;
}


@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

.is-invalid { border-color: #dc3545 !important; background-color: #fff8f8; }
.is-invalid:focus { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25); }
.error-message { color: #dc3545; font-size: 10px; margin-top: 2px; }
.is-invalid { animation: shake 0.2s ease-in-out; }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-3px); } 75% { transform: translateX(3px); } }

input[readonly] { background-color: #f9fafb; cursor: default; }
input[readonly]:focus { outline: none; border-color: #E5E7EB; }

.span-label { text-align: right; min-width: 70px !important; width: 70px !important; font-size: 0.8rem; margin-right: 10px; white-space: normal; word-break: keep-all;}
.card-body { background-color:#f3f4f6; font-size: 0.8rem;}
.tab-content2 {
    border: 1px #E5E7EB solid;
    border-top: 0;
    background-color: #fff;
    padding: 10px;
    height: 102px;
}
.container-fluid {
    max-width: 1600px;
}
.page-title {
    color: #374151;
    font-size: 1rem;
}
.form-control-sm, .form-select-sm {
    border-color: #E5E7EB;
    font-size: 0.8rem;
    border-radius: 4px;
}
.form-control-sm:focus, .form-select-sm:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 0.1rem rgba(37, 99, 235, 0.25);
}
.btn-sm {
    font-size: 0.8rem;
}
.badge {
    background-color: #0d6efd;
    color: #fff;
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: normal;
    padding: 6px 12px;
    border-radius: 6px;
}
.form-check-input {
    margin-top: 0;
}
.gap-3 {
    gap: 1rem;
}
.gap-4 {
    gap: 1.5rem;
}
.bg-white {
    background-color: #ffffff !important;
}
.rounded {
    border-radius: 4px !important;
}
.tab-button, .tab-button2 {
    cursor: pointer;
    transition: all 0.2s;
    outline: none;
}
.tab-button:hover, .tab-button2:hover {
    background-color: #f9fafb !important;
}
.tab-button.active, .tab-button2.active {
    background-color: white !important;
    border-bottom-color: white !important;
    color: #374151 !important;
    font-weight: 500;
}
.tab-button:not(.active), .tab-button2:not(.active) {
    background-color: #F3F4F6 !important;
    border-bottom-color: #aaa !important;
    color: #6B7280 !important;
}
.table {
    margin-bottom: 6px;
}
.table th {
    font-weight: 500;
    color: #aaa;
    border-color: #E5E7EB;
}
.table td {
    border-color: #E5E7EB;
    padding: 0.5rem;
}
.vehicle-detail-card {
    margin-bottom: 1rem;
    border: 1px solid #aaa;
}
.vehicle-detail-card .card-header {
    background-color: #141c28;
}
.vehicle-detail-card .card-header h6 span {
    color: #a0aec0;
    font-weight: normal;
}

.row-number {
    position: absolute;
    top: 2px;
    left: 2px;
    color: #2563eb;
    font-size: 10px;
    font-weight: bold;
    z-index: 1;
}

.merge-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.merge-operation-id.is-invalid {
    border-color: #dc3545 !important;
    background-color: #fff8f8;
}

.btn-outline-secondary:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.flatpickr-calendar {
    border: 1px solid #ddd !important;
    border-radius: 6px !important;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12) !important;
    font-family: inherit !important;
    font-size: 11px !important;
    overflow: hidden !important;
}

.flatpickr-calendar.multiMonth {
    width: 516px !important;
    max-width: 95vw !important;
}

.flatpickr-calendar.multiMonth .flatpickr-innerContainer {
    width: 100% !important;
}

.flatpickr-calendar.multiMonth .flatpickr-months {
    display: flex !important;
}

.flatpickr-calendar.multiMonth .flatpickr-month {
    flex: 1 !important;
}

.flatpickr-calendar.multiMonth .flatpickr-month:not(:last-child) {
    border-right: 1px solid #e9ecef !important;
}

.flatpickr-months {
    background: linear-gradient(135deg, #1f3241 0%, #2d4a5e 100%) !important;
    border-radius: 6px 6px 0 0 !important;
    display: flex !important;
}

.flatpickr-month {
    height: 28px !important;
    padding-right: 0 !important;
}

.flatpickr-current-month {
    padding: 3px 0 0 0 !important;
}

.flatpickr-current-month .flatpickr-monthDropdown-months {
    font-weight: 600 !important;
    color: #fff !important;
    font-size: 11px !important;
}

.flatpickr-current-month .numInputWrapper span {
    color: #fff !important;
}

.flatpickr-current-month input.cur-year {
    color: #fff !important;
    font-weight: 600 !important;
    font-size: 11px !important;
}

.flatpickr-months .flatpickr-month,
.flatpickr-months .flatpickr-next-month,
.flatpickr-months .flatpickr-prev-month {
    color: #fff !important;
    fill: #fff !important;
}

.flatpickr-months .flatpickr-next-month:hover svg,
.flatpickr-months .flatpickr-prev-month:hover svg {
    fill: #ffc107 !important;
}

.flatpickr-months .flatpickr-next-month,
.flatpickr-months .flatpickr-prev-month {
    width: 20px !important;
    height: 20px !important;
    padding: 2px !important;
}

.flatpickr-weekdays {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    margin: 0 !important;
}

.flatpickr-weekday {
    color: #495057 !important;
    font-weight: 600 !important;
    font-size: 10px !important;
    padding: 1px 0 !important;
}

.flatpickr-days {
    border: none !important;
    padding: 0 !important;
}

.flatpickr-day {
    color: #374151 !important;
    border-radius: 2px !important;
    margin: 0 !important;
    border: 1px solid transparent !important;
    max-width: 24px !important;
    width: 24px !important;
    height: 22px !important;
    line-height: 20px !important;
    font-size: 10px !important;
}

.flatpickr-day:hover {
    background: #e0f2fe !important;
    border-color: #2563eb !important;
    color: #2563eb !important;
}

.flatpickr-day.selected {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #fff !important;
    font-weight: 600 !important;
}

.flatpickr-day.selected:hover {
    background: #1d4ed8 !important;
}

.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #fff !important;
}

.flatpickr-day.inRange {
    background: #dbeafe !important;
    border-color: transparent !important;
    color: #1e40af !important;
}

.flatpickr-day.today {
    border-color: #ffc107 !important;
    background: #fffbeb !important;
    color: #374151 !important;
}

.flatpickr-day.today:hover {
    background: #fef3c7 !important;
    border-color: #f59e0b !important;
    color: #374151 !important;
}

.flatpickr-months .flatpickr-month {
    background: transparent !important;
}

span.flatpickr-weekday {
    background: #f8f9fa !important;
}

.flatpickr-calendar.showTimeInput.hasTime .flatpickr-time {
    border-top: 1px solid #e9ecef !important;
}

.flatpickr-calendar.multiMonth .dayContainer {
    width: 168px !important;
    min-width: 168px !important;
    max-width: 168px !important;
    position: relative !important;
}

.month-wrapper {
    flex: 1 !important;
    position: relative !important;
    padding: 2px !important;
    height: 135px !important;
}

.month-wrapper:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 1px;
    background-color: #e9ecef;
}

.flatpickr-calendar.multiMonth .flatpickr-days {
    display: flex !important;
    position: relative;
    width: 514px !important;
}

.flatpickr-calendar.multiMonth .flatpickr-days .dayContainer {
    padding: 0 !important;
}

.flatpickr-calendar.multiMonth .flatpickr-rContainer {
    width: 514px !important;
}

.compensation-table td {
    padding: 1px;
    font-size: 10px;
    vertical-align: middle;
}

.compensation-table input,
.compensation-table select {
    padding: 1px 5px;
    font-size: 10px;
    line-height: 120%;
    min-height: 20px;
    border: 1px #aaa solid !important;
}

.compensation-table button {
    padding: 0 3px !important;
}

.compensation-table input[type="number"] {
    -webkit-appearance: none;
    -moz-appearance: textfield;
    appearance: textfield;
}

.compensation-table input[type="number"]::-webkit-inner-spin-button,
.compensation-table input[number]::-webkit-outer-spin-button {
    display: none;
}

.compensation-price,
.compensation-qty,
.compensation-amount {
    text-align: right;
}




.expense-table td {
    padding: 1px;
    font-size: 10px;
    vertical-align: middle;
}

.expense-table input,
.expense-table select {
    padding: 1px 5px;
    font-size: 10px;
    line-height: 120%;
    min-height: 20px;
    border: 1px #aaa solid !important;
}

.expense-table button {
    padding: 0 3px !important;
}

.expense-table input[type="number"] {
    -webkit-appearance: none;
    -moz-appearance: textfield;
    appearance: textfield;
}

.expense-table input[type="number"]::-webkit-inner-spin-button,
.expense-table input[number]::-webkit-outer-spin-button {
    display: none;
}

.expense-amount {
    text-align: right;
}

.expense-agency-label {
    font-size: 9px;
    margin-left: 2px;
}
.expense-agency {
    width: 16px;
    height: 16px;
    min-height: 16px !important;
    margin: 0;
    cursor: pointer;
    flex-shrink: 0;
}
.add-expense-row,
.remove-expense-row {
    padding: 2px 6px;
    font-size: 10px;
}

.add-first-expense-row {
    font-size: 11px;
    padding: 4px 12px;
}

.expense-container .add-first-expense-row {
    font-size: 12px;
}

#operation-details-container .tab-content2.expense-tab {
    padding: 6px;
    max-height: 260px;
    overflow-y: auto;
    min-height: 102px;
}







@media (max-width: 768px) {
    body {
        padding: 2% !important;
        overflow-x: hidden !important;
    }
    
    .container-fluid {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    .page-title {
        font-size: 0.9rem !important;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-3 {
        flex-direction: column !important;
        gap: 10px !important;
        align-items: stretch !important;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-3 .d-flex {
        justify-content: space-between !important;
    }
    
    .card-body .row {
        flex-direction: column !important;
    }
    
    .card-body .row > [class*="col-"],
    .card-body .row > [style*="width:60%"],
    .card-body .row > [style*="width:40%"] {
        width: 100% !important;
        margin-bottom: 10px !important;
    }
    
    .d-flex.w-100.gap-2 {
        flex-direction: column !important;
        gap: 8px !important;
    }
    
    .d-flex.w-100.gap-2 > .d-flex,
    .d-flex.w-100.gap-2 > div {
        width: 100% !important;
    }
    
    .w-50 {
        width: 100% !important;
    }
    
    .span-label {
        min-width: 55px !important;
        width: 55px !important;
        font-size: 0.7rem !important;
        margin-right: 8px !important;
    }
    
    .form-control-sm, .form-select-sm {
        font-size: 0.7rem !important;
        padding: 4px 6px !important;
        height: auto !important;
    }
    
    .btn-sm {
        font-size: 0.7rem !important;
        padding: 4px 8px !important;
    }
    
    .tab-wrapper {
        flex-wrap: wrap !important;
    }
    
    .tab-item {
        flex: 1 !important;
        text-align: center !important;
        padding: 6px 4px !important;
        font-size: 0.65rem !important;
    }
    
    .tab-content {
        padding: 6px !important;
    }
    
    .tab-content2 {
        padding: 6px !important;
    }
    
    .vehicle-detail-card .card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
        padding: 12px !important;
    }
    
    .vehicle-detail-card .card-header h6 {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-bottom: 0 !important;
        width: 100% !important;
        gap: 8px !important;
    }
    
    .vehicle-detail-card .card-header h6 span:first-child {
        font-size: 0.75rem !important;
    }
    
    .vehicle-detail-card .card-header h6 span:nth-child(2) {
        font-size: 0.6rem !important;
        padding: 2px 6px !important;
    }
    
    .vehicle-detail-card .card-header h6 .btn-pdf-export {
        padding: 2px 6px !important;
        font-size: 0.6rem !important;
    }
    
    .vehicle-detail-card .card-header h6 .btn-pdf-export i {
        font-size: 0.6rem !important;
    }
    
    .card-header-actions {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 15px !important;
        margin-left: 0 !important;
    }
    
    .card-header-actions > .form-check {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
    }
    
    .card-header-actions > .form-check .form-check-label {
        font-size: 0.7rem !important;
        color: #fff !important;
        white-space: nowrap !important;
    }
    
    .card-header-actions > .form-check .form-check-input {
        margin: 0 !important;
    }
    
    .card-header-btns {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 6px !important;
        width: 100% !important;
    }
    
    .card-header-btns .merge-operation-id {
        width: 70px !important;
        font-size: 0.65rem !important;
        padding: 3px 4px !important;
    }
    
    .card-header-btns .btn {
        padding: 3px 6px !important;
        font-size: 0.6rem !important;
    }
    
    .vehicle-itinerary-table th, 
    .vehicle-itinerary-table td {
        font-size: 0.65rem !important;
        padding: 4px 2px !important;
    }
    
    .vehicle-itinerary-table td:last-child,
    .vehicle-itinerary-table th:last-child {
        width: auto !important;
        min-width: 90px !important;
        white-space: nowrap !important;
    }
    
    .vehicle-itinerary-table td:last-child .d-flex {
        gap: 2px !important;
        justify-content: center !important;
    }
    
    .vehicle-itinerary-table td:last-child .btn-sm {
        padding: 2px 4px !important;
        font-size: 0.5rem !important;
        min-width: 22px !important;
        height: auto !important;
    }
    
    .vehicle-itinerary-table td:last-child .btn-sm i {
        font-size: 0.45rem !important;
    }
    
    .vehicle-itinerary-table textarea {
        min-height: 40px !important;
        font-size: 0.6rem !important;
    }
    
    .vehicle-itinerary-table input {
        font-size: 0.6rem !important;
        padding: 2px 3px !important;
    }
    
    .options-container {
        gap: 6px !important;
    }
    
    .options-container label {
        font-size: 0.6rem !important;
    }
    
    .compensation-table th,
    .compensation-table td,
    .expense-table th,
    .expense-table td {
        font-size: 9px !important;
        padding: 2px 1px !important;
    }
    
    .compensation-table input,
    .compensation-table select,
    .expense-table input,
    .expense-table select {
        font-size: 9px !important;
        padding: 1px 3px !important;
    }
    
    .compensation-table button,
    .expense-table button {
        padding: 1px 3px !important;
    }
    
    .compensation-table .btn-sm,
    .expense-table .btn-sm {
        padding: 1px 3px !important;
        font-size: 8px !important;
    }
    
    .compensation-table .btn-sm i,
    .expense-table .btn-sm i {
        font-size: 8px !important;
    }
    
    #operation-details-container .tab-content2.expense-tab {
        max-height: 200px !important;
        min-height: auto !important;
        padding: 6px !important;
    }
    
    .d-flex.align-items-center.gap-4.my-2 {
        align-items: flex-start !important;
        gap: 8px !important;
    }
    
    
    .btn-primary, .btn-outline-secondary, .btn-outline-danger {
        padding: 5px 12px !important;
        font-size: 0.7rem !important;
    }
    
    
    
    
    .yxxx-l {
        width: 100% !important;
        padding-right: 5% !important;
        padding-left: 5% !important;
    }
    
    .yxxx-l input {
        margin: 0 !important;
    }
    
    .yxxx-l .d-flex.w-100 {
        flex-direction: column !important;
        gap: 12px !important;
    }
    
    .yxxx-l .row.mb-1 {
        margin-bottom: 0 !important;
    }
    
    .yxxx-l .row.mb-1 .col-md-12 {
        padding: 0 !important;
    }
    
    .yxxx-l .d-flex[style*="width: 50%; gap: 8px;"] {
        width: 100% !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        gap: 8px !important;
    }
    
    .yxxx-l .d-flex[style*="width: 50%; gap: 8px;"] > .d-flex {
        flex: 1 !important;
        width: auto !important;
        flex-direction: row !important;
    }
    
    .yxxx-l .d-flex[style*="width: 50%;"] {
        width: 100% !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        gap: 8px !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 25%;"] {
        width: 50% !important;
        flex: 1 !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 35%;"] {
        width: 70% !important;
        flex: 1 !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 15%;"] {
        width: 30% !important;
        flex: 1 !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 50%;"] {
        width: 100% !important;
        flex-direction: row !important;
        justify-content: space-between !important;
    }
    
    .yxxx-l .d-flex.align-items-center {
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }
    
    .yxxx-l .d-flex.align-items-center .span-label {
        flex-shrink: 0 !important;
        width: 65px !important;
        min-width: 65px !important;
        font-size: 0.7rem !important;
        white-space: nowrap !important;
    }
    
    .yxxx-l .d-flex.align-items-center .form-control-sm,
    .yxxx-l .d-flex.align-items-center .form-select-sm,
    .yxxx-l .d-flex.align-items-center .border,
    .yxxx-l .d-flex.align-items-center .w-100 {
        flex: 1 !important;
        width: auto !important;
        min-width: 0 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    
    .yxxx-l .d-flex.align-items-center .d-flex.gap-1 {
        flex: 1 !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }
    
    .yxxx-l .d-flex.align-items-center .d-flex.gap-1 input {
        flex: 1 !important;
        min-width: 0 !important;
        width: auto !important;
    }
    
    .yxxx-l .copy-stepcar-btn {
        flex-shrink: 0 !important;
        margin-left: 6px !important;
        white-space: nowrap !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 70%"] {
        width: 70% !important;
        flex-shrink: 0 !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 30%"] {
        width: 30% !important;
        flex-shrink: 0 !important;
    }
    
    .yxxx-l .form-check-input {
        flex-shrink: 0 !important;
    }
    
    .yxxx-l select.form-select-sm {
        flex: 1 !important;
        min-width: 0 !important;
        width: auto !important;
    }
    
    
    .vehicle-representative {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 12px !important;
        width: 100% !important;
    }
    
    .vehicle-representative > div:first-child {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        flex: 2 !important;
        min-width: 0 !important;
    }
    
    .vehicle-representative > div:first-child .span-label {
        flex-shrink: 0 !important;
        width: 45px !important;
        min-width: 45px !important;
    }
    
    .vehicle-representative > div:first-child .form-select-sm {
        flex: 1 !important;
        width: auto !important;
        min-width: 0 !important;
    }
    
    .representative-1,
    .representative-2 {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        flex: 1 !important;
        min-width: 0 !important;
    }
    
    .representative-1 .span-label {
        flex-shrink: 0 !important;
        width: 45px !important;
        min-width: 45px !important;
        font-size: 0.7rem !important;
        white-space: nowrap !important;
    }
    
    .representative-1 .form-control-sm,
    .representative-2 .form-control-sm {
        flex: 1 !important;
        width: auto !important;
        min-width: 0 !important;
    }
    
    .representative-2 .ms-2 {
        margin-left: 4px !important;
    }


    

    .adult-child {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }
    
    .adult-child .span-label {
        flex-shrink: 0 !important;
        width: 55px !important;
        min-width: 55px !important;
        font-size: 0.7rem !important;
        white-space: nowrap !important;
    }
    
    .adult-child .d-flex.gap-1 {
        flex: 1 !important;
        display: flex !important;
        flex-direction: row !important;
        gap: 6px !important;
        flex-wrap: nowrap !important;
    }
    
    .adult-child .d-flex.gap-1 input {
        flex: 1 !important;
        min-width: 0 !important;
        width: auto !important;
        margin: 0 !important;
    }
    
    .yxxx-l .d-flex.align-items-center .form-control-sm[id^="step_car_"] {
        flex: 4 !important;
    }
    
    
    .yxxx-l .d-flex.align-items-center[style*="width: 50%; gap: 8px;"] > .d-flex.align-items-center[style*="width: 70%;"] {
        flex: 2 !important;
    }

}

@media (max-width: 480px) {
    .card-header-actions > .form-check {
        flex: 0 0 auto !important;
    }
    
    .card-header-actions > .form-check .form-check-label {
        font-size: 0.6rem !important;
    }
    
    .vehicle-itinerary-table th, 
    .vehicle-itinerary-table td {
        font-size: 0.55rem !important;
        padding: 3px 1px !important;
    }
    
    .span-label {
        min-width: 50px !important;
        width: 50px !important;
        font-size: 0.65rem !important;
    }
    
    .tab-item {
        font-size: 0.6rem !important;
        padding: 4px 2px !important;
    }
    
    .compensation-table th,
    .compensation-table td,
    .expense-table th,
    .expense-table td {
        font-size: 8px !important;
    }
    
    .compensation-table th:nth-child(2),
    .compensation-table td:nth-child(2),
    .expense-table th:nth-child(2),
    .expense-table td:nth-child(2) {
        max-width: 70px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }
    
    .compensation-table input,
    .compensation-table select,
    .expense-table input,
    .expense-table select {
        font-size: 8px !important;
        padding: 1px 2px !important;
    }
    
    .vehicle-detail-card .card-header h6 button {
        padding: 2px 6px !important;
        font-size: 0.6rem !important;
    }
    
    .btn-primary, .btn-outline-secondary, .btn-outline-danger {
        padding: 4px 10px !important;
        font-size: 0.65rem !important;
    }
    
    .yxxx-l .d-flex.align-items-center {
        flex-wrap: wrap !important;
        gap: 8px !important;
    }
    
    .yxxx-l .d-flex.align-items-center .span-label {
        margin-bottom: 4px !important;
    }
    
    .yxxx-l .d-flex.align-items-center .form-control-sm,
    .yxxx-l .d-flex.align-items-center .form-select-sm,
    .yxxx-l .d-flex.align-items-center .border {
        width: 100% !important;
    }
    
    
    .yxxx-l .d-flex[style*="width: 50%; gap: 8px;"] {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
    }
    
    .yxxx-l .d-flex[style*="width: 50%; gap: 8px;"] > .d-flex {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 50%;"] .span-label {
        flex-shrink: 0 !important;
        width: 60px !important;
        min-width: 60px !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 50%;"] .d-flex.gap-1 {
        flex: 1 !important;
        display: flex !important;
        flex-direction: row !important;
        gap: 6px !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 50%;"] .d-flex.gap-1 input {
        flex: 1 !important;
        min-width: 0 !important;
        width: 50% !important;
    }
    
    .yxxx-l .d-flex.align-items-center[style*="width: 50%;"] .w-100 {
        flex: 1 !important;
        width: auto !important;
        min-width: 0 !important;
    }

}
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let startDateValue = null;
    let endDateValue = null;
    
    
    document.querySelectorAll('.vehicle-itinerary-table .datepicker-3months, #itinerary-table .datepicker-3months').forEach(function(el) {
        if (!el._flatpickr) {
            flatpickr(el, {
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
        }
    });
    

    
    const startDatePicker = flatpickr('#start_date', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
            if (startDateValue) {
                instance.redraw();
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                startDateValue = selectedDates[0];
                endDatePicker.setDate(selectedDates[0]);
                endDatePicker.open();
                endDatePicker.set('minDate', selectedDates[0]);
                setTimeout(function() {
                    endDatePicker.redraw();
                    instance.redraw();
                }, 10);
            } else {
                startDateValue = null;
                endDatePicker.set('minDate', null);
                endDatePicker.redraw();
                instance.redraw();
            }
        },
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const dayDate = dayElem.dateObj;
            if (!dayDate) return;
            
            const dayDateStr = dayDate.toDateString();
            
            if (startDateValue && dayDateStr === startDateValue.toDateString()) {
                dayElem.classList.remove('flatpickr-disabled');
                dayElem.classList.add('start-range-highlight');
            }
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
            if (startDateValue) {
                instance.redraw();
            }
        }
    });
    
    const endDatePicker = flatpickr('#end_date', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        minDate: startDatePicker.input.value || null,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
            if (startDateValue) {
                setTimeout(function() {
                    instance.redraw();
                }, 10);
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                endDateValue = selectedDates[0];
            } else {
                endDateValue = null;
            }
            instance.redraw();
        },
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const dayDate = dayElem.dateObj;
            if (!dayDate) return;
            
            const dayDateStr = dayDate.toDateString();
            
            if (startDateValue && dayDateStr === startDateValue.toDateString()) {
                dayElem.classList.remove('flatpickr-disabled');
                dayElem.classList.add('start-range-highlight');
            }
            
            if (endDateValue && dayDateStr === endDateValue.toDateString()) {
                dayElem.classList.remove('flatpickr-disabled');
                dayElem.classList.add('end-range-highlight');
            }
            
            if (startDateValue && endDateValue && dayDate) {
                const startTime = startDateValue.getTime();
                const endTime = endDateValue.getTime();
                const dayTime = dayDate.getTime();
                
                if (dayTime > startTime && dayTime < endTime) {
                    dayElem.classList.add('in-range-highlight');
                }
            }
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
            if (startDateValue) {
                instance.redraw();
            }
        }
    });
    
    const vehicles = @json($vehicles ?? []);
    const guides = @json($guides ?? []);
    const drivers = @json($drivers ?? []);
    const agencies = @json($agencies ?? []);
    const branches = @json($branches ?? []);
    const categories = @json($reservationCategories ?? []);
    
    let deletedItineraryIds = [];

    // async function submitForm(event) {
    //     event.preventDefault();
        
    //     const disabledFields = document.querySelectorAll('[disabled]');
    //     disabledFields.forEach(field => {
    //         field.removeAttribute('disabled');
    //         field.setAttribute('data-temp-disabled', 'true');
    //     });
        
    //     if (!validateDateRange()) {
    //         const tempDisabled = document.querySelectorAll('[data-temp-disabled="true"]');
    //         tempDisabled.forEach(field => {
    //             field.setAttribute('disabled', 'disabled');
    //             field.removeAttribute('data-temp-disabled');
    //         });
    //         return false;
    //     }
        
    //     let hasError = false;
        
    //     // document.querySelectorAll('.vehicle-select').forEach(selectField => {
    //     //     const vehicleIndex = selectField.id.replace('vehicle_select_', '');
            
    //     //     const card = selectField.closest('.card');
            
    //     //     if (card) {
    //     //         const cardBusId = card.getAttribute('data-bus-id') || '';
    //     //         const rows = card.querySelectorAll('.itinerary-row');
    //     //         rows.forEach(row => {
    //     //             const rowBusIdField = row.querySelector('.itinerary-bus-id');
    //     //             if (rowBusIdField && rowBusIdField.value === cardBusId) {
    //     //                 const vehicleIdInput = row.querySelector('.itinerary-vehicle-id');
    //     //                 if (vehicleIdInput) {
    //     //                     vehicleIdInput.value = selectField.value || '';
    //     //                 }
    //     //             }
    //     //         });
    //     //     }
    //     // });
        
    //     // document.querySelectorAll('.driver-select').forEach(selectField => {
    //     //     const card = selectField.closest('.card');
            
    //     //     if (card) {
    //     //         const cardBusId = card.getAttribute('data-bus-id');
    //     //         const rows = card.querySelectorAll('.itinerary-row');
    //     //         rows.forEach(row => {
    //     //             const rowBusIdField = row.querySelector('.itinerary-bus-id');
    //     //             if (rowBusIdField && rowBusIdField.value === cardBusId) {
    //     //                 const driverIdInput = row.querySelector('.itinerary-driver-id');
    //     //                 if (driverIdInput) {
    //     //                     driverIdInput.value = selectField.value || '';
    //     //                 }
    //     //             }
    //     //         });
    //     //     }
    //     // });
    
    //     // document.querySelectorAll('.guide-select').forEach(selectField => {
    //     //     const card = selectField.closest('.card');
            
    //     //     if (card) {
    //     //         const cardBusId = card.getAttribute('data-bus-id');
    //     //         const rows = card.querySelectorAll('.itinerary-row');
    //     //         rows.forEach(row => {
    //     //             const rowBusIdField = row.querySelector('.itinerary-bus-id');
    //     //             if (rowBusIdField && rowBusIdField.value === cardBusId) {
    //     //                 const guideIdInput = row.querySelector('.itinerary-guide-id');
    //     //                 if (guideIdInput) {
    //     //                     guideIdInput.value = selectField.value || '';
    //     //                 }
    //     //             }
    //     //         });
    //     //     }
    //     // });
        
    //     if (hasError) {
    //         const submitBtn = document.getElementById('saveBtn');
    //         submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> 保存';
    //         submitBtn.disabled = false;
    //         const tempDisabled = document.querySelectorAll('[data-temp-disabled="true"]');
    //         tempDisabled.forEach(field => {
    //             field.setAttribute('disabled', 'disabled');
    //             field.removeAttribute('data-temp-disabled');
    //         });
    //         return false;
    //     }
        
    //     document.querySelectorAll('input[name*="[vehicle_type_spec_check]"]').forEach(cb => {
    //         if (!cb.checked) {
    //             const hidden = document.createElement('input');
    //             hidden.type = 'hidden';
    //             hidden.name = cb.name;
    //             hidden.value = '0';
    //             cb.parentNode.appendChild(hidden);
    //         }
    //     });
        
    //     const form = document.getElementById('editForm');
    //     const formData = new FormData(form);
    //     formData.append('_method', 'PUT');
        
    //     if (deletedItineraryIds.length > 0) {
    //         deletedItineraryIds.forEach((id, index) => {
    //             formData.append(`deleted_itineraries[${index}]`, id);
    //         });
    //     }
        
    //     const submitBtn = document.getElementById('saveBtn');
    //     const originalText = submitBtn.innerHTML;
    //     submitBtn.innerHTML = '保存中...';
    //     submitBtn.disabled = true;
        
    //     removeErrorHighlights();
        
    //     try {
    //         const response = await fetch(form.action, {
    //             method: 'POST',
    //             headers: {
    //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
    //                 'X-Requested-With': 'XMLHttpRequest',
    //                 'Accept': 'application/json'
    //             },
    //             body: formData
    //         });
            
    //         const text = await response.text();
            
    //         let data;
    //         try {
    //             data = JSON.parse(text);
    //         } catch (e) {
    //             alert('サーバーからの応答が不正です: ' + text.substring(0, 100));
    //             submitBtn.innerHTML = originalText;
    //             submitBtn.disabled = false;
    //             const tempDisabled = document.querySelectorAll('[data-temp-disabled="true"]');
    //             tempDisabled.forEach(field => {
    //                 field.setAttribute('disabled', 'disabled');
    //                 field.removeAttribute('data-temp-disabled');
    //             });
    //             return;
    //         }
            
    //         if (data.success) {
    //             showSuccessMessage(data.message || '保存しました。');
    //             window.scrollTo(0, 0);
                
    //             submitBtn.innerHTML = originalText;
    //             submitBtn.disabled = false;
                
    //             const tempDisabled = document.querySelectorAll('[data-temp-disabled="true"]');
    //             tempDisabled.forEach(field => {
    //                 field.setAttribute('disabled', 'disabled');
    //                 field.removeAttribute('data-temp-disabled');
    //             });
                
    //             if (data.bus_assignments) {
    //                 updateBusAssignmentsData(data.bus_assignments);
    //             }
    //         } else {
    //             submitBtn.innerHTML = originalText;
    //             submitBtn.disabled = false;
    //             showErrorMessage(data.message || '保存中にエラーが発生しました。');
    //             window.scrollTo(0, 0);
    //             const tempDisabled = document.querySelectorAll('[data-temp-disabled="true"]');
    //             tempDisabled.forEach(field => {
    //                 field.setAttribute('disabled', 'disabled');
    //                 field.removeAttribute('data-temp-disabled');
    //             });
    //         }
    //     } catch (error) {
    //         submitBtn.innerHTML = originalText;
    //         submitBtn.disabled = false;
    //         alert('通信エラーが発生しました: ' + error.message);
    //         const tempDisabled = document.querySelectorAll('[data-temp-disabled="true"]');
    //         tempDisabled.forEach(field => {
    //             field.setAttribute('disabled', 'disabled');
    //             field.removeAttribute('data-temp-disabled');
    //         });
    //     }
        
    //     return false;
    // }
    
    async function submitForm(event) {
        event.preventDefault();
        
        const disabledFields = document.querySelectorAll('[disabled]');
        disabledFields.forEach(field => {
            field.removeAttribute('disabled');
            field.setAttribute('data-temp-disabled', 'true');
        });
        
        if (!validateDateRange()) {
            const tempDisabled = document.querySelectorAll('[data-temp-disabled="true"]');
            tempDisabled.forEach(field => {
                field.setAttribute('disabled', 'disabled');
                field.removeAttribute('data-temp-disabled');
            });
            return false;
        }
        
        document.querySelectorAll('input[name*="[vehicle_type_spec_check]"]').forEach(cb => {
            if (!cb.checked) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = cb.name;
                hidden.value = '0';
                cb.parentNode.appendChild(hidden);
            }
        });
        
        if (deletedItineraryIds.length > 0) {
            deletedItineraryIds.forEach((id, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `deleted_itineraries[${index}]`;
                input.value = id;
                document.getElementById('editForm').appendChild(input);
            });
        }
        
        document.getElementById('editForm').submit();
    }
    

    function showSuccessMessage(message) {
        const existingAlert = document.querySelector('.alert-success');
        if (existingAlert) existingAlert.remove();
        
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show py-2 mb-3 success-alert" role="alert">
                <i class="bi bi-check-circle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        const header = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-3');
        header.insertAdjacentHTML('afterend', alertHtml);
        
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 3000);
    }

    function showErrorMessage(message) {
        const existingAlert = document.querySelector('.alert-danger');
        if (existingAlert) existingAlert.remove();
        
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show py-2 mb-3 error-alert" role="alert">
                <i class="bi bi-exclamation-triangle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        const header = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-3');
        header.insertAdjacentHTML('afterend', alertHtml);
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function highlightErrors(errors) {
        for (const field in errors) {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                
                const existingError = input.parentNode.querySelector('.error-message');
                if (existingError) existingError.remove();
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = errors[field][0];
                input.parentNode.appendChild(errorDiv);
            }
        }
    }

    function removeErrorHighlights() {
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.error-message').forEach(el => {
            el.remove();
        });
    }

function updateBusDetailClickHandler(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const btn = this;
    const card = btn.closest('.card');
    const groupId = {{ $groupInfo->id }};
    const busId = card.getAttribute('data-bus-id');
    
    const vehicleSelect = card.querySelector('.vehicle-select');
    const driverSelect = card.querySelector('.driver-select');
    const guideSelect = card.querySelector('.guide-select');
    
    const vehicleId = vehicleSelect ? vehicleSelect.value : '';
    const driverId = driverSelect ? driverSelect.value : '';
    const guideId = guideSelect ? guideSelect.value : '';
    
    if (!vehicleId) {
        alert('車両を選択してください。');
        return;
    }
    
    const originalText = btn.innerHTML;
    btn.innerHTML = '更新中...';
    btn.disabled = true;
    
    const itineraries = [];
    const rows = card.querySelectorAll('tbody tr.itinerary-row');
    
    
    rows.forEach((row, index) => {
        const dateInput = row.querySelector('input[name*="[date]"]');
        let dateValue = dateInput ? dateInput.value : '';
        if (dateValue && dateValue.includes(' ')) {
            dateValue = dateValue.split(' ')[0];
        }
        
        let rowVehicleId = row.querySelector('.itinerary-vehicle-id')?.value;
        if (!rowVehicleId || rowVehicleId === '0') {
            rowVehicleId = vehicleId;
        }
        
        const timeStartInput = row.querySelector('input[name*="[time_start]"]');
        const timeEndInput = row.querySelector('input[name*="[time_end]"]');
        const startLocationInput = row.querySelector('input[name*="[start_location]"]');
        const endLocationInput = row.querySelector('input[name*="[end_location]"]');
        const itineraryTextarea = row.querySelector('textarea[name*="[itinerary]"]');
        
        const itineraryId = row.getAttribute('data-itinerary-id') || '';
        
        itineraries.push({
            id: itineraryId,
            date: dateValue,
            time_start: timeStartInput ? timeStartInput.value : '08:00',
            time_end: timeEndInput ? timeEndInput.value : '18:00',
            start_location: startLocationInput ? startLocationInput.value : '',
            end_location: endLocationInput ? endLocationInput.value : '',
            itinerary: itineraryTextarea ? itineraryTextarea.value : '',
            vehicle_id: rowVehicleId,
            driver_id: driverId,
            guide_id: guideId,
            bus_assignment_id: busId
        });
    });
    
    
    const busData = {
        bus_id: busId,
        vehicle_id: vehicleId,
        driver_id: driverId,
        guide_id: guideId,
        vehicle_number: card.querySelector('input[name*="[vehicle_number]"]')?.value || '',
        step_car: card.querySelector('input[name*="[step_car]"]')?.value || '',
        adult_count: card.querySelector('input[name*="[adult_count]"]')?.value || 0,
        child_count: card.querySelector('input[name*="[child_count]"]')?.value || 0,
        guide_count: card.querySelector('input[name*="[guide_count]"]')?.value || 0,
        other_count: card.querySelector('input[name*="[other_count]"]')?.value || 0,
        luggage_count: card.querySelector('input[name*="[luggage_count]"]')?.value || 0,
        vehicle_type_spec_check: card.querySelector('input[name*="[vehicle_type_spec_check]"]')?.checked ? 1 : 0,
        temporary_driver: card.querySelector('input[name*="[temporary_driver]"]')?.checked ? 1 : 0,
        accompanying: card.querySelector('input[name*="[accompanying]"]')?.value || '',
        representative: card.querySelector('input[name*="[representative]"]')?.value || '',
        representative_phone: card.querySelector('input[name*="[representative_phone]"]')?.value || '',
        attention: card.querySelector('input[name*="[attention]"]')?.value || '',
        operation_remarks: card.querySelector('textarea[name*="[operation_remarks]"]')?.value || '',
        operation_memo: card.querySelector('textarea[name*="[operation_memo]"]')?.value || '',
        operation_basic_remarks: card.querySelector('textarea[name*="[operation_basic_remarks]"]')?.value || '',
        doc_remarks: card.querySelector('textarea[name*="[doc_remarks]"]')?.value || '',
        history_remarks: card.querySelector('textarea[name*="[history_remarks]"]')?.value || '',
        lock_arrangement: card.querySelector('input[name*="[lock_arrangement]"]')?.checked ? 1 : 0,
        status_sent: card.querySelector('input[name*="[status_sent]"]')?.checked ? 1 : 0,
        status_finalized: card.querySelector('input[name*="[status_finalized]"]')?.checked ? 1 : 0,
        itineraries: itineraries,
        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
    };
    
    fetch(`/masters/group-infos/${groupId}/update-bus-assignment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': busData._token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(busData)
    })
    .then(async response => {
        const text = await response.text();
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert('運行詳細を更新しました。');
                location.reload();
            } else {
                alert('エラー: ' + data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (e) {
            console.error('JSON解析エラー:', e);
            alert('サーバーからの応答が不正です。');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Fetchエラー:', error);
        alert('更新中にエラーが発生しました: ' + error.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

    function addClickHandler(e) {
        e.preventDefault();
        e.stopPropagation();
        addRowAfter(this);
    }

    function deleteClickHandler(e) {
        e.preventDefault();
        e.stopPropagation();
        deleteRow(this);
    }

    function moveUpClickHandler(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = this;
        const row = btn.closest('tr.itinerary-row');
        const prevRow = row.previousElementSibling;
        
        if (prevRow && !prevRow.classList.contains('no-data-row')) {
            const table = row.closest('table');
            row.parentNode.insertBefore(row, prevRow);
            reindexRows(table);
            updateMoveButtons(table);
        }
    }

    function moveDownClickHandler(e) {
        e.preventDefault();
        e.stopPropagation();
        const btn = this;
        const row = btn.closest('tr.itinerary-row');
        const nextRow = row.nextElementSibling;
        
        if (nextRow && !nextRow.classList.contains('no-data-row')) {
            const table = row.closest('table');
            row.parentNode.insertBefore(nextRow, row);
            reindexRows(table);
            updateMoveButtons(table);
        }
    }

    function copyClickHandler(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const sourceCard = this.closest('.card');
        const container = document.getElementById('operation-details-container');
        
        const existingCards = document.querySelectorAll('#operation-details-container > .card');
        let maxIndex = 0;
        existingCards.forEach(card => {
            const idx = parseInt(card.getAttribute('data-vehicle-index'));
            if (!isNaN(idx) && idx > maxIndex) {
                maxIndex = idx;
            }
        });
        const newIndex = maxIndex + 1;
        
        const newBusId = 'copy_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        
        let sourceTable = sourceCard.querySelector('#itinerary-table tbody');
        if (!sourceTable) {
            sourceTable = sourceCard.querySelector('.vehicle-itinerary-table tbody');
        }
        if (!sourceTable) {
            sourceTable = sourceCard.querySelector('table tbody');
        }
        
        if (!sourceTable) {
            alert('行程テーブルが見つかりません。');
            return;
        }
        
        const sourceRows = sourceTable.querySelectorAll('tr.itinerary-row');
        
        const cleanedSourceRows = [];
        sourceRows.forEach(row => {
            const clonedRow = row.cloneNode(true);
            const dateInput = clonedRow.querySelector('input[name*="[date]"]');
            if (dateInput && dateInput.value.includes(' ')) {
                dateInput.value = dateInput.value.split(' ')[0];
            }
            cleanedSourceRows.push(clonedRow);
        });
        
        const newCard = createCopyOperationDetailCard(newIndex, newBusId, cleanedSourceRows, sourceCard);
        container.appendChild(newCard);
        newCard.querySelector('.btn-pdf-export')?.setAttribute('data-bus-id', newBusId);
        
        initNewCardSearch(newIndex);
    
        reindexAllTables();
        updateOperationDetailNumbers();
        refreshEventListeners();
    
        const newDateInputs = newCard.querySelectorAll('.datepicker-3months');
        newDateInputs.forEach(function(dateInput) {
            if (!dateInput._flatpickr) {
                flatpickr(dateInput, {
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
            }
        });
    
        // setTimeout(() => {
        //     setupSelectChangeHandlers(newIndex);
        // }, 100);
    }

    function refreshEventListeners() {
        document.querySelectorAll('.add-row-btn').forEach(btn => {
            btn.removeEventListener('click', addClickHandler);
            btn.addEventListener('click', addClickHandler);
        });

        document.querySelectorAll('.delete-row-btn').forEach(btn => {
            btn.removeEventListener('click', deleteClickHandler);
            btn.addEventListener('click', deleteClickHandler);
        });

        document.querySelectorAll('.move-up-btn').forEach(btn => {
            btn.removeEventListener('click', moveUpClickHandler);
            btn.addEventListener('click', moveUpClickHandler);
        });

        document.querySelectorAll('.move-down-btn').forEach(btn => {
            btn.removeEventListener('click', moveDownClickHandler);
            btn.addEventListener('click', moveDownClickHandler);
        });

        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.removeEventListener('click', copyClickHandler);
            btn.addEventListener('click', copyClickHandler);
        });

        document.querySelectorAll('.update-btn').forEach(btn => {
            btn.removeEventListener('click', updateBusDetailClickHandler);
            btn.addEventListener('click', updateBusDetailClickHandler);
        });
        
        document.querySelectorAll('.copy-stepcar-btn').forEach(btn => {
            btn.removeEventListener('click', btn._copyStepcarHandler);
            const copyStepcarHandler = function() {
                const vehicleIndex = this.getAttribute('data-vehicle-index');
                const groupNameInput = document.querySelector('input[name="group_name"]');
                const stepCarInput = document.getElementById(`step_car_${vehicleIndex}`);
                
                if (groupNameInput && stepCarInput) {
                    stepCarInput.value = groupNameInput.value;
                    stepCarInput.style.backgroundColor = '#e8f0fe';
                    setTimeout(() => {
                        stepCarInput.style.backgroundColor = '';
                    }, 200);
                }
            };
            btn.addEventListener('click', copyStepcarHandler);
            btn._copyStepcarHandler = copyStepcarHandler;
        });
        
        bindPdfExportEvents();
        
        
        const tabButtons2 = document.querySelectorAll('.tab-button2');
        if (tabButtons2.length > 0) {
            tabButtons2.forEach(button => {
                button.removeEventListener('click', button._tabClickHandler);
                const tabClickHandler = function() {
                    const container = this.getAttribute('data-container');
                    const tabId = this.getAttribute('data-tab2');
                    
                    const parentContainer = document.querySelector(`.tab-container-${container}`);
                    
                    if (parentContainer) {
                        const groupButtons = parentContainer.querySelectorAll('.tab-button2');
                        const groupContents = parentContainer.querySelectorAll('.tab-content2');
                        
                        groupButtons.forEach(btn => {
                            btn.classList.remove('active');
                            btn.style.backgroundColor = '#F3F4F6';
                            btn.style.borderBottomColor = '#E5E7EB';
                            btn.style.color = '#6B7280';
                        });
                        
                        this.classList.add('active');
                        this.style.backgroundColor = 'white';
                        this.style.borderBottomColor = 'white';
                        this.style.color = '#374151';
                        
                        groupContents.forEach(content => {
                            content.style.display = 'none';
                        });
                    }
                    
                    document.getElementById(tabId).style.display = 'block';
                };
                button.addEventListener('click', tabClickHandler);
                button._tabClickHandler = tabClickHandler;
            });
        }
        
        
        document.querySelectorAll('.add-first-compensation-row').forEach(btn => {
            btn.removeEventListener('click', btn._firstAddHandler);
            const firstAddHandler = function() {
                const busId = this.getAttribute('data-bus-id');
                const vehicleIndex = this.getAttribute('data-vehicle-index');
                const container = this.closest('.compensation-container');
                
                const tableHtml = `
                    <table class="table table-sm table-bordered compensation-table" style="font-size: 11px; margin-bottom: 5px;">
                        <thead style="text-align: center;">
                            <tr>
                                <th style="width: 20%; background-color: #f8f9fa;">対象日</th>
                                <th style="width: 25%; background-color: #f8f9fa;">報酬種別</th>
                                <th style="width: 15%; background-color: #f8f9fa;">単価</th>
                                <th style="width: 10%; background-color: #f8f9fa;">数量</th>
                                <th style="width: 15%; background-color: #f8f9fa;">金額</th>
                                <th style="width: 15%; background-color: #f8f9fa;">操作</th>
                            </tr>
                        </thead>
                        <tbody class="compensation-tbody">
                            <tr class="compensation-row" data-comp-index="0">
                                <td>
                                    <input type="date" class="form-control form-control-sm" 
                                           name="bus_assignments[${vehicleIndex}][compensations][0][target_date]" value="">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm compensation-type" 
                                            name="bus_assignments[${vehicleIndex}][compensations][0][comp_id]">
                                        <option value="">-- 選択 --</option>
                                        @foreach($compensationTypes ?? [] as $type)
                                            <option value="{{ $type->id }}">{{ $type->comp_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm compensation-price" 
                                           name="bus_assignments[${vehicleIndex}][compensations][0][price]" value="" step="1" min="0">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm compensation-qty" 
                                           name="bus_assignments[${vehicleIndex}][compensations][0][qty]" value="" step="1" min="0">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm compensation-amount" 
                                           name="bus_assignments[${vehicleIndex}][compensations][0][amount]" value="0" readonly style="background-color: #f3f4f6;">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-success add-compensation-row" style="padding: 2px 6px; font-size: 10px;">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-compensation-row" style="padding: 2px 6px; font-size: 10px;" disabled>
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="4" class="text-end">合計</td>
                                <td class="text-end"><span class="total-amount-display">¥ 0</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <input type="hidden" class="total-amount-hidden" name="bus_assignments[${vehicleIndex}][compensations_total]" value="0">
                `;
                
                container.innerHTML = tableHtml;
                
                initCompensationTable(container);
                
                container.querySelectorAll('.compensation-date').forEach(dateInput => {
                    if (!dateInput._flatpickr) {
                        flatpickr(dateInput, {
                            locale: 'ja',
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                            disableMobile: true
                        });
                    }
                });
            };
            btn.addEventListener('click', firstAddHandler);
            btn._firstAddHandler = firstAddHandler;
        });
    }

    function setupSelectChangeHandlers(vehicleIndex) {
        const vehicleSelect = document.getElementById(`vehicle_select_${vehicleIndex}`);
        const driverSelect = document.getElementById(`driver_select_${vehicleIndex}`);
        const guideSelect = document.getElementById(`guide_select_${vehicleIndex}`);

        if (vehicleSelect) {
            vehicleSelect.addEventListener('change', function() {
                const vehicleId = this.value;
                const card = this.closest('.card');
                
                if (card) {
                    const cardBusId = card.getAttribute('data-bus-id') || '';
                    const rows = card.querySelectorAll('.itinerary-row');
                    rows.forEach(row => {
                        const rowBusIdField = row.querySelector('.itinerary-bus-id');
                        if (rowBusIdField && rowBusIdField.value === cardBusId) {
                            const vehicleIdInput = row.querySelector('.itinerary-vehicle-id');
                            if (vehicleIdInput) {
                                vehicleIdInput.value = vehicleId;
                            }
                        }
                    });
                }
            });
        }

        if (driverSelect) {
            driverSelect.addEventListener('change', function() {
                const driverId = this.value;
                const card = this.closest('.card');
                
                if (card) {
                    const cardBusId = card.getAttribute('data-bus-id') || '';
                    const rows = card.querySelectorAll('.itinerary-row');
                    rows.forEach(row => {
                        const rowBusIdField = row.querySelector('.itinerary-bus-id');
                        if (rowBusIdField && rowBusIdField.value === cardBusId) {
                            const driverIdInput = row.querySelector('.itinerary-driver-id');
                            if (driverIdInput) {
                                driverIdInput.value = driverId;
                            }
                        }
                    });
                }
            });
        }

        if (guideSelect) {
            guideSelect.addEventListener('change', function() {
                const guideId = this.value;
                const card = this.closest('.card');
                
                if (card) {
                    const cardBusId = card.getAttribute('data-bus-id') || '';
                    const rows = card.querySelectorAll('.itinerary-row');
                    rows.forEach(row => {
                        const rowBusIdField = row.querySelector('.itinerary-bus-id');
                        if (rowBusIdField && rowBusIdField.value === cardBusId) {
                            const guideIdInput = row.querySelector('.itinerary-guide-id');
                            if (guideIdInput) {
                                guideIdInput.value = guideId;
                            }
                        }
                    });
                }
            });
        }
    }

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

    function setupSearch(type, items, formatter, containerId = null) {
        let searchInput, suggestionsDiv, hiddenId;
        
        if (containerId) {
            searchInput = document.getElementById(`${type}_search_${containerId}`);
            suggestionsDiv = document.getElementById(`${type}_suggestions_${containerId}`);
            hiddenId = document.getElementById(`${type}_id_${containerId}`);
        } else {
            searchInput = document.getElementById(`${type}_search`);
            suggestionsDiv = document.getElementById(`${type}_suggestions`);
            hiddenId = document.getElementById(`${type}_id`);
        }
        
        if (!searchInput) return;

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
    
            if (type === 'country') {
                const agencyCountryInput = document.getElementById('agency_country');
                if (agencyCountryInput) {
                    agencyCountryInput.value = this.value;
                }
            }
        });

        suggestionsDiv.addEventListener('click', function(e) {
            const suggestion = e.target.closest('.suggestion-item');
            if (!suggestion) return;

            const id = suggestion.dataset.id;
            const data = JSON.parse(suggestion.dataset.data);
            
            searchInput.value = data.display;
            if (hiddenId) hiddenId.value = id;
            suggestionsDiv.style.display = 'none';

            if (type === 'agency') {
                const agencyInput = document.getElementById('agency');
                const agencyCode = document.getElementById('agency_code');
                const agencyBranch = document.getElementById('agency_branch');
                const agencyPhone = document.getElementById('agency_phone');
                const agencyContactName = document.getElementById('agency_contact_name');
                const agencyCountry = document.getElementById('agency_country');
                
                if (agencyInput) agencyInput.value = data.display;
                if (agencyCode) agencyCode.value = data.agency_code || '';
                if (agencyBranch) agencyBranch.value = data.branch_name || '';
                if (agencyPhone) agencyPhone.value = data.phone || '';
                if (agencyContactName && data.manager && !agencyContactName.value) {
                    agencyContactName.value = data.manager || '';
                }
                if (agencyCountry && data.country && !agencyCountry.value) {
                    agencyCountry.value = data.country || '';
                }
            // } else if (type === 'guide') {
            //     const guideInput = document.getElementById('guide');
            //     if (guideInput) guideInput.value = data.display;
            //     const guideSelect = document.getElementById('guide_select');
            //     if (guideSelect) {
            //         const option = guideSelect.querySelector(`option[value="${id}"]`);
            //         if (option) {
            //             option.selected = true;
            //             guideSelect.dispatchEvent(new Event('change'));
            //         }
            //     }
            } else if (type === 'branch') {
                const branchInput = document.getElementById('vehicle_branch');
                if (branchInput) branchInput.value = data.display;
            } else if (type === 'category') {
                const categoryIdInput = document.getElementById('business_category_id');
                const categorySearchInput = document.getElementById('category_search');
                if (categoryIdInput) categoryIdInput.value = id;
                if (categorySearchInput) categorySearchInput.value = data.display;
            } else if (type === 'staff') {
                const staffTabInput = document.querySelector('input[name="agency_contact_name"]');
                if (staffTabInput) {
                    staffTabInput.value = data.display;
                }
            } else if (type === 'country') {
                const agencyCountryInput = document.getElementById('agency_country');
                if (agencyCountryInput) {
                    agencyCountryInput.value = data.country_name;
                }
            } else if (type === 'vehicle') {
                const vehicleIdInput = document.getElementById(`vehicle_id_${containerId}`);
                if (vehicleIdInput) {
                    vehicleIdInput.value = data.id;
                    vehicleIdInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                
                const card = searchInput.closest('.vehicle-detail-card');
                if (card) {
                    const busId = card.getAttribute('data-bus-id');
                    const rows = card.querySelectorAll('.itinerary-row');
                    rows.forEach(row => {
                        const vehicleIdInputInRow = row.querySelector('.itinerary-vehicle-id');
                        if (vehicleIdInputInRow) {
                            vehicleIdInputInRow.value = data.id;
                        }
                    });
                    
                    const hiddenVehicleId = card.querySelector(`input[name*="[vehicle_id]"]`);
                    if (hiddenVehicleId && hiddenVehicleId !== vehicleIdInput) {
                        hiddenVehicleId.value = data.id;
                    }
                }
            } else if (type === 'driver') {
                const driverIdInput = document.getElementById(`driver_id_${containerId}`);
                if (driverIdInput) {
                    driverIdInput.value = data.id;
                }
                driverIdInput.dispatchEvent(new Event('change'));
            } else if (type === 'guide') {
                const guideIdInput = document.getElementById(`guide_id_${containerId}`);
                if (guideIdInput) {
                    guideIdInput.value = data.id;
                }
                guideIdInput.dispatchEvent(new Event('change'));
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

    function addRowAfter(clickedButton) {
        const currentRow = clickedButton.closest('tr.itinerary-row');
        if (!currentRow) return;
    
        const table = currentRow.closest('table');
        const tbody = table.querySelector('tbody');
        
        const dateInput = currentRow.querySelector('input[name*="[date]"]');
        const date = dateInput ? dateInput.value : '';
        
        const vehicleGroup = currentRow.querySelector('input[name*="[vehicle_group]"]');
        const vehicleGroupValue = vehicleGroup ? vehicleGroup.value : '1';
        
        const card = currentRow.closest('.card');
        let cardBusId = card ? card.getAttribute('data-bus-id') : '';
        
        if (!cardBusId || cardBusId === '' || (typeof cardBusId === 'string' && (cardBusId.startsWith('copy_') || cardBusId.startsWith('split_')))) {
            const busIdInput = card ? card.querySelector('input[name*="[id]"]') : null;
            if (busIdInput && busIdInput.value) {
                cardBusId = busIdInput.value;
            }
        }
        
        const vehicleId = card.querySelector('.vehicle-select') ? card.querySelector('.vehicle-select').value : '';
        const driverId = card.querySelector('.driver-select') ? card.querySelector('.driver-select').value : '';
        const guideId = card.querySelector('.guide-select') ? card.querySelector('.guide-select').value : '';
        
        const allRows = Array.from(tbody.querySelectorAll('tr.itinerary-row:not(.no-data-row)'));
        let newIndex = allRows.length;
        
        const uniqueIndex = Date.now() + '_' + Math.random().toString(36).substr(2, 8);
        
        const newRow = createNewRow(date, uniqueIndex, vehicleGroupValue, cardBusId, vehicleId, driverId, guideId);
        
        const nextRow = currentRow.nextElementSibling;
        if (nextRow && !nextRow.classList.contains('no-data-row')) {
            tbody.insertBefore(newRow, nextRow);
        } else {
            tbody.appendChild(newRow);
        }
        
        const noDataRow = tbody.querySelector('.no-data-row');
        if (noDataRow) {
            noDataRow.remove();
        }
        
        reindexRows(table);
        updateMoveButtons(table);
    
        const newDateInput = newRow.querySelector('.datepicker-3months');
        if (newDateInput && !newDateInput._flatpickr) {
            flatpickr(newDateInput, {
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
        }
    }

    function deleteRow(clickedButton) {
        const row = clickedButton.closest('tr.itinerary-row');
        if (!row) return;
        
        const itineraryId = row.getAttribute('data-itinerary-id');
        const busId = row.getAttribute('data-bus-id');
        const groupId = {{ $groupInfo->id }};
        
        if (itineraryId && itineraryId !== '') {
            if (!confirm('この行程を削除してもよろしいですか？')) {
                return;
            }
            
            const originalText = clickedButton.innerHTML;
            clickedButton.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            clickedButton.disabled = true;
            
            fetch(`/masters/group-infos/${groupId}/delete-itinerary`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    itinerary_id: itineraryId,
                    bus_id: busId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const table = row.closest('table');
                    const tbody = table.querySelector('tbody');
                    row.remove();
                    
                    const rows = tbody.querySelectorAll('tr.itinerary-row:not(.no-data-row)');
                    if (rows.length === 0) {
                        tbody.innerHTML = `
                            <tr class="no-data-row">
                                <td colspan="6" class="text-center py-4" style="color: #6c757d; background-color: #f9f9f9;">
                                    <i class="bi bi-info-circle me-1"></i> 旅程データがありません。「+」ボタンを押して追加してください。
                                </tr>
                        `;
                    } else {
                        reindexRows(table);
                        updateMoveButtons(table);
                    }
                    
                    location.reload();
                } else {
                    alert('削除に失敗しました: ' + data.message);
                    clickedButton.innerHTML = originalText;
                    clickedButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('削除中にエラーが発生しました: ' + error.message);
                clickedButton.innerHTML = originalText;
                clickedButton.disabled = false;
            });
        } else {
            if (confirm('この行程を削除してもよろしいですか？')) {
                const table = row.closest('table');
                const tbody = table.querySelector('tbody');
                row.remove();
                
                const rows = tbody.querySelectorAll('tr.itinerary-row:not(.no-data-row)');
                if (rows.length === 0) {
                    tbody.innerHTML = `
                        <tr class="no-data-row">
                            <td colspan="6" class="text-center py-4" style="color: #6c757d; background-color: #f9f9f9;">
                                <i class="bi bi-info-circle me-1"></i> 旅程データがありません。「+」ボタンを押して追加してください。
                            </tr>
                    `;
                } else {
                    reindexRows(table);
                    updateMoveButtons(table);
                }
            }
        }
    }

    function createNewRow(date, uniqueIndex, vehicleGroup = '1', busId = '', vehicleId = '', driverId = '', guideId = '') {
        if (date && date.includes(' ')) {
            date = date.split(' ')[0];
        }
        
        const newRow = document.createElement('tr');
        newRow.className = 'itinerary-row';
        newRow.setAttribute('data-vehicle', vehicleGroup);
        newRow.setAttribute('data-index', uniqueIndex);
        newRow.setAttribute('data-bus-id', busId);
        newRow.setAttribute('data-itinerary-id', '');
        
        const table = newRow.closest('table');
        const rowNumber = table ? table.querySelectorAll('tr.itinerary-row:not(.no-data-row)').length + 1 : 1;
        
        newRow.innerHTML = `
            <td style="vertical-align: middle; text-align: center; background-color: #f9f9f9; position: relative;">
                <span class="row-number" style="position: absolute; top: 2px; left: 2px; color: #2563eb; font-size: 10px; font-weight: bold;">${rowNumber}</span>
                <input type="hidden" name="daily_itineraries[${uniqueIndex}][id]" value="">
                <input type="hidden" name="daily_itineraries[${uniqueIndex}][display_order]" value="${rowNumber}">
                <input type="hidden" name="daily_itineraries[${uniqueIndex}][bus_assignment_id]" value="${busId}" class="itinerary-bus-id">
                <input type="hidden" name="daily_itineraries[${uniqueIndex}][vehicle_id]" value="${vehicleId}" class="itinerary-vehicle-id">
                <input type="hidden" name="daily_itineraries[${uniqueIndex}][driver_id]" value="${driverId}" class="itinerary-driver-id">
                <input type="hidden" name="daily_itineraries[${uniqueIndex}][guide_id]" value="${guideId}" class="itinerary-guide-id">
                <input type="text" class="form-control form-control-sm border datepicker-3months" name="daily_itineraries[${uniqueIndex}][date]" value="${date}" style="width: 100%; text-align: center;" placeholder="YYYY-MM-DD">
                <input type="hidden" name="daily_itineraries[${uniqueIndex}][vehicle_group]" value="${vehicleGroup}">
             </td>
            <td style="padding: 2px;">
                <div class="d-flex flex-column" style="gap: 2px;">
                    <input type="time" class="form-control form-control-sm border" 
                           name="daily_itineraries[${uniqueIndex}][time_start]" value="08:00" 
                           style="width: 100%;" step="60">
                    <input type="text" class="form-control form-control-sm border" 
                           name="daily_itineraries[${uniqueIndex}][start_location]" value="" 
                           placeholder="開始場所" style="width: 100%;">
                </div>
            </td>
            <td style="padding: 2px;">
                <div class="d-flex flex-column" style="gap: 2px;">
                    <input type="time" class="form-control form-control-sm border" 
                           name="daily_itineraries[${uniqueIndex}][time_end]" value="18:00" 
                           style="width: 100%;" step="60">
                    <input type="text" class="form-control form-control-sm border" 
                           name="daily_itineraries[${uniqueIndex}][end_location]" value="" 
                           placeholder="終了場所" style="width: 100%;">
                </div>
            </td>
            <td style="vertical-align: middle; padding: 2px;">
                <textarea name="daily_itineraries[${uniqueIndex}][itinerary]" rows="2" 
                          class="form-control form-control-sm border" 
                          style="width: 100%; height: 100%; min-height: 60px;"></textarea>
            </td>
            <td style="padding: 2px; text-align: center; vertical-align: middle;">
                <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                    <input type="checkbox" class="form-check-input itinerary-select" 
                           id="select_itinerary_${uniqueIndex}" 
                           style="margin: 0; width: 18px; height: 18px; cursor: pointer;">
                </div>
            </td>
            <td style="padding: 2px; text-align: center; vertical-align: middle;">
                <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </td>
        `;
        
        return newRow;
    }

    function reindexRows(table) {
        const rows = table.querySelectorAll('tbody tr.itinerary-row:not(.no-data-row)');
        rows.forEach((row, idx) => {
            row.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.includes('daily_itineraries[')) {
                    const newName = name.replace(/daily_itineraries\[\d+\]/, `daily_itineraries[${idx}]`);
                    input.setAttribute('name', newName);
                }
            });
            
            const displayOrder = row.querySelector('input[name*="[display_order]"]');
            if (displayOrder) {
                displayOrder.value = idx + 1;
            }
            
            const rowNumber = row.querySelector('.row-number');
            if (rowNumber) {
                rowNumber.textContent = idx + 1;
            }
            
            const checkId = row.querySelector('[id^="select_itinerary_"]');
            if (checkId) {
                checkId.id = `select_itinerary_${idx}`;
            }
            
            row.setAttribute('data-index', idx);
        });
    }

    function updateMoveButtons(table) {
        const rows = table.querySelectorAll('tbody tr.itinerary-row:not(.no-data-row)');
        rows.forEach((row, index) => {
            const upBtn = row.querySelector('.move-up-btn');
            const downBtn = row.querySelector('.move-down-btn');
            
            if (upBtn) {
                upBtn.disabled = index === 0;
            }
            if (downBtn) {
                downBtn.disabled = index === rows.length - 1;
            }
        });
    }

    function reindexAllTables() {
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            reindexRows(table);
            updateMoveButtons(table);
        });
    }

    function createCopyOperationDetailCard(newIndex, newBusId, sourceRows, sourceCard) {
        const newCard = document.createElement('div');
        newCard.className = 'card shadow-sm mb-1 vehicle-detail-card';
        newCard.setAttribute('data-vehicle-id', 'new-vehicle');
        newCard.setAttribute('data-vehicle-index', newIndex);
        newCard.setAttribute('data-bus-id', newBusId);
    
        const getSourceOptions = () => {
            const optionsContainer = sourceCard.querySelector('.options-container');
            if (!optionsContainer) return [];
            const checkboxes = optionsContainer.querySelectorAll('input[type="checkbox"]');
            const selectedValues = [];
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedValues.push(cb.value);
                }
            });
            return selectedValues;
        };
    
        const sourceSelectedOptions = getSourceOptions();
        
        const vehicleGrades = @json($vehicleGrades ?? []);
        const reservationCategories = @json($reservationCategories ?? []);
        const options = @json($options ?? []);
    
        let tableRows = '';
        if (sourceRows && sourceRows.length > 0) {
            const cleanedSourceRows = [];
            sourceRows.forEach(row => {
                const clonedRow = row.cloneNode(true);
                const dateInput = clonedRow.querySelector('input[name*="[date]"]');
                if (dateInput && dateInput.value && dateInput.value.includes(' ')) {
                    dateInput.value = dateInput.value.split(' ')[0];
                }
                cleanedSourceRows.push(clonedRow);
            });
            tableRows = generateRowsFromSource(cleanedSourceRows, newIndex, newBusId);
        } else {
            tableRows = `
                <tr class="no-data-row">
                    <td colspan="6" class="text-center py-4" style="color: #6c757d; background-color: #f9f9f9;">
                        <i class="bi bi-info-circle me-1"></i> 旅程データがありません。「+」ボタンを押して追加してください。
                     </tr>
            `;
        }
    
        newCard.innerHTML = `
            <div class="card-header py-1 px-3 d-flex align-items-center" style="background-color: #141c28; border-bottom: 1px solid #aaa;">
                <h6 class="mb-0 me-3" style="color: #fff; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                    <span>運行詳細-${newIndex.toString().padStart(2, '0')}</span>
                    <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; background-color: #f59e0b; color: white;">未完成</span>
                    <button type="button" class="btn-pdf-export btn btn-sm" 
                            data-bus-id="${newBusId}" 
                            data-vehicle-index="${newIndex}"
                            style="background-color: #dc2626; border: none; color: white; padding: 2px 10px; font-size: 0.7rem; border-radius: 4px; display: flex; align-items: center; gap: 4px;">
                        <i class="bi bi-file-pdf"></i> 運行指示書PDF
                    </button>
                </h6>
                <div class="d-flex align-items-center ms-auto card-header-actions" style="gap: 15px;">
                    <div class="form-check d-flex align-items-center">
                        <label class="form-check-label me-2" for="bus_assignments_${newIndex}" style="font-size: 0.8rem; color: #fff;">最終確認</label>
                        <input type="checkbox" class="form-check-input" id="bus_assignments_${newIndex}" name="bus_assignments[${newIndex}][status_finalized]" value="1" style="margin: 0;">
                    </div>
                    <div class="form-check d-flex align-items-center">
                        <label class="form-check-label me-2" for="status_sent_${newIndex}" style="font-size: 0.8rem; color: #fff;">送信済</label>
                        <input type="checkbox" class="form-check-input" id="status_sent_${newIndex}" name="bus_assignments[${newIndex}][status_sent]" value="1" style="margin: 0;">
                    </div>
                    <div class="form-check d-flex align-items-center">
                        <label class="form-check-label me-2" for="lock_arrangement_${newIndex}" style="font-size: 0.8rem; color: #fff;">操作ロック</label>
                        <input type="checkbox" class="form-check-input" id="lock_arrangement_${newIndex}" name="bus_assignments[${newIndex}][lock_arrangement]" value="1" style="margin: 0;">
                    </div>
                    <div class="d-flex align-items-center card-header-btns" style="gap: 5px;">
                        <input type="text" class="form-control form-control-sm border merge-operation-id" placeholder="運行ID" style="width: 80px;">
                        <button type="button" class="btn btn-sm btn-primary merge-btn" style="font-size: 0.75rem; padding: 4px 8px;">統合</button>
                        <button type="button" class="btn btn-sm btn-secondary split-btn" style="font-size: 0.75rem; padding: 4px 8px;">分割</button>
                        <button type="button" class="btn btn-sm btn-info copy-btn" style="font-size: 0.75rem; padding: 4px 8px; background-color: #17a2b8; border-color: #17a2b8; color: white;">Copy</button>
                        <button type="button" class="btn btn-sm btn-success update-btn" style="font-size: 0.75rem; padding: 4px 8px; background-color: #28a745; border-color: #28a745; color: white;">更新</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-2">
                <input type="hidden" name="bus_assignments[${newIndex}][id]" value="">
                <input type="hidden" name="bus_assignments[${newIndex}][vehicle_index]" value="${newIndex}">
                
                <div class="row" style="margin-right: -5px; margin-left: -5px;">
                    <div class="col-md-6 yxxx-l" style="width:60%; padding-right: 5px; padding-left: 5px;">
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex w-100">
                                    <div class="d-flex align-items-center" style="width: 50%; gap: 8px;">
                                        <div class="d-flex align-items-center" style="width: 70%;">
                                            <span class="span-label">運行ID</span>
                                            <span class="border px-2 py-1 bg-white rounded w-100" style="color: #2563eb;">&nbsp;</span>
                                        </div>
                                        <div class="d-flex align-items-center vehicle_type_spec_check" style="width: 30%;">
                                            <span class="span-label" style="width: auto !important;">車種指定</span>
                                            <input type="checkbox" class="form-check-input" name="bus_assignments[${newIndex}][vehicle_type_spec_check]" value="1" style="margin: 0;">
                                        </div>
                                    </div>
                                    <div class="d-flex" style="width: 50%; gap: 8px;">
                                        <div class="d-flex align-items-center" style="width: 50%;">
                                            <span class="span-label">車両等級</span>
                                            <select name="bus_assignments[${newIndex}][vehicle_grade_id]" class="form-select form-select-sm border w-100">
                                                <option value="">-- 選択 --</option>
                                                ${vehicleGrades.map(g => `<option value="${g.id}">${g.description || g.grade_name}</option>`).join('')}
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center" style="width: 50%;">
                                            <span class="span-label">号車</span>
                                            <input type="text" class="form-control form-control-sm border w-100" name="bus_assignments[${newIndex}][vehicle_number]" value="${newIndex.toString().padStart(2, '0')}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex w-100">
                                    <div class="d-flex align-items-center row-step_car" style="width: 50%;">
                                        <span class="span-label" style="white-space: normal; word-break: break-all; line-height: 1.2; min-width: 70px;">ステップカー</span>
                                        <input type="text" class="form-control form-control-sm border w-100" name="bus_assignments[${newIndex}][step_car]" value="" id="step_car_${newIndex}">
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-1 copy-stepcar-btn border" 
                                                data-vehicle-index="${newIndex}" 
                                                style="padding: 4px 8px; font-size: 0.7rem; white-space: nowrap;"
                                                title="団体名をコピー">
                                            <i class="bi bi-files"></i> Copy
                                        </button>
                                    </div>
                                    <div class="d-flex" style="width: 50%; gap: 8px;">
                                        <div class="d-flex align-items-center adult-child" style="width: 50%;">
                                            <span class="span-label">人数</span>
                                            <div class="d-flex gap-1 flex-fill">
                                                <input type="number" class="form-control form-control-sm border flex-fill" name="bus_assignments[${newIndex}][adult_count]" value="" placeholder="大" min="0">
                                                <input type="number" class="form-control form-control-sm border flex-fill" name="bus_assignments[${newIndex}][child_count]" value="" placeholder="小" min="0">
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center" style="width: 50%;">
                                            <span class="span-label">荷物</span>
                                            <input type="text" class="form-control form-control-sm border w-100" name="bus_assignments[${newIndex}][luggage]" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex w-100 vehicle-representative">
                                    <div class="d-flex align-items-center" style="width: 50%;">
                                        <span class="span-label">車両</span>
                                        <div class="flex-1 position-relative w-100">
                                            <input type="text" class="form-control form-control-sm border search-input w-100" 
                                                   id="vehicle_search_${newIndex}" 
                                                   value="" 
                                                   autocomplete="off"
                                                   placeholder="-- 車両を選択 --">
                                            <input type="hidden" name="bus_assignments[${newIndex}][vehicle_id]" id="vehicle_id_${newIndex}" value="">
                                            <div class="suggestions-container" id="vehicle_suggestions_${newIndex}" style="display: none;"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center representative-1" style="width: 50%;">
                                        <span class="span-label">代表</span>
                                        <input type="text" class="form-control form-control-sm border" name="bus_assignments[${newIndex}][representative]" value="" placeholder="Name">
                                        
                                        <input type="text" class="form-control form-control-sm border ms-2" name="bus_assignments[${newIndex}][representative_phone]" value="" placeholder="Tel/Cell">
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex w-100">
                                    <div class="d-flex align-items-center" style="width: 50%;">
                                        <span class="span-label">運転手</span>
                                        <div class="flex-1 position-relative w-100">
                                            <input type="text" class="form-control form-control-sm border search-input w-100" 
                                                   id="driver_search_${newIndex}" 
                                                   value="" 
                                                   autocomplete="off"
                                                   placeholder="-- 運転手を選択 --">
                                            <input type="hidden" name="bus_assignments[${newIndex}][driver_id]" id="driver_id_${newIndex}" value="">
                                            <div class="suggestions-container" id="driver_suggestions_${newIndex}" style="display: none;"></div>
                                        </div>
                                            
                                        <span class="span-label">仮</span>
                                        <input type="checkbox" class="form-check-input" name="bus_assignments[${newIndex}][temporary_driver]" value="1" style="margin: 0;">
                                    </div>
                                    <div class="d-flex align-items-center" style="width: 50%;">
                                        <span class="span-label">添乗</span>
                                        <div class="flex-1 position-relative w-100">
                                            <input type="text" class="form-control form-control-sm border search-input w-100" 
                                                   id="guide_search_${newIndex}" 
                                                   value="" 
                                                   autocomplete="off"
                                                   placeholder="-- 添乗員を選択 --">
                                            <input type="hidden" name="bus_assignments[${newIndex}][guide_id]" id="guide_id_${newIndex}" value="">
                                            <div class="suggestions-container" id="guide_suggestions_${newIndex}" style="display: none;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                        <div class="tab-container-${newIndex}">
                            <div class="d-flex w-100" style="border-bottom: 1px solid #aaa;">
                                <span class="tab-button2 active flex-fill text-center px-2 py-1" data-container="${newIndex}" data-tab2="basic2-${newIndex}" style="background-color: white; border: 1px solid #aaa; border-bottom-color: white; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #374151; font-size: 0.8rem; cursor: pointer;">基本</span>
                                <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="${newIndex}" data-tab2="doc-${newIndex}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">給与</span>
                                <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="${newIndex}" data-tab2="expense-${newIndex}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">立替</span>
                            </div>
    
                            <div id="basic2-${newIndex}" class="tab-content2" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 102px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; overflow: auto;">
                                <div class="file-manager-${newIndex}" style="display: flex; flex-direction: column; height: 100%;">
                                    <div class="file-list file-list-bus" id="file-list-bus-${newBusId}" data-bus-id="${newBusId}" style="flex: 1; margin-bottom: 10px;">
                                        <div class="file-empty-bus" style="text-align: center; padding: 10px 0 0 0; color: #9ca3af; font-size: 11px;">
                                            <i class="bi bi-folder2-open"></i> ファイルはありません
                                        </div>
                                    </div>
                                    <div class="file-upload-bus" style="display: flex; justify-content: center; flex-shrink: 0;">
                                        <button type="button" class="btn-upload-file-bus btn btn-sm btn-primary mb-2" data-bus-id="${newBusId}" style="background-color: #2563eb; border: none; padding: 4px 12px; font-size: 11px;">
                                            <i class="bi bi-cloud-upload"></i> アップロード
                                        </button>
                                    </div>
                                </div>
                            </div>
    
                            <div id="doc-${newIndex}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; overflow: auto;">
                                <div class="compensation-container" data-bus-id="${newBusId}" data-vehicle-index="${newIndex}">
                                    <div class="text-center py-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary add-first-compensation-row" 
                                                data-bus-id="${newBusId}" data-vehicle-index="${newIndex}"
                                                style="font-size: 11px; padding: 4px 12px;">
                                            <i class="bi bi-plus-lg"></i> 手当を追加
                                        </button>
                                    </div>
                                    <input type="hidden" class="total-amount-hidden" name="bus_assignments[${newIndex}][compensations_total]" value="0">
                                </div>
                            </div>
    
                            <div id="expense-${newIndex}" class="tab-content2 expense-tab" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; overflow: auto;">
                                <div class="expense-container" data-bus-id="${newBusId}" data-vehicle-index="${newIndex}">
                                    <div class="text-center py-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary add-first-expense-row" 
                                                data-bus-id="${newBusId}" data-vehicle-index="${newIndex}">
                                            <i class="bi bi-plus-lg"></i> 立替を追加
                                        </button>
                                    </div>
                                    <input type="hidden" class="total-expense-hidden" name="bus_assignments[${newIndex}][expenses_total]" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="row mt-2">
                    <div class="col-md-12">
                        <table class="table table-bordered table-sm vehicle-itinerary-table" style="font-size: 0.8rem; background-color: white;" data-vehicle-table="${newIndex}">
                            <thead style="background-color: #f3f4f6; text-align: center;">
                                <tr>
                                    <th style="width: 10%; text-align: center; background-color: #f3f4f6;">運行日</th>
                                    <th style="width: 10%; text-align: center; background-color: #f3f4f6;">開始時刻/場所</th>
                                    <th style="width: 10%; text-align: center; background-color: #f3f4f6;">終了時刻/場所</th>
                                    <th style="text-align: center; background-color: #f3f4f6;">行程</th>
                                    <th style="width: 5%; text-align: center; background-color: #f3f4f6;">選択</th>
                                    <th style="width: 180px; text-align: center; background-color: #f3f4f6;">操作</th>
                                 </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-2" style="margin-right: -5px; margin-left: -5px;">
                    <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
                        <div class="d-flex w-100 mb-1">
                            <span class="span-label" style="min-width: 30px;">オプション</span>
                            <div class="options-container w-100" style="display: flex; flex-wrap: wrap; gap: 8px; border: 1px solid #aaa; border-radius: 4px; padding: 4px 8px; min-height: 28px; background-color: white;">
                                ${options.map(opt => `
                                <label class="d-flex align-items-center" style="cursor: pointer; font-size: 11px;">
                                    <input type="checkbox" name="bus_assignments[${newIndex}][options][]" value="${opt.id}" style="margin-right: 2px;" ${sourceSelectedOptions.includes(opt.id.toString()) ? 'checked' : ''}>
                                    ${opt.name}
                                </label>`).join('')}
                            </div>
                        </div>
                        <div class="d-flex w-100">
                            <span class="span-label" style="min-width: 30px;">備考</span>
                            <textarea name="bus_assignments[${newIndex}][operation_remarks]" rows="1" class="form-control form-control-sm border" placeholder="指示書に表示"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                        <textarea name="bus_assignments[${newIndex}][operation_memo]" rows="2" class="form-control form-control-sm border" style="height: 62px;" placeholder="手配メモ一"></textarea>
                    </div>
                </div>
            </div>
        `;
        
        
        
                
        const newExpenseFirstBtn = newCard.querySelector('.add-first-expense-row');
        if (newExpenseFirstBtn) {
            if (newExpenseFirstBtn._firstAddHandler) {
                newExpenseFirstBtn.removeEventListener('click', newExpenseFirstBtn._firstAddHandler);
            }
            const firstAddHandler = function() {
                const busId = this.getAttribute('data-bus-id');
                const vehicleIndex = this.getAttribute('data-vehicle-index');
                const container = this.closest('.expense-container');
                
                const tableHtml = `
                    <table class="table table-sm table-bordered expense-table" style="font-size: 11px; margin-bottom: 5px;">
                        <thead>
                            <tr>
                                <th style="width: 15%; background-color: #f8f9fa; text-align: center;">日付</th>
                                <th style="width: 20%; background-color: #f8f9fa; text-align: center;">種別</th>
                                <th style="width: 12%; background-color: #f8f9fa; text-align: center;">金額</th>
                                <th style="width: 15%; background-color: #f8f9fa; text-align: center;">支払方法</th>
                                <th style="width: 8%; background-color: #f8f9fa; text-align: center;">代理店</th>
                                <th style="width: 10%; background-color: #f8f9fa; text-align: center;">操作</th>
                            </tr>
                        </thead>
                        <tbody class="expense-tbody">
                            <tr class="expense-row" data-expense-index="0">
                                <td>
                                    <input type="date" class="form-control form-control-sm expense-date" 
                                           name="bus_assignments[${vehicleIndex}][expenses][0][expense_date]" 
                                           value="${new Date().toISOString().split('T')[0]}">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm expense-type" 
                                            name="bus_assignments[${vehicleIndex}][expenses][0][type_id]">
                                        <option value="">-- 選択 --</option>
                                        @foreach($expenseTypes ?? [] as $type)
                                            <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                        @endforeach
                                    </select>
                                 </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm expense-amount text-end" 
                                           name="bus_assignments[${vehicleIndex}][expenses][0][amount]" 
                                           value="" step="1" min="0">
                                 </td>
                                <td>
                                    <select class="form-select form-select-sm expense-payment" 
                                            name="bus_assignments[${vehicleIndex}][expenses][0][payment_method_id]">
                                        <option value="">-- 選択 --</option>
                                        @foreach($paymentMethods ?? [] as $method)
                                            <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                        @endforeach
                                    </select>
                                 </td>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center" style="margin: 0; min-height: 1rem;">
                                        <input type="checkbox" class="form-check-input expense-agency" 
                                               name="bus_assignments[${vehicleIndex}][expenses][0][agency_flag]" 
                                               value="1" id="expense_agency_${vehicleIndex}_0">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-success add-expense-row" style="padding: 2px 5px;">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-expense-row" style="padding: 2px 5px;">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="2" class="text-end">合計</td>
                                <td class="text-end"><span class="total-expense-display">¥ 0</span></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <input type="hidden" class="total-expense-hidden" name="bus_assignments[${vehicleIndex}][expenses_total]" value="0">
                `;
                
                container.innerHTML = tableHtml;
                
                if (typeof initExpenseTable === 'function') {
                    initExpenseTable(container);
                }
            };
            
            newExpenseFirstBtn.addEventListener('click', firstAddHandler);
            newExpenseFirstBtn._firstAddHandler = firstAddHandler;
        }
        
        const newCompensationFirstBtn = newCard.querySelector('.add-first-compensation-row');
        if (newCompensationFirstBtn) {
            if (newCompensationFirstBtn._firstAddHandler) {
                newCompensationFirstBtn.removeEventListener('click', newCompensationFirstBtn._firstAddHandler);
            }
            const compFirstAddHandler = function() {
                const busId = this.getAttribute('data-bus-id');
                const vehicleIndex = this.getAttribute('data-vehicle-index');
                const container = this.closest('.compensation-container');
                
                const tableHtml = `
                    <table class="table table-sm table-bordered compensation-table" style="font-size: 11px; margin-bottom: 5px;">
                        <thead style="text-align: center;">
                            <tr>
                                <th style="width: 20%; background-color: #f8f9fa;">対象日</th>
                                <th style="width: 25%; background-color: #f8f9fa;">報酬種別</th>
                                <th style="width: 15%; background-color: #f8f9fa;">単価</th>
                                <th style="width: 10%; background-color: #f8f9fa;">数量</th>
                                <th style="width: 15%; background-color: #f8f9fa;">金額</th>
                                <th style="width: 15%; background-color: #f8f9fa;">操作</th>
                            </tr>
                        </thead>
                        <tbody class="compensation-tbody">
                            <tr class="compensation-row" data-comp-index="0">
                                <tr>
                                    <input type="date" class="form-control form-control-sm" 
                                           name="bus_assignments[${vehicleIndex}][compensations][0][target_date]" value="">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm compensation-type" 
                                            name="bus_assignments[${vehicleIndex}][compensations][0][comp_id]">
                                        <option value="">-- 選択 --</option>
                                        @foreach($compensationTypes ?? [] as $type)
                                            <option value="{{ $type->id }}">{{ $type->comp_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm compensation-price" 
                                           name="bus_assignments[${vehicleIndex}][compensations][0][price]" value="" step="1" min="0">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm compensation-qty" 
                                           name="bus_assignments[${vehicleIndex}][compensations][0][qty]" value="" step="1" min="0">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm compensation-amount" 
                                           name="bus_assignments[${vehicleIndex}][compensations][0][amount]" value="0" readonly style="background-color: #f3f4f6;">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-success add-compensation-row" style="padding: 2px 6px; font-size: 10px;">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-compensation-row" style="padding: 2px 6px; font-size: 10px;">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="4" class="text-end">合計</td>
                                <td class="text-end"><span class="total-amount-display">¥ 0</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <input type="hidden" class="total-amount-hidden" name="bus_assignments[${vehicleIndex}][compensations_total]" value="0">
                `;
                
                container.innerHTML = tableHtml;
                
                if (typeof initCompensationTable === 'function') {
                    initCompensationTable(container);
                }
                
                container.querySelectorAll('.compensation-date').forEach(dateInput => {
                    if (!dateInput._flatpickr) {
                        flatpickr(dateInput, {
                            locale: 'ja',
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                            disableMobile: true
                        });
                    }
                });
            };
            newCompensationFirstBtn.addEventListener('click', compFirstAddHandler);
            newCompensationFirstBtn._firstAddHandler = compFirstAddHandler;
        }
    
        return newCard;
    }

    function generateRowsFromSource(sourceRows, newIndex, newBusId) {
        let rowsHtml = '';
        const baseTimestamp = Date.now();
        sourceRows.forEach((row, idx) => {
            const dateInput = row.querySelector('input[name*="[date]"]');
            const timeStartInput = row.querySelector('input[name*="[time_start]"]');
            const timeEndInput = row.querySelector('input[name*="[time_end]"]');
            const startLocationInput = row.querySelector('input[name*="[start_location]"]');
            const endLocationInput = row.querySelector('input[name*="[end_location]"]');
            const itineraryTextarea = row.querySelector('textarea[name*="[itinerary]"]');
            
            let date = dateInput ? dateInput.value : '';
            if (date && date.includes(' ')) {
                date = date.split(' ')[0];
            }
            
            const timeStart = timeStartInput ? timeStartInput.value : '08:00';
            const timeEnd = timeEndInput ? timeEndInput.value : '18:00';
            const startLocation = startLocationInput ? startLocationInput.value : '';
            const endLocation = endLocationInput ? endLocationInput.value : '';
            const itinerary = itineraryTextarea ? itineraryTextarea.value : '';
            const vehicleId = row.querySelector('.itinerary-vehicle-id')?.value || '';
            const driverId = row.querySelector('.itinerary-driver-id')?.value || '';
            const guideId = row.querySelector('.itinerary-guide-id')?.value || '';
            
            const uniqueId = baseTimestamp + '_' + Math.random().toString(36).substr(2, 8) + '_' + newIndex + '_' + idx;
            const globalIndex = uniqueId;
            
            rowsHtml += `
                <tr class="itinerary-row" data-vehicle="${newIndex}" data-index="${globalIndex}" data-bus-id="${newBusId}" data-itinerary-id="">
                    <td style="vertical-align: middle; text-align: center; background-color: #f9f9f9; position: relative;">
                        <span class="row-number" style="position: absolute; top: 2px; left: 2px; color: #2563eb; font-size: 10px; font-weight: bold;">${idx + 1}</span>
                        <input type="hidden" name="daily_itineraries[${globalIndex}][id]" value="">
                        <input type="hidden" name="daily_itineraries[${globalIndex}][display_order]" value="${idx + 1}">
                        <input type="hidden" name="daily_itineraries[${globalIndex}][bus_assignment_id]" value="" class="itinerary-bus-id">
                        <input type="hidden" name="daily_itineraries[${globalIndex}][vehicle_id]" value="${vehicleId}" class="itinerary-vehicle-id">
                        <input type="hidden" name="daily_itineraries[${globalIndex}][driver_id]" value="${driverId}" class="itinerary-driver-id">
                        <input type="hidden" name="daily_itineraries[${globalIndex}][guide_id]" value="${guideId}" class="itinerary-guide-id">
                        <input type="text" class="form-control form-control-sm border datepicker-3months" name="daily_itineraries[${globalIndex}][date]" value="${date}" style="width: 100%; text-align: center;" placeholder="YYYY-MM-DD">
                        <input type="hidden" name="daily_itineraries[${globalIndex}][vehicle_group]" value="${newIndex}">
                       </td>
                    <td style="padding: 2px;">
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <input type="time" class="form-control form-control-sm border" 
                                   name="daily_itineraries[${globalIndex}][time_start]" value="${timeStart}" 
                                   style="width: 100%;" step="60">
                            <input type="text" class="form-control form-control-sm border" 
                                   name="daily_itineraries[${globalIndex}][start_location]" value="${startLocation}" 
                                   placeholder="開始場所" style="width: 100%;">
                        </div>
                    </td>
                    <td style="padding: 2px;">
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <input type="time" class="form-control form-control-sm border" 
                                   name="daily_itineraries[${globalIndex}][time_end]" value="${timeEnd}" 
                                   style="width: 100%;" step="60">
                            <input type="text" class="form-control form-control-sm border" 
                                   name="daily_itineraries[${globalIndex}][end_location]" value="${endLocation}" 
                                   placeholder="終了場所" style="width: 100%;">
                        </div>
                    </td>
                    <td style="vertical-align: middle; padding: 2px;">
                        <textarea name="daily_itineraries[${globalIndex}][itinerary]" rows="2" 
                                  class="form-control form-control-sm border" 
                                  style="width: 100%; height: 100%; min-height: 60px;">${itinerary}</textarea>
                    </td>
                    <td style="padding: 2px; text-align: center; vertical-align: middle;">
                        <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                            <input type="checkbox" class="form-check-input itinerary-select" 
                                   id="select_itinerary_${globalIndex}" 
                                   style="margin: 0; width: 18px; height: 18px; cursor: pointer;">
                        </div>
                    </td>
                    <td style="padding: 2px; text-align: center; vertical-align: middle;">
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動">
                                <i class="bi bi-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動">
                                <i class="bi bi-arrow-down"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        return rowsHtml;
    }

    function updateOperationDetailNumbers() {
        const cards = document.querySelectorAll('#operation-details-container > .card');
        cards.forEach((card, index) => {
            const newIndex = index + 1;
            const headerH6 = card.querySelector('.card-header h6');
            if (headerH6) {
                const titleSpan = headerH6.querySelector('span:first-child');
                const pdfButton = headerH6.querySelector('.btn-pdf-export');
                
                if (titleSpan) {
                    titleSpan.textContent = `運行詳細-${newIndex.toString().padStart(2, '0')}`;
                }
                
                if (pdfButton) {
                    pdfButton.setAttribute('data-vehicle-index', newIndex);
                }
            }
            
            const vehicleIndexInput = card.querySelector('input[name*="[vehicle_index]"]');
            if (vehicleIndexInput) {
                vehicleIndexInput.value = newIndex;
            }
            
            card.setAttribute('data-vehicle-index', newIndex);
            
            const vehicleNumber = card.querySelector('input[name*="[vehicle_number]"]');
            if (vehicleNumber && (!vehicleNumber.value || vehicleNumber.value === vehicleNumber.defaultValue)) {
                vehicleNumber.value = newIndex.toString().padStart(2, '0');
            }
            
            card.querySelectorAll('input, select, textarea').forEach(el => {
                const name = el.getAttribute('name');
                if (name && name.match(/\[\d+\]/)) {
                    const newName = name.replace(/\[\d+\]/, `[${newIndex}]`);
                    el.setAttribute('name', newName);
                }
            });
        });
    }

    function handleSplitClick(card) {
        const selectedRows = [];
        card.querySelectorAll('.itinerary-select:checked').forEach(checkbox => {
            const row = checkbox.closest('tr.itinerary-row');
            if (row) {
                const index = row.getAttribute('data-index');
                selectedRows.push({ row, index });
            }
        });
        
        if (selectedRows.length === 0) {
            alert('分割する行程を選択してください');
            return;
        }
        
        splitSelectedRows(selectedRows, card);
    }

    function splitSelectedRows(selectedRows, sourceCard) {
        const itineraryIds = selectedRows.map(item => {
            const row = item.row;
            return row.getAttribute('data-itinerary-id');
        }).filter(id => id && id !== '');
        
        if (itineraryIds.length === 0) {
            alert('分割する行程を選択してください');
            return;
        }
        
        const groupId = {{ $groupInfo->id }};
        const sourceVehicleSelect = sourceCard.querySelector('.vehicle-select');
        const sourceVehicleId = sourceVehicleSelect ? sourceVehicleSelect.value : '';
        
        const splitBtn = sourceCard.querySelector('.split-btn');
        const originalText = splitBtn.innerHTML;
        splitBtn.innerHTML = '分割中...';
        splitBtn.disabled = true;
        
        fetch(`/masters/group-infos/${groupId}/split-itineraries`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                itinerary_ids: itineraryIds,
                source_vehicle_id: sourceVehicleId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('エラー: ' + data.message);
                splitBtn.innerHTML = originalText;
                splitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('分割処理に失敗しました: ' + error.message);
            splitBtn.innerHTML = originalText;
            splitBtn.disabled = false;
        });
    }

    const tabItems = document.querySelectorAll('.tab-item');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabItems.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            tabItems.forEach(t => {
                t.classList.remove('active');
                t.classList.add('inactive');
                t.style.backgroundColor = '#F3F4F6';
                t.style.borderBottomColor = '#aaa';
                t.style.color = '#6B7280';
            });
            
            this.classList.add('active');
            this.classList.remove('inactive');
            this.style.backgroundColor = 'white';
            this.style.borderBottomColor = 'white';
            this.style.color = '#374151';
            
            tabPanes.forEach(pane => {
                pane.style.display = 'none';
            });
            
            document.getElementById(tabId + '-tab').style.display = 'block';
        });
    });

    const tabButtons2 = document.querySelectorAll('.tab-button2');
    
    if (tabButtons2.length > 0) {
        tabButtons2.forEach(button => {
            button.addEventListener('click', function() {
                const container = this.getAttribute('data-container');
                const tabId = this.getAttribute('data-tab2');
                
                const parentContainer = document.querySelector(`.tab-container-${container}`);
                
                if (parentContainer) {
                    const groupButtons = parentContainer.querySelectorAll('.tab-button2');
                    const groupContents = parentContainer.querySelectorAll('.tab-content2');
                    
                    groupButtons.forEach(btn => {
                        btn.classList.remove('active');
                        btn.style.backgroundColor = '#F3F4F6';
                        btn.style.borderBottomColor = '#E5E7EB';
                        btn.style.color = '#6B7280';
                    });

                    this.classList.add('active');
                    this.style.backgroundColor = 'white';
                    this.style.borderBottomColor = 'white';
                    this.style.color = '#374151';

                    groupContents.forEach(content => {
                        content.style.display = 'none';
                    });
                }

                document.getElementById(tabId).style.display = 'block';
            });
        });
    }

    document.querySelectorAll('.split-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.card');
            handleSplitClick(card);
        });
    });

    document.querySelectorAll('.merge-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.card');
            const operationIdInput = this.closest('.d-flex').querySelector('.merge-operation-id');
            const sourceOperationId = operationIdInput.value.trim();
            
            if (!sourceOperationId) {
                alert('統合する運行IDを入力してください');
                return;
            }
            
            if (!/^\d+$/.test(sourceOperationId)) {
                alert('運行IDは数字で入力してください');
                return;
            }
            
            const targetBusId = card.getAttribute('data-bus-id');
            
            const targetOperationIdSpan = card.querySelector('.border.rounded');
            let targetOperationId = '';
            if (targetOperationIdSpan) {
                const idText = targetOperationIdSpan.textContent || '';
                const match = idText.match(/#(\d+)/);
                if (match) {
                    targetOperationId = match[1];
                }
            }
            
            if (sourceOperationId === targetOperationId) {
                alert('同じ運行ID (#' + sourceOperationId + ') には統合できません。');
                return;
            }
            
            const vehicleSelect = card.querySelector('.vehicle-select');
            const driverSelect = card.querySelector('.driver-select');
            
            const vehicleId = vehicleSelect ? vehicleSelect.value : '';
            const driverId = driverSelect ? driverSelect.value : '';
            
            const originalText = this.innerHTML;
            this.innerHTML = '統合中...';
            this.disabled = true;
            
            fetch('{{ route("masters.group-infos.merge-by-id", $groupInfo->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    target_bus_id: targetBusId,
                    source_operation_id: parseInt(sourceOperationId),
                    vehicle_id: vehicleId,
                    driver_id: driverId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('エラー: ' + data.message);
                    this.innerHTML = originalText;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('統合処理中にエラーが発生しました: ' + error.message);
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    });

    setupSearch('branch', branches, (item) => {
        return {
            display: item.branch_name,
            id: item.id,
            branch_name: item.branch_name,
            branch_code: item.branch_code
        };
    });

    setupSearch('category', categories, (item) => {
        return {
            display: item.category_name,
            id: item.id,
            category_name: item.category_name,
            category_code: item.category_code,
            color_code: item.color_code
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
    
    const staffs = @json($staffs ?? []);
    setupSearch('staff', staffs, (item) => {
        return {
            display: item.name,
            id: item.id,
            name: item.name
        };
    });
    
    const maxVehicleIndex = document.querySelectorAll('.vehicle-detail-card').length || 1;
    
    for (let i = 1; i <= maxVehicleIndex; i++) {
        setupSearch('vehicle', vehicles, (item) => {
            return {
                display: `${item.registration_number} ${item.vehicle_model ? '(' + item.vehicle_model.model_name + ')' : ''}`,
                id: item.id,
                registration_number: item.registration_number,
                vehicle_model: item.vehicle_model?.model_name || ''
            };
        }, i);
        
        setupSearch('driver', drivers, (item) => {
            return {
                display: `${item.name} ${item.driver_code ? '(' + item.driver_code + ')' : ''}`,
                id: item.id,
                name: item.name,
                driver_code: item.driver_code
            };
        }, i);
        
        setupSearch('guide', guides, (item) => {
            return {
                display: `${item.name} ${item.guide_code ? '(' + item.guide_code + ')' : ''}`,
                id: item.id,
                name: item.name,
                guide_code: item.guide_code
            };
        }, i);
    }
    
    const countries = @json($countries ?? []);
    setupSearch('country', countries, (item) => {
        return {
            display: item.country_name,
            id: item.id,
            country_name: item.country_name
        };
    });

    const initialVehicleSelects = document.querySelectorAll('[id^="vehicle_select_"]');
    initialVehicleSelects.forEach(select => {
        const id = select.id.replace('vehicle_select_', '');
        if (id && !isNaN(parseInt(id))) {
            setupSelectChangeHandlers(id);
        }
    });

    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    refreshEventListeners();
                }
            });
        });
        
        if (table.querySelector('tbody')) {
            observer.observe(table.querySelector('tbody'), { childList: true, subtree: true });
        }
        
        updateMoveButtons(table);
    });
    
    
        
    function initLockArrangementControl() {
        const lockCheckboxes = document.querySelectorAll('input[name*="[lock_arrangement]"]');
        if (lockCheckboxes.length === 0) return;
        
        lockCheckboxes.forEach(lockCheckbox => {
            const card = lockCheckbox.closest('.card');
            if (!card) return;
            
            function toggleLockFields(isLocked) {
                const lockableFields = [
                    // card.querySelectorAll('.vehicle-select'),
                    // card.querySelectorAll('.driver-select'),
                    card.querySelectorAll('.search-input[id^="vehicle_search_"]'),
                    card.querySelectorAll('.search-input[id^="driver_search_"]'),
                    card.querySelectorAll('input[name*="[start_date]"]'),
                    card.querySelectorAll('input[name*="[end_date]"]'),
                    card.querySelectorAll('input[name*="[start_time]"]'),
                    card.querySelectorAll('input[name*="[end_time]"]'),
                    card.querySelectorAll('input[name*="[start_location]"]'),
                    card.querySelectorAll('input[name*="[end_location]"]'),
                    card.querySelectorAll('.datepicker-3months'),
                    card.querySelectorAll('input[type="time"]'),
                ];
                
                lockableFields.forEach(fieldSet => {
                    fieldSet.forEach(element => {
                        if (isLocked) {
                            element.setAttribute('readonly', 'readonly');
                            element.classList.add('locked-field');
                        } else {
                            element.removeAttribute('readonly');
                            element.classList.remove('locked-field');
                        }
                    });
                });
                
                const buttonFields = [
                    card.querySelectorAll('.add-row-btn'),
                    card.querySelectorAll('.delete-row-btn'),
                    card.querySelectorAll('.move-up-btn'),
                    card.querySelectorAll('.move-down-btn'),
                ];
                
                buttonFields.forEach(fieldSet => {
                    fieldSet.forEach(element => {
                        if (isLocked) {
                            element.setAttribute('disabled', 'disabled');
                            element.classList.add('locked-field');
                        } else {
                            element.removeAttribute('disabled');
                            element.classList.remove('locked-field');
                        }
                    });
                });
                
                const selectFields = [
                    card.querySelectorAll('.vehicle-select'),
                    card.querySelectorAll('.driver-select'),
                    card.querySelectorAll('.guide-select'),
                ];
                
                selectFields.forEach(fieldSet => {
                    fieldSet.forEach(element => {
                        if (isLocked) {
                            element.setAttribute('disabled', 'disabled');
                            element.classList.add('locked-field');
                        } else {
                            element.removeAttribute('disabled');
                            element.classList.remove('locked-field');
                        }
                    });
                });
                
                card.querySelectorAll('.datepicker-3months').forEach(dateInput => {
                    if (dateInput._flatpickr) {
                        if (isLocked) {
                            dateInput._flatpickr.set('clickOpens', false);
                        } else {
                            dateInput._flatpickr.set('clickOpens', true);
                        }
                    }
                });
            }
            
            toggleLockFields(lockCheckbox.checked);
            
            lockCheckbox.addEventListener('change', function() {
                toggleLockFields(this.checked);
            });
        });
    }
    
    refreshEventListeners();
    
    initLockArrangementControl();
    
    bindInitialDeleteEvents();

    document.getElementById('editForm').addEventListener('submit', submitForm);
    
    



    function initNewCardSearch(cardIndex) {
        setupSearch('vehicle', vehicles, (item) => {
            return {
                display: `${item.registration_number} ${item.vehicle_model ? '(' + item.vehicle_model.model_name + ')' : ''}`,
                id: item.id,
                registration_number: item.registration_number,
                vehicle_model: item.vehicle_model?.model_name || ''
            };
        }, cardIndex);
        
        setupSearch('driver', drivers, (item) => {
            return {
                display: `${item.name} ${item.driver_code ? '(' + item.driver_code + ')' : ''}`,
                id: item.id,
                name: item.name,
                driver_code: item.driver_code
            };
        }, cardIndex);
        
        setupSearch('guide', guides, (item) => {
            return {
                display: `${item.name} ${item.guide_code ? '(' + item.guide_code + ')' : ''}`,
                id: item.id,
                name: item.name,
                guide_code: item.guide_code
            };
        }, cardIndex);
    }

});



function openIframeModal(url, title = '新規グループ作成') {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    
    if (!iframe || !modal) return;
    
    iframe.src = url;
    modalTitle.textContent = title;
    iframe.style.height = '480px';
    if (modalContent) modalContent.style.maxWidth = '550px';
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeIframeModal() {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    
    if (iframe) iframe.src = '';
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}


document.getElementById('newGroupBtn').addEventListener('click', function() {
    openIframeModal('{{ route('masters.group-infos.create') }}', '新規グループ作成');
});






function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

document.getElementById('btn-upload-file')?.addEventListener('click', function() {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.doc,.docx,.pdf,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png,.gif,.txt';
    fileInput.style.display = 'none';
    
    fileInput.onchange = function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        if (file.size > 10 * 1024 * 1024) {
            alert('ファイルサイズは10MB以下にしてください。');
            return;
        }
        
        uploadFile(file);
        document.body.removeChild(fileInput);
    };
    
    document.body.appendChild(fileInput);
    fileInput.click();
});

function uploadFile(file) {
    const groupId = {{ $groupInfo->id }};
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    const uploadBtn = document.getElementById('btn-upload-file');
    const originalText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> アップロード中...';
    uploadBtn.disabled = true;
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', `/masters/group-infos/${groupId}/upload-file`);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    refreshFileList();
                } else {
                    alert('アップロード失敗: ' + (response.message || '不明なエラー'));
                }
            } catch (e) {
                alert('アップロード失敗: サーバー応答が不正です');
            }
        } else {
            alert('アップロード失敗: HTTP ' + xhr.status);
        }
    };
    
    xhr.onerror = function() {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
        alert('アップロード失敗: ネットワークエラー');
    };
    
    xhr.send(formData);
}

function refreshFileList() {
    const groupId = {{ $groupInfo->id }};
    fetch(`/masters/group-infos/${groupId}/files`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            const fileList = document.getElementById('file-list');
            if (!fileList) return;
            
            if (data.success && data.files && data.files.length > 0) {
                let html = '';
                data.files.forEach(file => {
                    html += `
                        <div class="file-item" data-file-id="${file.id}" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-radius: 4px; margin-bottom: 4px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi ${file.icon}" style="color: #2563eb;"></i>
                                <span class="file-name" style="font-size: 12px;">${escapeHtml(file.file_name)}</span>
                                <span class="file-size" style="font-size: 11px; color: #6b7280;">(${file.size_for_humans})</span>
                            </div>
                            <div class="file-actions" style="display: flex; gap: 8px;">
                                <a href="/masters/group-files/${file.id}/download" class="btn-download" style="color: #2563eb; text-decoration: none;">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" class="btn-delete-file" data-file-id="${file.id}" style="background: none; border: none; color: #dc2626; cursor: pointer;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                fileList.innerHTML = html;
            } else {
                fileList.innerHTML = `
                    <div class="file-empty" style="text-align: center; padding: 60px 0 0 0; color: #9ca3af; font-size: 12px;">
                        <i class="bi bi-folder2-open"></i> ファイルはありません
                    </div>
                `;
            }
            
            bindDeleteFileEvents();
        })
        .catch(error => {
            console.error('刷新文件列表失败:', error);
        });
}

function bindDeleteFileEvents() {
    document.querySelectorAll('.btn-delete-file').forEach(btn => {
        const oldHandler = btn._deleteHandler;
        if (oldHandler) {
            btn.removeEventListener('click', oldHandler);
        }
        
        const handler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const fileId = this.getAttribute('data-file-id');
            
            if (!fileId) {
                alert('ファイルIDが見つかりません。');
                return;
            }
            
            if (!confirm('このファイルを削除してもよろしいですか？')) return;
            
            const url = `/masters/group-files/${fileId}`;
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    refreshFileList();
                } else {
                    alert('削除失敗: ' + data.message);
                }
            })
            .catch(error => {
                alert('削除失敗');
            });
        };
        
        btn.addEventListener('click', handler);
        btn._deleteHandler = handler;
    });
}

function bindInitialDeleteEvents() {
    document.querySelectorAll('.btn-delete-file').forEach(btn => {
        const oldHandler = btn._deleteHandler;
        if (oldHandler) {
            btn.removeEventListener('click', oldHandler);
        }
        
        const handler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const fileId = this.getAttribute('data-file-id');
            
            if (!fileId) {
                alert('ファイルIDが見つかりません。');
                return;
            }
            
            if (!confirm('このファイルを削除してもよろしいですか？')) return;
            
            const url = `/masters/group-files/${fileId}`;
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    refreshFileList();
                } else {
                    alert('削除失敗: ' + data.message);
                }
            })
            .catch(error => {
                alert('削除失敗');
            });
        };
        
        btn.addEventListener('click', handler);
        btn._deleteHandler = handler;
    });
}





function uploadFileToBus(file, busId, groupId, containerId) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    if (busId) {
        formData.append('bus_assignment_id', busId);
    }
    
    const uploadBtn = document.querySelector(`.btn-upload-file-bus[data-bus-id="${busId}"]`);
    const originalText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> アップロード中...';
    uploadBtn.disabled = true;
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', `/masters/group-infos/${groupId}/upload-file`);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    refreshFileListForBus(busId);
                } else {
                    alert('アップロード失敗: ' + (response.message || '不明なエラー'));
                }
            } catch (e) {
                alert('アップロード失敗: サーバー応答が不正です');
            }
        } else {
            alert('アップロード失敗: HTTP ' + xhr.status);
        }
    };
    
    xhr.onerror = function() {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
        alert('アップロード失敗: ネットワークエラー');
    };
    
    xhr.send(formData);
}

function refreshFileListForBus(busId) {
    const groupId = {{ $groupInfo->id }};
    fetch(`/masters/group-infos/${groupId}/files?bus_assignment_id=${busId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            const fileList = document.getElementById(`file-list-bus-${busId}`);
            if (!fileList) return;
            
            if (data.success && data.files && data.files.length > 0) {
                let html = '';
                data.files.forEach(file => {
                    html += `
                        <div class="file-item" data-file-id="${file.id}" style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-radius: 4px; margin-bottom: 4px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi ${file.icon}" style="color: #2563eb;"></i>
                                <span class="file-name" style="font-size: 11px;">${escapeHtml(file.file_name)}</span>
                                <span class="file-size" style="font-size: 10px; color: #6b7280;">(${file.size_for_humans})</span>
                            </div>
                            <div class="file-actions" style="display: flex; gap: 6px;">
                                <a href="/masters/group-files/${file.id}/download" class="btn-download" style="color: #2563eb; text-decoration: none;">
                                    <i class="bi bi-download" style="font-size: 12px;"></i>
                                </a>
                                <button type="button" class="btn-delete-file-bus" data-file-id="${file.id}" data-bus-id="${busId}" style="background: none; border: none; color: #dc2626; cursor: pointer;">
                                    <i class="bi bi-trash" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                fileList.innerHTML = html;
            } else {
                fileList.innerHTML = `
                    <div class="file-empty-bus" style="text-align: center; padding: 10px 0 0 0; color: #9ca3af; font-size: 11px;">
                        <i class="bi bi-folder2-open"></i> ファイルはありません
                    </div>
                `;
            }
            
            bindDeleteFileEventsForBus(busId);
        })
        .catch(error => {
            console.error('刷新文件列表失败:', error);
        });
}

function bindDeleteFileEventsForBus(busId) {
    const fileList = document.getElementById(`file-list-bus-${busId}`);
    if (!fileList) return;
    
    fileList.querySelectorAll('.btn-delete-file-bus').forEach(btn => {
        const oldHandler = btn._deleteHandler;
        if (oldHandler) {
            btn.removeEventListener('click', oldHandler);
        }
        
        const handler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const fileId = this.getAttribute('data-file-id');
            const busId = this.getAttribute('data-bus-id');
            
            if (!fileId) {
                alert('ファイルIDが見つかりません。');
                return;
            }
            
            if (!confirm('このファイルを削除してもよろしいですか？')) return;
            
            const url = `/masters/group-files/${fileId}`;
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    refreshFileListForBus(busId);
                } else {
                    alert('削除失敗: ' + data.message);
                }
            })
            .catch(error => {
                alert('削除失敗');
            });
        };
        
        btn.addEventListener('click', handler);
        btn._deleteHandler = handler;
    });
}

document.querySelectorAll('.btn-upload-file-bus').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const busId = this.getAttribute('data-bus-id');
        const groupId = {{ $groupInfo->id }};
        
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.doc,.docx,.pdf,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png,.gif,.txt';
        fileInput.style.display = 'none';
        
        fileInput.onchange = function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            if (file.size > 10 * 1024 * 1024) {
                alert('ファイルサイズは10MB以下にしてください。');
                return;
            }
            
            uploadFileToBus(file, busId, groupId);
            document.body.removeChild(fileInput);
        };
        
        document.body.appendChild(fileInput);
        fileInput.click();
    });
});

document.querySelectorAll('.file-list-bus').forEach(fileList => {
    const busId = fileList.getAttribute('data-bus-id');
    if (busId) {
        bindDeleteFileEventsForBus(busId);
    }
});







function initCompensationTable(container) {
    const tbody = container.querySelector('.compensation-tbody');
    if (!tbody) return;
    
    function updateAmount(row) {
        const price = parseFloat(row.querySelector('.compensation-price')?.value) || 0;
        const qty = parseFloat(row.querySelector('.compensation-qty')?.value) || 0;
        const amount = price * qty;
        const amountInput = row.querySelector('.compensation-amount');
        if (amountInput) {
            amountInput.value = amount;
        }
        updateTotalAmount(container);
    }
    
    function updateTotalAmount(container) {
        const rows = container.querySelectorAll('.compensation-row');
        let total = 0;
        rows.forEach(row => {
            const amount = parseFloat(row.querySelector('.compensation-amount')?.value) || 0;
            total += amount;
        });
        const displaySpan = container.querySelector('.total-amount-display');
        const hiddenInput = container.querySelector('.total-amount-hidden');
        if (displaySpan) {
            displaySpan.textContent = '¥ ' + total.toLocaleString();
        }
        if (hiddenInput) {
            hiddenInput.value = total;
        }
    }
    
    function reindexRows(container) {
        const rows = container.querySelectorAll('.compensation-row');
        const vehicleIndex = container.getAttribute('data-vehicle-index');
        rows.forEach((row, idx) => {
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/compensations\[\d+\]/, `compensations[${idx}]`);
                    input.setAttribute('name', newName);
                }
            });
            row.setAttribute('data-comp-index', idx);
        });
    }
    
    function addRow(row) {
        const newRow = document.createElement('tr');
        newRow.className = 'compensation-row';
        newRow.setAttribute('data-comp-index', 'new');
        
        const rowCount = tbody.querySelectorAll('.compensation-row').length;
        const vehicleIndex = container.getAttribute('data-vehicle-index');
        
        newRow.innerHTML = `
            <td>
                <input type="date" class="form-control form-control-sm" 
                       name="bus_assignments[${vehicleIndex}][compensations][${rowCount}][target_date]" value="">
            </td>
            <td>
                <select class="form-select form-select-sm compensation-type" 
                        name="bus_assignments[${vehicleIndex}][compensations][${rowCount}][comp_id]">
                    <option value="">-- 選択 --</option>
                    @foreach($compensationTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->comp_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm compensation-price" 
                       name="bus_assignments[${vehicleIndex}][compensations][${rowCount}][price]" value="" step="1" min="0">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm compensation-qty" 
                       name="bus_assignments[${vehicleIndex}][compensations][${rowCount}][qty]" value="" step="1" min="0">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm compensation-amount" 
                       name="bus_assignments[${vehicleIndex}][compensations][${rowCount}][amount]" value="0" readonly style="background-color: #f3f4f6;">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-success add-compensation-row" style="padding: 2px 6px; font-size: 10px;">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-compensation-row" style="padding: 2px 6px; font-size: 10px;">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </td>
        `;
        
        if (row) {
            row.parentNode.insertBefore(newRow, row.nextSibling);
        } else {
            tbody.appendChild(newRow);
        }
        
        const newDateInput = newRow.querySelector('.compensation-date');
        if (newDateInput && !newDateInput._flatpickr) {
            flatpickr(newDateInput, {
                locale: 'ja',
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true
            });
        }
        
        bindRowEvents(newRow, container);
        reindexRows(container);
        updateRemoveButtons(container);
        updateTotalAmount(container);
    }
    
    function removeRow(row, container) {
        const rows = container.querySelectorAll('.compensation-row');
        const rowId = row.getAttribute('data-comp-id');
        
        const idInput = row.querySelector('input[name*="[id]"]');
        const recordId = idInput ? idInput.value : null;
        
        row.remove();
        
        const remainingRows = container.querySelectorAll('.compensation-row');
        if (remainingRows.length === 0) {
            const busId = container.getAttribute('data-bus-id');
            const vehicleIndex = container.getAttribute('data-vehicle-index');
            
            const deletedIdsInput = document.createElement('input');
            deletedIdsInput.type = 'hidden';
            deletedIdsInput.name = `bus_assignments[${vehicleIndex}][deleted_compensation_ids][]`;
            deletedIdsInput.value = recordId;
            deletedIdsInput.className = 'deleted-compensation-id';
            container.appendChild(deletedIdsInput);
            
            const emptyHtml = `
                <div class="text-center py-3">
                    <button type="button" class="btn btn-sm btn-outline-primary add-first-compensation-row" 
                            data-bus-id="${busId}" data-vehicle-index="${vehicleIndex}"
                            style="font-size: 11px; padding: 4px 12px;">
                        <i class="bi bi-plus-lg"></i> 手当を追加
                    </button>
                </div>
            `;
            
            const hiddenInput = container.querySelector('.total-amount-hidden');
            container.innerHTML = emptyHtml;
            if (hiddenInput) container.appendChild(hiddenInput);
            container.appendChild(deletedIdsInput);
            
            if (typeof refreshEventListeners === 'function') {
                refreshEventListeners();
            }
            return;
        }
        
        reindexRows(container);
        updateRemoveButtons(container);
        updateTotalAmount(container);
    }
    
    function updateRemoveButtons(container) {
        const rows = container.querySelectorAll('.compensation-row');
        rows.forEach(row => {
            const removeBtn = row.querySelector('.remove-compensation-row');
            if (removeBtn) {
                removeBtn.disabled = false;
            }
        });
    }
    
    function bindRowEvents(row, container) {
        const priceInput = row.querySelector('.compensation-price');
        const qtyInput = row.querySelector('.compensation-qty');
        
        if (priceInput) {
            priceInput.removeEventListener('input', priceInput._inputHandler);
            priceInput._inputHandler = () => updateAmount(row);
            priceInput.addEventListener('input', priceInput._inputHandler);
        }
        
        if (qtyInput) {
            qtyInput.removeEventListener('input', qtyInput._inputHandler);
            qtyInput._inputHandler = () => updateAmount(row);
            qtyInput.addEventListener('input', qtyInput._inputHandler);
        }
        
        const addBtn = row.querySelector('.add-compensation-row');
        if (addBtn) {
            addBtn.removeEventListener('click', addBtn._clickHandler);
            addBtn._clickHandler = () => addRow(row);
            addBtn.addEventListener('click', addBtn._clickHandler);
        }
        
        const removeBtn = row.querySelector('.remove-compensation-row');
        if (removeBtn) {
            removeBtn.removeEventListener('click', removeBtn._clickHandler);
            removeBtn._clickHandler = () => removeRow(row, container);
            removeBtn.addEventListener('click', removeBtn._clickHandler);
        }
        
        updateAmount(row);
    }
    
    const existingRows = tbody.querySelectorAll('.compensation-row');
    existingRows.forEach(row => {
        bindRowEvents(row, container);
        
        const dateInput = row.querySelector('.compensation-date');
        if (dateInput && !dateInput._flatpickr) {
            flatpickr(dateInput, {
                locale: 'ja',
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true
            });
        }
    });
    
    updateRemoveButtons(container);
    updateTotalAmount(container);
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.compensation-container').forEach(container => {
            initCompensationTable(container);
        });
    }, 100);
});



function bindPdfExportEvents() {
    document.querySelectorAll('.btn-pdf-export').forEach(btn => {
        btn.removeEventListener('click', btn._pdfHandler);
        const pdfHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const busId = this.getAttribute('data-bus-id');
            const url = `/masters/group-infos/${busId}/export-pdf-bus-assignment`;
            window.open(url, '_blank');
        };
        btn.addEventListener('click', pdfHandler);
        btn._pdfHandler = pdfHandler;
    });
}




        
        
        



function initExpenseTable(container) {
    const tbody = container.querySelector('.expense-tbody');
    if (!tbody) return;
    
    function updateTotalAmount(container) {
        const rows = container.querySelectorAll('.expense-row');
        let total = 0;
        rows.forEach(row => {
            const amount = parseFloat(row.querySelector('.expense-amount')?.value) || 0;
            total += amount;
        });
        const displaySpan = container.querySelector('.total-expense-display');
        const hiddenInput = container.querySelector('.total-expense-hidden');
        if (displaySpan) {
            displaySpan.textContent = '¥ ' + total.toLocaleString();
        }
        if (hiddenInput) {
            hiddenInput.value = total;
        }
    }
    
    function reindexRows(container) {
        const rows = container.querySelectorAll('.expense-row');
        const vehicleIndex = container.getAttribute('data-vehicle-index');
        rows.forEach((row, idx) => {
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/expenses\[\d+\]/, `expenses[${idx}]`);
                    input.setAttribute('name', newName);
                }
            });
            row.setAttribute('data-expense-index', idx);
            
            const checkbox = row.querySelector('.expense-agency');
            if (checkbox) {
                const newId = `expense_agency_${vehicleIndex}_${idx}`;
                checkbox.id = newId;
                const label = row.querySelector('label[for^="expense_agency_"]');
                if (label) {
                    label.setAttribute('for', newId);
                }
            }
        });
    }
    
    function addExpenseRow(row) {
        const container = row.closest('.expense-container');
        const tbody = container.querySelector('.expense-tbody');
        const rowCount = tbody.querySelectorAll('.expense-row').length;
        const vehicleIndex = container.getAttribute('data-vehicle-index');
        const defaultDate = new Date().toISOString().split('T')[0];
        
        const newRow = document.createElement('tr');
        newRow.className = 'expense-row';
        newRow.setAttribute('data-expense-index', rowCount);
        newRow.innerHTML = `
            <td>
                <input type="date" class="form-control form-control-sm expense-date" 
                       name="bus_assignments[${vehicleIndex}][expenses][${rowCount}][expense_date]" 
                       value="${defaultDate}">
            </td>
            <td>
                <select class="form-select form-select-sm expense-type" 
                        name="bus_assignments[${vehicleIndex}][expenses][${rowCount}][type_id]">
                    <option value="">-- 選択 --</option>
                    @foreach($expenseTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm expense-amount text-end" 
                       name="bus_assignments[${vehicleIndex}][expenses][${rowCount}][amount]" 
                       value="" step="1" min="0">
            </td>
            <td>
                <select class="form-select form-select-sm expense-payment" 
                        name="bus_assignments[${vehicleIndex}][expenses][${rowCount}][payment_method_id]">
                    <option value="">-- 選択 --</option>
                    @foreach($paymentMethods ?? [] as $method)
                        <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center">
                <div class="form-check d-flex justify-content-center" style="margin: 0; min-height: 1rem;">
                    <input type="checkbox" class="form-check-input expense-agency" 
                           name="bus_assignments[${vehicleIndex}][expenses][${rowCount}][agency_flag]" 
                           value="1" id="expense_agency_${vehicleIndex}_${rowCount}">
                </div>
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-success add-expense-row" style="padding: 2px 5px;">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-expense-row" style="padding: 2px 5px;">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </td>
        `;
        
        if (row) {
            row.parentNode.insertBefore(newRow, row.nextSibling);
        } else {
            tbody.appendChild(newRow);
        }
        
        bindExpenseRowEvents(newRow, container);
        reindexRows(container);
        updateTotalAmount(container);
    }
    
    function removeExpenseRow(row, container) {
        const tbody = container.querySelector('.expense-tbody');
        const rows = tbody.querySelectorAll('.expense-row');
        
        const idInput = row.querySelector('input[name*="[id]"]');
        const recordId = idInput ? idInput.value : null;
        
        if (recordId && recordId !== '') {
            const vehicleIndex = container.getAttribute('data-vehicle-index');
            const deletedIdsInput = document.createElement('input');
            deletedIdsInput.type = 'hidden';
            deletedIdsInput.name = `bus_assignments[${vehicleIndex}][deleted_expense_ids][]`;
            deletedIdsInput.value = recordId;
            deletedIdsInput.className = 'deleted-expense-id';
            container.appendChild(deletedIdsInput);
        }
        
        row.remove();
        
        const remainingRows = tbody.querySelectorAll('.expense-row');
        if (remainingRows.length === 0) {
            const busId = container.getAttribute('data-bus-id');
            const vehicleIndex = container.getAttribute('data-vehicle-index');
            const hiddenInput = container.querySelector('.total-expense-hidden');
            
            container.innerHTML = `
                <div class="text-center py-3">
                    <button type="button" class="btn btn-sm btn-outline-primary add-first-expense-row" 
                            data-bus-id="${busId}" data-vehicle-index="${vehicleIndex}"
                            style="font-size: 11px; padding: 4px 12px;">
                        <i class="bi bi-plus-lg"></i> 立替を追加
                    </button>
                </div>
            `;
            if (hiddenInput) container.appendChild(hiddenInput);
            return;
        }
        
        reindexRows(container);
        updateTotalAmount(container);
    }
    
    function bindExpenseRowEvents(row, container) {
        const amountInput = row.querySelector('.expense-amount');
        if (amountInput) {
            amountInput.removeEventListener('input', amountInput._inputHandler);
            amountInput._inputHandler = () => updateTotalAmount(container);
            amountInput.addEventListener('input', amountInput._inputHandler);
        }
        
        const addBtn = row.querySelector('.add-expense-row');
        if (addBtn) {
            addBtn.removeEventListener('click', addBtn._clickHandler);
            addBtn._clickHandler = () => addExpenseRow(row);
            addBtn.addEventListener('click', addBtn._clickHandler);
        }
        
        const removeBtn = row.querySelector('.remove-expense-row');
        if (removeBtn) {
            removeBtn.removeEventListener('click', removeBtn._clickHandler);
            removeBtn._clickHandler = () => removeExpenseRow(row, container);
            removeBtn.addEventListener('click', removeBtn._clickHandler);
        }
        
        const agencyCheckbox = row.querySelector('.expense-agency');
        const agencyLabel = row.querySelector('label[for^="expense_agency_"]');
        if (agencyCheckbox && agencyLabel) {
            agencyLabel.removeEventListener('click', agencyLabel._clickHandler);
            agencyLabel._clickHandler = function(e) {
                e.preventDefault();
                agencyCheckbox.checked = !agencyCheckbox.checked;
            };
            agencyLabel.addEventListener('click', agencyLabel._clickHandler);
        }
    }
    
    const existingRows = tbody.querySelectorAll('.expense-row');
    existingRows.forEach(row => {
        bindExpenseRowEvents(row, container);
    });
    
    updateTotalAmount(container);
}

document.querySelectorAll('.expense-container').forEach(container => {
    if (container.querySelector('.expense-tbody')) {
        initExpenseTable(container);
    }
});

document.querySelectorAll('.add-first-expense-row').forEach(btn => {
    btn.removeEventListener('click', btn._firstAddHandler);
    const firstAddHandler = function() {
        const busId = this.getAttribute('data-bus-id');
        const vehicleIndex = this.getAttribute('data-vehicle-index');
        const container = this.closest('.expense-container');
        
        const tableHtml = `

            <table class="table table-sm table-bordered expense-table" style="font-size: 11px; margin-bottom: 5px;">
                <thead>
                    <tr>
                        <th style="width: 15%; background-color: #f8f9fa; text-align: center;">日付</th>
                        <th style="width: 20%; background-color: #f8f9fa; text-align: center;">種別</th>
                        <th style="width: 12%; background-color: #f8f9fa; text-align: center;">金額</th>
                        <th style="width: 15%; background-color: #f8f9fa; text-align: center;">支払方法</th>
                        <th style="width: 8%; background-color: #f8f9fa; text-align: center;">代理店</th>
                        <th style="width: 10%; background-color: #f8f9fa; text-align: center;">操作</th>
                    </tr>
                </thead>
                <tbody class="expense-tbody">
                    <tr class="expense-row" data-expense-index="0">
                        <td>
                            <input type="date" class="form-control form-control-sm expense-date" 
                                   name="bus_assignments[${vehicleIndex}][expenses][0][expense_date]" 
                                   value="${new Date().toISOString().split('T')[0]}">
                        </td>
                        <td>
                            <select class="form-select form-select-sm expense-type" 
                                    name="bus_assignments[${vehicleIndex}][expenses][0][type_id]">
                                <option value="">-- 選択 --</option>
                                @foreach($expenseTypes ?? [] as $type)
                                    <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                @endforeach
                            </select>
                         </td>
                        <td>
                            <input type="number" class="form-control form-control-sm expense-amount text-end" 
                                   name="bus_assignments[${vehicleIndex}][expenses][0][amount]" 
                                   value="" step="1" min="0">
                         </td>
                        <td>
                            <select class="form-select form-select-sm expense-payment" 
                                    name="bus_assignments[${vehicleIndex}][expenses][0][payment_method_id]">
                                <option value="">-- 選択 --</option>
                                @foreach($paymentMethods ?? [] as $method)
                                    <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                @endforeach
                            </select>
                         </td>
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center" style="margin: 0; min-height: 1rem;">
                                <input type="checkbox" class="form-check-input expense-agency" 
                                       name="bus_assignments[${vehicleIndex}][expenses][0][agency_flag]" 
                                       value="1" id="expense_agency_${vehicleIndex}_0">
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-outline-success add-expense-row" style="padding: 2px 5px;">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-expense-row" style="padding: 2px 5px;">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td colspan="2" class="text-end">合計</td>
                        <td class="text-end"><span class="total-expense-display">¥ 0</span></td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
            <input type="hidden" class="total-expense-hidden" name="bus_assignments[${vehicleIndex}][expenses_total]" value="0">
        `;
        
        container.innerHTML = tableHtml;
        initExpenseTable(container);
    };
    btn.addEventListener('click', firstAddHandler);
    btn._firstAddHandler = firstAddHandler;
});



</script>
@endpush