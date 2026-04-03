@extends('layouts.app')

@section('title', '顧客詳細: ' . $customer->customer_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.customers.index') }}">顧客管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $customer->customer_name }}</li>
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
                        <i class="bi bi-person"></i> 顧客詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.customers.edit', $customer) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.customers.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">顧客コード</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $customer->customer_code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">顧客名</dt>
                                <dd class="col-sm-8">
                                    {{ $customer->customer_name }}
                                    @if($customer->customer_name_kana)
                                        <br><small class="text-muted">{{ $customer->customer_name_kana }}</small>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">顧客タイプ</dt>
                                <dd class="col-sm-8">
                                    @if($customer->customer_type)
                                        <span class="badge bg-info">{{ $customer->customer_type }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">ステータス</dt>
                                <dd class="col-sm-8">
                                    @if($customer->is_active)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">担当者情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">担当者名</dt>
                                <dd class="col-sm-8">
                                    @if($customer->manager_name)
                                        {{ $customer->manager_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">メールアドレス</dt>
                                <dd class="col-sm-8">
                                    @if($customer->email)
                                        <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                            
                            <h6 class="border-bottom pb-2 mb-3 mt-4">請求情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">締め日</dt>
                                <dd class="col-sm-8">
                                    @if($customer->closing_day)
                                        <span class="badge bg-primary">{{ $customer->closing_day }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">支払方法</dt>
                                <dd class="col-sm-8">
                                    @if($customer->payment_method)
                                        {{ $customer->payment_method }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">連絡先情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">郵便番号</dt>
                                <dd class="col-sm-8">
                                    @if($customer->postal_code)
                                        〒{{ $customer->postal_code }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">住所</dt>
                                <dd class="col-sm-8">
                                    @if($customer->address)
                                        {{ $customer->address }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">電話番号</dt>
                                <dd class="col-sm-8">
                                    @if($customer->phone_number)
                                        <i class="bi bi-telephone me-1"></i>{{ $customer->phone_number }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">FAX番号</dt>
                                <dd class="col-sm-8">
                                    @if($customer->fax_number)
                                        <i class="bi bi-printer me-1"></i>{{ $customer->fax_number }}
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
                                    @if($customer->created_at)
                                        {{ $customer->created_at->format('Y/m/d H:i') }}
                                    @else
                                        <span class="text-muted">不明</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">最終更新日時</dt>
                                <dd class="col-sm-8">
                                    @if($customer->updated_at)
                                        {{ $customer->updated_at->format('Y/m/d H:i') }}
                                    @else
                                        <span class="text-muted">不明</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($customer->remarks)
                    <div class="row">
                        <div class="col-12">
                            <div class="info-section">
                                <h6 class="border-bottom pb-2 mb-3">備考</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $customer->remarks }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.customers.edit', $customer) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.customers.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.customers.destroy', $customer) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $customer->customer_name }}')">
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