<?php

namespace App\Services;

use App\Models\Ticket;
use PKPass\PKPass;

/**
 * Apple Wallet Pass Generator
 *
 * Voraussetzungen (einmalig einrichten):
 *  1. Apple Developer Account (99€/Jahr)
 *  2. Pass Type Identifier anlegen: z.B. pass.de.lucas-entertainment.ticket
 *  3. Zertifikat exportieren als .p12, dann:
 *     openssl pkcs12 -in cert.p12 -clcerts -nokeys -out certs/pass.crt
 *     openssl pkcs12 -in cert.p12 -nocerts -nodes -out certs/pass.key
 *  4. WWDR-Zertifikat herunterladen von Apple und als certs/AppleWWDRCA.pem speichern
 *  5. .env setzen:
 *     APPLE_WALLET_PASS_TYPE_ID=pass.de.lucas-entertainment.ticket
 *     APPLE_WALLET_TEAM_ID=XXXXXXXXXX
 *     APPLE_WALLET_CERT_PATH=/var/www/certs/pass.crt
 *     APPLE_WALLET_KEY_PATH=/var/www/certs/pass.key
 *     APPLE_WALLET_WWDR_PATH=/var/www/certs/AppleWWDRCA.pem
 *     APPLE_WALLET_KEY_PASSWORD=  (falls P12 passwortgeschützt)
 */
class AppleWalletService
{
    public function isConfigured(): bool
    {
        return !empty(config('wallet.pass_type_id'))
            && file_exists(config('wallet.cert_path', ''))
            && file_exists(config('wallet.key_path', ''));
    }

    public function generate(Ticket $ticket): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException(
                'Apple Wallet nicht konfiguriert. Bitte APPLE_WALLET_* in .env setzen.'
            );
        }

        $ticket->load(['seat', 'screening.movie', 'booking']);

        $pass = new PKPass(
            config('wallet.cert_path'),
            config('wallet.key_path'),
            config('wallet.key_password', ''),
            config('wallet.wwdr_path')
        );

        $pass->setData([
            'passTypeIdentifier' => config('wallet.pass_type_id'),
            'teamIdentifier'     => config('wallet.team_id'),
            'organizationName'   => 'Lucas Entertainment',
            'description'        => 'Kinokarte',
            'serialNumber'       => $ticket->ticket_code,
            'formatVersion'      => 1,

            'backgroundColor'    => 'rgb(13, 13, 13)',
            'foregroundColor'    => 'rgb(245, 245, 245)',
            'labelColor'         => 'rgb(201, 168, 76)',

            // Barcode (QR) — dein Scanner kann QR lesen
            'barcodes' => [[
                'message'         => $ticket->ticket_code,
                'format'          => 'PKBarcodeFormatQR',
                'messageEncoding' => 'iso-8859-1',
            ]],

            // Event-Ticket Format
            'eventTicket' => [
                'primaryFields' => [[
                    'key'   => 'event',
                    'label' => 'FILM',
                    'value' => $ticket->screening->movie?->title ?? 'Event',
                ]],
                'secondaryFields' => [
                    [
                        'key'         => 'date',
                        'label'       => 'DATUM',
                        'value'       => $ticket->screening->starts_at->format('d.m.Y'),
                        'dateStyle'   => 'PKDateStyleShort',
                    ],
                    [
                        'key'   => 'time',
                        'label' => 'EINLASS',
                        'value' => $ticket->screening->starts_at->format('H:i') . ' Uhr',
                    ],
                ],
                'auxiliaryFields' => [
                    [
                        'key'   => 'seat',
                        'label' => 'PLATZ',
                        'value' => $ticket->seat?->label ?? '—',
                    ],
                    [
                        'key'   => 'guest',
                        'label' => 'NAME',
                        'value' => $ticket->booking->customer_name ?? '',
                    ],
                ],
                'backFields' => [
                    [
                        'key'   => 'ref',
                        'label' => 'Buchungsnummer',
                        'value' => $ticket->booking->booking_ref ?? $ticket->ticket_code,
                    ],
                    [
                        'key'   => 'venue',
                        'label' => 'Veranstaltungsort',
                        'value' => 'Lucas Entertainment · Privatkino',
                    ],
                ],
            ],
        ]);

        // Logo (LE-Logo als PNG nötig — Platzhalter)
        $logoPath = public_path('images/le-logo-wallet.png');
        if (file_exists($logoPath)) {
            $pass->addFile($logoPath, 'logo.png');
            $pass->addFile($logoPath, 'logo@2x.png');
        }

        return $pass->create('string');
    }
}
