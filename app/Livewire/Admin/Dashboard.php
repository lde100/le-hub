<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Screening;
use App\Models\Ticket;
use App\Models\SeatRequest;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'activeEvents'    => Event::whereNotIn('status', ['finished','cancelled'])->with('screenings.movie')->orderBy('created_at','desc')->get(),
            'todayScreenings' => Screening::with('movie','tickets')->whereDate('starts_at', today())->get(),
            'pendingRequests' => SeatRequest::with('event')->where('status','pending')->count(),
            'recentTickets'   => Ticket::with(['booking','screening.movie'])->orderBy('created_at','desc')->limit(5)->get(),
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
