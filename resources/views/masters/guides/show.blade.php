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
            
            <div class="card shadow-sm card-edit">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-video3"></i> ガイド詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.guides.edit', $guide) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.guides.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">ガイドコード</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $guide->guide_code }}</code>
                                </dd>
                                
                                <dt class="col-sm-4">表示順序</dt>
                                <dd class="col-sm-8">
                                    @if($guide->display_order)
                                        <span class="badge bg-info">{{ $guide->display_order }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">氏名</dt>
                                <dd class="col-sm-8">
                                    <div>{{ $guide->name }}</div>
                                    @if($guide->name_kana)
                                        <small class="text-muted">{{ $guide->name_kana }}</small>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">雇用区分</dt>
                                <dd class="col-sm-8">
                                    @if($guide->employment_type == '自社')
                                        <span class="badge bg-primary">自社</span>
                                    @elseif($guide->employment_type == '契約')
                                        <span class="badge bg-warning">契約</span>
                                    @else
                                        <span class="badge bg-info">業務委託</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">状態</dt>
                                <dd class="col-sm-8">
                                    @if($guide->is_active)
                                        <span class="badge bg-success">稼働中</span>
                                    @else
                                        <span class="badge bg-secondary">停止</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">所属・連絡先情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">所属営業所</dt>
                                <dd class="col-sm-8">
                                    @if($guide->branch)
                                        <div>
                                            <strong>{{ $guide->branch->branch_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                コード: {{ $guide->branch->branch_code }}
                                                @if($guide->branch->phone_number)
                                                    <br>電話: {{ $guide->branch->phone_number }}
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">営業所住所</dt>
                                <dd class="col-sm-8">
                                    @if($guide->branch && $guide->branch->address)
                                        {{ $guide->branch->address }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">電話番号</dt>
                                <dd class="col-sm-8">
                                    @if($guide->phone_number)
                                        <a href="tel:{{ $guide->phone_number }}" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i>{{ $guide->phone_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">メールアドレス</dt>
                                <dd class="col-sm-8">
                                    @if($guide->email)
                                        <a href="mailto:{{ $guide->email }}" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i>{{ $guide->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($guide->remarks)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">備考</h6>
                            <dl class="row">
                                <dt class="col-sm-2">備考</dt>
                                <dd class="col-sm-10">{{ $guide->remarks }}</dd>
                            </dl>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4">{{ $guide->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $guide->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.guides.edit', $guide) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.guides.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.guides.destroy', $guide) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $guide->name }}')">
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