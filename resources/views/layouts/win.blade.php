<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        運行管理システム
        @if(View::hasSection('title'))
            - @yield('title')
        @endif
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    
    <script src="{{ asset('js/date-range-picker.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/datepicker.css') }}">

    <style>
    body {
        background-color: #F3F4F6 !important;
    }
    
    .container-fluid {
        background-color: #f3f4f6;
    }
    
    input {
        font-size: 14px !important;
        color: #111827 !important;
    }
    
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0.4;
        width: 14px;
        height: 14px;
        cursor: pointer;
    }
    
    select {
        font-size: 14px;
    }
    
    .tab-item {
        transition: all 0.1s;
        user-select: none;
        font-weight: normal;
    }
    
    .tab-item:hover {
        background-color: #e5e7eb !important;
    }
    
    div[style*="background-color: white"] {
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    
    input:focus, textarea:focus, select:focus {
        outline: none;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
    }
    
    .btn-primary:hover {
        background-color: #1d4ed8 !important;
    }
    
    .btn-danger:hover {
        background-color: #b91c1c !important;
    }
    </style>
    @stack('styles')
    
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/ja.min.js"></script>
</head>
<body>
    @yield('content')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>