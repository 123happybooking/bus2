@extends('layouts.app')

@section('title', '代理店詳細: ' . $agency->agency_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.agencies.index') }}">代理店管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $agency->agency_name }}</li>
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
                        <i class="bi bi-briefcase"></i> 代理店詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.agencies.edit', $agency) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.agencies.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">代理店コード</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $agency->agency_code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">代理店名</dt>
                                <dd class="col-sm-8">{{ $agency->agency_name }}</dd>
                                
                                <dt class="col-sm-4">支店名</dt>
                                <dd class="col-sm-8">
                                    @if($agency->branch_name)
                                        {{ $agency->branch_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">種類</dt>
                                <dd class="col-sm-8">
                                    @if($agency->type)
                                        <span class="badge bg-info">{{ $agency->type }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">国</dt>
                                <dd class="col-sm-8">
                                    @if($agency->country)
                                        <span class="badge bg-light text-dark">{{ $agency->country }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">表示順</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-info">{{ $agency->display_order ?? 0 }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">取引状態</dt>
                                <dd class="col-sm-8">
                                    @if($agency->is_active)
                                        <span class="badge bg-success">取引中</span>
                                    @else
                                        <span class="badge bg-secondary">停止</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">連絡先情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">郵便番号</dt>
                                <dd class="col-sm-8">
                                    @if($agency->postal_code)
                                        {{ $agency->postal_code }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">住所</dt>
                                <dd class="col-sm-8">
                                    @if($agency->address)
                                        {{ $agency->address }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">電話番号</dt>
                                <dd class="col-sm-8">
                                    @if($agency->phone_number)
                                        <a href="tel:{{ $agency->phone_number }}" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i>{{ $agency->phone_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">FAX番号</dt>
                                <dd class="col-sm-8">
                                    @if($agency->fax_number)
                                        {{ $agency->fax_number }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">メールアドレス</dt>
                                <dd class="col-sm-8">
                                    @if($agency->email)
                                        <a href="mailto:{{ $agency->email }}" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i>{{ $agency->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">責任者名</dt>
                                <dd class="col-sm-8">
                                    @if($agency->manager_name)
                                        {{ $agency->manager_name }}
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">請求情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">手数料率</dt>
                                <dd class="col-sm-8">
                                    @if($agency->commission_rate)
                                        <span class="badge bg-info">{{ $agency->commission_rate }}%</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">締日</dt>
                                <dd class="col-sm-8">
                                    @if($agency->closing_day)
                                        <span class="badge bg-primary">{{ $agency->closing_day }}日</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">支払日</dt>
                                <dd class="col-sm-8">
                                    @if($agency->payment_day)
                                        <span class="badge bg-primary">{{ $agency->payment_day }}日後</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">支払スケジュール</dt>
                                <dd class="col-sm-8">
                                    @if($agency->closing_day && $agency->payment_day)
                                        <span class="text-dark">{{ $agency->closing_day }}日締 / {{ $agency->payment_day }}日後支払</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">備考情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">備考</dt>
                                <dd class="col-sm-8">
                                    @if($agency->remarks)
                                        <div class="border rounded p-3 bg-light">
                                            {!! nl2br(e($agency->remarks)) !!}
                                        </div>
                                    @else
                                        <span class="text-muted">なし</span>
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
                                <dd class="col-sm-4">{{ $agency->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $agency->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.agencies.edit', $agency) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.agencies.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.agencies.destroy', $agency) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $agency->agency_name }}')">
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

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endpush