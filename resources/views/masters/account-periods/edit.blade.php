@extends('layouts.app')

@section('title', '会計周期の編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-periods.index') }}">会計周期管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">編集</li>
                </ol>
            </nav>
            
            <!-- 错误提示 -->
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle"></i> 入力エラーがあります
                </h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-range"></i> 会計周期の編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.account-periods.update', $period) }}" method="POST" id="periodForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- 周期名称 -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label required">周期名称</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" 
                                       value="{{ old('title', $period->title) }}" 
                                       required maxlength="50">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- 日期范围 (年月) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start" class="form-label required">開始年月</label>
                                <input type="month" class="form-control @error('start') is-invalid @enderror" 
                                       id="start" name="start" 
                                       value="{{ old('start', $period->start) }}"
                                       required>
                                @error('start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="end" class="form-label required">終了年月</label>
                                <input type="month" class="form-control @error('end') is-invalid @enderror" 
                                       id="end" name="end" 
                                       value="{{ old('end', $period->end) }}"
                                       required>
                                @error('end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.account-periods.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('start');
    const endInput = document.getElementById('end');

    // 日期联动逻辑：确保结束月份不能早于开始月份
    startInput.addEventListener('change', function () {
        if (this.value) {
            endInput.min = this.value;
            // 如果当前结束月份小于新的开始月份，自动修正
            if (endInput.value && endInput.value < this.value) {
                endInput.value = this.value;
            }
        }
    });

    endInput.addEventListener('change', function () {
        if (this.value) {
            startInput.max = this.value;
            // 如果当前开始月份大于新的结束月份，自动修正
            if (startInput.value && startInput.value > this.value) {
                startInput.value = this.value;
            }
        }
    });
});
</script>
@endpush