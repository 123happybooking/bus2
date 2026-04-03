@extends('layouts.app')

@section('title', 'スタッフ詳細: ' . $staff->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.staffs.index') }}">スタッフ管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $staff->name }}</li>
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
                        <i class="bi bi-person-badge"></i> スタッフ詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.staffs.edit', $staff) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.staffs.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">スタッフコード</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $staff->staff_code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">表示順序</dt>
                                <dd class="col-sm-8">
                                    @if($staff->display_order)
                                        <span class="badge bg-info">{{ $staff->display_order }}</span>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">スタッフ名</dt>
                                <dd class="col-sm-8">{{ $staff->name }}</dd>
                                
                                <dt class="col-sm-4">ログインID</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $staff->login_id }}</code>
                                </dd>
                                
                                <dt class="col-sm-4">権限</dt>
                                <dd class="col-sm-8">
                                    @php
                                        $roleLabels = [
                                            'admin' => ['label' => '管理者', 'class' => 'badge bg-danger'],
                                            'manager' => ['label' => 'マネージャー', 'class' => 'badge bg-warning'],
                                            'staff' => ['label' => '一般スタッフ', 'class' => 'badge bg-info'],
                                        ];
                                        $role = $roleLabels[$staff->role] ?? ['label' => '不明', 'class' => 'badge bg-secondary'];
                                    @endphp
                                    <span class="{{ $role['class'] }}">{{ $role['label'] }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">状態</dt>
                                <dd class="col-sm-8">
                                    @if($staff->is_active)
                                        <span class="badge bg-success">有効</span>
                                    @else
                                        <span class="badge bg-secondary">無効</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">連絡先情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">メールアドレス</dt>
                                <dd class="col-sm-8">
                                    @if($staff->email)
                                        <a href="mailto:{{ $staff->email }}" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i>{{ $staff->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">電話番号</dt>
                                <dd class="col-sm-8">
                                    @if($staff->phone_number)
                                        <a href="tel:{{ $staff->phone_number }}" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i>{{ $staff->phone_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                            
                            <h6 class="border-bottom pb-2 mb-3 mt-4">所属情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">所属営業所</dt>
                                <dd class="col-sm-8">
                                    @if($staff->branch)
                                        <div>
                                            <strong>{{ $staff->branch->branch_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                コード: {{ $staff->branch->branch_code }}
                                                @if($staff->branch->phone_number)
                                                    <br>電話: {{ $staff->branch->phone_number }}
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">営業所住所</dt>
                                <dd class="col-sm-8">
                                    @if($staff->branch && $staff->branch->address)
                                        {{ $staff->branch->address }}
                                    @else
                                        <span class="text-muted">未設定</span>
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
                                <dd class="col-sm-4">{{ $staff->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $staff->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.staffs.edit', $staff) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.staffs.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.staffs.destroy', $staff) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $staff->name }}')">
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