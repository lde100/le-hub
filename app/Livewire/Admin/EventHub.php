<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Ticket;
use Livewire\Component;

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

    public function getScreeningAttribute()
    {
        return $this->event->screenings->first();
    }

    public function getTicketsAttribute()
    {
        return $this->screening?->tickets
            ->whereIn('status', ['valid', 'used'])
            ->sortBy('seat.sort_order')
            ?? collect();
    }

    /**
     * Alle Links die für dieses Event relevant sind.
     * Gruppiert nach Kategorie für die UI.
     */
    public function getLinksAttribute(): array
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
        return view('livewire.admin.event-hub', [
            'screening' => $this->screening,
            'tickets'   => $this->tickets,
            'links'     => $this->links,
        ])->layout('layouts.app', ['title' => 'Hub · ' . $this->event->title]);
    }
}
