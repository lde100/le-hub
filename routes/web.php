<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Infoscreen\Screen;

// ── Public: Infoscreen ───────────────────────────────────────────────────────
Route::get('/screen', Screen::class)->defaults('channel', 'main');
Route::get('/screen/{channel}', Screen::class);
Route::get('/screen/{channel}/{screeningId}', Screen::class);

// ── Public: Event-Seite (WhatsApp-Link) ──────────────────────────────────────
Route::get('/event/{token}', \App\Livewire\Event\PublicEventPage::class)->name('event.public');

// ── Public: Ticket-Seiten ────────────────────────────────────────────────────
Route::get('/ticket/{code}', function (string $code) {
    $ticket = \App\Models\Ticket::with(['seat','screening.movie','screening.venue','booking'])
        ->where('ticket_code', $code)->firstOrFail();
    $seatMapSvg = app(\App\Services\TicketPdfService::class)
        ->buildSeatMapSvgPublic($ticket->screening->venue, $ticket->seat_id);
    return view('tickets.show', compact('ticket', 'seatMapSvg'));
})->name('ticket.show');

Route::get('/ticket/{code}/pdf', function (string $code) {
    $ticket = \App\Models\Ticket::where('ticket_code', $code)->firstOrFail();
    return app(\App\Services\TicketPdfService::class)->generate($ticket)
        ->download('ticket-'.$code.'.pdf');
})->name('ticket.pdf');

Route::get('/ticket/{code}/label', function (string $code) {
    $ticket = \App\Models\Ticket::with(['seat','screening.movie','booking'])
        ->where('ticket_code', $code)->firstOrFail();
    return view('tickets.label', compact('ticket'));
})->name('ticket.label');

Route::get('/ticket/{code}/wallet', function (string $code) {
    return redirect()->route('ticket.show', $code);
})->name('ticket.wallet');

// ── Public: Cinema-Screens ───────────────────────────────────────────────────
Route::get('/cinema/entrance/{screening}', \App\Livewire\Cinema\EntranceScreen::class)
    ->name('cinema.entrance');
Route::get('/cinema/post/{screening}', function (\App\Models\Screening $screening) {
    $attendances = \App\Models\Attendance::with('seat')
        ->where('screening_id', $screening->id)->orderBy('checked_in_at')->get();
    return view('cinema.post-event', compact('screening', 'attendances'));
})->name('cinema.post-event');

// ── Public: Ticker API ───────────────────────────────────────────────────────
Route::get('/api/ticker/{screeningId}', function (int $screeningId) {
    return response()->json(
        app(\App\Services\CheckinBroadcastService::class)->getTicker($screeningId)
    );
})->name('api.ticker');


// ── Overlays (vMix Luma-Key — Schwarz = transparent) ─────────────────────────
Route::get('/overlay/countdown/{screening}', function (\App\Models\Screening $screening, \Illuminate\Http\Request $req) {
    return view('overlays.countdown', [
        'screening' => $screening,
        'duration'  => (int) $req->get('duration', 5),
    ]);
})->name('overlay.countdown');

Route::get('/overlay/curtain/{screening}', function (\App\Models\Screening $screening, \Illuminate\Http\Request $req) {
    return view('overlays.curtain', [
        'screening' => $screening,
        'delay'     => (int) $req->get('delay', 500),
        'duration'  => (int) $req->get('duration', 2000),
    ]);
})->name('overlay.curtain');

Route::get('/overlay/reactions/{screening}', function (\App\Models\Screening $screening) {
    return view('overlays.reactions', ['screeningId' => $screening->id]);
})->name('overlay.reactions');

// ── Reactions API ──────────────────────────────────────────────────────────────
Route::post('/api/reaction/{screeningId}', function (int $screeningId, \Illuminate\Http\Request $req) {
    $emoji = $req->input('emoji', '👏');
    $xPct  = (float) $req->input('x_pct', 50);
    // Nur erlaubte Emojis
    $allowed = ['👏','❤️','😂','😱','🔥','⭐','🍿','😢','🎬','💫'];
    if (!in_array($emoji, $allowed)) return response()->json(['ok' => false], 422);
    app(\App\Services\CheckinBroadcastService::class)->addReaction($screeningId, $emoji, $xPct);
    return response()->json(['ok' => true]);
})->name('api.reaction');

Route::get('/api/reactions/{screeningId}', function (int $screeningId, \Illuminate\Http\Request $req) {
    $since = (int) $req->get('since', 0);
    return response()->json(
        app(\App\Services\CheckinBroadcastService::class)->getReactions($screeningId, $since)
    );
})->name('api.reactions');

Route::get('/api/screening-state/{screeningId}', function (int $screeningId) {
    $state = app(\App\Services\CheckinBroadcastService::class)->getState($screeningId);
    return response()->json(['state' => $state]);
})->name('api.screening-state');

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// ── Admin Backend ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));

    Route::get('/dashboard',  \App\Livewire\Admin\Dashboard::class)->name('dashboard');

    // Events
    Route::get('/events',       \App\Livewire\Admin\EventIndex::class)->name('admin.events');
    Route::get('/events/{event}', \App\Livewire\Admin\EventDetail::class)->name('admin.events.detail');
    Route::get('/events/{event}/hub', \App\Livewire\Admin\EventHub::class)->name('admin.events.hub');

    // Cinema
    Route::get('/cinema',        \App\Livewire\Admin\CinemaIndex::class)->name('cinema.index');
    Route::get('/cinema/scan',   \App\Livewire\Cinema\TicketScanner::class)->name('cinema.scan');
    Route::get('/cinema/checkin/{screening}', \App\Livewire\Cinema\CheckinScreen::class)->name('cinema.checkin');

    // Gastro
    Route::get('/gastro',        fn() => view('gastro.index'))->name('gastro.index');
    Route::get('/gastro/orders', fn() => view('gastro.orders'))->name('gastro.orders');
    Route::get('/gastro/menu',   fn() => view('gastro.menu'))->name('gastro.menu');

    // Kunden
    Route::get('/customers',     fn() => view('customers.index'))->name('customers.index');

    // Infoscreen Admin
    Route::get('/infoscreen',    fn() => view('infoscreen.admin'))->name('infoscreen.admin');
});
