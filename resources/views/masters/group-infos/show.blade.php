@extends('layouts.app')

@section('title', 'グループ情報詳細')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 page-title">グループ情報詳細</h5>
            <div class="d-flex gap-2 ms-5">
                <a href="{{ route('masters.drivers.index') }}" class="btn btn-outline-primary btn-sm px-2">
                    運転台帳
                </a>
                <a href="{{ route('masters.drivers.index') }}" class="btn btn-outline-primary btn-sm px-2">
                    運転手台帳
                </a>
                <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-outline-primary btn-sm px-2">
                    運転一覧
                </a>
                <a href="{{ route('masters.group-infos.index') }}" class="btn btn-outline-primary btn-sm px-2">
                    予約一覧
                </a>
                <a href="#" class="btn btn-outline-primary btn-sm px-2">
                    乘務指示書
                </a>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('masters.group-infos.edit', $groupInfo->id) }}" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-pencil"></i> 編集
            </a>
            <a href="{{ route('masters.group-infos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>
    
    <div id="alert-container"></div>
    
    <div class="card shadow-sm mb-1" style="overflow: hidden;">
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
                                        <span class="border px-2 py-1 bg-white rounded text-center w-100" style="background-color: 
                                            @switch($groupInfo->reservation_status)
                                                @case('予約') #ccf5ff
                                                @case('確定') #cbb87c
                                                @case('キャンセル') #d3d3d3
                                                @case('稼働不可') #2c2c2c; color:#fff
                                                @default #e5e7eb
                                            @endswitch">
                                            {{ $groupInfo->reservation_status ?? '不明' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-flex w-50 gap-2">
                                    <div class="d-flex align-items-center flex-fill">
                                        <span class="span-label" style="min-width: 50px;">担当</span>
                                        <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->agency_contact_name ?? '--' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center flex-fill">
                                        <span class="span-label" style="min-width: 50px;">営業所</span>
                                        <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->vehicle_branch ?? '--' }}</span>
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
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->agency ?? '--' }}</span>
                                </div>
                                
                                <div class="d-flex w-50 gap-2">
                                    <div class="d-flex align-items-center flex-fill">
                                        <span class="span-label" style="min-width: 50px; white-space: nowrap;">業務</span>
                                        <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->reservationCategory ? $groupInfo->reservationCategory->category_name : ($groupInfo->business_category ?? '--') }}</span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center flex-fill">
                                        <span class="span-label" style="min-width: 50px; white-space: nowrap;">行程</span>
                                        <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->itinerary_name ?? '--' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-md-12">
                            <div class="d-flex w-100 gap-2">
                                <div class="d-flex align-items-center w-50">
                                    <span class="span-label" style="min-width: 50px; white-space: nowrap;">団体名</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->group_name ?? '--' }}</span>
                                </div>
                                
                                <div class="d-flex w-50 gap-2">
                                    <div class="d-flex align-items-center w-50">
                                        <span class="span-label" style="min-width: 50px; white-space: nowrap;">人数</span>
                                        <div class="d-flex gap-1 flex-fill">
                                            <span class="border px-2 py-1 bg-white rounded text-center flex-fill">大人: {{ $groupInfo->adult_count ?? 0 }}</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center flex-fill">小人: {{ $groupInfo->child_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center w-50">
                                        <span class="span-label" style="min-width: 50px; white-space: nowrap;">荷物数</span>
                                        <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->luggage ?? '0' }}</span>
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
                                        <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->agt_tour_id ?? '--' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center flex-fill">
                                        <span class="span-label" style="min-width: 50px;">国籍</span>
                                        <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->agency_country ?? '--' }}</span>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center w-50">
                                    <span class="span-label" style="min-width: 50px; white-space: normal; word-break: keep-all;">オプション</span>
                                    <div class="flex-1 w-100">
                                        <div class="options-container" style="display: flex; flex-wrap: wrap; gap: 12px; border: 1px solid #aaa; border-radius: 4px; padding: 6px 10px; min-height: 32px; background-color: white;">
                                            @php
                                                $selectedOptions = $groupInfo->options ? explode(',', $groupInfo->options) : [];
                                                $allOptions = \App\Models\Masters\Option::where('is_active', true)->orderBy('display_order')->get();
                                            @endphp
                                            @foreach($allOptions as $option)
                                            <div class="d-flex align-items-center" style="gap: 4px;">
                                                <span class="badge bg-secondary" style="min-width: 24px; text-align: center;">{{ in_array($option->id, $selectedOptions) ? '✓' : '' }}</span>
                                                <span style="font-size: 11px;">{{ $option->name }}</span>
                                            </div>
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
                                <span class="border px-2 py-1 bg-white rounded w-100" style="min-height: 80px;">{{ $groupInfo->remarks ?? '--' }}</span>
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

                    <div id="tabContent" class="tab-content" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 193px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; overflow: auto;">
                        
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
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="file-empty" style="text-align: center; padding: 60px 0 0 0; color: #9ca3af; font-size: 12px;">
                                            <i class="bi bi-folder2-open"></i> ファイルはありません
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    
                        <div class="tab-pane" id="customer-tab" style="display: none;">
                            <div class="d-flex align-items-center mb-2">
                                <span class="span-label" style="white-space: nowrap; min-width: 70px;">担当</span>
                                <span class="border px-2 py-1 bg-white rounded w-100">{{ $groupInfo->agency_contact_name ?? '--' }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="span-label" style="white-space: nowrap; min-width: 70px;">国籍</span>
                                <span class="border px-2 py-1 bg-white rounded w-100">{{ $groupInfo->agency_country ?? '--' }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="span-label" style="white-space: nowrap; min-width: 70px;">予約ID</span>
                                <span class="border px-2 py-1 bg-white rounded w-100">{{ $groupInfo->id }}</span>
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
        @php
            $busAssignments = $groupInfo->busAssignments->sortBy('vehicle_index');
        @endphp
        
        @foreach($busAssignments as $index => $busAssignment)
            @php 
                $vehicleIndex = $loop->iteration;
                $vehicleName = $busAssignment->vehicle ? $busAssignment->vehicle->registration_number . ($busAssignment->vehicle->vehicleModel ? ' (' . $busAssignment->vehicle->vehicleModel->model_name . ')' : '') : '未分配车辆';
                $driverName = $busAssignment->driver ? $busAssignment->driver->name : '';
                $vehicleId = $busAssignment->vehicle_id;
                $driverId = $busAssignment->driver_id;
                
                // 计算完成状态
                $busItineraries = $groupInfo->dailyItineraries->where('bus_assignment_id', $busAssignment->id);
                $allCompleted = true;
                foreach ($busItineraries as $itinerary) {
                    if ($itinerary->operation_status !== '終了') {
                        $allCompleted = false;
                        break;
                    }
                }
                $completionStatus = $allCompleted ? '完成' : '未完成';
            @endphp
            <div class="card shadow-sm mb-1 vehicle-detail-card" data-vehicle-id="{{ $vehicleId }}" data-vehicle-index="{{ $vehicleIndex }}" data-bus-id="{{ $busAssignment->id }}">
                <div class="card-header py-1 px-3 d-flex align-items-center" style="background-color: #141c28; border-bottom: 1px solid #aaa;">
                    <h6 class="mb-0 me-3" style="color: #fff; font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                        <span>運行詳細-{{ sprintf('%02d', $vehicleIndex) }}</span>
                        <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; background-color: {{ $completionStatus == '完成' ? '#10b981' : '#f59e0b' }}; color: white;">
                            {{ $completionStatus }}
                        </span>
                        <!--<span style="font-size: 0.75rem; color: #a0aec0;">-->
                        <!--    {{ $vehicleName }} -->
                        <!--    @if($driverName)-->
                        <!--        ({{ $driverName }})-->
                        <!--    @endif-->
                        <!--</span>-->
                    </h6>
                    <div class="d-flex align-items-center ms-auto" style="gap: 15px;">
                        <div class="form-check d-flex align-items-center">
                            <label class="form-check-label me-2" style="font-size: 0.8rem; color: #fff;">最終確認</label>
                            <span class="badge bg-secondary">{{ $busAssignment->status_finalized ? '✓' : '' }}</span>
                        </div>
                        
                        <div class="form-check d-flex align-items-center">
                            <label class="form-check-label me-2" style="font-size: 0.8rem; color: #fff;">送信済</label>
                            <span class="badge bg-secondary">{{ $busAssignment->status_sent ? '✓' : '' }}</span>
                        </div>
                            
                        <div class="form-check d-flex align-items-center">
                            <label class="form-check-label me-2" style="font-size: 0.8rem; color: #fff;">操作ロック</label>
                            <span class="badge bg-secondary">{{ $busAssignment->lock_arrangement ? '✓' : '' }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="row" style="margin-right: -5px; margin-left: -5px;">
                        <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
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
                                            
                                            <div class="d-flex align-items-center" style="width: 30%;">
                                                <span class="span-label" style="width: auto !important;">車種指定</span>
                                                <span class="badge bg-secondary">{{ $busAssignment->vehicle_type_spec_check ? '✓' : '' }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex" style="width: 50%; gap: 8px;">
                                            <div class="d-flex align-items-center" style="width: 50%;">
                                                <span class="span-label">車両等級</span>
                                                <span class="border px-2 py-1 bg-white rounded w-100">{{ $busAssignment->vehicleGrade ? $busAssignment->vehicleGrade->description . ' (' . $busAssignment->vehicleGrade->grade_name . ')' : '--' }}</span>
                                            </div>
                                            
                                            <div class="d-flex align-items-center" style="width: 50%;">
                                                <span class="span-label">号車</span>
                                                <span class="border px-2 py-1 bg-white rounded w-100">{{ $busAssignment->vehicle_number ?? sprintf('%02d', $vehicleIndex) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex w-100">
                                        <div class="d-flex align-items-center" style="width: 50%;">
                                            <span class="span-label" style="white-space: normal; word-break: break-all; line-height: 1.2; min-width: 70px;">ステップカー</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="flex: 1;">{{ $busAssignment->step_car ?? '' }}</span>
                                        </div>
                                        
                                        <div class="d-flex" style="width: 50%; gap: 8px;">
                                            <div class="d-flex align-items-center" style="width: 50%;">
                                                <span class="span-label">人数</span>
                                                <div class="d-flex gap-1 flex-fill">
                                                    <span class="border px-2 py-1 bg-white rounded text-center flex-fill">大: {{ $busAssignment->adult_count ?? 0 }}</span>
                                                    <span class="border px-2 py-1 bg-white rounded text-center flex-fill">小: {{ $busAssignment->child_count ?? 0 }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex align-items-center" style="width: 50%;">
                                                <span class="span-label">荷物</span>
                                                <span class="border px-2 py-1 bg-white rounded w-100">{{ $busAssignment->luggage ?? '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex w-100">
                                        <div class="d-flex align-items-center" style="width: 50%;">
                                            <span class="span-label">車両</span>
                                            <span class="border px-2 py-1 bg-white rounded w-100">{{ $vehicleName }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="width: 25%;">
                                            <span class="span-label">代表</span>
                                            <span class="border px-2 py-1 bg-white rounded w-100">{{ $busAssignment->representative ?? '--' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="width: 25%;">
                                            <span class="border px-2 py-1 bg-white rounded w-100 ms-2">{{ $busAssignment->representative_phone ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex w-100">
                                        <div class="d-flex align-items-center" style="width: 35%;">
                                            <span class="span-label">運転手</span>
                                            <span class="border px-2 py-1 bg-white rounded w-100">{{ $driverName ?: '--' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="width: 15%;">
                                            <span class="span-label">仮</span>
                                            <span class="badge bg-secondary">{{ $busAssignment->temporary_driver ? '✓' : '' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="width: 50%;">
                                            <span class="span-label">添乗</span>
                                            <span class="border px-2 py-1 bg-white rounded w-100">{{ $busAssignment->guide ? $busAssignment->guide->name : '--' }}</span>
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
                                    <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $vehicleIndex }}" data-tab2="history2-{{ $vehicleIndex }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">精算</span>
                                </div>

                                <div id="basic2-{{ $vehicleIndex }}" class="tab-content2" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: auto; height: 102px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">
                                    <div class="file-manager-{{ $vehicleIndex }}" style="display: flex; flex-direction: column; height: 100%;">
                                        <div class="file-list file-list-bus" id="file-list-bus-{{ $busAssignment->id }}" data-bus-id="{{ $busAssignment->id }}" style="flex: 1; overflow-y: auto; margin-bottom: 10px;">
                                            @php
                                                $busFiles = \App\Models\Masters\GroupInfoFile::where('bus_assignment_id', $busAssignment->id)
                                                    ->orderBy('created_at', 'desc')
                                                    ->get();
                                            @endphp
                                            @if($busFiles->count() > 0)
                                                @foreach($busFiles as $file)
                                                <div class="file-item" data-file-id="{{ $file->id }}" style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-radius: 4px; margin-bottom: 4px;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <i class="bi {{ $file->icon }}" style="color: #2563eb;"></i>
                                                        <span class="file-name" style="font-size: 11px;">{{ $file->file_name }}</span>
                                                        <span class="file-size" style="font-size: 10px; color: #6b7280;">({{ $file->size_for_humans }})</span>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @else
                                                <div class="file-empty-bus" style="text-align: center; padding: 20px 0; color: #9ca3af; font-size: 11px;">
                                                    <i class="bi bi-folder2-open"></i> ファイルはありません
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div id="doc-{{ $vehicleIndex }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px;">
                                    <div class="dashed-box" style="color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db;">
                                        ---
                                    </div>
                                </div>

                                <div id="history2-{{ $vehicleIndex }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: auto; min-height: 100px; max-height: 100px; overflow-y: auto;">
                                    @php
                                        $singleLogs = \App\Models\Masters\BusAssignmentLog::where('bus_assignment_id', $busAssignment->id)
                                            ->orderBy('created_at', 'desc')
                                            ->get();
                                    @endphp
                                    @if($singleLogs->count() > 0)
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
                                                @foreach($singleLogs as $logIndex => $log)
                                                    <tr>
                                                        <td style="text-align: center;">{{ $logIndex + 1 }}</td>
                                                        <td>{{ $log->action_description }}</td>
                                                        <td>{{ $log->username ?? $log->user_id ?? 'system' }}</td>
                                                        <td style="white-space: nowrap;">{{ $log->created_at ? $log->created_at->format('Y/m/d H:i:s') : '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="dashed-box" style="color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db;">
                                            --
                                        </div>
                                    @endif
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
                                     </thead>
                                <tbody>
                                    @php
                                        $itineraries = $groupInfo->dailyItineraries->where('bus_assignment_id', $busAssignment->id)->sortBy('date');
                                    @endphp
                                    @forelse($itineraries as $itinerary)
                                    <tr>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($itinerary->date)->format('Y/m/d') }}</td>
                                        <td>
                                            {{ substr($itinerary->time_start, 0, 5) }}<br>
                                            <small>{{ $itinerary->start_location ?? '' }}</small>
                                        </td>
                                        <td>
                                            {{ substr($itinerary->time_end, 0, 5) }}<br>
                                            <small>{{ $itinerary->end_location ?? '' }}</small>
                                        </td>
                                        <td>{{ $itinerary->itinerary ?? '' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">行程データがありません</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-2" style="margin-right: -5px; margin-left: -5px;">
                        <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
                            <div class="d-flex w-100 mb-1">
                                <span class="span-label" style="min-width: 30px;">オプション</span>
                                    <div class="options-container w-100" style="display: flex; flex-wrap: wrap; gap: 8px; border: 1px solid #aaa; border-radius: 4px; padding: 4px 8px; min-height: 28px; background-color: white;">
                                        @php
                                            $busOptions = $busAssignment->options ? explode(',', $busAssignment->options) : [];
                                        @endphp
                                        @foreach($allOptions as $option)
                                        <div class="d-flex align-items-center" style="gap: 4px;">
                                            <span class="badge bg-secondary" style="min-width: 24px; text-align: center;">{{ in_array($option->id, $busOptions) ? '✓' : '' }}</span>
                                            <span style="font-size: 11px;">{{ $option->name }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                            </div>
                                
                            <div class="d-flex w-100">
                                <span class="span-label" style="min-width: 30px;">備考</span>
                                <span class="border px-2 py-1 bg-white rounded w-100">{{ $busAssignment->operation_remarks ?? '' }}</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                            <div class="border px-2 py-1 bg-white rounded w-100" style="min-height: 62px; height: auto; white-space: pre-wrap;">{{ $busAssignment->operation_memo ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="d-flex align-items-center gap-4 my-2">
        <div class="form-check d-flex align-items-center">
            <span class="badge bg-secondary me-2">{{ $groupInfo->ignore_operation ? '✓' : '' }}</span>
            <span class="form-check-label" style="font-size: 0.9rem;">運行無視</span>
        </div>
        <div class="form-check d-flex align-items-center">
            <span class="badge bg-secondary me-2">{{ $groupInfo->ignore_attendance ? '✓' : '' }}</span>
            <span class="form-check-label" style="font-size: 0.9rem;">勤怠無視</span>
        </div>
    </div>

    <div id="edit-section" class="d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex gap-2">
            <a href="{{ route('masters.group-infos.edit', $groupInfo->id) }}" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-pencil"></i> 編集
            </a>
            <a href="{{ route('masters.group-infos.index') }}" class="btn btn-sm btn-outline-secondary px-3 ms-2">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>
</div>
@endsection


@push('styles')
<style>
/* 保持原有样式，添加缺失的样式 */
.rounded { min-height: 29px; }
.text-gray { color: #6b7280; font-size: 11px; }
.span-label { text-align: right; min-width: 70px !important; width: 70px !important; font-size: 0.8rem; margin-right: 10px; white-space: normal; word-break: keep-all;}
.card {
    border: 1px solid #999;
    overflow: hidden;
}
.card-body {
    background-color:#f3f4f6;
    font-size: 0.8rem;
}
.tab-content2 {
    border: 1px #E5E7EB solid;
    border-top: 0;
    background-color: #fff;
    padding: 10px;
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
.page-title {
    color: #374151;
    font-size: 1rem;
}
.badge {
    background-color: #0d6efd;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
}
.bg-secondary {
    background-color: #6c757d !important;
}
.border {
    border: 1px solid #aaa !important;
    background-color: #fff;
    border-radius: 4px;
    padding: 4px 6px;
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

.tab-item {
    font-size: 11px;
    padding: 6px 16px;
    border-radius: 4px 4px 0 0;
    cursor: pointer;
}
.tab-item.active {
    background-color: white;
    color: #374151;
    border-top: 1px solid #d1d5db;
    border-left: 1px solid #d1d5db;
    border-right: 1px solid #d1d5db;
    border-bottom: none;
    z-index: 2;
}
.tab-item.inactive {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #aaa;
    border-bottom: 1px solid #d1d5db;
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

.dashed-box {
    color: #6b7280;
    font-size: 11px;
    padding: 16px;
    background-color: #f9fafb;
    border-radius: 4px;
    text-align: center;
    border: 1px dashed #d1d5db;
}

.options-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    border: 1px solid #aaa;
    border-radius: 4px;
    padding: 4px 8px;
    min-height: 28px;
    background-color: white;
}

.file-list {
    overflow-y: auto;
    min-height: 0;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 10px;
    border-bottom: 1px solid #e5e7eb;
    background-color: #f9fafb;
    border-radius: 4px;
    margin-bottom: 4px;
}

.file-actions {
    display: flex;
    gap: 6px;
}

.btn-download {
    color: #2563eb;
    text-decoration: none;
}

.btn-delete-file {
    background: none;
    border: none;
    color: #dc2626;
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 选项卡切换功能
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
    
    // 运行详细中的选项卡切换
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
    

});
</script>
@endpush