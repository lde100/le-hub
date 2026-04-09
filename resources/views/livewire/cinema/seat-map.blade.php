<div class="seat-map" x-data>
    {{-- Leinwand --}}
    <div class="screen-bar">
        <div class="screen-label">🎬 LEINWAND</div>
    </div>

    {{-- Zonen --}}
    <div class="seat-zones">
        @foreach ($seatsByRow as $row => $seats)
            <div class="seat-zone">
                <div class="zone-label">{{ $row }}</div>
                <div class="seat-row seat-row--{{ Str::slug($row) }}">
                    @foreach ($seats as $seat)
                        <button
                            wire:click="toggleSeat({{ $seat->id }})"
                            class="seat seat--{{ $seat->type }} {{ $this->getSeatStatusClass($seat->id) }}"
                            @if($this->isSeatTaken($seat->id)) disabled @endif
                            title="{{ $seat->label }}{{ $screening->base_price > 0 ? ' — ' . number_format($screening->base_price * $seat->price_modifier, 2) . ' €' : '' }}"
                        >
                            <span class="seat-icon">
                                @switch($seat->type)
                                    @case('couch')    🛋️ @break
                                    @case('recliner') 🪑 @break
                                    @default          💺
                                @endswitch
                            </span>
                            <span class="seat-label">{{ $seat->label }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Legende --}}
    <div class="seat-legend">
        <span class="legend-item"><span class="legend-dot free"></span> Frei</span>
        <span class="legend-item"><span class="legend-dot selected"></span> Ausgewählt</span>
        <span class="legend-item"><span class="legend-dot taken"></span> Belegt</span>
    </div>

    {{-- Auswahl-Footer --}}
    @if (count($selectedSeats) > 0)
        <div class="seat-footer">
            <div class="seat-summary">
                <strong>{{ count($selectedSeats) }} Platz/Plätze</strong>
                @if ($totalPrice > 0)
                    — {{ number_format($totalPrice, 2) }} €
                @endif
            </div>
            <button wire:click="confirmSelection" class="btn-confirm">
                Weiter →
            </button>
        </div>
    @endif
</div>
