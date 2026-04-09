<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login · Lucas Entertainment</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<style>
:root { --gold:#C9A84C; --dark:#0D0D0D; }
* { box-sizing:border-box; margin:0; padding:0; }
body { background:var(--dark); color:#f5f5f5; font-family:system-ui,sans-serif; min-height:100dvh; display:flex; align-items:center; justify-content:center; padding:1rem; }

.login-card {
    width:100%; max-width:400px;
    background:#141414;
    border:1px solid #C9A84C33;
    border-radius:20px;
    padding:2.5rem 2rem;
}
.le-logo { display:flex; align-items:center; gap:.75rem; justify-content:center; margin-bottom:2.5rem; }
.le-badge { width:44px; height:44px; background:var(--gold); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:17px; color:#000; letter-spacing:-1px; }
.le-text { text-align:left; }
.le-text .title { font-size:1.1rem; font-weight:700; }
.le-text .sub { font-size:.7rem; color:#666; letter-spacing:.12em; text-transform:uppercase; }

.form-group { margin-bottom:1.25rem; }
label { display:block; font-size:.8rem; color:#888; margin-bottom:.4rem; letter-spacing:.05em; }
input { width:100%; background:#0D0D0D; border:1px solid #2a2a2a; border-radius:10px; padding:.875rem 1rem; color:#f5f5f5; font-size:1rem; outline:none; transition:border-color .2s; }
input:focus { border-color:var(--gold); }
.error { color:#EF4444; font-size:.8rem; margin-top:.375rem; }

.btn-login { width:100%; background:var(--gold); color:#000; font-weight:700; padding:.9rem; border-radius:10px; border:none; font-size:1rem; cursor:pointer; margin-top:.5rem; letter-spacing:.02em; }
.btn-login:hover { opacity:.9; }

.remember { display:flex; align-items:center; gap:.5rem; font-size:.85rem; color:#888; margin-bottom:1.25rem; }
.remember input { width:auto; }
</style>
</head>
<body>
<div class="login-card">
    <div class="le-logo">
        <div class="le-badge">LE</div>
        <div class="le-text">
            <div class="title">Lucas Entertainment</div>
            <div class="sub">Hub · Admin</div>
        </div>
    </div>

    @if ($errors->any())
    <div style="background:#EF444411; border:1px solid #EF444433; border-radius:8px; padding:.75rem 1rem; margin-bottom:1.25rem; color:#EF4444; font-size:.875rem;">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="form-group">
            <label>E-Mail</label>
            <input type="email" name="email" value="{{ old('email') }}" autofocus autocomplete="email">
        </div>
        <div class="form-group">
            <label>Passwort</label>
            <input type="password" name="password" autocomplete="current-password">
        </div>
        <div class="remember">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember" style="margin:0; cursor:pointer;">Angemeldet bleiben</label>
        </div>
        <button type="submit" class="btn-login">Anmelden →</button>
    </form>
</div>
</body>
</html>
