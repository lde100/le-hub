<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LE-HUB Core — Customers, Products, Orders, Invoices, Payments
 *
 * Bewusst universell gehalten:
 * - Customer = Heimkino-Gast ODER IT-Kunde (context-Feld unterscheidet)
 * - Product  = Getränk/Gericht ODER IT-Leistung/Lizenz (category-Feld)
 * - Order    = Gastro-Tab ODER IT-Auftrag
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── CUSTOMERS ─────────────────────────────────────────────────────
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();          // IT-Kontext
            $table->text('address')->nullable();            // IT-Kontext
            $table->string('tax_id')->nullable();           // USt-ID
            $table->string('customer_number')->unique()->nullable(); // LE-K-0001
            $table->string('barcode')->nullable()->index(); // Kundenkarte
            $table->enum('context', ['guest', 'client', 'both'])->default('guest');
            // guest = Heimkino/Gastro, client = IT, both = beides
            $table->json('meta')->nullable();               // flexibel erweiterbar
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── PRODUCT CATEGORIES ────────────────────────────────────────────
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // "Getränke", "Speisen", "IT-Stunden"
            $table->string('slug')->unique();
            $table->string('module')->default('gastro');    // 'gastro' | 'cinema' | 'it'
            $table->string('icon')->nullable();             // Emoji oder Icon-Name
            $table->string('color', 7)->nullable();         // Hex für Infoscreen
            $table->integer('sort_order')->default(0);
            $table->boolean('show_on_infoscreen')->default(true);
            $table->timestamps();
        });

        // ── PRODUCTS ──────────────────────────────────────────────────────
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('unit')->default('Stück');       // "Stück", "Stunde", "Flasche"
            $table->string('barcode')->nullable()->index();
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('show_on_infoscreen')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // ── ORDERS ────────────────────────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();       // LE-O-240001
            $table->string('module')->default('gastro');    // 'gastro' | 'cinema' | 'it'
            $table->string('status')->default('open');
            // open | confirmed | preparing | ready | closed | cancelled
            $table->string('table_ref')->nullable();        // "Tisch 1", "Heimkino", frei
            $table->text('notes')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tip_amount', 10, 2)->default(0.00);
            $table->json('meta')->nullable();               // z.B. screening_id Referenz
            $table->timestamps();
        });

        // ── ORDER PARTICIPANTS (Personen an einem Tab) ───────────────────
        Schema::create('order_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');                         // Schnell-Name ohne Customer-Account
            $table->string('color', 7)->nullable();         // Farbe für UI-Unterscheidung
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ── ORDER ITEMS ───────────────────────────────────────────────────
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');                         // Snapshot des Produktnamens
            $table->decimal('unit_price', 10, 2);
            $table->decimal('quantity', 8, 3)->default(1);
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('pending');
            // pending | preparing | ready | served | cancelled
            $table->text('notes')->nullable();              // "ohne Eis" etc.
            $table->timestamps();
        });

        // ── ORDER ITEM SPLITS (Zuweisung + Aufteilung) ───────────────────
        Schema::create('order_item_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('order_participants')->cascadeOnDelete();
            $table->decimal('share_amount', 10, 2);        // Betrag dieser Person für diesen Posten
            $table->decimal('share_fraction', 5, 4)->nullable(); // z.B. 0.5 bei halbhalb
            $table->string('split_type')->default('assigned');
            // 'assigned' = direkt zugewiesen
            // 'equal'    = gleich aufgeteilt auf N Personen
            // 'custom'   = manuell eingegebener Betrag/Anteil
            $table->timestamps();
        });

        // ── INVOICES ──────────────────────────────────────────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();     // LE-R-240001
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_name');
            $table->text('recipient_address')->nullable();
            $table->string('recipient_tax_id')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(19.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('status')->default('draft');
            // draft | sent | paid | overdue | cancelled
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // ── PAYMENTS ──────────────────────────────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('participant_name')->nullable(); // Bei Tab-Split ohne Customer
            $table->decimal('amount', 10, 2);
            $table->string('method');
            // 'cash' | 'paypal_friends' | 'paypal_invoice' | 'transfer' | 'free'
            $table->string('reference')->nullable();        // PayPal-Transaktion-ID etc.
            $table->string('status')->default('completed');
            // 'pending' | 'completed' | 'refunded'
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── INFOSCREEN SLIDES ─────────────────────────────────────────────
        Schema::create('infoscreen_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('channel')->default('main');
            // 'main' = Heimkino-TV, 'menu' = iPad Menükarte, 'payment' = PayPal QR
            $table->string('type');
            // 'menu_category' | 'now_playing' | 'upcoming' | 'paypal_qr'
            // | 'custom_text' | 'custom_image' | 'product_spotlight'
            $table->json('config')->nullable();             // type-spezifische Config
            $table->integer('duration_seconds')->default(10);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infoscreen_slides');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('order_item_splits');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('order_participants');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('customers');
    }
};
