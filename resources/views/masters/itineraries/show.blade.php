@extends('layouts.app')

@section('title', '行程詳細: ' . $itinerary->itinerary_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.itineraries.index') }}">行程管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $itinerary->itinerary_name }}</li>
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
                        <i class="bi bi-map"></i> 行程詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.itineraries.edit', $itinerary) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.itineraries.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">行程コード</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $itinerary->itinerary_code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">行程名</dt>
                                <dd class="col-sm-8">
                                    <strong>{{ $itinerary->itinerary_name }}</strong>
                                </dd>
                                
                                <dt class="col-sm-4">カテゴリー</dt>
                                <dd class="col-sm-8">
                                    @if($itinerary->category)
                                        <span class="badge bg-info">{{ $itinerary->category }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">登録日時</dt>
                                <dd class="col-sm-8">
                                    @if($itinerary->created_at)
                                        {{ $itinerary->created_at->format('Y/m/d H:i') }}
                                    @else
                                        <span class="text-muted">不明</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">最終更新日時</dt>
                                <dd class="col-sm-8">
                                    @if($itinerary->updated_at)
                                        {{ $itinerary->updated_at->format('Y/m/d H:i') }}
                                    @else
                                        <span class="text-muted">不明</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($itinerary->remarks)
                    <div class="row">
                        <div class="col-12">
                            <div class="info-section">
                                <h6 class="border-bottom pb-2 mb-3">備考</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $itinerary->remarks }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.itineraries.edit', $itinerary) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.itineraries.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.itineraries.destroy', $itinerary) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $itinerary->itinerary_name }}')">
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

.text-muted {
    color: #6c757d !important;
}
</style>
@endpush