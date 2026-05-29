@extends('layouts.app')

@section('title', '友達')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-people me-2"></i>友達</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFriendModal">
            <i class="bi bi-plus-lg"></i> 友達追加
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.friends.index') }}" class="row g-2">
            <div class="col">
                <input type="text" name="search" class="form-control form-control-sm" style="border-color: #E5E7EB;" 
                       placeholder="会社名で検索"
                       value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" style="border-color: #E5E7EB; width: 130px;">
                    <option value="">全てのステータス</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>承認済</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>申請中</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>拒否</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm px-3" 
                        style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                    検索
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('masters.friends.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
                   style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                    クリア
                </a>
            </div>
        </form>
    </div>
    
    @if(request('search') || request('status'))
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            検索条件: 
            @if(request('search')) "{{ request('search') }}" @endif
            @if(request('status')) @if(request('search')) , @endif ステータス: {{ request('status') == 'accepted' ? '承認済' : (request('status') == 'pending' ? '申請中' : '拒否') }} @endif
            @if($friends->count() > 0)
                - {{ $friends->total() }}件の結果が見つかりました
            @else
                - 該当する友達が見つかりませんでした
            @endif
        </div>
    @endif

    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 table-list">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>会社名</th>
                        <th width="100">ステータス</th>
                        <th width="200">申請日時</th>
                        <th width="200" class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($friends as $index => $friend)
                    <tr>
                        <td>{{ $friends->firstItem() + $index }}</td>
                        <td>{{ $friend->friend_company_name }}</td>
                        <td>
                            @if($friend->status == 'accepted')
                                <span class="badge bg-success">承認済</span>
                            @elseif($friend->status == 'pending')
                                <span class="badge bg-warning">申請中</span>
                            @else
                                <span class="badge bg-secondary">拒否</span>
                            @endif
                        </td>
                        <td>{{ $friend->created_at ? \Carbon\Carbon::parse($friend->created_at)->format('Y/m/d H:i') : '-' }}</td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center flex-wrap">
                                @if($friend->status == 'pending')
                                    @if($friend->is_sender == 1)
                                        <button type="button" class="btn btn-sm btn-outline-warning cancel-friend" 
                                                data-url="{{ route('masters.friends.cancel', $friend->id) }}" 
                                                data-name="{{ $friend->friend_company_name }}" title="取消">
                                            <i class="bi bi-x-circle"></i> 取消
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-success accept-friend" 
                                                data-url="{{ route('masters.friends.update', $friend->id) }}" 
                                                data-name="{{ $friend->friend_company_name }}"
                                                data-status="accepted"
                                                title="承認">
                                            <i class="bi bi-check-lg"></i> 承認
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger reject-friend" 
                                                data-url="{{ route('masters.friends.update', $friend->id) }}" 
                                                data-name="{{ $friend->friend_company_name }}"
                                                data-status="rejected"
                                                title="拒否">
                                            <i class="bi bi-x-lg"></i> 拒否
                                        </button>
                                    @endif
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-friend" 
                                            data-url="{{ route('masters.friends.destroy', $friend->id) }}" 
                                            data-name="{{ $friend->friend_company_name }}" title="削除">
                                        <i class="bi bi-trash"></i> 削除
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            @if(request('search') || request('status'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致する友達が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-people display-6 mb-2"></i>
                                    <p class="mb-0">友達が登録されていません</p>
                                    <p class="small">「友達追加」ボタンから友達を追加してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($friends->hasPages() || $friends->total() > 0)
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
                        <li class="page-item {{ $friends->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $friends->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
    
                        @php
                            $current = $friends->currentPage();
                            $last = $friends->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp
    
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $friends->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                        @endif
    
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $friends->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                            </li>
                        @endfor
    
                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $friends->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                            </li>
                        @endif
    
                        <li class="page-item {{ !$friends->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $friends->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
    
            <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                表示中：{{ $friends->firstItem() ?? 0 }} - {{ $friends->lastItem() ?? 0 }} / 全 {{ $friends->total() }} 件
            </div>
        </div>
    @endif
</div>

<div class="modal fade" id="addFriendModal" tabindex="-1" aria-labelledby="addFriendModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addFriendModalLabel">
                    <i class="bi bi-building-plus me-2"></i>友達追加
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <form id="addFriendForm" method="POST" action="{{ route('masters.friends.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="search_company" class="form-label">会社検索</label>
                        <input type="text" class="form-control" id="search_company" placeholder="会社名で検索" autocomplete="off">
                        <div id="companySearchResults" class="list-group mt-2" style="display: none; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                    <input type="hidden" name="friend_company_id" id="selected_company_id">
                    <div id="selectedCompanyInfo" class="alert alert-info mt-2" style="display: none;">
                        <i class="bi bi-building me-2"></i>
                        <span id="selectedCompanyName"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary" id="submitFriendBtn" disabled>友達申請を送信</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteFriendForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="acceptFriendForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
</form>

<form id="rejectFriendForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
</form>

<form id="cancelFriendForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.getElementById('per_page_select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            const search = document.querySelector('input[name="search"]')?.value;
            const status = document.querySelector('select[name="status"]')?.value;
            url.searchParams.set('per_page', this.value);
            if (search) {
                url.searchParams.set('search', search);
            }
            if (status) {
                url.searchParams.set('status', status);
            }
            window.location.href = url.toString();
        });
    }

    let searchTimeout;
    const searchInput = document.getElementById('search_company');
    const resultsDiv = document.getElementById('companySearchResults');
    const selectedCompanyId = document.getElementById('selected_company_id');
    const selectedCompanyInfo = document.getElementById('selectedCompanyInfo');
    const selectedCompanyNameSpan = document.getElementById('selectedCompanyName');
    const submitBtn = document.getElementById('submitFriendBtn');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 1) {
                if (resultsDiv) {
                    resultsDiv.style.display = 'none';
                    resultsDiv.innerHTML = '';
                }
                return;
            }
            
            searchTimeout = setTimeout(function() {
                fetch('{{ route("masters.friends.search") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (resultsDiv) {
                            if (data.length > 0) {
                                let html = '';
                                data.forEach(company => {
                                    html += `<a href="#" class="list-group-item list-group-item-action company-item" 
                                                data-id="${company.id}"
                                                data-name="${escapeHtml(company.name)}">
                                                <div><strong>${escapeHtml(company.name)}</strong></div>
                                            </a>`;
                                });
                                resultsDiv.innerHTML = html;
                                resultsDiv.style.display = 'block';
                                
                                document.querySelectorAll('.company-item').forEach(item => {
                                    item.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        const id = this.getAttribute('data-id');
                                        const name = this.getAttribute('data-name');
                                        
                                        if (selectedCompanyId) selectedCompanyId.value = id;
                                        if (selectedCompanyNameSpan) selectedCompanyNameSpan.textContent = name;
                                        if (selectedCompanyInfo) selectedCompanyInfo.style.display = 'block';
                                        if (resultsDiv) resultsDiv.style.display = 'none';
                                        if (searchInput) searchInput.value = name;
                                        if (submitBtn) submitBtn.disabled = false;
                                    });
                                });
                            } else {
                                resultsDiv.innerHTML = '<div class="list-group-item text-muted">会社が見つかりません</div>';
                                resultsDiv.style.display = 'block';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }, 300);
        });
    }

    document.addEventListener('click', function(e) {
        if (searchInput && resultsDiv && !searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    const deleteButtons = document.querySelectorAll('.delete-friend');
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const name = this.getAttribute('data-name');
                if (confirm(`「${name}」を友達から削除しますか？`)) {
                    const form = document.getElementById('deleteFriendForm');
                    if (form) {
                        form.action = url;
                        form.submit();
                    }
                }
            });
        });
    }

    const acceptButtons = document.querySelectorAll('.accept-friend');
    if (acceptButtons.length > 0) {
        acceptButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const name = this.getAttribute('data-name');
                if (confirm(`「${name}」からの友達申請を承認しますか？`)) {
                    const form = document.getElementById('acceptFriendForm');
                    if (form) {
                        form.action = url;
                        
                        let input = document.querySelector('input[name="status"]');
                        if (!input) {
                            input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'status';
                            form.appendChild(input);
                        }
                        input.value = 'accepted';
                        
                        form.submit();
                    }
                }
            });
        });
    }

    const rejectButtons = document.querySelectorAll('.reject-friend');
    if (rejectButtons.length > 0) {
        rejectButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const name = this.getAttribute('data-name');
                if (confirm(`「${name}」からの友達申請を拒否しますか？\nこの操作は元に戻せません。`)) {
                    const form = document.getElementById('rejectFriendForm');
                    if (form) {
                        form.action = url;
                        
                        let input = document.querySelector('input[name="status"]');
                        if (!input) {
                            input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'status';
                            form.appendChild(input);
                        }
                        input.value = 'rejected';
                        
                        form.submit();
                    }
                }
            });
        });
    }

    const cancelButtons = document.querySelectorAll('.cancel-friend');
    if (cancelButtons.length > 0) {
        cancelButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const name = this.getAttribute('data-name');
                if (confirm(`「${name}」への友達申請を取り消しますか？`)) {
                    const form = document.getElementById('cancelFriendForm');
                    if (form) {
                        form.action = url;
                        form.submit();
                    }
                }
            });
        });
    }
});
</script>
@endpush