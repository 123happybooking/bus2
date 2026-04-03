@extends('layouts.app')

@section('title', 'ログイン履歴')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-clock-history"></i>ログイン履歴</h4>
            </div>
            
            <div class="mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('masters.login-histories.index') }}" class="row g-2">
                        <div class="col-md-2">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="ログインID・IPアドレス・スタッフ名で検索"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="staff_id" class="form-control">
                                <option value="">スタッフを選択</option>
                                @foreach($staffList as $id => $name)
                                    <option value="{{ $id }}" {{ request('staff_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">ステータス</option>
                                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>成功</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>失敗</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="start_date" class="form-control" 
                                   value="{{ request('start_date') }}" 
                                   placeholder="開始日">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="end_date" class="form-control" 
                                   value="{{ request('end_date') }}" 
                                   placeholder="終了日">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-search"></i> 検索
                            </button>
                        </div>
                    
                        @if(request()->anyFilled(['search', 'staff_id', 'status', 'start_date', 'end_date']))
                            <div class="col-md-1">
                                <a href="{{ route('masters.login-histories.index') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center px-2" style="height:100%;">
                                    <i class="bi bi-x-circle me-1"></i> クリア
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
            
            @if(request()->anyFilled(['search', 'staff_id', 'status', 'start_date', 'end_date']))
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    検索結果: {{ $loginHistories->total() }}件
                    @if(request('search')) - 検索キーワード: "{{ request('search') }}" @endif
                    @if(request('staff_id')) - スタッフ: {{ $staffList[request('staff_id')] ?? '' }} @endif
                    @if(request('status')) - ステータス: {{ request('status') == 'success' ? '成功' : '失敗' }} @endif
                    @if(request('start_date')) - 期間: {{ request('start_date') }} @endif
                    @if(request('end_date')) ~ {{ request('end_date') }} @endif
                </div>
            @endif
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th width="80">ID</th>
                                <th>スタッフ</th>
                                <th>ログインID</th>
                                <th>日時</th>
                                <th>IPアドレス</th>
                                <th>ステータス</th>
                                <th>ユーザーエージェント</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loginHistories as $history)
                            <tr>
                                <td class="text-center">{{ $history->id }}</td>
                                <td>
                                    @if($history->staff)
                                        {{ $history->staff->name }}
                                    @else
                                        <span class="text-muted">削除済み</span>
                                    @endif
                                </td>
                                <td><code>{{ $history->login_id }}</code></td>
                                <td>{{ $history->logged_at ? date('Y/m/d H:i:s', strtotime($history->logged_at)) : '-' }}</td>
                                <td><code>{{ $history->ip_address }}</code></td>
                                <td>
                                    @if($history->status == 'success')
                                        <span class="badge bg-success">成功</span>
                                    @else
                                        <span class="badge bg-danger">失敗</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($history->user_agent, 50) }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    @if(request()->anyFilled(['search', 'staff_id', 'status', 'start_date', 'end_date']))
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致するログイン履歴が見つかりませんでした</p>
                                            <p class="small">検索条件を変更してお試しください</p>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-clock-history display-6 mb-2"></i>
                                            <p class="mb-0">ログイン履歴がありません</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($loginHistories->hasPages())
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item {{ $loginHistories->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $loginHistories->previousPageUrl() }}">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                @php
                                    $current = $loginHistories->currentPage();
                                    $last = $loginHistories->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                @endphp
            
                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $loginHistories->url(1) }}">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif
            
                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $loginHistories->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endfor
            
                                @if($end < $last)
                                    @if($end < $last - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $loginHistories->url($last) }}">{{ $last }}</a>
                                    </li>
                                @endif
            
                                <li class="page-item {{ !$loginHistories->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $loginHistories->nextPageUrl() }}">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: {{ $loginHistories->firstItem() ?? 0 }} - {{ $loginHistories->lastItem() ?? 0 }} / 全 {{ $loginHistories->total() }} 件
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection