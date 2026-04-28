@extends('layouts.app')

@section('title', '運転手詳細: ' . $driver->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.drivers.index') }}">運転手管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $driver->name }}</li>
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
                        <i class="bi bi-person-badge"></i> 運転手詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.drivers.edit', $driver->id) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.drivers.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">運転手コード</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $driver->driver_code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">表示順序</dt>
                                <dd class="col-sm-8">
                                    @if($driver->display_order)
                                        <span class="badge bg-info">{{ $driver->display_order }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">状態</dt>
                                <dd class="col-sm-8">
                                    @if($driver->is_active)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">氏名</dt>
                                <dd class="col-sm-8">{{ $driver->name }}</dd>
                                
                                <dt class="col-sm-4">氏名（カナ）</dt>
                                <dd class="col-sm-8">{{ $driver->name_kana ?? '--' }}</dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">所属情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">支店</dt>
                                <dd class="col-sm-8">
                                    @if($driver->branch)
                                        <div>
                                            <strong>{{ $driver->branch->branch_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                コード: {{ $driver->branch->branch_code }}
                                                @if($driver->branch->phone_number)
                                                    <br>電話: {{ $driver->branch->phone_number }}
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">支店住所</dt>
                                <dd class="col-sm-8">
                                    @if($driver->branch && $driver->branch->address)
                                        {{ $driver->branch->address }}
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
                                <dt class="col-sm-4">電話番号</dt>
                                <dd class="col-sm-8">
                                    @if($driver->phone_number)
                                        <a href="tel:{{ $driver->phone_number }}" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i>{{ $driver->phone_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">メールアドレス</dt>
                                <dd class="col-sm-8">
                                    @if($driver->email)
                                        <a href="mailto:{{ $driver->email }}" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i>{{ $driver->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">入社・免許情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">生年月日</dt>
                                <dd class="col-sm-8">
                                    @if($driver->birth_date)
                                        {{ \Carbon\Carbon::parse($driver->birth_date)->format('Y年m月d日') }}
                                        ({{ \Carbon\Carbon::parse($driver->birth_date)->age }}歳)
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">入社日</dt>
                                <dd class="col-sm-8">
                                    {{ \Carbon\Carbon::parse($driver->hire_date)->format('Y年m月d日') }}
                                    ({{ \Carbon\Carbon::parse($driver->hire_date)->diffInYears(now()) }}年目)
                                </dd>
                                
                                <dt class="col-sm-4">免許種類</dt>
                                <dd class="col-sm-8">{{ $driver->license_type ?? '--' }}</dd>
                                
                                <dt class="col-sm-4">免許有効期限</dt>
                                <dd class="col-sm-8">
                                    @php
                                        $expirationDate = \Carbon\Carbon::parse($driver->license_expiration_date);
                                        $daysRemaining = now()->diffInDays($expirationDate, false);
                                        $daysRemainingInt = (int)round($daysRemaining);
                                        $isExpiring = $daysRemainingInt <= 30 && $daysRemainingInt >= 0;
                                        $isExpired = $daysRemainingInt < 0;
                                    @endphp
                                    <span class="{{ $isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : '') }}">
                                        {{ $expirationDate->format('Y年m月d日') }}
                                    </span>
                                    @if($driver->is_active)
                                        @if($isExpired)
                                            <span class="badge bg-danger ms-1">期限切れ ({{ abs($daysRemainingInt) }}日前)</span>
                                        @elseif($isExpiring)
                                            <span class="badge bg-warning ms-1">間近 (あと{{ $daysRemainingInt }}日)</span>
                                        @else
                                            <span class="badge bg-success ms-1">有効 (あと{{ $daysRemainingInt }}日)</span>
                                        @endif
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($driver->remarks)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">備考</h6>
                            <dl class="row">
                                <dt class="col-sm-2">備考</dt>
                                <dd class="col-sm-10">{{ $driver->remarks }}</dd>
                            </dl>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4">{{ $driver->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $driver->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.drivers.edit', $driver->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.drivers.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.drivers.destroy', $driver->id) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $driver->name }}')">
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