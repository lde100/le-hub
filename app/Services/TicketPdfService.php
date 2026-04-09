<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Venue;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketPdfService
{
    public function generate(Ticket $ticket): \Barryvdh\DomPDF\PDF
    {
        $ticket->load(['seat', 'screening.movie', 'screening.venue', 'booking']);

        $seatMapSvg = $this->buildSeatMapSvg(
            $ticket->screening->venue,
            $ticket->seat_id
        );

        $pdf = Pdf::loadView('tickets.pdf', [
            'ticket'     => $ticket,
            'seatMapSvg' => $seatMapSvg,
            'qrDataUrl'  => $this->buildQrDataUrl($ticket->ticket_code),
        ]);

        $pdf->setPaper([0, 0, 595, 280], 'landscape'); // DIN A5 quer
        $pdf->set_option('isRemoteEnabled', false);
        $pdf->set_option('defaultFont', 'courier');

        return $pdf;
    }

    /**
     * Saalplan-SVG mit markiertem Sitz.
     * Koordinaten basieren auf dem Heimkino-Layout.
     */
    private function buildSeatMapSvg(Venue $venue, int $highlightSeatId): string
    {
        $seats = $venue->seats()->where('is_active', true)->get();

        // Layout-Koordinaten pro Sitz (label => [x, y, w, h])
        // Heimkino: Couch oben links, Sessel oben rechts, Tisch unten
        $coords = [
            'Couch L'   => [10,  20, 52, 28],
            'Couch R'   => [66,  20, 52, 28],
            'Sessel'    => [140, 20, 40, 28],
            'Stuhl'     => [184, 20, 40, 28],
            'Tisch L1'  => [10,  68, 52, 22],
            'Tisch M1'  => [66,  68, 52, 22],
            'Tisch R1'  => [122, 68, 52, 22],
            'Tisch L2'  => [10,  94, 52, 22],
            'Tisch M2'  => [66,  94, 52, 22],
            'Tisch R2'  => [122, 94, 52, 22],
        ];

        $svgParts = [];
        $svgParts[] = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 130" width="240" height="130">';
        // Leinwand
        $svgParts[] = '<rect x="10" y="4" width="220" height="8" rx="2" fill="#C9A84C" opacity=".5"/>';
        $svgParts[] = '<text x="120" y="11" text-anchor="middle" font-size="5" fill="#C9A84C" font-family="Courier,monospace">LEINWAND</text>';

        foreach ($seats as $seat) {
            $c = $coords[$seat->label] ?? null;
            if (!$c) continue;
            [$x, $y, $w, $h] = $c;
            $isHighlighted = $seat->id === $highlightSeatId;
            $fill   = $isHighlighted ? '#C9A84C' : '#1A1A1A';
            $stroke = $isHighlighted ? '#C9A84C' : '#444';
            $text   = $isHighlighted ? '#000' : '#888';

            $svgParts[] = sprintf(
                '<rect x="%d" y="%d" width="%d" height="%d" rx="4" fill="%s" stroke="%s" stroke-width="1"/>',
                $x, $y, $w, $h, $fill, $stroke
            );
            $svgParts[] = sprintf(
                '<text x="%s" y="%s" text-anchor="middle" font-size="5" fill="%s" font-family="Courier,monospace">%s</text>',
                $x + $w / 2, $y + $h / 2 + 2, $text, htmlspecialchars($seat->label)
            );

            if ($isHighlighted) {
                // Pfeil nach unten auf dem Sitz
                $cx = $x + $w / 2;
                $svgParts[] = sprintf(
                    '<polygon points="%s,%s %s,%s %s,%s" fill="#C9A84C"/>',
                    $cx, $y - 6,
                    $cx - 4, $y - 1,
                    $cx + 4, $y - 1
                );
            }
        }

        $svgParts[] = '</svg>';
        return implode("\n", $svgParts);
    }

    /**
     * QR-Code als Data-URL (via Google Chart API — kann lokal ersetzt werden)
     */
    private function buildQrDataUrl(string $code): string
    {
        // Für DomPDF muss der QR inline sein — wir nutzen ein einfaches SVG-QR
        // Placeholder: in Produktion durch endroid/qr-code ersetzen
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&format=png&data=' . urlencode($code);
        return $url; // DomPDF lädt externe URLs wenn isRemoteEnabled=true, sonst als Platzhalter
    }
}
