<?php

namespace App\Livewire\Infoscreen;

use App\Models\InfoscreenSlide;
use App\Models\Screening;
use App\Models\ProductCategory;
use App\Services\CheckinBroadcastService;
use Livewire\Component;
use Livewire\Attributes\Polling;

class Screen extends Component
{
    public string $channel = 'main';
    public int $currentIndex = 0;
    public array $slides = [];

    // Scan-Overlay
    public ?array $lastScan = null;
    public int $lastSeenSeq = 0;

    // Optional: an eine Vorstellung gebunden für Check-in Overlay
    public ?int $screeningId = null;
    public string $screeningState = 'countdown';
    public int $lastStateSeq = 0;
    public int $lastGongSeq  = 0;

    public function mount(string $channel = 'main', ?int $screeningId = null): void
    {
        $this->channel     = $channel;
        $this->screeningId = $screeningId;
        $this->loadSlides();
        if ($this->screeningId) {
            $broadcast = app(\App\Services\CheckinBroadcastService::class);
            $this->screeningState = $broadcast->getState($this->screeningId);
            $this->lastStateSeq   = $broadcast->getStateSeq($this->screeningId);
        }
    }

    // Polling für Scan-Overlay (nur wenn screeningId gesetzt)
    #[Polling(800)]
    public function pollScans(): void
    {
        if (!$this->screeningId) return;

        $broadcast = app(\App\Services\CheckinBroadcastService::class);

        // State
        $stateSeq = $broadcast->getStateSeq($this->screeningId);
        if ($stateSeq > $this->lastStateSeq) {
            $this->screeningState = $broadcast->getState($this->screeningId);
            $this->lastStateSeq   = $stateSeq;
            $this->dispatch('state-changed', state: $this->screeningState);
        }

        // Gong-Trigger
        $gong = $broadcast->getGongTrigger($this->screeningId);
        if ($gong && $gong['seq'] > $this->lastGongSeq) {
            $this->lastGongSeq = $gong['seq'];
            $this->dispatch('play-gong', count: $gong['count']);
        }

        if ($broadcast->isNewScan($this->screeningId, $this->lastSeenSeq)) {
            $scan = $broadcast->getLatestScan($this->screeningId);
            $this->lastScan    = $scan;
            $this->lastSeenSeq = $scan['seq'];
            $this->dispatch('show-welcome', scan: $scan);
        }
    }

    public function loadSlides(): void
    {
        $this->slides = InfoscreenSlide::forChannel($this->channel)
            ->get()
            ->map(fn($s) => [
                'id'       => $s->id,
                'title'    => $s->title,
                'type'     => $s->type,
                'config'   => $s->config ?? [],
                'duration' => $s->duration_seconds,
                'data'     => $this->resolveSlideData($s),
            ])
            ->toArray();
    }

    private function resolveSlideData(InfoscreenSlide $slide): array
    {
        return match($slide->type) {
            'now_playing'    => $this->getNowPlaying(),
            'upcoming'       => $this->getUpcoming(),
            'menu_category'  => $this->getMenuCategory($slide->config['category_slug'] ?? ''),
            'paypal_qr'      => ['paypal_me' => $slide->config['paypal_me'] ?? config('services.paypal_me', '')],
            'countdown'      => $this->getCountdown($slide->config['screening_id'] ?? $this->screeningId),
            default          => $slide->config ?? [],
        };
    }

    private function getNowPlaying(): array
    {
        $screening = Screening::with('movie')
            ->where('status', 'open')
            ->where('starts_at', '<=', now())
            ->where('starts_at', '>=', now()->subHours(4))
            ->orderBy('starts_at', 'desc')
            ->first();
        if (!$screening) return ['empty' => true];
        return [
            'title'    => $screening->movie->title,
            'poster'   => $screening->movie->poster_path,
            'duration' => $screening->movie->duration_formatted,
            'rating'   => $screening->movie->rating,
            'started'  => $screening->starts_at->format('H:i'),
        ];
    }

    private function getUpcoming(): array
    {
        return Screening::with('movie')
            ->where('status', 'scheduled')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(fn($s) => [
                'title'  => $s->movie->title,
                'date'   => $s->starts_at->isoFormat('ddd D.M.'),
                'time'   => $s->starts_at->format('H:i'),
                'poster' => $s->movie->poster_path,
            ])
            ->toArray();
    }

    private function getMenuCategory(string $slug): array
    {
        if (!$slug) return [];
        $cat = ProductCategory::with(['products' => fn($q) => $q->where('is_available', true)->orderBy('sort_order')])
            ->where('slug', $slug)->first();
        if (!$cat) return [];
        return [
            'name'     => $cat->name,
            'icon'     => $cat->icon,
            'color'    => $cat->color,
            'products' => $cat->products->map(fn($p) => [
                'name'  => $p->name,
                'price' => $p->price > 0 ? number_format($p->price, 2) . ' €' : null,
            ])->toArray(),
        ];
    }

    private function getCountdown(?int $screeningId): array
    {
        if (!$screeningId) return ['empty' => true];
        $screening = Screening::with('movie')->find($screeningId);
        if (!$screening) return ['empty' => true];
        return [
            'title'      => $screening->movie?->title ?? 'Vorstellung',
            'starts_at'  => $screening->starts_at->toIso8601String(),
            'date_label' => $screening->starts_at->isoFormat('dddd, D. MMMM'),
            'time_label' => $screening->starts_at->format('H:i'),
        ];
    }

    public function render()
    {
        return view('livewire.infoscreen.screen')
            ->layout('layouts.infoscreen');
    }
}
