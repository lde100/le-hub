<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\InfoscreenSlide;
use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        // ── Kategorien ────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Getränke',      'slug' => 'getraenke',  'module' => 'gastro', 'icon' => '🥤', 'color' => '#3B82F6', 'sort_order' => 10],
            ['name' => 'Snacks',        'slug' => 'snacks',     'module' => 'gastro', 'icon' => '🍿', 'color' => '#F59E0B', 'sort_order' => 20],
            ['name' => 'Speisen',       'slug' => 'speisen',    'module' => 'gastro', 'icon' => '🍽️',  'color' => '#10B981', 'sort_order' => 30],
            ['name' => 'Kino-Ticket',   'slug' => 'tickets',    'module' => 'cinema', 'icon' => '🎟️',  'color' => '#8B5CF6', 'sort_order' => 40, 'show_on_infoscreen' => false],
            ['name' => 'IT-Leistungen', 'slug' => 'it-service', 'module' => 'it',     'icon' => '💻', 'color' => '#6366F1', 'sort_order' => 50, 'show_on_infoscreen' => false],
        ];

        foreach ($categories as $cat) {
            ProductCategory::firstOrCreate(['slug' => $cat['slug']], array_merge(
                $cat, ['show_on_infoscreen' => $cat['show_on_infoscreen'] ?? true]
            ));
        }

        $getraenke = ProductCategory::where('slug', 'getraenke')->first();
        $snacks    = ProductCategory::where('slug', 'snacks')->first();
        $speisen   = ProductCategory::where('slug', 'speisen')->first();

        // ── Beispiel-Produkte ─────────────────────────────────────────────
        $products = [
            // Getränke
            ['category_id' => $getraenke->id, 'name' => 'Wasser (0,5l)',     'price' => 0.00, 'unit' => 'Flasche', 'sort_order' => 10],
            ['category_id' => $getraenke->id, 'name' => 'Cola (0,33l)',      'price' => 0.00, 'unit' => 'Dose',    'sort_order' => 20],
            ['category_id' => $getraenke->id, 'name' => 'Bier (0,33l)',      'price' => 0.00, 'unit' => 'Flasche', 'sort_order' => 30],
            ['category_id' => $getraenke->id, 'name' => 'Wein (Glas)',       'price' => 0.00, 'unit' => 'Glas',    'sort_order' => 40],
            ['category_id' => $getraenke->id, 'name' => 'Sekt (Glas)',       'price' => 0.00, 'unit' => 'Glas',    'sort_order' => 50],
            // Snacks
            ['category_id' => $snacks->id,    'name' => 'Popcorn (Schüssel)', 'price' => 0.00, 'unit' => 'Portion', 'sort_order' => 10],
            ['category_id' => $snacks->id,    'name' => 'Chips',              'price' => 0.00, 'unit' => 'Schüssel','sort_order' => 20],
            ['category_id' => $snacks->id,    'name' => 'Gummibärchen',       'price' => 0.00, 'unit' => 'Portion', 'sort_order' => 30],
            // Speisen
            ['category_id' => $speisen->id,   'name' => 'Pizza (Stück)',      'price' => 0.00, 'unit' => 'Stück',   'sort_order' => 10],
            ['category_id' => $speisen->id,   'name' => 'Sandwich',           'price' => 0.00, 'unit' => 'Stück',   'sort_order' => 20],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['name' => $p['name'], 'category_id' => $p['category_id']],
                array_merge($p, ['is_available' => true, 'show_on_infoscreen' => true])
            );
        }

        // ── Infoscreen-Slides ─────────────────────────────────────────────
        $slides = [
            // Haupt-Screen
            ['title' => 'Jetzt im Kino',   'channel' => 'main',    'type' => 'now_playing',      'duration_seconds' => 15, 'sort_order' => 10],
            ['title' => 'Demnächst',        'channel' => 'main',    'type' => 'upcoming',         'duration_seconds' => 10, 'sort_order' => 20],
            ['title' => 'Getränkekarte',    'channel' => 'main',    'type' => 'menu_category',    'duration_seconds' => 12, 'sort_order' => 30, 'config' => ['category_slug' => 'getraenke']],
            ['title' => 'Snack-Karte',      'channel' => 'main',    'type' => 'menu_category',    'duration_seconds' => 10, 'sort_order' => 40, 'config' => ['category_slug' => 'snacks']],
            ['title' => 'PayPal Bezahlung', 'channel' => 'main',    'type' => 'paypal_qr',        'duration_seconds' => 8,  'sort_order' => 50, 'config' => ['paypal_me' => 'LucasEntertainment']],
            // Menü-Screen (iPad)
            ['title' => 'Getränke (Menü)',  'channel' => 'menu',    'type' => 'menu_category',    'duration_seconds' => 20, 'sort_order' => 10, 'config' => ['category_slug' => 'getraenke']],
            ['title' => 'Snacks (Menü)',    'channel' => 'menu',    'type' => 'menu_category',    'duration_seconds' => 20, 'sort_order' => 20, 'config' => ['category_slug' => 'snacks']],
            ['title' => 'Speisen (Menü)',   'channel' => 'menu',    'type' => 'menu_category',    'duration_seconds' => 20, 'sort_order' => 30, 'config' => ['category_slug' => 'speisen']],
        ];

        foreach ($slides as $slide) {
            InfoscreenSlide::firstOrCreate(
                ['title' => $slide['title'], 'channel' => $slide['channel']],
                array_merge($slide, ['is_active' => true, 'config' => $slide['config'] ?? null])
            );
        }
    }
}
