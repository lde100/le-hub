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
    Route::get('/cinema', fn() => view('cinema.index'))->name('cinema.index');
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
