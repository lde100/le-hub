<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Venue;
use Livewire\Component;

class EventIndex extends Component
{
    public bool $showForm = false;
    public string $title       = '';
    public string $type        = 'cinema';
    public ?int   $venueId     = null;
    public string $description = '';
    public string $seatingMode = 'seated';

    public function openForm(): void
    {
        $this->reset(['title','type','venueId','description','seatingMode']);
        $this->type = 'cinema';
        $this->seatingMode = 'seated';
        $this->showForm = true;
    }

    public function createEvent(): void
    {
        $this->validate(['title' => 'required|min:2']);

        $venue = $this->venueId
            ? Venue::find($this->venueId)
            : Venue::where('is_active', true)->first();

        Event::create([
            'title'        => $this->title,
            'type'         => $this->type,
            'description'  => $this->description ?: null,
            'venue_id'     => $venue?->id,
            'seating_mode' => $this->seatingMode,
            'status'       => 'draft',
        ]);

        $this->showForm = false;
    }

    public function deleteEvent(int $id): void
    {
        Event::find($id)?->delete();
    }

    public function render()
    {
        return view('livewire.admin.event-index', [
            'events' => Event::with(['venue','polls','screenings.movie'])
                ->orderBy('created_at','desc')->get(),
            'venues' => Venue::where('is_active', true)->get(),
        ])->layout('layouts.app', ['title' => 'Events']);
    }
}
