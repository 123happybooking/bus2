@extends('layouts.driver')

@section('content')
<div class="mobile-container">
    <div class="header">
        <button class="menu-btn" id="menuBtn">
            <span class="menu-icon">☰</span>
        </button>
        <div class="month-selector" id="monthSelector">
            <span class="month-year" id="monthYear"></span>
            <span class="dropdown-arrow">▼</span>
        </div>
        <div class="header-right">
            <div class="task-count">
                <span class="task-number" id="taskCount">0</span>
            </div>
            <button class="search-btn" id="searchBtn">
                <span class="search-icon">🔍</span>
            </button>
        </div>
    </div>

    <div class="calendar-wrapper" id="calendarWrapper">
        <div class="calendar-header">
            <span class="current-month" id="currentMonth"></span>
        </div>
        <div class="calendar-weekdays">
            <span>月</span><span>火</span><span>水</span><span>木</span><span>金</span><span>土</span><span>日</span>
        </div>
        <div class="calendar-days" id="calendarDays"></div>
    </div>

    <div class="itinerary-section">
        <div class="section-title" id="sectionTitle"></div>
        <div class="itinerary-list" id="itineraryList">
            <div class="loading">読み込み中...</div>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="/images/logo.svg">
    </div>
    <div class="sidebar-menu-container">
        <div class="sidebar-user">
            {{ session('staff_name', 'ゲスト') }}
        </div>
        <ul class="sidebar-menu">
            <li>
                <span>📬</span>すべての受信トレイ
            </li>
            <li>
                <span>📬</span>すべての受信トレイ
            </li>
            <li>
                <span>📬</span>すべての受信トレイ
            </li>
            <li>
                <span>📬</span>すべての受信トレイ
            </li>
            <li>
                <span>📬</span>すべての受信トレイ
            </li>
            <li id="settingsBtn">
                <span>⚙️</span>設定
            </li>
            <li class="divider"></li>
            <li id="logoutBtn">
                <span>🚪</span>ログアウト
            </li>
        </ul>
    </div>
</div>

<div class="date-picker-modal" id="datePickerModal">
    <div class="date-picker-header">
        <h4>年月を選択</h4>
        <button class="close-picker" id="closePicker">×</button>
    </div>
    <div class="year-month-picker">
        <button id="decadePrev">◀◀</button>
        <button id="yearPrev">◀</button>
        <span class="year-month-display" id="yearMonthDisplay"></span>
        <button id="yearNext">▶</button>
        <button id="decadeNext">▶▶</button>
    </div>
    <div class="month-grid" id="monthGrid"></div>
    <div class="year-nav">
        <button id="yearPrevNav">前年</button>
        <button id="yearNextNav">翌年</button>
    </div>
</div>

<style>
.calendar-wrapper {
    background-color: var(--card-bg);
    margin: 12px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.calendar-header {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 14px 16px;
    background-color: var(--card-bg);
}

.current-month {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    padding: 10px 0;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    background-color: var(--card-bg);
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    padding: 8px 0;
    background-color: var(--card-bg);
}

.calendar-day {
    padding: 8px 4px;
    position: relative;
    cursor: pointer;
    border-radius: 12px;
    width: 44px;
    margin: 2px auto;
    background-color: var(--calendar-day-bg);
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    height: 56px;
}

.calendar-day-number {
    font-size: 15px;
    font-weight: 500;
    color: var(--text-primary);
    line-height: 1.3;
    margin-top: 4px;
}

.calendar-day-count {
    font-size: 11px;
    font-weight: 600;
    color: var(--count-text-color);
    background-color: var(--bg-color);
    border-radius: 16px;
    padding: 2px 6px;
    min-width: 24px;
    display: inline-block;
    margin-top: 4px;
    line-height: 1.2;
}

.calendar-day.selected {
    background-color: var(--accent-color);
}

.calendar-day.selected .calendar-day-number {
    color: var(--accent-text);
}

.calendar-day.selected .calendar-day-count {
    color: var(--count-text-color);
    background-color: var(--card-bg);
}

.calendar-day.other-month {
    background-color: var(--calendar-day-other);
}

.calendar-day.other-month .calendar-day-number {
    color: var(--text-muted);
}

.itinerary-section {
    background-color: transparent;
    margin: 0 12px;
    overflow: hidden;
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
    color: var(--accent-color);
}

.arrow-container {
    width: 100%;
    display: flex;
    align-items: center;
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
    font-size: 12px;
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

.loading, .empty {
    text-align: center;
    padding: 40px;
    color: var(--text-secondary);
    font-size: 13px;
}

.sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100%;
    background-color: var(--sidebar-bg);
    z-index: 101;
    transition: left 0.3s ease;
    display: flex;
    flex-direction: column;
}

.sidebar.open {
    left: 0;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.3);
}

