<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\Seat;
use Illuminate\Database\Seeder;

class HomecinemaSeeder extends Seeder
{
    /**
     * Layout basierend auf realem Heimkino (3,80 x 10m):
     *
     *  [ LEINWAND ]
     *
     *  [Couch L] [Couch R]        [Sessel Gold] [Klappstuhl]
     *
     *  [Tisch L1] [Tisch M1] [Tisch R1]
     *  [Tisch L2] [Tisch M2] [Tisch R2]
     */
    public function run(): void
    {
        $venue = Venue::firstOrCreate(
            ['slug' => 'heimkino'],
            [
                'name'        => 'Heimkino',
                'description' => 'Lucas Entertainment Heimkino — 3,80 × 10 m',
                'is_active'   => true,
            ]
        );

        $seats = [
            // Couch — Reihe A (vorne, links)
            ['label' => 'Couch L', 'row' => 'Couch', 'position' => 1, 'type' => 'couch',    'sort_order' => 10],
            ['label' => 'Couch R', 'row' => 'Couch', 'position' => 2, 'type' => 'couch',    'sort_order' => 11],

            // Sessel — Reihe A (vorne, rechts)
            ['label' => 'Sessel',  'row' => 'Sessel', 'position' => 1, 'type' => 'recliner', 'sort_order' => 20],
            ['label' => 'Stuhl',   'row' => 'Sessel', 'position' => 2, 'type' => 'standard', 'sort_order' => 21],

            // Tisch — Reihe B (hinten, 6 Plätze: 3 links + 3 rechts vom Tisch)
            ['label' => 'Tisch L1', 'row' => 'Tisch', 'position' => 1, 'type' => 'standard', 'sort_order' => 30],
            ['label' => 'Tisch M1', 'row' => 'Tisch', 'position' => 2, 'type' => 'standard', 'sort_order' => 31],
            ['label' => 'Tisch R1', 'row' => 'Tisch', 'position' => 3, 'type' => 'standard', 'sort_order' => 32],
            ['label' => 'Tisch L2', 'row' => 'Tisch', 'position' => 4, 'type' => 'standard', 'sort_order' => 33],
            ['label' => 'Tisch M2', 'row' => 'Tisch', 'position' => 5, 'type' => 'standard', 'sort_order' => 34],
            ['label' => 'Tisch R2', 'row' => 'Tisch', 'position' => 6, 'type' => 'standard', 'sort_order' => 35],
        ];

        foreach ($seats as $data) {
            Seat::firstOrCreate(
                ['venue_id' => $venue->id, 'label' => $data['label']],
                array_merge($data, ['venue_id' => $venue->id, 'is_active' => true, 'price_modifier' => 1.00])
            );
        }
    }
}
