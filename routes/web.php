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
