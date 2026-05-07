@extends('layouts.app')

@section('title', '料金詳細: ' . $fee->fee_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.fees.index') }}">料金マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $fee->fee_name }}</li>
                </ol>
            </nav>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-cash"></i> 料金詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.fees.edit', $fee) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.fees.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">料金コード</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $fee->fee_code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">表示順</dt>
                                <dd class="col-sm-8">
                                    @if($fee->display_order)
                                        <span class="badge bg-info">{{ $fee->display_order }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">項目名</dt>
                                <dd class="col-sm-8">{{ $fee->fee_name }}</dd>
                                
                                <dt class="col-sm-4">区分</dt>
                                <dd class="col-sm-8">
                                    @if($fee->fee_category)
                                        <span class="badge bg-info">{{ $fee->fee_category }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">料金情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">税率</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-primary">{{ number_format($fee->tax_rate, 1) }}%</span>
                                </dd>
                                
                                <dt class="col-sm-4">標準単価</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-success">¥{{ number_format($fee->default_amount) }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">税込単価</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-success">
                                        ¥{{ number_format($fee->default_amount * (1 + $fee->tax_rate / 100)) }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">状態</dt>
                                <dd class="col-sm-8">
                                    @if($fee->is_active)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4">{{ $fee->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $fee->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.fees.edit', $fee) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.fees.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.fees.destroy', $fee) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $fee->fee_name }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
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
    font-size: 0.85em;
}

.d-flex.gap-2 > * {
    margin-right: 0.5rem;
}
.d-flex.gap-2 > *:last-child {
    margin-right: 0;
}
</style>
@endpush