.sidebar-header {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px 16px;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--sidebar-header-bg);
}

.sidebar-header img {
    max-width: 120px;
    height: auto;
}

.close-sidebar {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-primary);
    position: absolute;
    top: 16px;
    right: 16px;
}

.sidebar-menu-container {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-user {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px;
    font-weight: 500;
    color: var(--sidebar-user-text);
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    padding-bottom: 20px;
}

.sidebar-menu li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    font-size: 14px;
    color: var(--text-primary);
    transition: background-color 0.2s;
}

.sidebar-menu li:active {
    background-color: var(--bg-color);
}

.sidebar-menu li span:first-child {
    font-size: 18px;
    width: 28px;
}

.sidebar-menu .divider {
    height: 1px;
    background-color: var(--border-color);
    margin: 8px 0;
    padding: 0;
}

.sidebar-menu-container::-webkit-scrollbar {
    width: 4px;
}

.sidebar-menu-container::-webkit-scrollbar-track {
    background: rgba(128, 128, 128, 0.2);
    border-radius: 2px;
}

.sidebar-menu-container::-webkit-scrollbar-thumb {
    background: rgba(128, 128, 128, 0.4);
    border-radius: 2px;
}

.date-picker-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--card-bg);
    z-index: 200;
    display: none;
    flex-direction: column;
}

.date-picker-modal.show {
    display: flex;
}

.date-picker-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--sidebar-header-bg);
    color: var(--text-white);
}

.date-picker-header h4 {
    font-size: 16px;
}

.close-picker {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-white);
}

.year-month-picker {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background-color: var(--card-bg);
}

.year-month-picker button {
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
    padding: 8px;
    color: var(--text-primary);
}

.year-month-display {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
}

.month-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    padding: 20px;
    background-color: var(--card-bg);
}

.month-item {
    text-align: center;
    padding: 12px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    color: var(--text-primary);
}

.month-item.selected {
    background-color: var(--accent-color);
    color: var(--accent-text);
}

.year-nav {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 16px;
    background-color: var(--card-bg);
    border-top: 1px solid var(--border-color);
}

.year-nav button {
    background: none;
    border: none;
    font-size: 14px;
    cursor: pointer;
    padding: 8px 20px;
    background-color: rgba(128, 128, 128, 0.2);
    border-radius: 20px;
    color: var(--text-primary);
}
</style>

<script>
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth() + 1;
let selectedYear = currentYear;
let selectedMonth = currentMonth;
let selectedDay = null;
let events = [];

function formatDate(year, month, day) {
    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

function getDaysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

function getFirstDayWeekday(year, month) {
    const date = new Date(year, month - 1, 1);
    const day = date.getDay();
    return day === 0 ? 6 : day - 1;
}

function updateCalendar() {
    const firstDayWeekday = getFirstDayWeekday(currentYear, currentMonth);
    const daysInMonth = getDaysInMonth(currentYear, currentMonth);
    const prevMonthDays = getDaysInMonth(currentYear, currentMonth - 1);
    
    let calendarHtml = '';
    
    for (let i = 0; i < firstDayWeekday; i++) {
        const prevDay = prevMonthDays - firstDayWeekday + i + 1;
        calendarHtml += `
            <div class="calendar-day other-month" data-year="${currentYear}" data-month="${currentMonth - 1}" data-day="${prevDay}">
                <div class="calendar-day-number">${prevDay}</div>
            </div>
        `;
    }
    
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = formatDate(currentYear, currentMonth, day);
        const event = events.find(e => e.date === dateStr);
        const eventCount = event ? event.count : 0;
        const selectedClass = (currentYear === selectedYear && currentMonth === selectedMonth && day === selectedDay) ? 'selected' : '';
        
        if (eventCount > 0) {
            calendarHtml += `
                <div class="calendar-day ${selectedClass}" data-year="${currentYear}" data-month="${currentMonth}" data-day="${day}">
                    <div class="calendar-day-number">${day}</div>
                    <div class="calendar-day-count">${eventCount}</div>
                </div>
            `;
        } else {
            calendarHtml += `
                <div class="calendar-day ${selectedClass}" data-year="${currentYear}" data-month="${currentMonth}" data-day="${day}">
                    <div class="calendar-day-number">${day}</div>
                </div>
            `;
        }
    }
    
    const totalCells = 42;
    const currentCells = firstDayWeekday + daysInMonth;
    const remainingCells = totalCells - currentCells;
    
    for (let i = 1; i <= remainingCells; i++) {
        calendarHtml += `
            <div class="calendar-day other-month" data-year="${currentYear}" data-month="${currentMonth + 1}" data-day="${i}">
                <div class="calendar-day-number">${i}</div>
            </div>
        `;
    }
    
    const calendarDays = document.getElementById('calendarDays');
    if (calendarDays) {
        calendarDays.innerHTML = calendarHtml;
    }
    
    const currentMonthEl = document.getElementById('currentMonth');
    if (currentMonthEl) {
        currentMonthEl.innerText = `${currentYear}年${currentMonth}月`;
    }
    
    const monthYearEl = document.getElementById('monthYear');
    if (monthYearEl) {
        monthYearEl.innerText = `${currentYear}年${currentMonth}月`;
    }
    
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.addEventListener('click', function() {
            document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
            this.classList.add('selected');
            
            const year = parseInt(this.getAttribute('data-year'));
            const month = parseInt(this.getAttribute('data-month'));
            const dayNum = parseInt(this.getAttribute('data-day'));
            selectedYear = year;
            selectedMonth = month;
            selectedDay = dayNum;
            
            loadItineraries(year, month, dayNum);
        });
    });
}

