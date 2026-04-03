@extends('admin.layouts.app')

@section('title', 'ユーザー詳細: ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">ユーザー管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $user->name }}</li>
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
                        <i class="bi bi-people"></i> ユーザー詳細
                    </h5>
                    <div>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">ID</dt>
                                <dd class="col-sm-8">{{ $user->id }}</dd>
                                
                                <dt class="col-sm-4">名前</dt>
                                <dd class="col-sm-8">{{ $user->name }}</dd>
                                
                                <dt class="col-sm-4">ログインID</dt>
                                <dd class="col-sm-8">{{ $user->login_id }}</dd>
                                
                                <dt class="col-sm-4">会社名</dt>
                                <dd class="col-sm-8">{{ $user->user_company_name ?? '-' }}</dd>
                                
                                <dt class="col-sm-4">プラン</dt>
                                <dd class="col-sm-8">
                                    @if($user->user_plan == 'basic')
                                        ベーシック
                                    @elseif($user->user_plan == 'premium')
                                        プレミアム
                                    @elseif($user->user_plan == 'enterprise')
                                        エンタープライズ
                                    @else
                                        -
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">契約開始日</dt>
                                <dd class="col-sm-8">{{ $user->user_start_day ? $user->user_start_day->format('Y/m/d') : '-' }}</dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">登録日時</dt>
                                <dd class="col-sm-8">{{ $user->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-4">最終更新日時</dt>
                                <dd class="col-sm-8">{{ $user->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $user->name }}')">
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