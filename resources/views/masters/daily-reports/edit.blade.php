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
</style>
@endpush