function loadCalendarData() {
    fetch(`/driver/calendar-data?year=${currentYear}&month=${currentMonth}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                events = data.events || [];
                
                const today = new Date();
                const todayStr = formatDate(today.getFullYear(), today.getMonth() + 1, today.getDate());
                const todayEvent = events.find(e => e.date === todayStr);
                const todayCount = todayEvent ? todayEvent.count : 0;
                
                const taskCountEl = document.getElementById('taskCount');
                if (taskCountEl) taskCountEl.innerText = todayCount;
                
                updateCalendar();
                
                if (currentYear === today.getFullYear() && currentMonth === today.getMonth() + 1) {
                    selectedDay = today.getDate();
                    loadItineraries(currentYear, currentMonth, today.getDate());
                    setTimeout(() => {
                        document.querySelectorAll('.calendar-day').forEach(day => {
                            const dayNum = parseInt(day.getAttribute('data-day'));
                            if (dayNum === today.getDate() && !day.classList.contains('other-month')) {
                                day.classList.add('selected');
                            }
                        });
                    }, 100);
                } else if (events.length > 0) {
                    const firstEventDate = events[0].date;
                    const parts = firstEventDate.split('-');
                    if (parts.length === 3) {
                        const year = parseInt(parts[0]);
                        const month = parseInt(parts[1]);
                        const day = parseInt(parts[2]);
                        selectedDay = day;
                        loadItineraries(year, month, day);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading calendar data:', error);
            const itineraryList = document.getElementById('itineraryList');
            if (itineraryList) itineraryList.innerHTML = '<div class="empty">データの読み込みに失敗しました</div>';
        });
}

function loadItineraries(year, month, day) {
    const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    const dateObj = new Date(year, month - 1, day);
    const weekday = weekdays[dateObj.getDay()];
    
    const sectionTitle = document.getElementById('sectionTitle');
    const itineraryList = document.getElementById('itineraryList');
    
    if (sectionTitle) {
        sectionTitle.innerHTML = `📅 ${year}年${month}月${day}日 (${weekday}) の予定`;
    }
    if (itineraryList) {
        itineraryList.innerHTML = '<div class="loading">読み込み中...</div>';
    }
    
    fetch(`/driver/itineraries/${dateStr}`)
        .then(response => response.json())
        .then(data => {
            if (itineraryList) {
                if (data.success && data.itineraries && data.itineraries.length > 0) {
                    let listHtml = '';
                    data.itineraries.forEach(item => {
                        listHtml += `
                            <div class="itinerary-card" data-id="${item.id}">
                                <div class="itinerary-row">
                                    <div class="itinerary-left">
                                        <div class="start-time">${escapeHtml(item.time_start)}</div>
                                        <div class="start-location">${escapeHtml(item.start_location || '未設定')}</div>
                                    </div>
                    
                                    <div class="itinerary-center">
                                        <div class="itinerary-vehicle">${escapeHtml(item.vehicle || '')}</div>
                                        <div class="arrow-container">
                                            <div class="arrow-line"></div>
                                            <div class="arrow-triangle"></div>
                                        </div>
                                        <div class="itinerary-date">${escapeHtml(item.date)}</div>
                                    </div>
                    
                                    <div class="itinerary-right">
                                        <div class="end-time">${escapeHtml(item.time_end)}</div>
                                        <div class="end-location">${escapeHtml(item.end_location || '未設定')}</div>
                                    </div>
                                </div>
                    
                                <div class="itinerary-footer">
                                    <span class="detail-link">行程详情 &gt;</span>
                                </div>
                            </div>
                        `;
                    });
                    itineraryList.innerHTML = listHtml;
                    
                    document.querySelectorAll('.itinerary-card').forEach(card => {
                        card.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            if (id) {
                                window.location.href = `/driver/itinerary/${id}`;
                            }
                        });
                    });
                } else {
                    itineraryList.innerHTML = '<div class="empty">予定はありません</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading itineraries:', error);
            if (itineraryList) {
                itineraryList.innerHTML = '<div class="empty">データの読み込みに失敗しました</div>';
            }
        });
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function openDatePicker() {
    const modal = document.getElementById('datePickerModal');
    if (modal) modal.classList.add('show');
    updateMonthGrid();
}

function closeDatePicker() {
    const modal = document.getElementById('datePickerModal');
    if (modal) modal.classList.remove('show');
}

function updateMonthGrid() {
    const months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
    let html = '';
    months.forEach((month, index) => {
        const isSelected = (selectedYear === currentYear && selectedMonth === index + 1);
        html += `<div class="month-item ${isSelected ? 'selected' : ''}" data-month="${index + 1}">${month}</div>`;
    });
    const monthGrid = document.getElementById('monthGrid');
    const yearMonthDisplay = document.getElementById('yearMonthDisplay');
    if (monthGrid) monthGrid.innerHTML = html;
    if (yearMonthDisplay) yearMonthDisplay.innerText = `${selectedYear}年`;
    
    document.querySelectorAll('.month-item').forEach(item => {
        item.addEventListener('click', function() {
            const month = parseInt(this.getAttribute('data-month'));
            selectedMonth = month;
            currentYear = selectedYear;
            currentMonth = selectedMonth;
            selectedDay = null;
            loadCalendarData();
            closeDatePicker();
        });
    });
}

const menuBtn = document.getElementById('menuBtn');
if (menuBtn) {
    menuBtn.addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.style.display = 'block';
    });
}

const closeSidebar = document.getElementById('closeSidebar');
if (closeSidebar) {
    closeSidebar.addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.style.display = 'none';
    });
}

const sidebarOverlay = document.getElementById('sidebarOverlay');
if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) sidebar.classList.remove('open');
        this.style.display = 'none';
    });
}

const searchBtn = document.getElementById('searchBtn');
if (searchBtn) {
    searchBtn.addEventListener('click', function() {
        window.location.href = '/driver/search';
    });
}

const monthSelector = document.getElementById('monthSelector');
if (monthSelector) {
    monthSelector.addEventListener('click', openDatePicker);
}

const closePicker = document.getElementById('closePicker');
if (closePicker) {
    closePicker.addEventListener('click', closeDatePicker);
}

const yearPrev = document.getElementById('yearPrev');
if (yearPrev) {
    yearPrev.addEventListener('click', function() {
        selectedYear--;
        updateMonthGrid();
    });
}

const yearNext = document.getElementById('yearNext');
if (yearNext) {
    yearNext.addEventListener('click', function() {
        selectedYear++;
        updateMonthGrid();
    });
}

const decadePrev = document.getElementById('decadePrev');
if (decadePrev) {
    decadePrev.addEventListener('click', function() {
        selectedYear -= 10;
        updateMonthGrid();
    });
}

const decadeNext = document.getElementById('decadeNext');
if (decadeNext) {
    decadeNext.addEventListener('click', function() {
        selectedYear += 10;
        updateMonthGrid();
    });
}

const yearPrevNav = document.getElementById('yearPrevNav');
if (yearPrevNav) {
    yearPrevNav.addEventListener('click', function() {
        selectedYear--;
        updateMonthGrid();
    });
}

const yearNextNav = document.getElementById('yearNextNav');
if (yearNextNav) {
    yearNextNav.addEventListener('click', function() {
        selectedYear++;
        updateMonthGrid();
    });
}

const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
        if (confirm('ログアウトしますか？')) {
            const token = document.querySelector('meta[name="csrf-token"]');
            fetch('/driver/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token ? token.content : ''
                }
            }).then(() => {
                window.location.href = '/masters/login';
            }).catch(() => {
                window.location.href = '/masters/login';
            });
        }
    });
}


const settingsBtn = document.getElementById('settingsBtn');
if (settingsBtn) {
    settingsBtn.addEventListener('click', function() {
        window.location.href = '/driver/settings';
    });
}

loadCalendarData();
</script>
@endsection