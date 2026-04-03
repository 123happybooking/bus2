@extends('layouts.app')

@section('title', '予約分類詳細: ' . $reservationCategory->category_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.reservation-categories.index') }}">予約分類マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $reservationCategory->category_name }}</li>
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
                        <i class="bi bi-tag"></i> 予約分類詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.reservation-categories.edit', $reservationCategory) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.reservation-categories.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">カテゴリコード</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $reservationCategory->category_code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">表示順</dt>
                                <dd class="col-sm-8">
                                    @if($reservationCategory->display_order)
                                        <span class="badge bg-info">{{ $reservationCategory->display_order }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">分類名</dt>
                                <dd class="col-sm-8">{{ $reservationCategory->category_name }}</dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">表示情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">カラーコード</dt>
                                <dd class="col-sm-8">
                                    <div class="d-flex align-items-center">
                                        <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: {{ $reservationCategory->color_code }}; border-radius: 3px;"></div>
                                        <code>{{ $reservationCategory->color_code }}</code>
                                    </div>
                                </dd>
                                
                                <dt class="col-sm-4">表示ラベル</dt>
                                <dd class="col-sm-8">
                                    <span class="badge" style="background-color: {{ $reservationCategory->color_code }}; color: white;">
                                        {{ $reservationCategory->category_name }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">状態</dt>
                                <dd class="col-sm-8">
                                    @if($reservationCategory->is_active)
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
                                <dd class="col-sm-4">{{ $reservationCategory->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $reservationCategory->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.reservation-categories.edit', $reservationCategory) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.reservation-categories.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.reservation-categories.destroy', $reservationCategory) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $reservationCategory->category_name }}')">
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