@extends('layouts.app')

@section('title', '取引先詳細: ' . $partner->partner_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.partners.index') }}">取引先管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $partner->partner_name }}</li>
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
                        <i class="bi bi-building"></i> 取引先詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.partners.edit', $partner) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.partners.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">取引先コード</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $partner->partner_code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">会社名</dt>
                                <dd class="col-sm-8">{{ $partner->partner_name }}</dd>
                                
                                <dt class="col-sm-4">支店名</dt>
                                <dd class="col-sm-8">
                                    @if($partner->branch_name)
                                        {{ $partner->branch_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">郵便番号</dt>
                                <dd class="col-sm-8">
                                    @if($partner->postal_code)
                                        {{ $partner->postal_code }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">住所</dt>
                                <dd class="col-sm-8">
                                    @if($partner->address)
                                        {{ $partner->address }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">連絡先情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">電話番号</dt>
                                <dd class="col-sm-8">
                                    @if($partner->phone_number)
                                        {{ $partner->phone_number }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">FAX番号</dt>
                                <dd class="col-sm-8">
                                    @if($partner->fax_number)
                                        {{ $partner->fax_number }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">担当者名</dt>
                                <dd class="col-sm-8">
                                    @if($partner->manager_name)
                                        {{ $partner->manager_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">インボイス番号</dt>
                                <dd class="col-sm-8">
                                    @if($partner->invoice_number)
                                        <span class="badge bg-info">{{ $partner->invoice_number }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">取引状態</dt>
                                <dd class="col-sm-8">
                                    @if($partner->is_active)
                                        <span class="badge bg-success">取引中</span>
                                    @else
                                        <span class="badge bg-secondary">停止</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">支払条件</h6>
                            <dl class="row">
                                <dt class="col-sm-4">締め日</dt>
                                <dd class="col-sm-8">
                                    @if($partner->closing_day)
                                        {{ $partner->closing_day }}日
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">支払条件</dt>
                                <dd class="col-sm-8">
                                    @if($partner->payment_month !== null || $partner->payment_day !== null)
                                        @if($partner->payment_month == 0)当月
                                        @elseif($partner->payment_month == 1)翌月
                                        @elseif($partner->payment_month == 2)翌々月
                                        @endif
                                        @if($partner->payment_day){{ $partner->payment_day }}日@endif
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
                                <dd class="col-sm-8">{{ $partner->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-4">最終更新日時</dt>
                                <dd class="col-sm-8">{{ $partner->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($partner->remarks)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">備考</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $partner->remarks }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.partners.edit', $partner) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.partners.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.partners.destroy', $partner) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $partner->partner_name }}')">
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