<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Säle (erweiterbar für mehrere Räume)
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // "Heimkino", "Seminarraum" etc.
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Sitzplätze — flexibel: Typ, Reihe, Position
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('label');             // "A1", "Couch L", "Recliner 1"
            $table->string('row')->nullable();   // "A", "B", "Couch"
            $table->integer('position');         // 1, 2, 3 innerhalb der Reihe
            $table->enum('type', ['standard', 'recliner', 'couch', 'vip'])->default('standard');
            $table->decimal('price_modifier', 4, 2)->default(1.00); // Multiplikator auf Basispreis
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Filme / Veranstaltungen
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->text('synopsis')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('rating')->nullable();     // "FSK 12", "FSK 16"
            $table->string('genre')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->string('trailer_url')->nullable();
            $table->year('release_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Vorstellungen
        Schema::create('screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained();
            $table->foreignId('movie_id')->constrained();
            $table->dateTime('starts_at');
            $table->dateTime('doors_open_at')->nullable();
            $table->decimal('base_price', 8, 2)->default(0.00);
            $table->enum('status', ['scheduled', 'open', 'sold_out', 'cancelled', 'finished'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Buchungen
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref', 12)->unique(); // z.B. "LE-240001"
            $table->foreignId('screening_id')->constrained();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('total_amount', 8, 2)->default(0.00);
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'free'])->default('free');
            $table->enum('status', ['active', 'cancelled', 'checked_in'])->default('active');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });

        // Tickets (ein Ticket = ein Sitzplatz pro Buchung)
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 20)->unique(); // Barcode-Wert
            $table->foreignId('booking_id')->constrained();
            $table->foreignId('seat_id')->constrained();
            $table->foreignId('screening_id')->constrained();
            $table->decimal('price', 8, 2)->default(0.00);
            $table->enum('status', ['valid', 'used', 'cancelled'])->default('valid');
            $table->timestamp('scanned_at')->nullable();
            $table->string('scanned_by')->nullable();
            $table->timestamps();

            $table->unique(['screening_id', 'seat_id']); // kein Doppelbuchung
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('screenings');
        Schema::dropIfExists('movies');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('venues');
    }
};
