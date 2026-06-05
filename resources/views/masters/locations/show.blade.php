@extends('layouts.app')

@section('title', '場所施設詳細: ' . $location->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.locations.index') }}">場所施設管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $location->name }}</li>
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
            
            <div class="card shadow-sm card-edit">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt"></i> 場所施設詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.locations.edit', $location) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.locations.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">施設名</dt>
                                <dd class="col-sm-8">
                                    <strong>{{ $location->name }}</strong>
                                </dd>
                                
                                <dt class="col-sm-4">地区</dt>
                                <dd class="col-sm-8">
                                    {{ $location->area ?? '-' }}
                                </dd>
                                
                                <dt class="col-sm-4">分類</dt>
                                <dd class="col-sm-8">
                                    {{ $location->category ?? '-' }}
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">連絡先情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">住所</dt>
                                <dd class="col-sm-8">
                                    @if($location->address)
                                        <i class="bi bi-geo-alt-fill me-1"></i>{{ $location->address }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">電話番号</dt>
                                <dd class="col-sm-8">
                                    @if($location->phone)
                                        <a href="tel:{{ $location->phone }}" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i>{{ $location->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($location->remark)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">備考</h6>
                            <dl class="row">
                                <dt class="col-sm-2">備考</dt>
                                <dd class="col-sm-10">{{ $location->remark }}</dd>
                            </dl>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4">{{ $location->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $location->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.locations.edit', $location) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.locations.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.locations.destroy', $location) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $location->name }}')">
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

.card-edit .card-body dl {
    margin-bottom: 0;
}

.card-edit .card-body dt {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.85rem;
}

.card-edit .card-body dd {
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}
</style>
@endpush