@extends('layouts.app')

@section('title', '区分詳細: ' . $category->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-categories.index') }}">勘定科目区分マスター</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $category->name }}</li>
                </ol>
            </nav>
            
            <!-- 成功消息 -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif

            <!-- 错误消息 -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif
            
            <!-- 详情卡片 -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> 区分詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.account-categories.edit', $category) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.account-categories.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- 左侧：基本情報 -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">ID</dt>
                                <dd class="col-sm-8">
                                    <span class="text-muted small">#{{ $category->id }}</span>
                                </dd>

                                <dt class="col-sm-4">区分名称</dt>
                                <dd class="col-sm-8 fw-bold fs-5">{{ $category->name }}</dd>
                                
                                <dt class="col-sm-4">貸借区分</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $category->mark }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">レベル (階層)</dt>
                                <dd class="col-sm-8">
                                    @if($category->level == 1)
                                        <span class="text-start fw-bold">五大要素</span>
                                    @elseif($category->level == 2)
                                        <span class="text-start fw-bold">大分類</span>
                                    @elseif($category->level == 3)
                                        <span class="text-start fw-bold">中分類</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 底部：システム情報 -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4 text-muted small">{{ $category->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4 text-muted small">{{ $category->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 操作按钮区域 -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-1">
                            <a href="{{ route('masters.account-categories.edit', $category) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.account-categories.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <!-- 删除确认脚本 -->
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当に区分「${name}」を削除しますか？\n関連する勘定科目がある場合は削除できません。\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.account-categories.destroy', $category) }}" method="POST" 
                                  class="d-inline" 
                                  onsubmit="return confirmDelete('{{ $category->name }}')">
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
    padding: 0.5em 0.8em;
}

.dl-horizontal dt {
    font-weight: 600;
    color: #495057;
}
</style>
@endpush