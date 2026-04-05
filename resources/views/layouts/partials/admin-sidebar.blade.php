<aside
    id="admin-sidebar"
    class="admin-sidebar admin-sidebar-panel relative overflow-hidden text-white p-4 lg:p-5 flex flex-col"
    style="background-color: {{ $globalSetting->theme_primary ?? '#c05b7b' }};"
>
    <div class="admin-sidebar-glow absolute inset-0"></div>

    <div class="relative z-10 admin-user-chip rounded-2xl p-3">
        <a href="{{ route('admin.dashboard') }}" class="block min-w-0">
            <div class="min-w-0">
                <p class="text-xs text-white/75">Selamat Datang</p>
                <p class="font-semibold truncate">{{ auth()->user()?->name }}</p>
            </div>
        </a>
    </div>

    <div class="relative z-10 flex-1 min-h-0 overflow-y-auto mt-4 pr-1 space-y-4">
        <div class="admin-menu-section space-y-2 text-sm">
            <p class="admin-menu-label">Menu Utama</p>
            @if(auth()->user()?->hasPermission('dashboard.view'))<a class="admin-link {{ request()->routeIs('admin.dashboard') ? 'admin-link-active' : '' }}" href="{{ route('admin.dashboard') }}"><span>Beranda Admin</span></a>@endif
            @if(auth()->user()?->hasPermission('portfolio.view'))<a class="admin-link {{ request()->routeIs('admin.portfolios.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.portfolios.index') }}"><span>Portfolio</span></a>@endif
            @if(auth()->user()?->hasPermission('service.view'))<a class="admin-link {{ request()->routeIs('admin.services.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.services.index') }}"><span>Daftar Harga</span></a>@endif
            @if(auth()->user()?->hasPermission('booking.view'))<a class="admin-link {{ request()->routeIs('admin.bookings.index') ? 'admin-link-active' : '' }}" href="{{ route('admin.bookings.index') }}"><span>Kelola Reservasi</span></a>@endif
            @if(auth()->user()?->hasPermission('report.view'))<a class="admin-link {{ request()->routeIs('admin.reports.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.reports.index') }}"><span>Laporan</span></a>@endif
            @if(auth()->user()?->hasPermission('backup.view'))<a class="admin-link {{ request()->routeIs('admin.backup.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.backup.index') }}"><span>Backup Database</span></a>@endif
        </div>

        <div class="admin-menu-section space-y-2 text-sm">
            <p class="admin-menu-label">Manajemen</p>
            @if(auth()->user()?->hasPermission('recycle.view'))<a class="admin-link {{ request()->routeIs('admin.recycle-bin.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.recycle-bin.index') }}"><span>Recycle Bin</span></a>@endif
            @if(auth()->user()?->hasPermission('access.view'))<a class="admin-link {{ request()->routeIs('admin.access.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.access.index') }}"><span>Hak Akses</span></a>@endif
            @if(auth()->user()?->hasPermission('user.view'))<a class="admin-link {{ request()->routeIs('admin.users.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.users.index') }}"><span>User Data</span></a>@endif
            @if(auth()->user()?->hasPermission('activity.view'))<a class="admin-link {{ request()->routeIs('admin.activity-logs.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.activity-logs.index') }}"><span>Log Aktivitas</span></a>@endif
            @if(auth()->user()?->hasPermission('setting.view'))<a class="admin-link {{ request()->routeIs('admin.operational-hours.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.operational-hours.index') }}"><span>Jam Operasional</span></a>@endif
            @if(auth()->user()?->hasPermission('setting.view'))<a class="admin-link {{ request()->routeIs('admin.settings.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.settings.index') }}"><span>Pengaturan Website</span></a>@endif
        </div>
    </div>

    <div class="relative z-10 mt-4 pt-4 admin-sidebar-divider space-y-2">
        <a class="admin-link {{ request()->routeIs('admin.profile.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.profile.index') }}"><span>Profile</span></a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="btn-admin-logout">Keluar</button>
        </form>
    </div>
</aside>
