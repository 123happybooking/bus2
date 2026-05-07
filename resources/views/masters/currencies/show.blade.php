@extends('layouts.app')

@section('title', '通貨詳細: ' . $currency->currency_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.currencies.index') }}">通貨・為替レートマスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $currency->currency_name }} ({{ $currency->currency_code }})</li>
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
                        <i class="bi bi-currency-exchange"></i> 通貨詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.currencies.edit', $currency) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.currencies.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- 左侧：基本情報 -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">ID</dt>
                                <dd class="col-sm-8">
                                    <span class="text-muted small">#{{ $currency->id }}</span>
                                </dd>

                                <dt class="col-sm-4">通貨コード</dt>
                                <dd class="col-sm-8 fw-bold text-primary">{{ $currency->currency_code }}</dd>
                                
                                <dt class="col-sm-4">通貨名称</dt>
                                <dd class="col-sm-8">{{ $currency->currency_name }}</dd>
                                
                                <dt class="col-sm-4">通貨記号</dt>
                                <dd class="col-sm-8 fs-5">{{ $currency->symbol }}</dd>

                                <dt class="col-sm-4">小数桁数</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $currency->decimal_digits }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">ソート順</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-info text-dark">{{ $currency->sort }}</span>
                                </dd>
                            </dl>
                        </div>
                        
                        <!-- 右侧：為替レート情報 -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">為替レート情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">対円レート</dt>
                                <dd class="col-sm-8">
                                    <div class="form-text small mt-1">
                                        1 {{ $currency->currency_code }} = {{ $currency->rate_to_jpy }} JPY 
                                    </div>
                                </dd>

                                <dt class="col-sm-4">適用開始日</dt>
                                <dd class="col-sm-8">
                                    {{ \Carbon\Carbon::parse($currency->rate_valid_from)->format('Y/m/d') }}
                                </dd>
                                
                                <dt class="col-sm-4">適用終了日</dt>
                                <dd class="col-sm-8">
                                    @if($currency->rate_valid_to)
                                        <span class="text-danger fw-medium">{{ \Carbon\Carbon::parse($currency->rate_valid_to)->format('Y/m/d') }}</span>
                                    @else
                                        <span class="badge bg-success">無期限</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 底部：システム情報 -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4 text-muted small">{{ $currency->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4 text-muted small">{{ $currency->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 操作按钮区域 -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-1">
                            <a href="{{ route('masters.currencies.edit', $currency) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.currencies.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <!-- 删除确认脚本 -->
                            <script>
                            function confirmDelete(name, code) {
                                return confirm(`本当に通貨「${name} (${code})」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.currencies.destroy', $currency) }}" method="POST" 
                                  class="d-inline" 
                                  onsubmit="return confirmDelete('{{ $currency->currency_name }}', '{{ $currency->currency_code }}')">
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

/* 确保长文本在移动端也能良好显示 */
.dl-horizontal dt {
    font-weight: 600;
    color: #495057;
}
</style>
@endpush