<?php

namespace App\Livewire\Admin;

use App\Models\Event;
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

    private function getScreening()
    {
        return $this->event->screenings->first();
    }

    private function getTickets()
    {
        return $this->getScreening()?->tickets
            ->whereIn('status', ['valid', 'used'])
            ->sortBy('seat.sort_order')
            ?? collect();
    }

    private function buildLinks(): array
    {
        $screening   = $this->getScreening();
        $screeningId = $screening?->id;
        $baseUrl     = config('app.url');

        $links = [
            'event' => [
                'label' => 'Event-Seite',
                'items' => [[
                    'label'   => '🔗 Einladungs-Link',
                    'url'     => $this->event->public_url,
                    'hint'    => 'Für WhatsApp-Gruppe',
                    'primary' => true,
                    'share'   => true,
                    'qr'      => true,
                ]],
            ],
        ];

        if ($screeningId) {
            $links['overlays'] = [
                'label' => 'vMix Overlays (Schwarz = transparent)',
                'items' => [
                    ['label'=>'🎬 Countdown (5 Sek)', 'url'=>"{$baseUrl}/overlay/countdown/{$screeningId}?duration=5", 'hint'=>'5-4-3-2-1 mit Filmkorn', 'qr'=>true],
                    ['label'=>'🎭 Vorhang',            'url'=>"{$baseUrl}/overlay/curtain/{$screeningId}",              'hint'=>'Samtvorhänge öffnen sich', 'qr'=>true],
                    ['label'=>'🎉 Live-Reaktionen',    'url'=>"{$baseUrl}/overlay/reactions/{$screeningId}",            'hint'=>'Dauerhaft aktiv lassen', 'qr'=>true],
                ],
            ];
            $links['screens'] = [
                'label' => 'Screens',
                'items' => [
                    ['label'=>'🎬 Beamer',         'url'=>"{$baseUrl}/screen/main/{$screeningId}?autoplay=1",    'hint'=>'vMix Browser-Input', 'qr'=>true],
                    ['label'=>'📺 Einlass-Screen', 'url'=>"{$baseUrl}/cinema/entrance/{$screeningId}?autoplay=1",'hint'=>'2. Monitor', 'qr'=>true],
                    ['label'=>'📟 Einlass-PC',     'url'=>"{$baseUrl}/cinema/checkin/{$screeningId}",            'hint'=>'Braucht Login', 'auth'=>true, 'qr'=>false],
                    ['label'=>'⭐ Post-Event',      'url'=>"{$baseUrl}/cinema/post/{$screeningId}",              'hint'=>'Nach dem Film', 'qr'=>true],
                    ['label'=>'📋 Menü-iPad',       'url'=>"{$baseUrl}/screen/menu?autoplay=1",                  'hint'=>'Nur Menükarte', 'qr'=>true],
                ],
            ];
        }

        return $links;
    }

    public function render()
    {
        return view('livewire.admin.event-hub', [
            'screening' => $this->getScreening(),
            'tickets'   => $this->getTickets(),
            'links'     => $this->buildLinks(),
        ])->layout('layouts.app', ['title' => 'Hub · ' . $this->event->title]);
    }
}
