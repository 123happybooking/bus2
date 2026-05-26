@extends('layouts.app')

@section('title', '運転手実績')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">運転手実績</h5>
    </div>
    
    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.driver-performance.index') }}" class="row g-1" id="searchForm">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">開始日</span>
                        <input type="text" name="start_date" value="{{ $startDate }}" 
                               class="form-control form-control-sm datepicker-3months" style="width: 120px; border-color: #E5E7EB;" id="start_date">
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <select name="period" class="form-select form-select-sm" id="period_select">
                            <option value="1" {{ $period == 1 ? 'selected' : '' }}>1週間</option>
                            <option value="2" {{ $period == 2 ? 'selected' : '' }}>2週間</option>
                            <option value="3" {{ $period == 3 ? 'selected' : '' }}>3週間</option>
                            <option value="4" {{ $period == 4 ? 'selected' : '' }}>1ヶ月</option>
                        </select>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">営業所</span>
                        <div class="branch-dropdown">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 140px; text-align: left; background-color: #fff; border-color: #ced4da;">
                                    <span id="branchSelectedText">選択</span>
                                    <span id="branchSelectedCount" class="selected-count" style="display: none;">0</span>
                                </button>
                                <div class="dropdown-menu p-0" style="min-width: 200px;">
                                    <div class="dropdown-header border-bottom px-3 py-2">
                                        <label class="d-flex align-items-center w-100" style="cursor: pointer;">
                                            <input type="checkbox" id="branchSelectAll" class="me-2"> 
                                            <span>全て選択</span>
                                        </label>
                                    </div>
                                    <div style="max-height: 250px; overflow-y: auto;">
                                        @foreach($branches ?? [] as $branch)
                                            <label class="dropdown-item d-flex align-items-center" style="cursor: pointer;">
                                                <input type="checkbox" name="branch_checkbox" value="{{ $branch->id }}" class="me-2 branch-checkbox"
                                                    {{ in_array($branch->id, (array)$branchIds) ? 'checked' : '' }}>
                                                {{ $branch->branch_name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運転手</span>
                        <select name="driver_id" class="form-select form-select-sm" style="width: 140px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($allDrivers ?? [] as $driver)
                                <option value="{{ $driver->id }}" {{ $driverId == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} @if($driver->driver_code)({{ $driver->driver_code }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm px-3"
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                            検索
                        </button>
                        <a href="{{ route('masters.driver-performance.index', ['reset_search' => 1]) }}" class="btn btn-sm btn-outline-secondary px-3"
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                            リセット
                        </a>
                        <button type="button" class="btn btn-sm btn-success px-3" onclick="openExportModal()"
                                style="background-color: #28a745; border-color: #28a745; color: white; font-size: 0.875rem;">
                            <i class="bi bi-file-excel"></i> Excel出力
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="table table-bordered table-sm performance-table" style="font-size: 0.75rem;">
            <thead>
                <tr>
                    <th class="text-center" style="position: sticky; left: 0; background-color: #f8f9fa; z-index: 11; min-width: 100px; vertical-align: middle;">運転手名</th>
                    @foreach($dates as $date)
                        <th class="text-center date-header" style="background-color: #e9ecef; min-width: 80px;">
                            {{ $date['display'] }}
                        </th>
                    @endforeach
                 </thead>
            </thead>
            <tbody>
                @foreach($drivers as $driver)
                    @php
                        $rowBgColor = $loop->index % 2 == 0 ? '#f8f9fa' : '#ffffff';
                    @endphp
                    <tr style="background-color: {{ $rowBgColor }};">
                        <td style="position: sticky; left: 0; background-color: {{ $rowBgColor }}; z-index: 11;">
                            <strong>{{ $driver->name }}</strong>
                        </td>
                        @foreach($dates as $date)
                            @php
                                $dateStr = $date['date_str'];
                                $stat = $statistics[$driver->id][$dateStr] ?? ['count' => 0, 'workload' => 0];
                                $workload = $stat['workload'];
                                $formattedWorkload = is_numeric($workload) && floor($workload) == $workload ? (int)$workload : $workload;
                            @endphp
                            <td class="text-center" style="background-color: {{ $rowBgColor }};">
                                {{ $stat['count'] }}<br>
                                {{ $formattedWorkload }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $footBgColor = '#e9ecef';
                @endphp
                <tr style="background-color: {{ $footBgColor }}; font-weight: bold;">
                    <td style="position: sticky; left: 0; background-color: {{ $footBgColor }}; z-index: 11; text-align: center;">
                        合計
                    </td>
                    @foreach($dates as $date)
                        @php
                            $dateStr = $date['date_str'];
                            $totalStat = $totalStatistics[$dateStr] ?? ['count' => 0, 'formatted_workload' => 0];
                        @endphp
                        <td class="text-center" style="background-color: {{ $footBgColor }};">
                            {{ $totalStat['count'] }}<br>
                            {{ $totalStat['formatted_workload'] }}
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div id="exportModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 10000; overflow: auto;">
    <div style="position: relative; width: 100%; min-height: 100%; display: flex; justify-content: center; align-items: center; padding: 20px;">
        <div style="background-color: #fff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); width: 400px; max-width: 90%;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background-color: #374151; border-radius: 8px 8px 0 0;">
                <span style="color: #fff; font-size: 16px; font-weight: 500;">Excel出力オプション</span>
                <button onclick="closeExportModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #fff;">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p class="mb-3" style="font-size: 14px; color: #374151;">出力する項目を選択してください：</p>
                <div class="mb-3">
                    <label class="d-flex align-items-center" style="cursor: pointer;">
                        <input type="checkbox" id="export_count" value="count" class="me-2" checked>
                        <span>予約数</span>
                    </label>
                </div>
                <div class="mb-3">
                    <label class="d-flex align-items-center" style="cursor: pointer;">
                        <input type="checkbox" id="export_workload" value="workload" class="me-2" checked>
                        <span>工数</span>
                    </label>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeExportModal()">キャンセル</button>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportExcel()">出力</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="exportForm" method="POST" action="{{ route('masters.driver-performance.export') }}" style="display: none;">
    @csrf
    <input type="hidden" name="start_date" id="export_start_date">
    <input type="hidden" name="end_date" id="export_end_date">
    <input type="hidden" name="branch_ids" id="export_branch_ids">
    <input type="hidden" name="driver_id" id="export_driver_id">
    <input type="hidden" name="export_options" id="export_options">
</form>
@endsection

@push('styles')
<style>
.performance-table th,
.performance-table td {
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.performance-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #e9ecef;
}

.performance-table th:first-child {
    position: sticky !important;
    left: 0 !important;
    z-index: 11 !important;
    background-color: #f8f9fa;
}

.performance-table td:first-child {
    position: sticky !important;
    left: 0 !important;
    z-index: 11 !important;
}

.datepicker-3months {
    border-color: #E5E7EB;
    border-radius: 4px;
    font-size: 0.8rem;
}

.table-responsive {
    overflow-x: auto;
}

.branch-dropdown .dropdown-toggle {
    min-width: 140px;
    text-align: left;
    background-color: #fff !important;
    border-color: #ced4da !important;
}

.branch-dropdown .dropdown-toggle:after {
    float: right;
    margin-top: 8px;
}

.branch-dropdown .dropdown-menu {
    min-width: 220px;
}

.branch-dropdown .dropdown-item {
    cursor: pointer;
}

.selected-count {
    background-color: #0d6efd;
    color: white;
    border-radius: 10px;
    padding: 0 6px;
    font-size: 0.7rem;
    margin-left: 8px;
    display: inline-block;
    min-width: 20px;
    text-align: center;
}
</style>
@endpush

@push('scripts')
<script>
function submitPeriod() {
    document.getElementById('searchForm').submit();
}

function initBranchSelect() {
    const checkboxes = document.querySelectorAll('.branch-checkbox');
    const selectAllCheckbox = document.getElementById('branchSelectAll');
    const branchSelectedText = document.getElementById('branchSelectedText');
    const branchSelectedCount = document.getElementById('branchSelectedCount');
    const searchForm = document.getElementById('searchForm');
    
    function updateBranchDisplay() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        const count = selected.length;
        
        if (count === 0) {
            branchSelectedText.textContent = '営業所';
            branchSelectedCount.style.display = 'none';
        } else {
            branchSelectedText.textContent = '営業所';
            branchSelectedCount.textContent = count;
            branchSelectedCount.style.display = 'inline-block';
        }
        
        if (selectAllCheckbox) {
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        }
        
        document.querySelectorAll('.branch-hidden-input').forEach(input => input.remove());
        
        selected.forEach(cb => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'branch_ids[]';
            hiddenInput.value = cb.value;
            hiddenInput.className = 'branch-hidden-input';
            searchForm.appendChild(hiddenInput);
        });
    }
    
    function toggleCheckbox(checkbox) {
        checkbox.checked = !checkbox.checked;
        updateBranchDisplay();
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.removeEventListener('change', checkbox._changeHandler);
        const changeHandler = function(e) {
            e.stopPropagation();
            updateBranchDisplay();
        };
        checkbox._changeHandler = changeHandler;
        checkbox.addEventListener('change', changeHandler);
        
        checkbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    if (selectAllCheckbox) {
        selectAllCheckbox.removeEventListener('change', selectAllCheckbox._changeHandler);
        const selectAllHandler = function(e) {
            e.stopPropagation();
            const isChecked = this.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateBranchDisplay();
        };
        selectAllCheckbox._changeHandler = selectAllHandler;
        selectAllCheckbox.addEventListener('change', selectAllHandler);
        
        selectAllCheckbox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    document.querySelectorAll('.branch-dropdown .dropdown-item').forEach(item => {
        item.removeEventListener('click', item._clickHandler);
        
        const clickHandler = function(e) {
            if (e.target.type === 'checkbox') {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            const checkbox = this.querySelector('input[type="checkbox"]');
            if (checkbox) {
                toggleCheckbox(checkbox);
            }
        };
        item._clickHandler = clickHandler;
        item.addEventListener('click', clickHandler);
    });
    
    updateBranchDisplay();
}

function openExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function exportExcel() {
    const countChecked = document.getElementById('export_count').checked;
    const workloadChecked = document.getElementById('export_workload').checked;
    
    if (!countChecked && !workloadChecked) {
        alert('出力する項目を少なくとも1つ選択してください。');
        return;
    }
    
    const exportOptions = [];
    if (countChecked) exportOptions.push('count');
    if (workloadChecked) exportOptions.push('workload');
    
    const startDate = document.querySelector('input[name="start_date"]').value;
    
    const period = parseInt(document.getElementById('period_select').value);
    let start = new Date(startDate);
    let end = new Date(start);
    if (period === 1) {
        end.setDate(start.getDate() + 6);
    } else if (period === 2) {
        end.setDate(start.getDate() + 13);
    } else if (period === 3) {
        end.setDate(start.getDate() + 20);
    } else if (period === 4) {
        end.setMonth(start.getMonth() + 1);
        end.setDate(end.getDate() - 1);
    }
    const endDate = end.toISOString().split('T')[0];
    
    const branchCheckboxes = document.querySelectorAll('.branch-checkbox:checked');
    const branchIds = Array.from(branchCheckboxes).map(cb => cb.value);
    
    const driverId = document.querySelector('select[name="driver_id"]').value;
    
    document.getElementById('export_start_date').value = startDate;
    document.getElementById('export_end_date').value = endDate;
    document.getElementById('export_branch_ids').value = JSON.stringify(branchIds);
    document.getElementById('export_driver_id').value = driverId;
    document.getElementById('export_options').value = JSON.stringify(exportOptions);
    
    document.getElementById('exportForm').submit();
    
    closeExportModal();
}

document.addEventListener('DOMContentLoaded', function() {
    initBranchSelect();
    
    const periodSelect = document.getElementById('period_select');
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            submitPeriod();
        });
    }
    
    flatpickr('.datepicker-3months', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                submitPeriod();
            }
        },
        onReady: function(selectedDates, dateStr, instance) {
            const daysContainer = instance.daysContainer;
            if (daysContainer) {
                const dayContainers = daysContainer.querySelectorAll('.dayContainer');
                dayContainers.forEach(function(dayContainer) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'month-wrapper';
                    dayContainer.parentNode.insertBefore(wrapper, dayContainer);
                    wrapper.appendChild(dayContainer);
                });
            }
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeExportModal();
    }
});

document.addEventListener('click', function(e) {
    const modal = document.getElementById('exportModal');
    if (modal && modal.style.display === 'block' && e.target === modal) {
        closeExportModal();
    }
});
</script>
@endpush