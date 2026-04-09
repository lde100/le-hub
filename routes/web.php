<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Infoscreen\Screen;

// ── Infoscreen (öffentlich, kein Auth) ────────────────────────────────────────
// /screen        → Haupt-TV-Screen (alle Slides)
// /screen/menu   → iPad Menükarte
// /screen/custom → Benutzerdefinierter Channel
Route::get('/screen', Screen::class)->defaults('channel', 'main');
Route::get('/screen/{channel}', Screen::class);

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// ── Admin Backend (auth required) ────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Cinema
    Route::get('/cinema', \App\Livewire\Admin\CinemaIndex::class)->name('cinema.index');
    Route::get('/cinema/screenings', fn() => view('cinema.screenings'))->name('cinema.screenings');
    Route::get('/cinema/scan', \App\Livewire\Cinema\TicketScanner::class)->name('cinema.scan');

    // Gastro / Bestellungen
    Route::get('/gastro', fn() => view('gastro.index'))->name('gastro.index');
    Route::get('/gastro/orders', fn() => view('gastro.orders'))->name('gastro.orders');
    Route::get('/gastro/menu', fn() => view('gastro.menu'))->name('gastro.menu');

    // Kunden
    Route::get('/customers', fn() => view('customers.index'))->name('customers.index');

    // Infoscreen Admin
    Route::get('/infoscreen', fn() => view('infoscreen.admin'))->name('infoscreen.admin');
});

// ── Öffentliche Event-Seite (WhatsApp-Link) ───────────────────────────────────
Route::get('/event/{token}', \App\Livewire\Event\PublicEventPage::class)->name('event.public');

// ── Ticket Download ────────────────────────────────────────────────────────────
Route::get('/ticket/{code}/pdf', function (string $code) {
    $ticket = \App\Models\Ticket::where('ticket_code', $code)->firstOrFail();
    $pdf = app(\App\Services\TicketPdfService::class)->generate($ticket);
    return $pdf->download('ticket-' . $code . '.pdf');
})->name('ticket.pdf');

Route::get('/ticket/{code}/wallet', function (string $code) {
    // Apple Wallet entfernt (kein Developer Account)
    // PNG-Ticket: /ticket/{code}
    return redirect()->route('ticket.show', $code);
})->name('ticket.wallet');

// ── Check-in Screen (Admin) ────────────────────────────────────────────────────
Route::get('/cinema/checkin/{screening}', \App\Livewire\Cinema\CheckinScreen::class)
    ->middleware('auth')
    ->name('cinema.checkin');

// ── Einlass-Screen (Screen 2 — öffentlich, läuft auf zweitem Monitor) ─────────
Route::get('/cinema/entrance/{screening}', \App\Livewire\Cinema\EntranceScreen::class)
    ->name('cinema.entrance');

// Infoscreen mit Screening-Bindung (für Check-in Overlay)
Route::get('/screen/{channel}/{screening}', \App\Livewire\Infoscreen\Screen::class)
    ->name('screen.screening');

Route::get('/ticket/{code}/label', function (string $code) {
    $ticket = \App\Models\Ticket::with(['seat','screening.movie','booking'])
        ->where('ticket_code', $code)->firstOrFail();
    return view('tickets.label', compact('ticket'));
})->name('ticket.label');

Route::get('/api/ticker/{screeningId}', function (int $screeningId) {
    $data = app(\App\Services\CheckinBroadcastService::class)->getTicker($screeningId);
    return response()->json($data);
})->name('api.ticker');

Route::get('/cinema/post/{screening}', function (\App\Models\Screening $screening) {
    $attendances = \App\Models\Attendance::with('seat')
        ->where('screening_id', $screening->id)
        ->orderBy('checked_in_at')
        ->get();
    return view('cinema.post-event', compact('screening', 'attendances'));
})->name('cinema.post-event');
