<?php

namespace App\Livewire\Cinema;

use App\Models\Ticket;
use Livewire\Component;

class TicketScanner extends Component
{
    public string $scanResult = '';
    public ?array $lastScan = null;
    public string $mode = 'scan'; // 'scan' | 'camera' (iPhone QR via Kamera)

    // Wird vom JS BarcodeListener via Alpine/Livewire-Event aufgerufen
    public function handleScan(string $code): void
    {
        $ticket = Ticket::with(['seat', 'screening.movie', 'booking'])
            ->where('ticket_code', $code)
            ->first();

        if (!$ticket) {
            $this->lastScan = [
                'status'  => 'error',
                'message' => 'Ticket nicht gefunden',
                'code'    => $code,
            ];
            return;
        }

        if ($ticket->status === 'used') {
            $this->lastScan = [
                'status'   => 'warning',
                'message'  => 'Bereits entwertet',
                'code'     => $code,
                'ticket'   => $this->formatTicket($ticket),
                'used_at'  => $ticket->scanned_at?->format('H:i d.m.Y'),
            ];
            return;
        }

        if ($ticket->status === 'cancelled') {
            $this->lastScan = [
                'status'  => 'error',
                'message' => 'Ticket storniert',
                'code'    => $code,
            ];
            return;
        }

        // Gültiges Ticket — entwerten
        $ticket->markAsUsed(auth()->user()?->name ?? 'Scanner');

        $this->lastScan = [
            'status'  => 'success',
            'message' => 'Gültig ✓',
            'code'    => $code,
            'ticket'  => $this->formatTicket($ticket),
        ];

        $this->dispatch('ticket-scanned', result: $this->lastScan);
    }

    private function formatTicket(Ticket $ticket): array
    {
        return [
            'code'        => $ticket->ticket_code,
            'seat'        => $ticket->seat->label,
            'movie'       => $ticket->screening->movie->title,
            'starts_at'   => $ticket->screening->starts_at->format('H:i d.m.Y'),
            'customer'    => $ticket->booking->customer_name,
            'booking_ref' => $ticket->booking->booking_ref,
        ];
    }

    public function render()
    {
        return view('livewire.cinema.ticket-scanner');
    }
}
