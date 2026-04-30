@extends('layouts.app')

@section('title', '月度汇总详细: ' . $sum->year . '年' . $sum->month . '月')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.account-month-sums.index') }}">月度汇总</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $sum->year }}年{{ $sum->month }}月</li>
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
                        <i class="bi bi-calendar-check"></i> 月度汇总詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.account-month-sums.index') }}" class="btn btn-light btn-sm">
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
                                    <span class="text-muted small">#{{ $sum->id }}</span>
                                </dd>

                                <dt class="col-sm-4">対象期間</dt>
                                <dd class="col-sm-8 fw-bold text-primary fs-5">
                                    {{ $sum->year }}年 {{ $sum->month }}月
                                </dd>

                                <dt class="col-sm-4">流水号 (SN)</dt>
                                <dd class="col-sm-8">
                                    <code>{{ $sum->sn }}</code>
                                </dd>
                                
                                <dt class="col-sm-4">操作者</dt>
                                <dd class="col-sm-8">{{ $sum->created_by }}</dd>
                            </dl>
                        </div>
                        
                        <!-- 右侧：统计信息（可选） -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">データ概要</h6>
                            <dl class="row">
                                <dt class="col-sm-5">包含科目数</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-info">{{ count($detail) }} 件</span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 明细数据表格 -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">明細データ一覧</h6>
                            
                            @if(count($detail) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>勘定科目</th>
                                                <th>補助科目</th>
                                                <th class="text-end">期首残高</th>
                                                <th class="text-end">借方合計</th>
                                                <th class="text-end">貸方合計</th>
                                                <th class="text-end">期末残高</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($detail as $item)
                                                <tr>
                                                    <td class="fw-bold">{{ $item->account->name ?? '' }}</td>
                                                    <td class="fw-bold">{{ $item->subAccount->name ?? '' }}</td>
                                                    <td class="text-end">{{$item->account->category->remark=="借" ? number_format($item->money_start, 0) : number_format(abs($item->money_start), 0) }}</td>
                                                    <td class="text-end text-primary">{{ number_format($item->money_jie, 0) }}</td>
                                                    <td class="text-end text-danger">{{ number_format($item->money_dai, 0) }}</td>
                                                    <td class="text-end fw-bold">{{$item->account->category->remark=="借" ? number_format($item->money_end, 0) : number_format(abs($item->money_end), 0) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-circle"></i> 该月度下没有明细数据。
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 底部：システム情報 -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4 text-muted small">{{ $sum->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4 text-muted small">{{ $sum->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 操作按钮区域 -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-1">
                            <a href="{{ route('masters.account-month-sums.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <!-- 删除确认脚本 -->
                            <script>
                            function confirmDelete() {
                                return confirm(`本当に{{ $sum->year }}年{{ $sum->month }}月のデータを削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.account-month-sums.destroy', $sum) }}" method="POST" 
                                  class="d-inline" 
                                  onsubmit="return confirmDelete()">
                                @csrf
                                @method('DELETE')
                                <!-- <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> 削除
                                </button> -->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
code {
    font-size: 0.9em;
    color: #d63384;
}
/* 确保长文本在移动端也能良好显示 */
dl.row dt {
    font-weight: 600;
    color: #495057;
}
</style>
@endpush
@endsection