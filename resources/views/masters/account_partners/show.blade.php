@extends('layouts.app')

@section('title', '取引先詳細: ' . $partner->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account_partners.index') }}">取引先マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $partner->name }}</li>
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
                        <i class="bi bi-people"></i> 取引先詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.account_partners.edit', $partner) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.account_partners.index') }}" class="btn btn-light btn-sm">
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
                                    <span class="text-muted small">#{{ $partner->id }}</span>
                                </dd>

                                <dt class="col-sm-4">取引先名</dt>
                                <dd class="col-sm-8 fw-bold text-primary">{{ $partner->name }}</dd>
                                
                                <dt class="col-sm-4">分類</dt>
                                <dd class="col-sm-8">
                                    @if($partner->category)
                                        <span class="badge bg-secondary">{{ $partner->category }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">会社名</dt>
                                <dd class="col-sm-8">
                                    {{ $partner->company_name ?? '-' }}
                                </dd>

                                <dt class="col-sm-4">登録番号</dt>
                                <dd class="col-sm-8">
                                    @if($partner->registration_number)
                                        <span class="font-monospace">{{ $partner->registration_number }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <!-- 右侧：連絡先情報 -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">連絡先情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">住所</dt>
                                <dd class="col-sm-8">
                                    @if($partner->address)
                                        <div class="small">{{ nl2br(e($partner->address)) }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">電話</dt>
                                <dd class="col-sm-8">
                                    @if($partner->phone)
                                        <a href="tel:{{ $partner->phone }}" class="text-decoration-none">
                                            <i class="bi bi-telephone"></i> {{ $partner->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">責任者</dt>
                                <dd class="col-sm-8">
                                    {{ $partner->person_in_charge ?? '-' }}
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
                                <dd class="col-sm-4 text-muted small">{{ $partner->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4 text-muted small">{{ $partner->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 操作按钮区域 -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-1">
                            <a href="{{ route('masters.account_partners.edit', $partner) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.account_partners.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <!-- 删除确认脚本 -->
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に取引先「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.account_partners.destroy', $partner) }}" method="POST" 
                                  class="d-inline" 
                                  onsubmit="return confirmDelete('{{ $partner->name }}')">
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
dl.row dt {
    font-weight: 600;
    color: #495057;
}
</style>
@endpush