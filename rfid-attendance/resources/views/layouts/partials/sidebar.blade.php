@php
    $user = Auth::user();
    $dashboardRoute = 'dashboard';
    $dashboardActive = request()->routeIs('dashboard');

    if ($user->hasRole('admin')) {
        $dashboardRoute = 'admin.dashboard';
        $dashboardActive = request()->routeIs('admin.dashboard');
    } elseif ($user->hasRole('teacher')) {
        $dashboardRoute = 'teacher.classes.index';
        $dashboardActive = request()->routeIs('teacher.classes.*');
    } elseif ($user->hasRole('secretary')) {
        $dashboardRoute = 'secretary.classroom.show';
        $dashboardActive = request()->routeIs('secretary.classroom.show');
    } elseif ($user->hasRole('student')) {
        $dashboardRoute = 'me.attendance.index';
        $dashboardActive = request()->routeIs('me.attendance.index');
    }
@endphp

<div class="px-6 py-6">
    <a href="{{ route($dashboardRoute) }}" class="text-xl font-bold tracking-wide text-white">
        {{ __('ui.app.name') }}
    </a>
    <div class="text-xs text-slate-400 mt-1">{{ __('ui.app.school') }} &bull; {{ __('ui.app.class') }}</div>
</div>

<nav class="flex-1 px-4 space-y-1">
    <a href="{{ route($dashboardRoute) }}" class="sidebar-link {{ $dashboardActive ? 'sidebar-link-active' : '' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5l9-7 9 7V19a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-8.5z"/>
        </svg>
        <span>{{ __('ui.nav.dashboard') }}</span>
    </a>

    @if ($user->hasRole('admin'))
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V7l8-4z"/>
            </svg>
            <span>{{ __('ui.nav.admin_panel') }}</span>
        </a>
        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-1a4 4 0 00-4-4h-1M9 20H4v-1a4 4 0 014-4h1m7-5a4 4 0 10-8 0 4 4 0 008 0z"/>
            </svg>
            <span>{{ __('ui.nav.users') }}</span>
        </a>
        <a href="{{ route('admin.classrooms.index') }}" class="sidebar-link {{ request()->routeIs('admin.classrooms.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m9-5v6a9 9 0 01-18 0v-6"/>
            </svg>
            <span>{{ __('ui.nav.classrooms') }}</span>
        </a>
        <a href="{{ route('admin.rfid-cards.index') }}" class="sidebar-link {{ request()->routeIs('admin.rfid-cards.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 8h20M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/>
            </svg>
            <span>{{ __('ui.nav.rfid_cards') }}</span>
        </a>
        <a href="{{ route('admin.devices.index') }}" class="sidebar-link {{ request()->routeIs('admin.devices.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M3 9h2m-2 6h2m12-6h2m-2 6h2M7 7h10v10H7z"/>
            </svg>
            <span>{{ __('ui.nav.devices') }}</span>
        </a>
        <a href="{{ route('admin.settings.attendance.edit') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
            </svg>
            <span>{{ __('ui.nav.attendance_settings') }}</span>
        </a>
    @endif

    @if ($user->hasRole('teacher'))
        <a href="{{ route('teacher.classes.index') }}" class="sidebar-link {{ request()->routeIs('teacher.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm4 2h8m-8 4h8m-8 4h5"/>
            </svg>
            <span>{{ __('ui.nav.my_classes') }}</span>
        </a>
    @endif

    @if ($user->hasRole('secretary'))
        <a href="{{ route('secretary.classroom.show') }}" class="sidebar-link {{ request()->routeIs('secretary.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 2h6a2 2 0 012 2v2h2a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h2V4a2 2 0 012-2z"/>
            </svg>
            <span>{{ __('ui.nav.secretary_dashboard') }}</span>
        </a>
        <a href="{{ route('secretary.absence-requests.index') }}" class="sidebar-link {{ request()->routeIs('secretary.absence-requests.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/>
            </svg>
            <span>{{ __('ui.nav.absence_requests') }}</span>
        </a>
    @endif

    @if ($user->hasRole('student'))
        <a href="{{ route('me.attendance.index') }}" class="sidebar-link {{ request()->routeIs('me.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span>{{ __('ui.nav.my_attendance') }}</span>
        </a>
        <a href="{{ route('me.profile.edit') }}" class="sidebar-link {{ request()->routeIs('me.profile.*') ? 'sidebar-link-active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 00-8 0m4-9a4 4 0 110 8 4 4 0 010-8z"/>
            </svg>
            <span>{{ __('ui.nav.my_profile') }}</span>
        </a>
    @endif
</nav>

<div class="px-4 py-6 text-xs text-slate-500">
    {{ __('ui.app.version') }} &bull; {{ __('ui.app.name') }}
</div>
