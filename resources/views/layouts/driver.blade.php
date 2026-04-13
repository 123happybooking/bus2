<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '運転手ポータル')</title>
    <style>
        :root {
            --bg-color: #1f3241;
            --card-bg: #2a3f4f;
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.6);
            --text-muted: rgba(255, 255, 255, 0.4);
            --accent-color: #ffc107;
            --accent-text: #1f3241;
            --border-color: rgba(255, 255, 255, 0.1);
            --calendar-day-bg: #3a5a6e;
            --calendar-day-other: #2a4a5e;
            --header-bg: #1f3241;
            --sidebar-bg: #2a3f4f;
            --sidebar-header-bg: #1f3241;
            --itinerary-time-bg: #1f3241;
            --button-bg: #ffc107;
            --button-text: #1f3241;
            --count-text-color: #ffffff;
            --text-white: #fff;
        }

        :root[data-theme="light"] {
            --bg-color: #f5f7fa;
            --card-bg: #ffffff;
            --text-primary: #1f3241;
            --text-secondary: rgba(0, 0, 0, 0.6);
            --text-muted: rgba(0, 0, 0, 0.3);
            --accent-color: #2563eb;
            --accent-text: #ffffff;
            --border-color: rgba(0, 0, 0, 0.1);
            --calendar-day-bg: #e8edf2;
            --calendar-day-other: #dce3e9;
            --header-bg: #ffffff;
            --sidebar-bg: #ffffff;
            --sidebar-header-bg: #2563eb;
            --itinerary-time-bg: #e8edf2;
            --button-bg: #2563eb;
            --button-text: #ffffff;
            --count-text-color: #1f3241;
            --text-white: #fff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--bg-color);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        
        .page-title {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-primary);
            text-align: center;
            flex: 1;
        }
        
        .back-arrow {
            width: 10px;
            height: 10px;
            border-left: 2px solid var(--text-primary);
            border-bottom: 2px solid var(--text-primary);
            transform: rotate(45deg);
        }
        
        .mobile-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: var(--bg-color);
            min-height: 100vh;
            padding-bottom: 20px;
        }
        
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background-color: var(--header-bg);
            color: var(--text-primary);
            gap: 8px;
        }
        
        .menu-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            flex-shrink: 0;
        }
        
        .month-selector {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: rgba(128, 128, 128, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.8rem;
            color: var(--text-primary);
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .month-year {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            margin-left: auto;
        }
        
        .task-count {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--accent-color);
            border-radius: 20px;
            padding: 4px 10px;
            flex-shrink: 0;
        }
        
        .task-number {
            font-size: 14px;
            font-weight: 600;
            color: var(--accent-text);
            text-align: center;
        }
        
        .search-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            flex-shrink: 0;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
    <script>
        (function() {
            const savedTheme = localStorage.getItem('driver_theme');
            if (savedTheme === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</body>
</html>