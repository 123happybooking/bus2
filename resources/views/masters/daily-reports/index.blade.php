@extends('layouts.app')

@section('title', '运行日报')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">运行日报</h5>
    </div>

    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.daily-reports.index') }}" class="row g-2">
            <div class="col-auto">
                <input type="text" name="date_from" value="{{ request('date_from') }}" 
                       class="form-control form-control-sm datepicker-3months" style="width: 140px; border-color: #E5E7EB;" placeholder="YYYY-MM-DD" autocomplete="off">
            </div>
            <div class="col-auto">
                <input type="text" name="date_to" value="{{ request('date_to') }}" 
                       class="form-control form-control-sm datepicker-3months" style="width: 140px; border-color: #E5E7EB;" placeholder="YYYY-MM-DD" autocomplete="off">
            </div>
            <div class="col">
                <select name="driver_id" class="form-select form-select-sm" style="border-color: #E5E7EB;">
                    <option value="">-- 全ての運転手 --</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <select name="vehicle_id" class="form-select form-select-sm" style="border-color: #E5E7EB;">
                    <option value="">-- 全ての車両 --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->registration_number }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm px-3" 
                        style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                    検索
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('masters.daily-reports.index') }}" class="btn btn-sm btn-outline-secondary px-3" 
                   style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                    リセット
                </a>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert" style="font-size: 0.875rem;">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert" style="font-size: 0.875rem;">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0" style="border-color: #E5E7EB; font-size: 0.875rem;">
            <thead style="background-color: #F3F4F6;">
                <tr>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">日付</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">運転手</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">車両</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 90px;">出庫時間</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">出庫距離</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 90px;">帰庫時間</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">帰庫距離</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">走行距離</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 80px;">編集許可</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 80px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                @php
                    $distance = null;
                    if ($report->start_mileage && $report->end_mileage) {
                        $distance = $report->end_mileage - $report->start_mileage;
                    }
                @endphp
                <tr>
                    <td class="text-center px-2 py-1 align-middle">{{ \Carbon\Carbon::parse($report->date)->format('Y/m/d') }}</td>
                    <td class="px-2 py-1 align-middle">{{ $report->driver->name ?? '-' }}</td>
                    <td class="px-2 py-1 align-middle">{{ $report->vehicle->registration_number ?? '-' }}</td>
                    <td class="text-center px-2 py-1 align-middle">{{ $report->start_time ? \Carbon\Carbon::parse($report->start_time)->format('H:i') : '-' }}</td>
                    <td class="text-end px-2 py-1 align-middle">{{ $report->start_mileage ? number_format($report->start_mileage) : '-' }}</td>
                    <td class="text-center px-2 py-1 align-middle">{{ $report->end_time ? \Carbon\Carbon::parse($report->end_time)->format('H:i') : '-' }}</td>
                    <td class="text-end px-2 py-1 align-middle">{{ $report->end_mileage ? number_format($report->end_mileage) : '-' }}</td>
                    <td class="text-end px-2 py-1 align-middle">{{ $distance !== null ? number_format($distance) : '-' }}</td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if($report->allow_edit)
                            <span class="badge bg-success" style="background-color: #10b981 !important;">ON</span>
                        @else
                            <span class="badge bg-secondary" style="background-color: #6c757d !important;">OFF</span>
                        @endif
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <a href="{{ route('masters.daily-reports.edit', $report->id) }}" 
                           style="color: #2563eb; text-decoration: none;">
                            編集
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-3" style="color: #9ca3af;">データがありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div style="color: #6b7280; font-size: 0.875rem;">
            全 {{ $reports->total() }} 件中 
            {{ $reports->firstItem() ?? 0 }} - {{ $reports->lastItem() ?? 0 }} 件表示
        </div>
        <div>
            {{ $reports->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-sm th, .table-sm td {
    padding: 0.2rem 0.2rem !important;
    vertical-align: middle;
    border-color: #E5E7EB;
    color: #111827;
    font-size: 0.8rem;
}

.table-bordered {
    border: 1px solid #E5E7EB;
}

.table thead th {
    border-bottom-width: 1px;
    font-weight: 500;
    background-color: #F3F4F6;
    color: #374151;
    white-space: nowrap;
}

.pagination {
    margin-bottom: 0;
    gap: 2px;
}

.pagination .page-link {
    color: #374151;
    border-color: #E5E7EB;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.pagination .page-item.active .page-link {
    background-color: #2563eb;
    border-color: #2563eb;
    color: white;
}

.form-control:focus, .form-select:focus, .btn:focus {
    box-shadow: none;
    border-color: #2563eb;
}

.container-fluid {
    max-width: 1600px;
}

a:hover {
    text-decoration: underline !important;
}

.alert {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.alert-dismissible .btn-close {
    padding: 0.75rem 1rem;
}
</style>
@endpush

@push('scripts')
<script>
let startDateValue = null;
let endDateValue = null;

const startDatePicker = flatpickr('input[name="date_from"]', {
    locale: 'ja',
    dateFormat: 'Y-m-d',
    showMonths: 3,
    allowInput: true,
    clickOpens: true,
    disableMobile: true,
    onOpen: function(selectedDates, dateStr, instance) {
        instance.calendarContainer.style.zIndex = '9999';
        if (startDateValue) {
            instance.redraw();
        }
    },
    onChange: function(selectedDates, dateStr, instance) {
        if (selectedDates.length > 0) {
            startDateValue = selectedDates[0];
            endDatePicker.setDate(selectedDates[0]);
            endDatePicker.open();
            endDatePicker.set('minDate', selectedDates[0]);
            setTimeout(function() {
                endDatePicker.redraw();
                instance.redraw();
            }, 10);
        } else {
            startDateValue = null;
            endDatePicker.set('minDate', null);
            endDatePicker.redraw();
            instance.redraw();
        }
    },
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        const dayDate = dayElem.dateObj;
        if (!dayDate) return;
        
        const dayDateStr = dayDate.toDateString();
        
        if (startDateValue && dayDateStr === startDateValue.toDateString()) {
            dayElem.classList.remove('flatpickr-disabled');
            dayElem.classList.add('start-range-highlight');
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
        if (startDateValue) {
            instance.redraw();
        }
    }
});

const endDatePicker = flatpickr('input[name="date_to"]', {
    locale: 'ja',
    dateFormat: 'Y-m-d',
    showMonths: 3,
    allowInput: true,
    clickOpens: true,
    disableMobile: true,
    minDate: startDatePicker.input.value || null,
    onOpen: function(selectedDates, dateStr, instance) {
        instance.calendarContainer.style.zIndex = '9999';
        if (startDateValue) {
            setTimeout(function() {
                instance.redraw();
            }, 10);
        }
    },
    onChange: function(selectedDates, dateStr, instance) {
        if (selectedDates.length > 0) {
            endDateValue = selectedDates[0];
        } else {
            endDateValue = null;
        }
        instance.redraw();
    },
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        const dayDate = dayElem.dateObj;
        if (!dayDate) return;
        
        const dayDateStr = dayDate.toDateString();
        
        if (startDateValue && dayDateStr === startDateValue.toDateString()) {
            dayElem.classList.remove('flatpickr-disabled');
            dayElem.classList.add('start-range-highlight');
        }
        
        if (endDateValue && dayDateStr === endDateValue.toDateString()) {
            dayElem.classList.remove('flatpickr-disabled');
            dayElem.classList.add('end-range-highlight');
        }
        
        if (startDateValue && endDateValue && dayDate) {
            const startTime = startDateValue.getTime();
            const endTime = endDateValue.getTime();
            const dayTime = dayDate.getTime();
            
            if (dayTime > startTime && dayTime < endTime) {
                dayElem.classList.add('in-range-highlight');
            }
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
        if (startDateValue) {
            instance.redraw();
        }
    }
});
</script>
@endpush