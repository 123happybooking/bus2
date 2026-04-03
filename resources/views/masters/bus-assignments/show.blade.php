@extends('layouts.app')

@section('title', '運行割当詳細')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 page-title">運行割当詳細</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('masters.group-infos.edit', $busAssignment->groupInfo?->id) }}" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-pencil"></i> 編集
            </a>
            <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>

    <div id="operation-details-container">
        @php $index = 1; @endphp
        <div class="card shadow-sm mb-1 vehicle-detail-card" data-vehicle-index="{{ $index }}" data-bus-id="{{ $busAssignment->id }}">
            <div class="card-header py-2 px-3 d-flex align-items-center" style="background-color: #141c28; border-bottom: 1px solid #aaa;">
                <h6 class="mb-0 me-3" style="color: #fff; font-size: 0.875rem; font-weight: 500;">
                    運行詳細
                </h6>
            </div>
            <div class="card-body p-2">
                
            <div class="row" style="margin-right: -5px; margin-left: -5px;">
                <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
                    <input type="hidden" name="id" value="{{ $busAssignment->id }}">
                    <input type="hidden" name="vehicle_index" value="{{ $index }}">

                    <div class="row mb-1">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center w-100" style="gap: 8px; justify-content: space-between;">
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">運行ID</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="min-width: 338px; color: #2563eb;">
                                        {{ $busAssignment->id ?? '' }}
                                    </span>
                                </div>

                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">号車</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 60px; text-align: center;">
                                        {{ $busAssignment->vehicle_number ?? sprintf('%02d', $index) }}
                                    </span>
                                </div>

                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">最終確認</span>
                                    <input type="checkbox" class="form-check-input" disabled {{ $busAssignment->status_finalized ? 'checked' : '' }} style="margin: 0;">
                                </div>

                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">送信</span>
                                    <input type="checkbox" class="form-check-input" disabled {{ $busAssignment->status_sent ? 'checked' : '' }} style="margin: 0;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-1">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center w-100" style="gap: 15px;">
                                <div class="d-flex align-items-center" style="flex: 1; gap: 8px;">
                                    <span class="span-label" style="white-space: normal; word-break: break-all; line-height: 1.2; min-width: 70px;">ステップカー</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="flex: 1; min-width: 338px;">
                                        {{ $busAssignment->step_car ?? '' }}
                                    </span>
                                </div>

                                <div class="d-flex align-items-center" style="flex: 2; gap: 5px; justify-content: flex-end;">
                                    <span class="span-label" style="white-space: nowrap; line-height: 29px;">人数</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 58px; text-align: center;">{{ $busAssignment->adult_count ?? 0 }}</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 58px; text-align: center;">{{ $busAssignment->child_count ?? 0 }}</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 60px; text-align: center;">{{ $busAssignment->guide_count ?? 0 }}</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 60px; text-align: center;">{{ $busAssignment->other_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center w-100" style="gap: 10px;">
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">車両</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 338px;">
                                        @if($busAssignment->vehicle)
                                            {{ $busAssignment->vehicle->registration_number }}
                                            @if($busAssignment->vehicle->vehicleModel)
                                                ({{ $busAssignment->vehicle->vehicleModel->model_name }})
                                            @endif
                                        @else
                                            --
                                        @endif
                                    </span>
                                </div>

                                <div class="d-flex align-items-center" style="margin-left: auto; gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">車種指定</span>
                                    <input type="checkbox" class="form-check-input" disabled {{ $busAssignment->vehicle_type_spec_check ? 'checked' : '' }} style="margin: 0;">
                                </div>

                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">荷物</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 60px; text-align: center;">{{ $busAssignment->luggage_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center w-100" style="gap: 8px;">
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">運転手</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 150px;">
                                        @if($busAssignment->driver)
                                            {{ $busAssignment->driver->name }}
                                            @if($busAssignment->driver->driver_code)
                                                ({{ $busAssignment->driver->driver_code }})
                                            @endif
                                        @else
                                            --
                                        @endif
                                    </span>
                                </div>

                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap; width: 20px !important; min-width: 20px !important; margin-right: 0;">仮</span>
                                    <input type="checkbox" class="form-check-input" disabled {{ $busAssignment->temporary_driver ? 'checked' : '' }} style="margin: 0;">
                                </div>

                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap; width: 33px !important; min-width: 33px !important; margin-right: 0;">添乗</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 90px;">
                                        @if($busAssignment->guide)
                                            {{ $busAssignment->guide->name }}
                                            @if($busAssignment->guide->guide_code)
                                                ({{ $busAssignment->guide->guide_code }})
                                            @endif
                                        @else
                                            --
                                        @endif
                                    </span>
                                </div>

                                <div class="d-flex align-items-center" style="margin-left: auto; gap: 8px;">
                                    <span class="span-label" style="white-space: nowrap;">代表</span>
                                    <span class="border px-2 py-1 bg-white rounded" style="width: 100px;">{{ $busAssignment->representative ?? '' }}</span>
                                    <span class="border px-2 py-1 bg-white rounded">{{ $busAssignment->representative_phone ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                    <div class="row mt-2" style="margin-right: -5px; margin-left: -5px;">
                        <div class="col-md-12" style="padding-right: 5px; padding-left: 5px;">
                            <div class="tab-container-{{ $index }}">
                                <div class="d-flex w-100" style="border-bottom: 1px solid #aaa;">
                                    <span class="tab-button2 active flex-fill text-center px-2 py-1" data-container="{{ $index }}" data-tab2="basic2-{{ $index }}" style="background-color: white; border: 1px solid #aaa; border-bottom-color: white; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #374151; font-size: 0.8rem; cursor: pointer;">基本</span>
                                    <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $index }}" data-tab2="doc-{{ $index }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">DOC</span>
                                    <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $index }}" data-tab2="history2-{{ $index }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">履歴</span>
                                </div>

                                <div id="basic2-{{ $index }}" class="tab-content2" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-start">
                                                <span class="span-label">備考</span>
                                                <div class="border px-2 py-1 bg-white rounded" style="width: 100%; min-height: 80px;">
                                                    {{ $busAssignment->operation_basic_remarks ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="doc-{{ $index }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-start">
                                                <span class="span-label">備考</span>
                                                <div class="border px-2 py-1 bg-white rounded" style="width: 100%; min-height: 80px;">
                                                    {{ $busAssignment->doc_remarks ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="history2-{{ $index }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-start">
                                                <span class="span-label">備考</span>
                                                <div class="border px-2 py-1 bg-white rounded" style="width: 100%; min-height: 80px;">
                                                    {{ $busAssignment->history_remarks ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
            <div class="row mt-2">
                <div class="col-md-12">
                    <table class="table table-bordered table-sm" style="font-size: 0.8rem; background-color: white;" data-vehicle-table="{{ $index }}">
                        <thead style="background-color: #f3f4f6; text-align: center;">
                            <tr>
                                <th style="width: 10%; text-align: center; background-color: #f3f4f6;">運行日</th>
                                <th style="width: 10%; text-align: center; background-color: #f3f4f6;">開始時刻/場所</th>
                                <th style="width: 10%; text-align: center; background-color: #f3f4f6;">終了時刻/場所</th>
                                <th style="text-align: center; background-color: #f3f4f6;">行程</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($busAssignment->dailyItineraries ?? [] as $itinerary)
                            <tr>
                                <td style="vertical-align: middle; text-align: center; background-color: #f9f9f9;">
                                    {{ $itinerary->date ? \Carbon\Carbon::parse($itinerary->date)->format('Y-m-d') : '' }}
                                </td>
                                <td style="padding: 2px;">
                                    <div class="d-flex flex-column">
                                        <div>{{ $itinerary->time_start ? \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') : '' }}</div>
                                        <div><small>{{ $itinerary->start_location ?? '' }}</small></div>
                                    </div>
                                </td>
                                <td style="padding: 2px;">
                                    <div class="d-flex flex-column">
                                        <div>{{ $itinerary->time_end ? \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') : '' }}</div>
                                        <div><small>{{ $itinerary->end_location ?? '' }}</small></div>
                                    </div>
                                </td>
                                <td style="vertical-align: middle; padding: 2px;">
                                    {{ $itinerary->itinerary ?? '' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4" style="color: #6c757d; background-color: #f9f9f9;">
                                    <i class="bi bi-info-circle me-1"></i> 旅程データがありません。
                                </td>
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
                        <span class="border px-2 py-1 bg-white rounded" style="width: 100%;">
                            {{ $busAssignment->attention ?? '' }}
                        </span>
                    </div>

                    <div class="d-flex w-100">
                        <span class="span-label" style="min-width: 30px;">備考</span>
                        <span class="border px-2 py-1 bg-white rounded" style="width: 100%;">
                            {{ $busAssignment->operation_remarks ?? '' }}
                        </span>
                    </div>
                </div>

                <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                    <div class="border px-2 py-1 bg-white rounded" style="min-height: 62px;">
                        {{ $busAssignment->operation_memo ?? '' }}
                    </div>
                </div>
            </div>
        </div>
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
.tab-content { padding-top: 4px; }
.btn-primary { background-color: #2563eb; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
.btn-primary:hover { background-color: #1d4ed8; }
.btn-primary:disabled { background-color: #93c5fd; cursor: not-allowed; }
.btn-outline-danger { border-color: #dc2626; color: #dc2626; font-size: 12px; padding: 6px 16px; }
.btn-outline-danger:hover { background-color: #dc2626; color: white; }
.dashed-box { color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db; }
.span-label { text-align: right; min-width: 50px !important; width: 50px !important; font-size: 0.8rem; margin-right: 10px; white-space: nowrap;}
.card-body { background-color:#f3f4f6; font-size: 0.8rem;}
.container-fluid { max-width: 1600px; }
.page-title { color: #374151; font-size: 1rem; }
.form-control-sm, .form-select-sm { border-color: #E5E7EB; font-size: 0.8rem; border-radius: 4px; }
.form-control-sm:focus, .form-select-sm:focus { border-color: #2563eb; box-shadow: 0 0 0 0.1rem rgba(37, 99, 235, 0.25); }
.btn-sm { font-size: 0.8rem; }
.form-check-input { margin-top: 0; }
.gap-3 { gap: 1rem; }
.gap-4 { gap: 1.5rem; }
.bg-white { background-color: #ffffff !important; }
.rounded { border-radius: 4px !important; min-height: 29px;}
.table { margin-bottom: 6px; }
.table th { font-weight: 500; color: #aaa; border-color: #E5E7EB; }
.table td { border-color: #E5E7EB; padding: 0.5rem; }
.vehicle-detail-card { margin-bottom: 1rem; border: 1px solid #aaa; }
.vehicle-detail-card .card-header { background-color: #141c28; }
.vehicle-detail-card .card-header h6 span { color: #a0aec0; font-weight: normal; }
.tab-button2 { cursor: pointer; transition: all 0.2s; outline: none; }
.tab-button2:hover { background-color: #f9fafb !important; }
.tab-button2.active { background-color: white !important; border-bottom-color: white !important; color: #374151 !important; font-weight: 500; }
.tab-button2:not(.active) { background-color: #F3F4F6 !important; border-bottom-color: #aaa !important; color: #6B7280 !important; }
.tab-content2 { border: 1px #E5E7EB solid; border-top: 0; background-color: #fff; padding: 10px; height: 140px; }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-button2').forEach(button => {
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
});
</script>
@endpush