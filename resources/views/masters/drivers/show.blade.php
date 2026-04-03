@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-person-badge me-2"></i>運転手詳細</h4>
        <div>
            <a href="{{ route('masters.drivers.edit', $driver->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> 編集
            </a>
            <a href="{{ route('masters.drivers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list"></i> 一覧へ戻る
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">基本情報</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">運転手コード</label>
                            <p class="mb-0">{{ $driver->driver_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">表示順序</label>
                            <p class="mb-0">
                                @if($driver->display_order)
                                    <span class="badge bg-info">{{ $driver->display_order }}</span>
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">状態</label>
                            <p class="mb-0">
                                @if($driver->is_active)
                                    <span class="badge bg-success">有効</span>
                                @else
                                    <span class="badge bg-secondary">無効</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">氏名</label>
                            <p class="mb-0">{{ $driver->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">氏名（カナ）</label>
                            <p class="mb-0">{{ $driver->name_kana }}</p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label text-muted small mb-1">支店</label>
                            <p class="mb-0">
                                @if($driver->branch)
                                    {{ $driver->branch->branch_code }} - {{ $driver->branch->branch_name }}
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">連絡先情報</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">電話番号</label>
                            <p class="mb-0">
                                @if($driver->phone_number)
                                    <a href="tel:{{ $driver->phone_number }}" class="text-decoration-none">
                                        <i class="bi bi-telephone me-1"></i>{{ $driver->phone_number }}
                                    </a>
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">メールアドレス</label>
                            <p class="mb-0">
                                @if($driver->email)
                                    <a href="mailto:{{ $driver->email }}" class="text-decoration-none">
                                        <i class="bi bi-envelope me-1"></i>{{ $driver->email }}
                                    </a>
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">入社・免許情報</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">生年月日</label>
                            <p class="mb-0">
                                @if($driver->birth_date)
                                    {{ \Carbon\Carbon::parse($driver->birth_date)->format('Y年m月d日') }}
                                    ({{ \Carbon\Carbon::parse($driver->birth_date)->age }}歳)
                                @else
                                    <span class="text-muted">未設定</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">入社日</label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($driver->hire_date)->format('Y年m月d日') }}
                                ({{ \Carbon\Carbon::parse($driver->hire_date)->diffInYears(now()) }}年目)
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">免許種類</label>
                            <p class="mb-0">{{ $driver->license_type }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">免許有効期限</label>
                            @php
                                $expirationDate = \Carbon\Carbon::parse($driver->license_expiration_date);
                                $daysRemaining = now()->diffInDays($expirationDate, false);
                                $daysRemainingInt = (int)round($daysRemaining);
                                $isExpiring = $daysRemainingInt <= 30 && $daysRemainingInt >= 0;
                                $isExpired = $daysRemainingInt < 0;
                            @endphp
                            <p class="mb-0 {{ $isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : '') }}">
                                {{ $expirationDate->format('Y年m月d日') }}
                                @if($driver->is_active)
                                    @if($isExpired)
                                        <span class="badge bg-danger">期限切れ ({{ abs($daysRemainingInt) }}日前)</span>
                                    @elseif($isExpiring)
                                        <span class="badge bg-warning">間近 (あと{{ $daysRemainingInt }}日)</span>
                                    @else
                                        <span class="badge bg-success">有効 (あと{{ $daysRemainingInt }}日)</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($driver->remarks)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">備考</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p class="mb-0">{{ $driver->remarks }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">システム情報</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">作成日時</label>
                        <p class="mb-0">{{ $driver->created_at->format('Y/m/d H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">更新日時</label>
                        <p class="mb-0">{{ $driver->updated_at->format('Y/m/d H:i') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('masters.drivers.edit', $driver->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> 編集する
                        </a>
                        <form action="{{ route('masters.drivers.destroy', $driver->id) }}" method="POST" 
                              onsubmit="return confirm('本当に削除しますか？この操作は元に戻せません。')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash"></i> 削除する
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection