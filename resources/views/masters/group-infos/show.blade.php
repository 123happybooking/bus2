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
                            <div class="d-flex w-100">
                                <div class="d-flex align-items-center me-2 flex-fill">
                                    <span class="span-label">予約ID</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center" style="color: #2563eb;">{{ $groupInfo->id ?? '0' }}</span>
                                </div>
                                <div class="d-flex align-items-center me-2 flex-fill">
                                    <span class="span-label">状態</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center" style="background-color: 
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
                                <div class="d-flex align-items-center me-2 flex-fill">
                                    <span class="span-label">担当</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->agency_contact_name ?? '--' }}</span>
                                </div>
                                <div class="d-flex align-items-center flex-fill">
                                    <span class="span-label">営業所</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->vehicle_branch ?? '--' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-1">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <div class="d-flex align-items-center" style="flex: 1;">
                                    <span class="span-label" style="white-space: nowrap;">行程名</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->itinerary_name ?? '--' }}</span>
                                </div>
                                
                                <div class="d-flex align-items-center" style="flex: 1;">
                                    <span class="span-label" style="white-space: nowrap;">業務分類</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->reservationCategory ? $groupInfo->reservationCategory->category_name : ($groupInfo->business_category ?? '--') }}</span>
                                </div>
                                
                                <div class="d-flex align-items-center" style="flex: 1.2;">
                                    <span class="span-label" style="white-space: nowrap;">予約日</span>
                                    <div class="d-flex align-items-center" style="flex: 1;">
                                        <span class="border px-2 py-1 bg-white rounded text-center" style="flex: 1; min-width: 0;">
                                            {{ $groupInfo->start_date ? \Carbon\Carbon::parse($groupInfo->start_date)->format('Y-m-d') : '--' }}
                                        </span>
                                        <span class="mx-1">~</span>
                                        <span class="border px-2 py-1 bg-white rounded text-center" style="flex: 1; min-width: 0;">
                                            {{ $groupInfo->end_date ? \Carbon\Carbon::parse($groupInfo->end_date)->format('Y-m-d') : '--' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-md-12">
                            <div class="d-flex w-100">
                                <div class="d-flex align-items-center me-2 flex-fill">
                                    <span class="span-label">代理店</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->agency ?? '--' }}</span>
                                </div>
                                <div class="d-flex align-items-center me-2 flex-fill">
                                    <span class="span-label">ガイド</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->guide ?? '--' }}</span>
                                </div>
                                <div class="d-flex align-items-center me-2 flex-fill">
                                    <span class="span-label">車輛等級</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">
                                        {{ $groupInfo->vehicleGrade ? $groupInfo->vehicleGrade->description . ' (' . $groupInfo->vehicleGrade->grade_name . ')' : '--' }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center flex-fill">
                                    <span class="span-label">国籍</span>
                                    <span class="border px-2 py-1 bg-white rounded w-100 text-center">{{ $groupInfo->agency_country ?? '--' }}</span>
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
                        <div class="tab-wrapper" style="border-bottom: 1px solid #aaa;">
                            <span class="tab-item active" data-tab="basic" style="background-color: white; border: 1px solid #aaa; border-bottom-color: white; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #374151; font-size: 0.8rem; padding: 2px 16px; cursor: default;">基本</span>
                            <span class="tab-item inactive" data-tab="customer" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; padding: 2px 16px; cursor: default; margin-left: -1px;">顧客</span>
                            <span class="tab-item inactive" data-tab="history" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; padding: 2px 16px; cursor: default; margin-left: -1px;">履歴</span>
                        </div>
                        <div class="tab-line"></div>
                    </div>

                    <div id="tabContent" class="tab-content" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 157px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">
                        <div class="tab-pane active" id="basic-tab">
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center w-100" style="gap: 10px;">
                                        <span class="span-label" style="white-space: nowrap; min-width: 70px;">団体名</span>
                                        <span class="border px-2 py-1 bg-white rounded w-100" style="flex: 1;">{{ $groupInfo->group_name ?? '--' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center w-100" style="gap: 10px;">
                                        <span class="span-label" style="white-space: nowrap; min-width: 70px;">予約ID</span>
                                        <span class="border px-2 py-1 bg-white rounded w-100" style="flex: 1;">{{ $groupInfo->agt_tour_id ?? '--' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center w-100" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap; min-width: 70px;">人数</span>
                                        <div class="d-flex align-items-center" style="flex: 1; gap: 5px; justify-content: space-between;">
                                            <span class="border px-2 py-1 bg-white rounded text-center" style="flex: 1;">大人: {{ $groupInfo->adult_count ?? 0 }}</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center" style="flex: 1;">小人: {{ $groupInfo->child_count ?? 0 }}</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center" style="flex: 1;">Guide: {{ $groupInfo->guide_count ?? 0 }}</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center" style="flex: 1;">他: {{ $groupInfo->other_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center w-100" style="gap: 10px;">
                                        <span class="span-label" style="white-space: nowrap; min-width: 70px;">荷物数</span>
                                        <span class="border px-2 py-1 bg-white rounded" style="width: 100px;">{{ $groupInfo->luggage_count ?? 0 }}</span>
                                    </div>
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
            @endphp
            <div class="card shadow-sm mb-1 vehicle-detail-card" data-vehicle-id="{{ $vehicleId }}" data-vehicle-index="{{ $vehicleIndex }}" data-bus-id="{{ $busAssignment->id }}">
                <div class="card-header py-2 px-3 d-flex align-items-center" style="background-color: #141c28; border-bottom: 1px solid #aaa;">
                    <h6 class="mb-0 me-3" style="color: #fff; font-size: 0.875rem; font-weight: 500;">
                        運行詳細-{{ sprintf('%02d', $vehicleIndex) }} 
                        <span style="font-size: 0.75rem; margin-left: 10px; color: #a0aec0;">
                            {{ $vehicleName }} 
                            @if($driverName)
                                ({{ $driverName }})
                            @endif
                        </span>
                    </h6>
                    <!--<div class="d-flex align-items-center ms-auto" style="gap: 15px;">-->
                    <!--    <div class="form-check d-flex align-items-center">-->
                    <!--        <label class="form-check-label me-2" style="font-size: 0.8rem; color: #fff;">操作ロック</label>-->
                    <!--        <span class="badge bg-secondary">{{ $busAssignment->lock_arrangement ? 'ON' : 'OFF' }}</span>-->
                    <!--    </div>-->
                    <!--</div>-->
                </div>
                <div class="card-body p-2">
                    <div class="row" style="margin-right: -5px; margin-left: -5px;">
                        <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center w-100" style="gap: 8px; justify-content: space-between;">
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">運行ID</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="min-width: 338px; color: #2563eb;">{{ $busAssignment->id ?? '' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">号車</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="width: 60px;">{{ $busAssignment->vehicle_number ?? sprintf('%02d', $vehicleIndex) }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">最終確認</span>
                                            <span class="badge bg-secondary">{{ $busAssignment->status_finalized ? '✓' : '' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">送信</span>
                                            <span class="badge bg-secondary">{{ $busAssignment->status_sent ? '✓' : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center w-100" style="gap: 15px;">
                                        <div class="d-flex align-items-center" style="flex: 1; gap: 8px;">
                                            <span class="span-label" style="white-space: normal; word-break: break-all; line-height: 1.2; min-width: 70px;">ステップカー</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="flex: 1;">{{ $busAssignment->step_car ?? '' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="flex: 2; gap: 5px; justify-content: flex-end;">
                                            <span class="span-label" style="white-space: nowrap; line-height: 29px;">人数</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center">大人: {{ $busAssignment->adult_count ?? 0 }}</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center">小人: {{ $busAssignment->child_count ?? 0 }}</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center">Guide: {{ $busAssignment->guide_count ?? 0 }}</span>
                                            <span class="border px-2 py-1 bg-white rounded text-center">その他: {{ $busAssignment->other_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center w-100" style="gap: 10px;">
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">車両</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="width: 338px;">{{ $vehicleName }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="margin-left: auto; gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">車種指定</span>
                                            <span class="badge bg-secondary">{{ $busAssignment->vehicle_type_spec_check ? '✓' : '' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">荷物</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="width: 60px;">{{ $busAssignment->luggage_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center w-100" style="gap: 8px;">
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">運転手</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="width: 150px;">{{ $driverName ?: '--' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap; width: 20px !important; min-width: 20px !important; margin-right: 0;">仮</span>
                                            <span class="badge bg-secondary">{{ $busAssignment->temporary_driver ? '✓' : '' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap; width: 33px !important; min-width: 33px !important; margin-right: 0;">添乗</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="width: 90px;">{{ $busAssignment->guide ? $busAssignment->guide->name : '--' }}</span>
                                        </div>

                                        <div class="d-flex align-items-center" style="margin-left: auto; gap: 8px;">
                                            <span class="span-label" style="white-space: nowrap;">代表</span>
                                            <span class="border px-2 py-1 bg-white rounded" style="width: 100px;">{{ $busAssignment->representative ?? '--' }}</span>
                                            <span class="border px-2 py-1 bg-white rounded">{{ $busAssignment->representative_phone ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                            <div class="tab-container-{{ $vehicleIndex }}">
                                <div class="d-flex w-100" style="border-bottom: 1px solid #aaa;">
                                    <span class="tab-button2 active flex-fill text-center px-2 py-1" data-container="{{ $vehicleIndex }}" data-tab2="basic2-{{ $vehicleIndex }}" style="background-color: white; border: 1px solid #aaa; border-bottom-color: white; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #374151; font-size: 0.8rem; cursor: pointer;">基本</span>
                                    <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $vehicleIndex }}" data-tab2="doc-{{ $vehicleIndex }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">DOC</span>
                                    <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $vehicleIndex }}" data-tab2="history2-{{ $vehicleIndex }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">履歴</span>
                                </div>

                                <div id="basic2-{{ $vehicleIndex }}" class="tab-content2" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-start">
                                                <span class="span-label">備考</span>
                                                <span class="border px-2 py-1 bg-white rounded w-100" style="height: 80px;">{{ $busAssignment->operation_basic_remarks ?? '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="doc-{{ $vehicleIndex }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-start">
                                                <span class="span-label">備考</span>
                                                <span class="border px-2 py-1 bg-white rounded w-100" style="height: 80px;">{{ $busAssignment->doc_remarks ?? '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="history2-{{ $vehicleIndex }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-start">
                                                <span class="span-label">備考</span>
                                                <span class="border px-2 py-1 bg-white rounded w-100" style="height: 80px;">{{ $busAssignment->history_remarks ?? '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <table class="table table-bordered table-sm" style="font-size: 0.8rem; background-color: white;">
                                <thead style="background-color: #f3f4f6; text-align: center;">
                                    <tr>
                                        <th style="width: 10%;">運行日</th>
                                        <th style="width: 10%;">開始時刻/場所</th>
                                        <th style="width: 10%;">終了時刻/場所</th>
                                        <th>行程</th>
                                    </tr>
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
                                <span class="span-label" style="min-width: 30px;">注意</span>
                                <span class="border px-2 py-1 bg-white rounded w-100">{{ $busAssignment->attention ?? '' }}</span>
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
        <div class="form-check d-flex align-items-center ps-0">
            <span class="badge bg-secondary">{{ $groupInfo->ignore_operation ? '✓' : '' }}</span>
            <span class="form-check-label ms-2" style="font-size: 0.9rem;">運行無視</span>
        </div>
        <div class="form-check d-flex align-items-center ps-0">
            <span class="badge bg-secondary">{{ $groupInfo->ignore_attendance ? '✓' : '' }}</span>
            <span class="form-check-label ms-2" style="font-size: 0.9rem;">勤怠無視</span>
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
.rounded { min-height: 29px; }
.text-gray { color: #6b7280; font-size: 11px; }
.span-label { text-align: right; min-width: 50px !important; width: 50px !important; font-size: 0.8rem; margin-right: 10px; white-space: nowrap;}
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
    height: 140px;
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endpush