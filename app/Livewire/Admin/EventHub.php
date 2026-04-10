<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\Computed;

class EventHub extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        $this->event = $event->load([
            'screenings.movie',
            'screenings.tickets.booking',
            'screenings.tickets.seat',
            'venue',
            'seatRequests',
        ]);
    }

    #[Computed]
    public function screening()
    {
        return $this->event->screenings->first();
    }

    #[Computed]
    public function tickets()
    {
        return $this->screening?->tickets
            ->whereIn('status', ['valid', 'used'])
            ->sortBy('seat.sort_order')
            ?? collect();
    }

    #[Computed]
    public function links(): array
    {
        $screeningId = $this->screening?->id;
        $baseUrl     = config('app.url');

        $links = [
            'event' => [
                'label' => 'Event-Seite',
                'items' => [
                    [
                        'label'   => '🔗 Einladungs-Link',
                        'url'     => $this->event->public_url,
                        'hint'    => 'Für WhatsApp-Gruppe',
                        'primary' => true,
                        'share'   => true,
                    ],
                ],
            ],
        ];

        if ($screeningId) {
            $links['overlays'] = [
                'label' => 'vMix Overlays (Schwarz = transparent)',
                'items' => [
                    [
                        'label' => '🎬 Film-Countdown (5 Sek)',
                        'url'   => "{$baseUrl}/overlay/countdown/{$screeningId}?duration=5",
                        'hint'  => 'Klassischer 5-4-3-2-1 Countdown mit Filmkorn + Kratzer',
                        'qr'    => true,
                    ],
                    [
                        'label' => '🎭 Vorhang öffnet sich',
                        'url'   => "{$baseUrl}/overlay/curtain/{$screeningId}",
                        'hint'  => 'Rote Samtvorhänge öffnen sich (2 Sek)',
                        'qr'    => true,
                    ],
                    [
                        'label' => '🎉 Live-Reaktionen',
                        'url'   => "{$baseUrl}/overlay/reactions/{$screeningId}",
                        'hint'  => 'Emojis von Gästen fliegen hoch — dauerhaft aktiv halten',
                        'qr'    => true,
                    ],
                ],
            ];

            $links['screens'] = [
                'label' => 'Screens',
                'items' => [
                    [
                        'label' => '🎬 Beamer / Projektor',
                        'url'   => "{$baseUrl}/screen/main/{$screeningId}?autoplay=1",
                        'hint'  => 'vMix Browser-Input oder TV-Browser',
                        'qr'    => true,
                    ],
                    [
                        'label' => '📺 Einlass-Screen (2. Monitor)',
                        'url'   => "{$baseUrl}/cinema/entrance/{$screeningId}?autoplay=1",
                        'hint'  => 'Zeigt Countdown + Sitzplan bei Scan',
                        'qr'    => true,
                    ],
                    [
                        'label' => '📟 Einlass-PC (Scanner)',
                        'url'   => "{$baseUrl}/cinema/checkin/{$screeningId}",
                        'hint'  => 'Braucht Login — Barcode-Scanner hier',
                        'auth'  => true,
                        'qr'    => false,
                    ],
                    [
                        'label' => '⭐ Post-Event Screen',
                        'url'   => "{$baseUrl}/cinema/post/{$screeningId}",
                        'hint'  => 'Nach dem Film anzeigen',
                        'qr'    => true,
                    ],
                    [
                        'label' => '📋 Menü-Screen (iPad)',
                        'url'   => "{$baseUrl}/screen/menu?autoplay=1",
                        'hint'  => 'Nur Menü/Getränkekarte',
                        'qr'    => true,
                    ],
                ],
            ];
        }

        return $links;
    }

    public function render()
    {
        return view('livewire.admin.event-hub')
            ->layout('layouts.app', ['title' => 'Hub · ' . $this->event->title]);
    }
}
