@extends('layouts.app')

@section('title', 'ガイド詳細: ' . $guide->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.guides.index') }}">ガイド管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $guide->name }}</li>
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
                        <i class="bi bi-person-video3"></i> ガイド詳細: {{ $guide->name }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('masters.guides.edit', $guide) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.guides.index') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> 一覧へ
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-section mb-4">
                                <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">ガイドコード</div>
                                    <div class="col-8">
                                        <code class="fs-5">{{ $guide->guide_code }}</code>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">表示順序</div>
                                    <div class="col-8">
                                        @if($guide->display_order)
                                            <span class="badge bg-info">{{ $guide->display_order }}</span>
                                        @else
                                            <span class="text-muted">未設定</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">氏名</div>
                                    <div class="col-8">
                                        <div class="fs-5">{{ $guide->name }}</div>
                                        <div class="text-muted small">{{ $guide->name_kana }}</div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">所属営業所</div>
                                    <div class="col-8">
                                        <div>{{ $guide->branch->branch_name }}</div>
                                        <div class="text-muted small">{{ $guide->branch->branch_code }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-section mb-4">
                                <h6 class="border-bottom pb-2 mb-3">連絡先情報</h6>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">電話番号</div>
                                    <div class="col-8">
                                        @if($guide->phone_number)
                                            <a href="tel:{{ $guide->phone_number }}" class="text-decoration-none">
                                                <i class="bi bi-telephone me-1"></i>{{ $guide->phone_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">未設定</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">メールアドレス</div>
                                    <div class="col-8">
                                        @if($guide->email)
                                            <a href="mailto:{{ $guide->email }}" class="text-decoration-none">
                                                <i class="bi bi-envelope me-1"></i>{{ $guide->email }}
                                            </a>
                                        @else
                                            <span class="text-muted">未設定</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-section mb-4">
                                <h6 class="border-bottom pb-2 mb-3">ステータス情報</h6>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">雇用区分</div>
                                    <div class="col-8">
                                        @if($guide->employment_type == '自社')
                                            <span class="badge bg-primary">自社</span>
                                        @elseif($guide->employment_type == '契約')
                                            <span class="badge bg-warning">契約</span>
                                        @else
                                            <span class="badge bg-info">業務委託</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">状態</div>
                                    <div class="col-8">
                                        @if($guide->is_active)
                                            <span class="badge bg-success">稼働中</span>
                                        @else
                                            <span class="badge bg-secondary">停止</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">登録日時</div>
                                    <div class="col-8">{{ $guide->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted">更新日時</div>
                                    <div class="col-8">{{ $guide->updated_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($guide->remarks)
                    <div class="row">
                        <div class="col-12">
                            <div class="info-section">
                                <h6 class="border-bottom pb-2 mb-3">備考</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $guide->remarks }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <div>
                            <a href="{{ route('masters.guides.edit', $guide) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            <a href="{{ route('masters.guides.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧へ戻る
                            </a>
                        </div>
                        
                        <div>
                            <form action="{{ route('masters.guides.destroy', $guide) }}" method="POST" 
                                  onsubmit="return confirm('このガイドを削除しますか？\nこの操作は元に戻せません。')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i> 削除する
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
.info-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.info-section h6 {
    color: #495057;
    font-weight: 600;
}

.row.mb-2 {
    padding-bottom: 0.5rem;
    border-bottom: 1px dashed #dee2e6;
}

.row.mb-2:last-child {
    border-bottom: none;
}
</style>
@endpush