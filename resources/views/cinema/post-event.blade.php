<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<title>Danke · Lucas Entertainment</title>
@vite(['resources/css/app.css'])
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { background:#0D0D0D; color:#f5f5f5; font-family:system-ui,sans-serif; }
.kiosk-mode { cursor:none; user-select:none; }
</style>
</head>
<body class="kiosk-mode">
<div style="width:100vw; height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:3rem;">

    <div style="width:48px; height:48px; background:#C9A84C; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:18px; color:#000; margin-bottom:2rem;">LE</div>

    <div style="font-size:.75rem; color:#C9A84C; letter-spacing:.2em; text-transform:uppercase; margin-bottom:1rem;">Danke für euren Besuch</div>

    <div style="font-size:3.5rem; font-weight:900; line-height:1.1; margin-bottom:.75rem; max-width:800px;">
        {{ $screening->movie?->title ?? 'Vorstellung' }}
    </div>

    <div style="color:#555; font-size:1rem; margin-bottom:3rem;">
        {{ $screening->starts_at->isoFormat('dddd, D. MMMM YYYY') }}
    </div>

    @if($attendances->count())
    <div style="max-width:600px;">
        <div style="font-size:.7rem; color:#444; letter-spacing:.15em; text-transform:uppercase; margin-bottom:1.25rem;">Dabei waren</div>
        <div style="display:flex; flex-wrap:wrap; gap:.625rem; justify-content:center;">
            @foreach($attendances as $a)
            <div style="background:#141414; border:1px solid #2a2a2a; border-radius:99px; padding:.375rem 1rem; font-size:.9rem; color:#888;">
                {{ $a->guest_name }}
                @if($a->seat) <span style="color:#555; font-size:.8rem;">· {{ $a->seat->label }}</span> @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div style="margin-top:3rem; font-size:2.5rem;">🍿 🎬 ⭐</div>
</div>
</body>
</html>
