@extends('layouts.app')

@section('title', '税区分詳細: ' . $tax->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-taxs.index') }}">税区分マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $tax->name }}</li>
                </ol>
            </nav>
            
            <!-- 成功消息 -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif

            <!-- 错误消息 -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif
            
            <!-- 详情卡片 -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-percent"></i> 税区分詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.account-taxs.edit', $tax) }}" class="btn btn-light btn-sm me-1">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.account-taxs.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- 左侧：基本情報 -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3 text-primary">基本情報</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted small">ID</dt>
                                <dd class="col-sm-8 mb-3">
                                    <span class="text-muted">#{{ $tax->id }}</span>
                                </dd>

                                <dt class="col-sm-4">税区分コード</dt>
                                <dd class="col-sm-8 mb-3">
                                    <span class="badge bg-primary fs-6">{{ $tax->code }}</span>
                                </dd>

                                <dt class="col-sm-4">税区分名称</dt>
                                <dd class="col-sm-8 mb-3 fw-bold fs-5">{{ $tax->name }}</dd>
                                
                                <dt class="col-sm-4">税率</dt>
                                <dd class="col-sm-8 mb-3">
                                    <span class="display-6 text-success fw-bold">{{ number_format($tax->rate, 2) }}%</span>
                                </dd>
                                
                                <dt class="col-sm-4">税計算方法</dt>
                                <dd class="col-sm-8 mb-3">
                                    <span class="badge bg-secondary fs-6">{{ $tax->calculation_type }}</span>
                                </dd>

                                <dt class="col-sm-4">インボイス対応</dt>
                                <dd class="col-sm-8 mb-3">
                                    @if($tax->is_invoice_eligible == 1)
                                        <span class="badge bg-success fs-6 border border-success">
                                            <i class="bi bi-patch-check-fill me-1"></i>適格
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger fs-6">
                                            <i class="bi bi-slash-circle me-1"></i>非対象
                                        </span>
                                    @endif
                                </dd>
                            </dl>
                        </div>

                    </div>
                    
                    <!-- 底部：システム情報 -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3 text-muted">システム情報</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-2 small text-muted">登録日時</dt>
                                <dd class="col-sm-4 text-muted small mb-2">{{ $tax->created_at->format('Y/m/d H:i:s') }}</dd>
                                
                                <dt class="col-sm-2 small text-muted">最終更新日時</dt>
                                <dd class="col-sm-4 text-muted small mb-2">{{ $tax->updated_at->format('Y/m/d H:i:s') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 操作按钮区域 -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('masters.account-taxs.edit', $tax) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.account-taxs.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <!-- 删除确认脚本 -->
                            <script>
                            function confirmDelete(name, code) {
                                return confirm(`本当に税区分「${name} (${code})」を削除しますか？\n\n【重要】\nこの税区分を使用している取引データ（売上、仕入など）がある場合は削除できません。\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.account-taxs.destroy', $tax) }}" method="POST" 
                                  class="d-inline" 
                                  onsubmit="return confirmDelete('{{ $tax->name }}', '{{ $tax->code }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i> 削除
                                </button>
                            </form>
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
.badge {
    font-size: 0.9em;
    padding: 0.5em 0.8em;
    font-weight: 500;
}
.display-6 {
    font-size: 1.8rem;
}
dl.row {
    margin-bottom: 0;
}
dt {
    font-weight: 600;
    color: #495057;
}
</style>
@endpush