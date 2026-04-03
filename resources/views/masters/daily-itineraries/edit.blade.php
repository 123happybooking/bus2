@extends('layouts.app')

@section('title', '日別旅程編集')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0 page-title">日別旅程編集</h5>
        <a href="{{ route('masters.daily-itineraries.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1">
            <i class="bi bi-arrow-left"></i> 一覧に戻る
        </a>
    </div>
    
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
    
    <form method="POST" action="{{ route('masters.daily-itineraries.update', $dailyItinerary->id) }}" id="editForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="group_info_id" value="{{ $dailyItinerary->group_info_id }}">

        <div class="card shadow-sm mb-3 itinerary-info-card">
            <div class="card-header py-2 px-3 card-header-bg">
                <h6 class="mb-0 card-title">
                    <i class="bi bi-calendar-day me-1"></i> 旅程基本情報
                </h6>
            </div>
            
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="date" class="form-label me-2 mb-0 form-label-custom required">日付</label>
                            <input type="date" name="date" id="date" class="form-control form-control-sm form-control-custom @error('date') is-invalid @enderror" 
                                   value="{{ old('date', $dailyItinerary->date instanceof \Carbon\Carbon ? $dailyItinerary->date->format('Y-m-d') : $dailyItinerary->date) }}" required style="width: 140px;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="time_start" class="form-label me-2 mb-0 form-label-custom required">開始</label>
                            <input type="time" name="time_start" id="time_start" class="form-control form-control-sm form-control-custom @error('time_start') is-invalid @enderror" 
                                   value="{{ old('time_start', $dailyItinerary->time_start ? substr($dailyItinerary->time_start, 0, 5) : '') }}" required style="width: 100px;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="time_end" class="form-label me-2 mb-0 form-label-custom required">終了</label>
                            <input type="time" name="time_end" id="time_end" class="form-control form-control-sm form-control-custom @error('time_end') is-invalid @enderror" 
                                   value="{{ old('time_end', $dailyItinerary->time_end ? substr($dailyItinerary->time_end, 0, 5) : '') }}" required style="width: 100px;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="start_location" class="form-label me-2 mb-0 form-label-custom" style="width: 60px;">出発地</label>
                            <input type="text" name="start_location" id="start_location" class="form-control form-control-sm form-control-custom @error('start_location') is-invalid @enderror" 
                                   value="{{ old('start_location', $dailyItinerary->start_location) }}" placeholder="出発地" style="width: 100%;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="end_location" class="form-label me-2 mb-0 form-label-custom" style="width: 60px;">到着地</label>
                            <input type="text" name="end_location" id="end_location" class="form-control form-control-sm form-control-custom @error('end_location') is-invalid @enderror" 
                                   value="{{ old('end_location', $dailyItinerary->end_location) }}" placeholder="到着地" style="width: 100%;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="accommodation" class="form-label me-2 mb-0 form-label-custom">宿泊</label>
                            <select name="accommodation" id="accommodation" class="form-select form-select-sm form-control-custom" style="width: 80px;">
                                <option value="0" {{ old('accommodation', $dailyItinerary->accommodation) == '0' ? 'selected' : '' }}>無</option>
                                <option value="1" {{ old('accommodation', $dailyItinerary->accommodation) == '1' ? 'selected' : '' }}>有</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="d-flex">
                            <label for="itinerary" class="form-label me-2 mb-0 form-label-custom" style="width: 60px; padding-top: 2px;">行程説明</label>
                            <textarea name="itinerary" id="itinerary" rows="2" class="form-control form-control-sm form-control-custom @error('itinerary') is-invalid @enderror" 
                                      placeholder="行程説明">{{ old('itinerary', $dailyItinerary->itinerary) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="d-flex">
                            <label for="remarks" class="form-label me-2 mb-0 form-label-custom" style="width: 60px; padding-top: 2px;">備考</label>
                            <textarea name="remarks" id="remarks" rows="2" class="form-control form-control-sm form-control-custom @error('remarks') is-invalid @enderror" 
                                      placeholder="備考">{{ old('remarks', $dailyItinerary->remarks) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3 vehicle-card">
            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center card-header-bg">
                <h6 class="mb-0 card-title">
                    <i class="bi bi-truck me-1"></i> 車両情報
                </h6>
                <button type="button" class="btn btn-light btn-sm py-0 px-2 add-row-btn-header" id="addVehicleRowBtn">
                    <i class="bi bi-plus-lg"></i> 車両を追加
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 vehicle-table" id="vehicleTable">
                        <thead class="table-header">
                            <tr>
                                <th class="text-center px-1 py-1" style="width: 40px;">No.</th>
                                <th class="text-center px-1 py-1" style="width: 300px;">車両名</th>
                                <th class="text-center px-1 py-1" style="width: 100px;">号車</th>
                                <th class="text-center px-1 py-1" style="width: 60px;">定員</th>
                                <th class="text-center px-1 py-1" style="width: 250px;">運転手</th>
                                <th class="text-center px-1 py-1" style="width: 110px;">操作</th>
                            </tr>
                        </thead>
                        <tbody id="vehicleBody">
                            @php
                                $oldVehicles = old('vehicles', []);
                                $vehicleCount = 0;
                            @endphp
                            
                            @if(count($oldVehicles) > 0)
                                @foreach($oldVehicles as $index => $oldVehicle)
                                @php
                                    $index = (int)$index;
                                    $orderNumber = $index + 1;
                                    $vehicleCount++;
                                @endphp
                                <tr data-index="{{ $index }}" data-id="{{ $oldVehicle['id'] ?? '' }}">
                                    <td class="text-center align-middle px-1 py-1 vehicle-order">{{ $orderNumber }}</td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[{{ $index }}][vehicle_id]" class="form-select form-select-sm vehicle-select @error('vehicles.'.$index.'.vehicle_id') is-invalid @enderror" 
                                                data-row-index="{{ $index }}" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($vehicles ?? [] as $vehicle)
                                                <option value="{{ $vehicle->id }}" 
                                                        data-registration="{{ $vehicle->registration_number }}"
                                                        data-vehicle-code="{{ $vehicle->vehicle_code }}"
                                                        data-type="{{ $vehicle->vehicleType->type_name ?? '' }}"
                                                        data-model="{{ $vehicle->vehicleModel->model_name ?? '' }}"
                                                        data-branch="{{ $vehicle->branch->branch_name ?? '' }}"
                                                        data-seating="{{ $vehicle->seating_capacity }}"
                                                        data-vehicle-name="{{ $vehicle->registration_number }} @if($vehicle->vehicleModel)({{ $vehicle->vehicleModel->model_name }})@endif"
                                                        {{ old('vehicles.'.$index.'.vehicle_id', $oldVehicle['vehicle_id'] ?? '') == $vehicle->id ? 'selected' : '' }}>
                                                    {{ $vehicle->registration_number }} 
                                                    @if($vehicle->vehicleModel)
                                                        ({{ $vehicle->vehicleModel->model_name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_name]" class="vehicle-name" value="{{ old('vehicles.'.$index.'.vehicle_name', $oldVehicle['vehicle_name'] ?? '') }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_type]" class="vehicle-type" value="{{ old('vehicles.'.$index.'.vehicle_type', $oldVehicle['vehicle_type'] ?? '') }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_model]" class="vehicle-model" value="{{ old('vehicles.'.$index.'.vehicle_model', $oldVehicle['vehicle_model'] ?? '') }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_branch]" class="vehicle-branch" value="{{ old('vehicles.'.$index.'.vehicle_branch', $oldVehicle['vehicle_branch'] ?? '') }}">
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" name="vehicles[{{ $index }}][vehicle_number]" class="form-control form-control-sm vehicle-number bg-light @error('vehicles.'.$index.'.vehicle_number') is-invalid @enderror" 
                                               value="{{ old('vehicles.'.$index.'.vehicle_number', $oldVehicle['vehicle_number'] ?? '') }}" style="width: 100%;" readonly>
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" class="form-control form-control-sm bg-light seating-capacity" value="{{ old('vehicles.'.$index.'.seating_capacity', $oldVehicle['seating_capacity'] ?? '') }}" readonly style="width: 100%;">
                                        <input type="hidden" name="vehicles[{{ $index }}][seating_capacity]" class="seating-capacity-hidden" value="{{ old('vehicles.'.$index.'.seating_capacity', $oldVehicle['seating_capacity'] ?? '') }}">
                                    </td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[{{ $index }}][driver_id]" class="form-select form-select-sm driver-select @error('vehicles.'.$index.'.driver_id') is-invalid @enderror" 
                                                data-row-index="{{ $index }}" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($drivers ?? [] as $driver)
                                                <option value="{{ $driver->id }}" 
                                                        data-driver-name="{{ $driver->name }}"
                                                        data-driver-code="{{ $driver->driver_code }}"
                                                        data-driver-phone="{{ $driver->phone_number }}"
                                                        {{ old('vehicles.'.$index.'.driver_id', $oldVehicle['driver_id'] ?? '') == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }} 
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[{{ $index }}][driver_name]" class="driver-name" value="{{ old('vehicles.'.$index.'.driver_name', $oldVehicle['driver_name'] ?? '') }}">
                                    </td>
                                    <td class="text-center align-middle px-1 py-1">
                                        <div class="d-flex justify-content-center gap-1 action-buttons">
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn p-0 action-btn" title="上へ移動" {{ $loop->first ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn p-0 action-btn" title="下へ移動" {{ $loop->last ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm add-row-btn p-0 action-btn" title="行を追加">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn p-0 action-btn" title="行を削除" {{ $vehicleCount <= 1 ? 'disabled' : '' }}>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="vehicles[{{ $index }}][id]" value="{{ $oldVehicle['id'] ?? '' }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][display_order]" class="vehicle-display-order" value="{{ $orderNumber }}">
                                    </td>
                                </tr>
                                @endforeach
                            @elseif(isset($dailyItinerary->busAssignments) && $dailyItinerary->busAssignments->count() > 0)
                                @foreach($dailyItinerary->busAssignments as $index => $busAssignment)
                                @php
                                    $orderNumber = $index + 1;
                                    $vehicleCount++;
                                @endphp
                                <tr data-index="{{ $index }}" data-id="{{ $busAssignment->id }}">
                                    <td class="text-center align-middle px-1 py-1 vehicle-order">{{ $orderNumber }}</td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[{{ $index }}][vehicle_id]" class="form-select form-select-sm vehicle-select" data-row-index="{{ $index }}" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($vehicles ?? [] as $vehicle)
                                                <option value="{{ $vehicle->id }}" 
                                                        data-registration="{{ $vehicle->registration_number }}"
                                                        data-vehicle-code="{{ $vehicle->vehicle_code }}"
                                                        data-type="{{ $vehicle->vehicleType->type_name ?? '' }}"
                                                        data-model="{{ $vehicle->vehicleModel->model_name ?? '' }}"
                                                        data-branch="{{ $vehicle->branch->branch_name ?? '' }}"
                                                        data-seating="{{ $vehicle->seating_capacity }}"
                                                        data-vehicle-name="{{ $vehicle->registration_number }} @if($vehicle->vehicleModel)({{ $vehicle->vehicleModel->model_name }})@endif"
                                                        {{ $busAssignment->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                                    {{ $vehicle->registration_number }} 
                                                    @if($vehicle->vehicleModel)
                                                        ({{ $vehicle->vehicleModel->model_name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_name]" class="vehicle-name" value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->registration_number . ($busAssignment->vehicle->vehicleModel ? ' (' . $busAssignment->vehicle->vehicleModel->model_name . ')' : '') : '' }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_type]" class="vehicle-type" value="{{ $busAssignment->vehicle && $busAssignment->vehicle->vehicleType ? $busAssignment->vehicle->vehicleType->type_name : '' }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_model]" class="vehicle-model" value="{{ $busAssignment->vehicle && $busAssignment->vehicle->vehicleModel ? $busAssignment->vehicle->vehicleModel->model_name : '' }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_branch]" class="vehicle-branch" value="{{ $busAssignment->vehicle && $busAssignment->vehicle->branch ? $busAssignment->vehicle->branch->branch_name : '' }}">
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" name="vehicles[{{ $index }}][vehicle_number]" class="form-control form-control-sm vehicle-number bg-light" 
                                               value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->vehicle_code : '' }}" style="width: 100%;" readonly>
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" class="form-control form-control-sm bg-light seating-capacity" 
                                               value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->seating_capacity : '' }}" readonly style="width: 100%;">
                                        <input type="hidden" name="vehicles[{{ $index }}][seating_capacity]" class="seating-capacity-hidden" 
                                               value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->seating_capacity : '' }}">
                                    </td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[{{ $index }}][driver_id]" class="form-select form-select-sm driver-select" data-row-index="{{ $index }}" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($drivers ?? [] as $driver)
                                                <option value="{{ $driver->id }}" 
                                                        data-driver-name="{{ $driver->name }}"
                                                        data-driver-code="{{ $driver->driver_code }}"
                                                        data-driver-phone="{{ $driver->phone_number }}"
                                                        {{ $busAssignment->driver_id == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }} 
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[{{ $index }}][driver_name]" class="driver-name" value="{{ $busAssignment->driver ? $busAssignment->driver->name : '' }}">
                                    </td>
                                    <td class="text-center align-middle px-1 py-1">
                                        <div class="d-flex justify-content-center gap-1 action-buttons">
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn p-0 action-btn" title="上へ移動" {{ $loop->first ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn p-0 action-btn" title="下へ移動" {{ $loop->last ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm add-row-btn p-0 action-btn" title="行を追加">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn p-0 action-btn" title="行を削除" {{ $dailyItinerary->busAssignments->count() <= 1 ? 'disabled' : '' }}>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="vehicles[{{ $index }}][id]" value="{{ $busAssignment->id }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][display_order]" class="vehicle-display-order" value="{{ $orderNumber }}">
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr data-index="0" data-id="">
                                    <td class="text-center align-middle px-1 py-1 vehicle-order">1</td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[0][vehicle_id]" class="form-select form-select-sm vehicle-select" data-row-index="0" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($vehicles ?? [] as $vehicle)
                                                <option value="{{ $vehicle->id }}" 
                                                        data-registration="{{ $vehicle->registration_number }}"
                                                        data-vehicle-code="{{ $vehicle->vehicle_code }}"
                                                        data-type="{{ $vehicle->vehicleType->type_name ?? '' }}"
                                                        data-model="{{ $vehicle->vehicleModel->model_name ?? '' }}"
                                                        data-branch="{{ $vehicle->branch->branch_name ?? '' }}"
                                                        data-seating="{{ $vehicle->seating_capacity }}"
                                                        data-vehicle-name="{{ $vehicle->registration_number }} @if($vehicle->vehicleModel)({{ $vehicle->vehicleModel->model_name }})@endif">
                                                    {{ $vehicle->registration_number }} 
                                                    @if($vehicle->vehicleModel)
                                                        ({{ $vehicle->vehicleModel->model_name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[0][vehicle_name]" class="vehicle-name" value="">
                                        <input type="hidden" name="vehicles[0][vehicle_type]" class="vehicle-type" value="">
                                        <input type="hidden" name="vehicles[0][vehicle_model]" class="vehicle-model" value="">
                                        <input type="hidden" name="vehicles[0][vehicle_branch]" class="vehicle-branch" value="">
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" name="vehicles[0][vehicle_number]" class="form-control form-control-sm vehicle-number bg-light" value="" style="width: 100%;" readonly>
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" class="form-control form-control-sm bg-light seating-capacity" value="" readonly style="width: 100%;">
                                        <input type="hidden" name="vehicles[0][seating_capacity]" class="seating-capacity-hidden" value="">
                                    </td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[0][driver_id]" class="form-select form-select-sm driver-select" data-row-index="0" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($drivers ?? [] as $driver)
                                                <option value="{{ $driver->id }}" 
                                                        data-driver-name="{{ $driver->name }}"
                                                        data-driver-code="{{ $driver->driver_code }}"
                                                        data-driver-phone="{{ $driver->phone_number }}">
                                                    {{ $driver->name }} 
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[0][driver_name]" class="driver-name" value="">
                                    </td>
                                    <td class="text-center align-middle px-1 py-1">
                                        <div class="d-flex justify-content-center gap-1 action-buttons">
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn p-0 action-btn" title="上へ移動" disabled>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn p-0 action-btn" title="下へ移動" disabled>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm add-row-btn p-0 action-btn" title="行を追加">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn p-0 action-btn" title="行を削除" disabled>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="vehicles[0][id]" value="">
                                        <input type="hidden" name="vehicles[0][display_order]" class="vehicle-display-order" value="1">
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <template id="newVehicleRowTemplate">
            <tr>
                <td class="text-center align-middle px-1 py-1 vehicle-order"></td>
                <td class="px-1 py-1">
                    <select name="vehicles[__index__][vehicle_id]" class="form-select form-select-sm vehicle-select" data-row-index="__index__" style="width: 100%;">
                        <option value="">-- 選択 --</option>
                        @foreach($vehicles ?? [] as $vehicle)
                            <option value="{{ $vehicle->id }}" 
                                    data-registration="{{ $vehicle->registration_number }}"
                                    data-vehicle-code="{{ $vehicle->vehicle_code }}"
                                    data-type="{{ $vehicle->vehicleType->type_name ?? '' }}"
                                    data-model="{{ $vehicle->vehicleModel->model_name ?? '' }}"
                                    data-branch="{{ $vehicle->branch->branch_name ?? '' }}"
                                    data-seating="{{ $vehicle->seating_capacity }}"
                                    data-vehicle-name="{{ $vehicle->registration_number }} @if($vehicle->vehicleModel)({{ $vehicle->vehicleModel->model_name }})@endif">
                                {{ $vehicle->registration_number }} 
                                @if($vehicle->vehicleModel)
                                    ({{ $vehicle->vehicleModel->model_name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="vehicles[__index__][vehicle_name]" class="vehicle-name" value="">
                    <input type="hidden" name="vehicles[__index__][vehicle_type]" class="vehicle-type" value="">
                    <input type="hidden" name="vehicles[__index__][vehicle_model]" class="vehicle-model" value="">
                    <input type="hidden" name="vehicles[__index__][vehicle_branch]" class="vehicle-branch" value="">
                </td>
                <td class="px-1 py-1">
                    <input type="text" name="vehicles[__index__][vehicle_number]" class="form-control form-control-sm vehicle-number bg-light" value="" style="width: 100%;" readonly>
                </td>
                <td class="px-1 py-1">
                    <input type="text" class="form-control form-control-sm bg-light seating-capacity" value="" readonly style="width: 100%;">
                    <input type="hidden" name="vehicles[__index__][seating_capacity]" class="seating-capacity-hidden" value="">
                </td>
                <td class="px-1 py-1">
                    <select name="vehicles[__index__][driver_id]" class="form-select form-select-sm driver-select" data-row-index="__index__" style="width: 100%;">
                        <option value="">-- 選択 --</option>
                        @foreach($drivers ?? [] as $driver)
                            <option value="{{ $driver->id }}" 
                                    data-driver-name="{{ $driver->name }}"
                                    data-driver-code="{{ $driver->driver_code }}"
                                    data-driver-phone="{{ $driver->phone_number }}">
                                {{ $driver->name }} 
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="vehicles[__index__][driver_name]" class="driver-name" value="">
                </td>
                <td class="text-center align-middle px-1 py-1">
                    <div class="d-flex justify-content-center gap-1 action-buttons">
                        <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn p-0 action-btn" title="上へ移動">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn p-0 action-btn" title="下へ移動">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm add-row-btn p-0 action-btn" title="行を追加">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn p-0 action-btn" title="行を削除">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="vehicles[__index__][id]" value="">
                    <input type="hidden" name="vehicles[__index__][display_order]" class="vehicle-display-order" value="">
                </td>
            </tr>
        </template>

        <div class="d-flex justify-content-start mt-3">
            <button type="submit" form="editForm" class="btn btn-primary btn-sm px-3 py-1 btn-save">
                <i class="bi bi-check-circle"></i> 保存
            </button>
            <a href="{{ route('masters.daily-itineraries.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-1 ms-2 btn-cancel">
                <i class="bi bi-x-circle"></i> キャンセル
            </a>
        </div>
    </form>
</div>
@endsection


@push('styles')
<style>
.container-fluid {
    max-width: 1600px;
}

.page-title {
    color: #374151;
    font-size: 1rem;
}

.btn-save {
    background-color: #2563eb;
    border-color: #2563eb;
    font-size: 0.875rem;
}

.btn-cancel {
    border-color: #E5E7EB;
    color: #374151;
    font-size: 0.875rem;
}

.btn-cancel:hover {
    background-color: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
}

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

.info-card, .itinerary-info-card, .vehicle-card {
    border-color: #E5E7EB;
    border-radius: 6px;
}

.card-header-bg {
    background-color: #F3F4F6;
    border-bottom: 1px solid #E5E7EB;
}

.card-title {
    color: #fff;
    font-size: 0.875rem;
    font-weight: 500;
}

.info-table {
    font-size: 0.875rem;
}

.info-table th {
    color: #374151;
    font-weight: 500;
    background-color: #f9fafb;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.info-table td {
    color: #111827;
    padding: 0.25rem 0.5rem;
}

.form-label-custom {
    color: #374151;
    font-size: 0.875rem;
}

.form-control-custom, .form-select-custom {
    border-color: #E5E7EB;
    font-size: 0.875rem;
    border-radius: 4px;
    box-shadow: none;
}

.form-control-custom:focus, .form-select-custom:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 0.1rem rgba(37, 99, 235, 0.25);
}

.readonly-field {
    background-color: #f9fafb !important;
    border-color: #E5E7EB;
    font-size: 0.875rem;
}

.required::after {
    content: " *";
    color: #dc3545;
    font-size: 0.8rem;
}

.group-link {
    color: #2563eb;
    text-decoration: none;
}

.group-link:hover {
    text-decoration: underline;
}

.status-badge {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    color: #111827;
}

.total-pax {
    color: #2563eb;
    font-weight: 500;
    margin-left: 0.5rem;
}

.vehicle-table {
    border-color: #E5E7EB;
    font-size: 0.8rem;
}

.vehicle-table th,
.vehicle-table td {
    border: 1px solid #E5E7EB;
}

.table-header {
    background-color: #F3F4F6;
}

.table-header th {
    color: #374151;
    font-weight: 600;
}

.vehicle-table tbody tr:hover {
    background-color: #f8f9fa;
}

.vehicle-table tbody tr td {
    vertical-align: middle;
}

.vehicle-table .form-control-sm,
.vehicle-table .form-select-sm {
    border-color: #E5E7EB;
    font-size: 0.8rem;
    height: 24px;
    padding: 0 4px;
    border-radius: 4px;
}

.action-buttons {
    gap: 0.25rem;
}

.action-btn {
    width: 20px;
    height: 20px;
    font-size: 0.7rem;
    line-height: 1;
    padding: 0 !important;
    border-color: #E5E7EB;
}

.btn-outline-secondary.action-btn:hover {
    background-color: #e5e7eb;
    border-color: #d1d5db;
    color: #374151;
}

.btn-outline-success.action-btn:hover {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-outline-danger.action-btn:hover {
    background-color: #dc2626;
    border-color: #dc2626;
    color: white;
}

.add-row-btn-header {
    border-color: #E5E7EB;
    font-size: 0.8rem;
    background-color: white;
}

.add-row-btn-header:hover {
    background-color: #f3f4f6;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.is-invalid:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.25) !important;
}

.invalid-feedback {
    font-size: 0.75rem;
    margin-top: 0;
}

.gap-2 {
    gap: 0.5rem;
}

.gap-1 {
    gap: 0.25rem;
}

.action-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const template = document.getElementById('newVehicleRowTemplate');
    const tbody = document.getElementById('vehicleBody');
    
    function setupVehicleSelectListeners() {
        document.querySelectorAll('.vehicle-select').forEach(select => {
            select.addEventListener('change', function() {
                const row = this.closest('tr');
                const selectedOption = this.options[this.selectedIndex];
                
                if (selectedOption && selectedOption.value) {
                    const vehicleNameInput = row.querySelector('.vehicle-name');
                    if (vehicleNameInput) {
                        vehicleNameInput.value = selectedOption.dataset.vehicleName || selectedOption.textContent.trim();
                    }
                    
                    const vehicleTypeInput = row.querySelector('.vehicle-type');
                    if (vehicleTypeInput) {
                        vehicleTypeInput.value = selectedOption.dataset.type || '';
                    }
                    
                    const vehicleModelInput = row.querySelector('.vehicle-model');
                    if (vehicleModelInput) {
                        vehicleModelInput.value = selectedOption.dataset.model || '';
                    }
                    
                    const vehicleBranchInput = row.querySelector('.vehicle-branch');
                    if (vehicleBranchInput) {
                        vehicleBranchInput.value = selectedOption.dataset.branch || '';
                    }
                    
                    const seatingCapacity = row.querySelector('.seating-capacity');
                    const seatingCapacityHidden = row.querySelector('.seating-capacity-hidden');
                    if (seatingCapacity && seatingCapacityHidden) {
                        const capacity = selectedOption.dataset.seating || '';
                        seatingCapacity.value = capacity;
                        seatingCapacityHidden.value = capacity;
                    }
                    
                    const vehicleNumberInput = row.querySelector('.vehicle-number');
                    if (vehicleNumberInput) {
                        vehicleNumberInput.value = selectedOption.dataset.vehicleCode || selectedOption.dataset.registration || '';
                    }
                } else {
                    const vehicleNameInput = row.querySelector('.vehicle-name');
                    if (vehicleNameInput) vehicleNameInput.value = '';
                    
                    const vehicleTypeInput = row.querySelector('.vehicle-type');
                    if (vehicleTypeInput) vehicleTypeInput.value = '';
                    
                    const vehicleModelInput = row.querySelector('.vehicle-model');
                    if (vehicleModelInput) vehicleModelInput.value = '';
                    
                    const vehicleBranchInput = row.querySelector('.vehicle-branch');
                    if (vehicleBranchInput) vehicleBranchInput.value = '';
                    
                    const seatingCapacity = row.querySelector('.seating-capacity');
                    const seatingCapacityHidden = row.querySelector('.seating-capacity-hidden');
                    if (seatingCapacity && seatingCapacityHidden) {
                        seatingCapacity.value = '';
                        seatingCapacityHidden.value = '';
                    }
                    
                    const vehicleNumberInput = row.querySelector('.vehicle-number');
                    if (vehicleNumberInput) {
                        vehicleNumberInput.value = '';
                    }
                }
            });
        });
    }
    
    function setupDriverSelectListeners() {
        document.querySelectorAll('.driver-select').forEach(select => {
            select.addEventListener('change', function() {
                const row = this.closest('tr');
                const selectedOption = this.options[this.selectedIndex];
                
                const driverNameInput = row.querySelector('.driver-name');
                if (driverNameInput) {
                    if (selectedOption && selectedOption.value) {
                        driverNameInput.value = selectedOption.dataset.driverName || '';
                    } else {
                        driverNameInput.value = '';
                    }
                }
            });
        });
    }
    
    setupVehicleSelectListeners();
    setupDriverSelectListeners();
    
    function addVehicleRow(clickedButton) {
        if (!template || !tbody) return;
        
        let targetRow = null;
        if (clickedButton) {
            targetRow = clickedButton.closest('tr');
        }
        
        const rows = tbody.querySelectorAll('tr');
        let newIndex = rows.length;
        
        if (targetRow) {
            const rowArray = Array.from(rows);
            const targetIndex = rowArray.indexOf(targetRow);
            newIndex = targetIndex + 1;
        }
        
        const templateContent = template.content.cloneNode(true);
        const newRow = templateContent.querySelector('tr');
        
        const elements = newRow.querySelectorAll('[name*="__index__"]');
        elements.forEach(element => {
            const name = element.getAttribute('name');
            if (name) {
                element.setAttribute('name', name.replace(/__index__/g, newIndex));
            }
        });
        
        const selects = newRow.querySelectorAll('[data-row-index="__index__"]');
        selects.forEach(select => {
            select.setAttribute('data-row-index', newIndex);
        });
        
        const orderCell = newRow.querySelector('.vehicle-order');
        if (orderCell) {
            orderCell.textContent = newIndex + 1;
        }
        
        const orderInput = newRow.querySelector('.vehicle-display-order');
        if (orderInput) {
            orderInput.value = newIndex + 1;
        }
        
        if (targetRow) {
            targetRow.parentNode.insertBefore(newRow, targetRow.nextSibling);
        } else {
            tbody.appendChild(newRow);
        }
        
        setupVehicleSelectListeners();
        setupDriverSelectListeners();
        
        updateDisplayOrders();
        updateMoveButtons();
        updateDeleteButtons();
    }
    
    function deleteVehicleRow(button) {
        const rows = tbody.querySelectorAll('tr');
        
        if (rows.length <= 1) {
            alert('少なくとも1行の車両情報が必要です！');
            return;
        }
        
        if (confirm('この車両情報を削除してもよろしいですか？')) {
            const row = button.closest('tr');
            if (row) {
                row.remove();
                updateDisplayOrders();
                updateMoveButtons();
                updateDeleteButtons();
            }
        }
    }
    
    function moveRowUp(button) {
        const row = button.closest('tr');
        if (!row) return;
        
        const prevRow = row.previousElementSibling;
        
        if (prevRow) {
            row.parentNode.insertBefore(row, prevRow);
            updateDisplayOrders();
            updateMoveButtons();
        }
    }
    
    function moveRowDown(button) {
        const row = button.closest('tr');
        if (!row) return;
        
        const nextRow = row.nextElementSibling;
        
        if (nextRow) {
            row.parentNode.insertBefore(nextRow, row);
            updateDisplayOrders();
            updateMoveButtons();
        }
    }
    
    function updateDisplayOrders() {
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach((row, index) => {
            const orderCell = row.querySelector('.vehicle-order');
            if (orderCell) {
                orderCell.textContent = index + 1;
            }
            
            const orderInput = row.querySelector('.vehicle-display-order');
            if (orderInput) {
                orderInput.value = index + 1;
            }
            
            const selects = row.querySelectorAll('[data-row-index]');
            selects.forEach(select => {
                select.setAttribute('data-row-index', index);
            });
            
            const elements = row.querySelectorAll('[name^="vehicles["]');
            elements.forEach(element => {
                const name = element.getAttribute('name');
                if (name) {
                    const newName = name.replace(/vehicles\[\d+\]/, `vehicles[${index}]`);
                    element.setAttribute('name', newName);
                }
            });
        });
    }
    
    function updateMoveButtons() {
        const rows = tbody.querySelectorAll('tr');
        
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
    
    function updateDeleteButtons() {
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach((row) => {
            const deleteBtn = row.querySelector('.delete-row-btn');
            if (deleteBtn) {
                deleteBtn.disabled = rows.length <= 1;
            }
        });
    }
    
    const addBtn = document.getElementById('addVehicleRowBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addVehicleRow();
        });
    }
    
    const vehicleTable = document.getElementById('vehicleTable');
    if (vehicleTable) {
        vehicleTable.addEventListener('click', function(e) {
            const target = e.target;
            
            if (target.closest('.delete-row-btn')) {
                e.preventDefault();
                deleteVehicleRow(target.closest('.delete-row-btn'));
            } else if (target.closest('.move-up-btn')) {
                e.preventDefault();
                moveRowUp(target.closest('.move-up-btn'));
            } else if (target.closest('.move-down-btn')) {
                e.preventDefault();
                moveRowDown(target.closest('.move-down-btn'));
            } else if (target.closest('.add-row-btn')) {
                e.preventDefault();
                addVehicleRow(target.closest('.add-row-btn'));
            }
        });
    }
    
    setTimeout(() => {
        updateMoveButtons();
        updateDeleteButtons();
    }, 100);
    
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            updateDisplayOrders();
            
            const date = document.getElementById('date').value;
            const timeStart = document.getElementById('time_start').value;
            const timeEnd = document.getElementById('time_end').value;
            
            if (!date) {
                alert('日付は必須です。');
                e.preventDefault();
                return false;
            }
            
            if (!timeStart) {
                alert('開始時刻は必須です。');
                e.preventDefault();
                return false;
            }
            
            if (!timeEnd) {
                alert('終了時刻は必須です。');
                e.preventDefault();
                return false;
            }
            
            if (timeStart >= timeEnd) {
                alert('終了時刻は開始時刻より後でなければなりません。');
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
@endpush