<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>作成日・指示日の入力</title>
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'msyh', 'genshin', sans-serif;
        background: #f5f7fa;
        padding: 40px 20px;
        color: #111827;
    }
    .card {
        max-width: 560px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        padding: 28px 32px;
    }
    h2 {
        color: #dc2626;
        font-size: 18px;
        margin: 0 0 16px 0;
    }
    .desc {
        font-size: 13px;
        color: #4b5563;
        margin-bottom: 24px;
        line-height: 1.7;
    }
    .field {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
    }
    .field label {
        width: 90px;
        flex-shrink: 0;
        font-size: 13px;
        text-align: right;
        padding-right: 12px;
        color: #374151;
    }
    .field input[type="text"] {
        flex: 1;
        padding: 8px 10px;
        font-size: 13px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        background: #fffbd9;
    }
    .field input[type="text"]:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37,99,235,0.2);
    }
    .error {
        color: #dc2626;
        font-size: 12px;
        margin: 0 0 16px 102px;
    }
    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    .btn {
        padding: 8px 20px;
        font-size: 13px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
    }
    .btn-cancel {
        background: #e5e7eb;
        color: #374151;
    }
    .btn-cancel:hover { background: #d1d5db; }
    .btn-submit {
        background: #dc2626;
        color: #fff;
    }
    .btn-submit:hover { background: #b91c1c; }

    /* ========== flatpickr 强制 3 个月显示（来自 layouts.app 的等价 CSS） ========== */
    .flatpickr-calendar {
        border: 1px solid #ddd !important;
        border-radius: 6px !important;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12) !important;
        font-family: inherit !important;
        font-size: 11px !important;
        overflow: hidden !important;
    }

    .flatpickr-calendar.multiMonth {
        width: 516px !important;
        max-width: 95vw !important;
    }

    .flatpickr-calendar.multiMonth .flatpickr-innerContainer {
        width: 100% !important;
    }

    .flatpickr-calendar.multiMonth .flatpickr-months {
        display: flex !important;
    }

    .flatpickr-calendar.multiMonth .flatpickr-month {
        flex: 1 !important;
    }

    .flatpickr-calendar.multiMonth .flatpickr-month:not(:last-child) {
        border-right: 1px solid #e9ecef !important;
    }

    .flatpickr-months {
        background: linear-gradient(135deg, #1f3241 0%, #2d4a5e 100%) !important;
        border-radius: 6px 6px 0 0 !important;
        display: flex !important;
    }

    .flatpickr-month {
        height: 28px !important;
        padding-right: 0 !important;
    }

    .flatpickr-current-month {
        padding: 3px 0 0 0 !important;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months {
        font-weight: 600 !important;
        color: #fff !important;
        font-size: 11px !important;
    }

    .flatpickr-current-month .numInputWrapper span {
        color: #fff !important;
    }

    .flatpickr-current-month input.cur-year {
        color: #fff !important;
        font-weight: 600 !important;
        font-size: 11px !important;
    }

    .flatpickr-months .flatpickr-month,
    .flatpickr-months .flatpickr-next-month,
    .flatpickr-months .flatpickr-prev-month {
        color: #fff !important;
        fill: #fff !important;
    }

    .flatpickr-months .flatpickr-next-month:hover svg,
    .flatpickr-months .flatpickr-prev-month:hover svg {
        fill: #ffc107 !important;
    }

    .flatpickr-months .flatpickr-next-month,
    .flatpickr-months .flatpickr-prev-month {
        width: 20px !important;
        height: 20px !important;
        padding: 2px !important;
    }

    .flatpickr-weekdays {
        background: #f8f9fa !important;
        border-bottom: 1px solid #e9ecef !important;
        margin: 0 !important;
    }

    .flatpickr-weekday {
        color: #495057 !important;
        font-weight: 600 !important;
        font-size: 10px !important;
        padding: 1px 0 !important;
    }

    .flatpickr-days {
        border: none !important;
        padding: 0 !important;
    }

    .flatpickr-day {
        color: #374151 !important;
        border-radius: 2px !important;
        margin: 0 !important;
        border: 1px solid transparent !important;
        max-width: 24px !important;
        width: 24px !important;
        height: 22px !important;
        line-height: 20px !important;
        font-size: 10px !important;
    }

    .flatpickr-day:hover {
        background: #e0f2fe !important;
        border-color: #2563eb !important;
        color: #2563eb !important;
    }

    .flatpickr-day.selected {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
        font-weight: 600 !important;
    }

    .flatpickr-day.selected:hover {
        background: #1d4ed8 !important;
    }

    .flatpickr-day.today {
        border-color: #ffc107 !important;
        background: #fffbeb !important;
        color: #374151 !important;
    }

    .flatpickr-day.today:hover {
        background: #fef3c7 !important;
        border-color: #f59e0b !important;
        color: #374151 !important;
    }

    .flatpickr-months .flatpickr-month {
        background: transparent !important;
    }

    span.flatpickr-weekday {
        background: #f8f9fa !important;
    }

    .flatpickr-calendar.multiMonth .dayContainer {
        width: 168px !important;
        min-width: 168px !important;
        max-width: 168px !important;
        position: relative !important;
    }

    .month-wrapper {
        flex: 1 !important;
        position: relative !important;
        padding: 2px !important;
        height: 135px !important;
    }

    .month-wrapper:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 1px;
        background-color: #e9ecef;
    }

    .flatpickr-calendar.multiMonth .flatpickr-days {
        display: flex !important;
        position: relative;
        width: 514px !important;
    }

    .flatpickr-calendar.multiMonth .flatpickr-days .dayContainer {
        padding: 0 !important;
    }

    .flatpickr-calendar.multiMonth .flatpickr-rContainer {
        width: 514px !important;
    }
</style>
</head>
<body>
    <div class="card">
        <h2>作成日または指示日が未設定です</h2>
        <p class="desc">
            運行指示書PDF2を出力するには、「作成日」と「指示日」の入力が必要です。<br>
            以下のフォームに入力して「PDF作成」を押してください。
        </p>

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="error">{{ $error }}</div>
            @endforeach
        @endif

        <form id="datesForm" novalidate>
            @csrf
        
            <div class="field">
                <label for="created_date">作成日</label>
                <input type="text"
                       id="created_date"
                       name="created_date"
                       class="datepicker-3months"
                       value="{{ old('created_date', $busAssignment->created_date ? \Carbon\Carbon::parse($busAssignment->created_date)->format('Y-m-d') : '') }}"
                       placeholder="YYYY-MM-DD"
                       autocomplete="off"
                       required>
            </div>
        
            <div class="field">
                <label for="instruction_date">指示日</label>
                <input type="text"
                       id="instruction_date"
                       name="instruction_date"
                       class="datepicker-3months"
                       value="{{ old('instruction_date', $busAssignment->instruction_date ? \Carbon\Carbon::parse($busAssignment->instruction_date)->format('Y-m-d') : '') }}"
                       placeholder="YYYY-MM-DD"
                       autocomplete="off"
                       required>
            </div>
        
            <div class="actions">
                <button type="button" class="btn btn-cancel" onclick="closeOrBack()">キャンセル</button>
                <button type="submit" class="btn btn-submit" id="submitPdfBtn">PDF作成</button>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/ja.min.js"></script>
    <script>
        function closeOrBack() {
            if (window.opener) {
                window.close();
            } else {
                history.back();
            }
        }
    
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.datepicker-3months').forEach(function(el) {
                if (el._flatpickr) return;
                flatpickr(el, {
                    locale: 'ja',
                    dateFormat: 'Y-m-d',
                    showMonths: 3,
                    allowInput: true,
                    clickOpens: true,
                    mode: 'single',
                    disableMobile: true,
                    onOpen: function(selectedDates, dateStr, instance) {
                        instance.calendarContainer.style.zIndex = '99999';
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        const daysContainer = instance.daysContainer;
                        if (daysContainer) {
                            const dayContainers = daysContainer.querySelectorAll('.dayContainer');
                            dayContainers.forEach(function(dayContainer) {
                                if (!dayContainer.parentNode.classList.contains('month-wrapper')) {
                                    const wrapper = document.createElement('div');
                                    wrapper.className = 'month-wrapper';
                                    dayContainer.parentNode.insertBefore(wrapper, dayContainer);
                                    wrapper.appendChild(dayContainer);
                                }
                            });
                        }
                    }
                });
            });
    
            const form = document.getElementById('datesForm');
            const submitBtn = document.getElementById('submitPdfBtn');
    
            form.addEventListener('submit', function(e) {
                e.preventDefault();
    
                const createdDate = form.querySelector('input[name="created_date"]').value.trim();
                const instructionDate = form.querySelector('input[name="instruction_date"]').value.trim();
    
                if (!createdDate) {
                    alert('作成日を入力してください。');
                    form.querySelector('input[name="created_date"]').focus();
                    return;
                }
                if (!instructionDate) {
                    alert('指示日を入力してください。');
                    form.querySelector('input[name="instruction_date"]').focus();
                    return;
                }
    
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const actionUrl = '{{ route('masters.group-infos.save-instruction-dates-and-export-pdf2', $busAssignment->id) }}';
    
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = '作成中...';
    
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('created_date', createdDate);
                formData.append('instruction_date', instructionDate);
    
                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(async function(response) {
                    if (response.status === 422) {
                        const data = await response.json();
                        const messages = [];
                        if (data.errors) {
                            Object.values(data.errors).forEach(function(arr) {
                                arr.forEach(function(m) { messages.push(m); });
                            });
                        }
                        alert(messages.join('\n') || '入力内容を確認してください。');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        return null;
                    }
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (!data) return;
    
                    if (data.success && data.pdf_url) {
                        window.open(data.pdf_url, '_blank');
    
                        if (window.opener && !window.opener.closed) {
                            try {
                                window.opener.location.reload();
                            } catch (err) {}
                        }
    
                        setTimeout(function() {
                            window.close();
                            setTimeout(function() {
                                document.body.innerHTML =
                                    '<div style="padding:40px;text-align:center;font-family:sans-serif;">' +
                                    '<h3>PDFを作成しました</h3>' +
                                    '<p>このタブは手動で閉じてください。</p>' +
                                    '</div>';
                            }, 300);
                        }, 500);
                    } else {
                        alert(data.message || 'PDF作成に失敗しました。');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(function(err) {
                    alert('エラーが発生しました: ' + err.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        });
    </script>
</body>
</html>