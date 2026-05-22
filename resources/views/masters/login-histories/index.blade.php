@extends('layouts.app')

@section('title', 'ログイン履歴')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-clock-history"></i>ログイン履歴</h4>
            </div>
            
            <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
                <form method="GET" action="{{ route('masters.login-histories.index') }}" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control form-control-sm" style="border-color: #E5E7EB; width: 180px;" 
                               placeholder="ログインID・IPアドレス・スタッフ名"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select name="staff_id" class="form-select form-select-sm" style="border-color: #E5E7EB; width: 150px;">
                            <option value="">スタッフを選択</option>
                            @foreach($staffList as $id => $name)
                                <option value="{{ $id }}" {{ request('staff_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm" style="border-color: #E5E7EB; width: 100px;">
                            <option value="">ステータス</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>成功</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>失敗</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="date" name="start_date" class="form-control form-control-sm" style="border-color: #E5E7EB; width: 140px;" 
                               value="{{ request('start_date') }}" placeholder="開始日">
                    </div>
                    <div class="col-auto">
                        <input type="date" name="end_date" class="form-control form-control-sm" style="border-color: #E5E7EB; width: 140px;" 
                               value="{{ request('end_date') }}" placeholder="終了日">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm px-3" 
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                    </div>
                    @if(request()->anyFilled(['search', 'staff_id', 'status', 'start_date', 'end_date']))
                        <div class="col-auto">
                            <a href="{{ route('masters.login-histories.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
                               style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                                クリア
                            </a>
                        </div>
                    @endif
                </form>
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
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <button type="button" id="batchDeleteBtn" class="btn btn-danger btn-sm" disabled>
                            <i class="bi bi-trash"></i> 選択した履歴を削除
                        </button>
                        <span class="text-muted ms-2" id="selectedCount" style="font-size: 0.8rem;">0件選択</span>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 table-list">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>No.</th>
                                <th>スタッフ</th>
                                <th>ログインID</th>
                                <th>日時</th>
                                <th>IPアドレス</th>
                                <th>ステータス</th>
                                <th style="width: 30%;">ユーザーエージェント</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loginHistories as $index => $history)
                            <tr data-id="{{ $history->id }}">
                                <td class="text-center">
                                    <input type="checkbox" class="history-checkbox" value="{{ $history->id }}">
                                </td>
                                <td>{{ $loginHistories->firstItem() + $index }}</td>
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
                                <td colspan="8" class="text-center py-4">
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
                
                @if($loginHistories->hasPages() || $loginHistories->total() > 0)
                    <div class="mt-3">
                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                            
                            <div class="d-flex align-items-center">
                                <label for="per_page_select" class="form-label small text-muted mb-0 me-2" style="white-space: nowrap;">
                                    表示件数:
                                </label>
                                <select id="per_page_select" class="form-select form-select-sm" style="font-size: 0.75rem; min-width: 80px;">
                                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                                    <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                                </select>
                            </div>
                
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item {{ $loginHistories->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $loginHistories->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
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
                                            <a class="page-link" href="{{ $loginHistories->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                                        @endif
                                    @endif
                
                                    @for($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $loginHistories->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                                        </li>
                                    @endfor
                
                                    @if($end < $last)
                                        @if($end < $last - 1)
                                            <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $loginHistories->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                                        </li>
                                    @endif
                
                                    <li class="page-item {{ !$loginHistories->hasMorePages() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $loginHistories->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                
                        <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                            表示中：{{ $loginHistories->firstItem() ?? 0 }} - {{ $loginHistories->lastItem() ?? 0 }} / 全 {{ $loginHistories->total() }} 件
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.history-checkbox');
    const batchDeleteBtn = document.getElementById('batchDeleteBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.history-checkbox:checked');
        const count = checked.length;
        if (selectedCountSpan) {
            selectedCountSpan.textContent = count + '件選択';
        }
        if (batchDeleteBtn) {
            batchDeleteBtn.disabled = count === 0;
        }
    }
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateSelectedCount();
        });
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    
    if (batchDeleteBtn) {
        batchDeleteBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.history-checkbox:checked');
            if (checked.length === 0) return;
            
            const ids = Array.from(checked).map(cb => cb.value);
            
            if (confirm('選択した ' + ids.length + ' 件のログイン履歴を削除してもよろしいですか？\nこの操作は元に戻せません。')) {
                fetch('{{ route("masters.login-histories.batch-delete") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('削除に失敗しました: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('エラーが発生しました: ' + error.message);
                });
            }
        });
    }
});

document.getElementById('per_page_select').addEventListener('change', function() {
    const url = new URL(window.location.href);
    const search = document.querySelector('input[name="search"]')?.value;
    url.searchParams.set('per_page', this.value);
    if (search) {
        url.searchParams.set('search', search);
    }
    window.location.href = url.toString();
});
</script>
@endpush