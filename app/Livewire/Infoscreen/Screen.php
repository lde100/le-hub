<?php

namespace App\Livewire\Infoscreen;

use App\Models\InfoscreenSlide;
use App\Models\Screening;
use App\Models\ProductCategory;
use Livewire\Component;
use Livewire\Attributes\Lazy;

class Screen extends Component
{
    public string $channel = 'main';
    public int $currentIndex = 0;
    public array $slides = [];

    public function mount(string $channel = 'main'): void
    {
        $this->channel = $channel;
        $this->loadSlides();
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
            'now_playing' => $this->getNowPlaying(),
            'upcoming'    => $this->getUpcoming(),
            'menu_category' => $this->getMenuCategory($slide->config['category_slug'] ?? ''),
            'paypal_qr'   => ['paypal_me' => $slide->config['paypal_me'] ?? ''],
            default       => $slide->config ?? [],
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
                'title'    => $s->movie->title,
                'date'     => $s->starts_at->format('D d.m.'),
                'time'     => $s->starts_at->format('H:i'),
                'poster'   => $s->movie->poster_path,
            ])
            ->toArray();
    }

    private function getMenuCategory(string $slug): array
    {
        if (!$slug) return [];

        $category = ProductCategory::with(['products' => fn($q) => $q->where('is_available', true)->orderBy('sort_order')])
            ->where('slug', $slug)
            ->first();

        if (!$category) return [];

        return [
            'name'     => $category->name,
            'icon'     => $category->icon,
            'color'    => $category->color,
            'products' => $category->products->map(fn($p) => [
                'name'  => $p->name,
                'price' => $p->price > 0 ? number_format($p->price, 2) . ' €' : null,
                'unit'  => $p->unit,
            ])->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.infoscreen.screen')
            ->layout('layouts.infoscreen');
    }
}
