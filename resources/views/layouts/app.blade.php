<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title ?? 'LE Hub' }} · Lucas Entertainment</title>
@vite(['resources/css/app.css','resources/js/app.js'])
@livewireStyles
<style>
:root { --gold:#C9A84C; --dark:#0D0D0D; --surface:#141414; --border:#1e1e1e; --text:#f5f5f5; --muted:#888; }
* { box-sizing:border-box; margin:0; padding:0; }
body { background:var(--dark); color:var(--text); font-family:system-ui,-apple-system,sans-serif; min-height:100dvh; display:flex; }

/* Sidebar */
.sidebar { width:220px; flex-shrink:0; background:var(--surface); border-right:1px solid var(--border); display:flex; flex-direction:column; min-height:100dvh; position:sticky; top:0; }
.sidebar-logo { padding:1.25rem 1.25rem 1rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:.625rem; }
.sidebar-badge { width:32px; height:32px; background:var(--gold); border-radius:7px; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:12px; color:#000; flex-shrink:0; }
.sidebar-name { font-size:.85rem; font-weight:700; line-height:1.2; }
.sidebar-sub { font-size:.65rem; color:#555; letter-spacing:.1em; text-transform:uppercase; }

.nav { padding:.75rem .625rem; flex:1; }
.nav-section { font-size:.6rem; color:#444; letter-spacing:.15em; text-transform:uppercase; padding:.5rem .625rem .25rem; margin-top:.5rem; }
.nav-item { display:flex; align-items:center; gap:.625rem; padding:.6rem .75rem; border-radius:8px; color:var(--muted); font-size:.875rem; text-decoration:none; transition:all .15s; margin-bottom:2px; }
.nav-item:hover { background:#ffffff08; color:var(--text); }
.nav-item.active { background:#C9A84C18; color:var(--gold); font-weight:600; }
.nav-icon { font-size:1rem; width:20px; text-align:center; flex-shrink:0; }

.sidebar-footer { padding:.75rem 1rem; border-top:1px solid var(--border); }
.user-info { font-size:.8rem; color:#555; margin-bottom:.5rem; }
.btn-logout { width:100%; padding:.5rem; background:transparent; border:1px solid #2a2a2a; border-radius:8px; color:#666; font-size:.8rem; cursor:pointer; }
.btn-logout:hover { border-color:#444; color:#888; }

/* Main Content */
.main { flex:1; min-width:0; display:flex; flex-direction:column; }
.topbar { padding:1rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:var(--surface); position:sticky; top:0; z-index:10; }
.page-title { font-size:1.1rem; font-weight:700; }
.content { padding:1.5rem; flex:1; }

/* Mobile: Sidebar ausblenden */
@media (max-width: 768px) {
    .sidebar { display:none; }
}
</style>
</head>
<body>

<nav class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-badge">LE</div>
        <div>
            <div class="sidebar-name">Lucas Entertainment</div>
            <div class="sidebar-sub">Hub</div>
        </div>
    </div>

    <div class="nav">
        <div class="nav-section">Übersicht</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon">⚡</span> Dashboard
        </a>

        <div class="nav-section">Kino</div>
        <a href="{{ route('cinema.index') }}" class="nav-item {{ request()->routeIs('cinema.*') ? 'active' : '' }}">
            <span class="nav-icon">🎬</span> Vorstellungen
        </a>
        <a href="{{ route('cinema.scan') }}" class="nav-item">
            <span class="nav-icon">📟</span> Scanner
        </a>

        <div class="nav-section">Bestellung</div>
        <a href="{{ route('gastro.index') }}" class="nav-item {{ request()->routeIs('gastro.*') ? 'active' : '' }}">
            <span class="nav-icon">🍿</span> Gastro / POS
        </a>
        <a href="{{ route('gastro.menu') }}" class="nav-item">
            <span class="nav-icon">📋</span> Menü verwalten
        </a>

        <div class="nav-section">Gäste</div>
        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <span class="nav-icon">👥</span> Kunden / Gäste
        </a>

        <div class="nav-section">Screens</div>
        <a href="{{ route('infoscreen.admin') }}" class="nav-item {{ request()->routeIs('infoscreen.*') ? 'active' : '' }}">
            <span class="nav-icon">📺</span> Infoscreen
        </a>
        <a href="/screen?autoplay=1" target="_blank" class="nav-item">
            <span class="nav-icon">↗</span> Screen öffnen
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-info">{{ auth()->user()?->name }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Abmelden</button>
        </form>
    </div>
</nav>

<div class="main">
    <div class="topbar">
        <div class="page-title">{{ $title ?? 'Dashboard' }}</div>
        <div style="display:flex; gap:.75rem; align-items:center;">
            @yield('topbar-actions')
            {{ $topbarActions ?? '' }}
        </div>
    </div>
    <div class="content">
        {{ $slot }}
    </div>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>
