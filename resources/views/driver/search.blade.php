@extends('layouts.driver')

@section('title', '予定を検索')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="backBtn">
            <div class="back-arrow"></div>
        </button>
        <div class="page-title">予定を検索</div>
        <div class="header-right">
            <div style="width: 32px;"></div>
        </div>
    </div>

    <div class="search-form-container">
        <form method="GET" action="{{ route('driver.search') }}" class="search-form" id="searchForm">
            <div class="search-input-wrapper">
                <input type="text" name="keyword" id="searchKeyword" value="{{ $keyword }}" placeholder="団体名・行程・場所で検索" class="search-input" autocomplete="off">
                <button type="button" class="search-clear" id="clearSearch" style="display: {{ $keyword ? 'flex' : 'none' }};">×</button>
            </div>
            <button type="submit" class="search-submit">検索</button>
        </form>
    </div>

    <div class="itinerary-section">
        <div class="section-title">
            📋 検索結果 ({{ $itineraries->total() }}件)
        </div>
        <div class="itinerary-list">
            @forelse($itineraries as $itinerary)
            <div class="itinerary-card" data-id="{{ $itinerary->id }}">
                <div class="itinerary-row">
                    <div class="itinerary-left">
                        <div class="start-time">{{ \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') }}</div>
                        <div class="start-location">{{ $itinerary->start_location ?? '未設定' }}</div>
                    </div>

                    <div class="itinerary-center">
                        <div class="itinerary-vehicle">{{ $itinerary->vehicle ?? '' }}</div>
                        <div class="arrow-container">
                            <div class="arrow-line"></div>
                            <div class="arrow-triangle"></div>
                        </div>
                        <div class="itinerary-date">{{ \Carbon\Carbon::parse($itinerary->date)->format('m月d日') }}</div>
                    </div>

                    <div class="itinerary-right">
                        <div class="end-time">{{ \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') }}</div>
                        <div class="end-location">{{ $itinerary->end_location ?? '未設定' }}</div>
                    </div>
                </div>

                <div class="itinerary-footer">
                    <span class="detail-link">行程详情 &gt;</span>
                </div>
            </div>
            @empty
            <div class="empty">該当する予定はありません</div>
            @endforelse
        </div>
        
        @if($itineraries->hasPages())
        <div class="pagination-container">
            {{ $itineraries->appends(['keyword' => $keyword])->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<script>
    document.getElementById('backBtn').addEventListener('click', function() {
        window.location.href = '{{ route("driver.dashboard") }}';
    });

    document.querySelectorAll('.itinerary-card').forEach(card => {
        card.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            window.location.href = `/driver/itinerary/${id}`;
        });
    });

    const searchInput = document.getElementById('searchKeyword');
    const clearBtn = document.getElementById('clearSearch');

    if (searchInput && clearBtn) {
        searchInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                clearBtn.style.display = 'flex';
            } else {
                clearBtn.style.display = 'none';
            }
        });
        
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            searchInput.focus();
            searchForm.submit();
        });
    }
</script>
@endpush

@push('scripts')
<style>
.search-form-container {
    padding: 12px;
    background-color: var(--bg-color);
}

.search-form {
    display: flex;
    gap: 8px;
}

.search-input-wrapper {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    width: 100%;
    padding: 12px 40px 12px 16px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    background-color: var(--card-bg);
    color: var(--text-primary);
    outline: none;
}

.search-input::placeholder {
    color: var(--text-secondary);
}

.search-clear {
    position: absolute;
    right: 12px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: var(--text-secondary);
    color: var(--bg-color);
    border: none;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.search-clear:hover {
    background-color: var(--accent-color);
    color: var(--accent-text);
}

.search-submit {
    padding: 12px 20px;
    background-color: var(--accent-color);
    color: var(--accent-text);
    border: none;
    border-radius: 12px;
    font-size: 14px;
    cursor: pointer;
    font-weight: 600;
}

.itinerary-section {
    background-color: transparent;
    margin: 0 12px;
}

.section-title {
    padding: 14px 0;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
}

.itinerary-list {
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.itinerary-card {
    padding: 16px;
    background-color: var(--card-bg);
    cursor: pointer;
    border-radius: 16px;
}

.itinerary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.itinerary-left {
    text-align: left;
    width: 25%;
    flex-shrink: 0;
}

.itinerary-center {
    width: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.itinerary-right {
    text-align: right;
    width: 25%;
    flex-shrink: 0;
}

.start-time {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

.start-location {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 120px;
}

.end-time {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

.end-location {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 120px;
}

.itinerary-vehicle {
    font-size: 13px;
    color: var(--accent-color);
}

.arrow-container {
    width: 100%;
    display: flex;
    align-items: center;
    margin: 8px 0;
}

.arrow-line {
    flex: 1;
    height: 2px;
    background-color: var(--text-secondary);
}

.arrow-triangle {
    width: 0;
    height: 0;
    border-left: 8px solid var(--text-secondary);
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
}

.itinerary-date {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-secondary);
}

.itinerary-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 12px;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
}

.detail-link {
    font-size: 13px;
    color: var(--accent-color);
    text-decoration: none;
}

.empty {
    text-align: center;
    padding: 40px;
    color: var(--text-secondary);
    font-size: 13px;
}

.pagination-container {
    padding: 16px;
    text-align: center;
    background-color: transparent;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination li {
    display: inline-block;
}

.pagination a, .pagination span {
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-primary);
    font-size: 13px;
    background-color: var(--card-bg);
}

.pagination .active span {
    background-color: var(--accent-color);
    color: var(--accent-text);
    border-color: var(--accent-color);
}
</style>
@endpush