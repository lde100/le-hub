<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── GUESTS ────────────────────────────────────────────────────────
        // Lightweight Gast-Profile — kein Passwort, Identität via Magic Token
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('magic_token', 64)->unique()->nullable();   // URL-Token für Magic Link
            $table->string('guest_number')->unique()->nullable();       // LE-G-0001
            $table->integer('loyalty_points')->default(0);
            $table->integer('visit_count')->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->json('meta')->nullable();                           // Avatar-Farbe, Notizen etc.
            $table->timestamps();
        });

        // ── LOYALTY TRANSACTIONS ──────────────────────────────────────────
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->integer('points');                                  // positiv = Earn, negativ = Redeem
            $table->string('type');
            // 'visit' | 'bonus' | 'redeem' | 'manual' | 'referral'
            $table->string('description')->nullable();
            $table->morphs('source');                                   // z.B. source_type=Booking, source_id=5
            $table->timestamps();
        });

        // ── EVENTS ───────────────────────────────────────────────────────
        // Übergeordnete Klammer — ein Event kann mehrere Screening-Slots haben
        // (erst Termin finden, dann Film bestimmen, dann buchen)
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');                                    // "Kinoabend April"
            $table->text('description')->nullable();
            $table->string('type')->default('cinema');
            // 'cinema' | 'live' | 'party' | 'custom'
            $table->string('status')->default('draft');
            // draft → polling_date → polling_film → booking_open → confirmed → finished → cancelled
            $table->string('public_token', 32)->unique();              // URL-Token für öffentlichen Zugang
            $table->foreignId('venue_id')->nullable()->constrained('venues')->nullOnDelete();
            $table->string('seating_mode')->default('seated');
            // 'seated' = Saalplan, 'open' = nur Kapazität, 'mixed' = beides
            $table->integer('max_capacity')->nullable();               // für open/mixed
            $table->boolean('allow_seat_requests')->default(true);
            $table->boolean('allow_walk_in')->default(true);           // Kasse am Abend
            $table->boolean('walk_in_needs_seat')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // ── EVENT_SCREENINGS (Termin-Kandidaten + bestätigte Vorstellungen) ──
        // Vor Bestätigung: mögliche Termine (poll_options referenzieren diese)
        // Nach Bestätigung: der gewählte Termin wird zur echten Screening
        Schema::create('event_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('screening_id')->nullable()->constrained()->nullOnDelete();
            // null solange nur Termin-Kandidat, gefüllt nach Bestätigung
            $table->dateTime('proposed_at');                           // vorgeschlagener Termin
            $table->boolean('is_confirmed')->default(false);
            $table->timestamps();
        });

        // ── EVENT POLLS ───────────────────────────────────────────────────
        Schema::create('event_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            // 'date_selection' | 'film_vote' | 'film_wish'
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            // 'open' | 'closed' | 'confirmed'
            $table->string('vote_mode')->default('single');
            // 'single' (ein Termin wählen) | 'multi' (mehrere Filme liken) | 'wish' (Freitext)
            $table->boolean('allow_new_options')->default(false);      // Gäste können eigene Optionen vorschlagen
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
        });

        // ── POLL OPTIONS ──────────────────────────────────────────────────
        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('event_polls')->cascadeOnDelete();
            $table->string('type');
            // 'date' | 'movie_emby' | 'movie_tmdb' | 'movie_custom' | 'live_event'
            $table->string('label');                                    // Anzeigename
            $table->dateTime('date_value')->nullable();                 // für type=date
            $table->string('movie_title')->nullable();
            $table->string('movie_year')->nullable();
            $table->string('movie_poster_path')->nullable();
            $table->text('movie_synopsis')->nullable();
            $table->string('movie_genre')->nullable();
            $table->integer('movie_duration')->nullable();
            $table->string('movie_rating')->nullable();
            $table->string('external_id')->nullable();                 // Emby-ID oder TMDB-ID
            $table->string('external_source')->nullable();             // 'emby' | 'tmdb'
            $table->boolean('is_winner')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('suggested_by_guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->timestamps();
        });

        // ── POLL VOTES ────────────────────────────────────────────────────
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('event_polls')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');                              // Snapshot auch wenn kein Guest-Account
            $table->string('vote_value')->default('yes');
            // 'yes' | 'no' | 'maybe' (für Termine) | 'like' | 'wish' (für Filme)
            $table->timestamps();

            $table->unique(['poll_id', 'option_id', 'guest_id']);     // kein Doppelvote per Account
        });

        // ── SEAT REQUESTS ─────────────────────────────────────────────────
        Schema::create('seat_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->json('requested_seat_ids')->nullable();            // bevorzugte Plätze
            $table->string('status')->default('pending');
            // 'pending' | 'confirmed' | 'declined' | 'waitlist'
            $table->foreignId('assigned_seat_id')->nullable()->constrained('seats')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // ── ATTENDANCE (Statistik wer war dabei) ─────────────────────────
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seat_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->timestamp('checked_in_at')->nullable();
            $table->integer('loyalty_points_earned')->default(0);
            $table->timestamps();

            $table->unique(['screening_id', 'guest_id']);
        });

        // ── screenings: seating_mode + event_id ergänzen ─────────────────
        Schema::table('screenings', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->after('id')->constrained('events')->nullOnDelete();
            $table->string('seating_mode')->default('seated')->after('status');
            $table->integer('max_capacity')->nullable()->after('seating_mode');
        });
    }

    public function down(): void
    {
        Schema::table('screenings', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn(['event_id', 'seating_mode', 'max_capacity']);
        });
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('seat_requests');
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('event_polls');
        Schema::dropIfExists('event_slots');
        Schema::dropIfExists('events');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('guests');
    }
};
