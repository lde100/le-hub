/**
 * TheaterGong — Web Audio API Gong-Synthesizer
 *
 * Kein externes File nötig. Simuliert einen Metallgong durch
 * überlagerte Sinuswellen mit exponentiellem Ausklingen.
 *
 * Usage:
 *   import TheaterGong from './audio/TheaterGong';
 *   const gong = new TheaterGong();
 *   gong.play(1);  // 1 Gong
 *   gong.play(3);  // 3 Gongs nacheinander
 */
export default class TheaterGong {
    constructor() {
        this._ctx = null;
    }

    _getCtx() {
        if (!this._ctx) {
            this._ctx = new (window.AudioContext || window.webkitAudioContext)();
        }
        // iOS/Chrome braucht Resume nach User-Gesture — wir versuchen es
        if (this._ctx.state === 'suspended') {
            this._ctx.resume();
        }
        return this._ctx;
    }

    /**
     * Einen einzelnen Gong-Schlag synthetisieren.
     * @param {number} startOffset  Sekunden ab jetzt
     */
    _strike(startOffset = 0) {
        const ctx = this._getCtx();
        const t   = ctx.currentTime + startOffset;

        // Master Gain
        const master = ctx.createGain();
        master.connect(ctx.destination);
        master.gain.setValueAtTime(0.0, t);
        master.gain.linearRampToValueAtTime(0.7, t + 0.01);  // schneller Attack
        master.gain.exponentialRampToValueAtTime(0.001, t + 6.0); // 6 Sek Ausklingen

        // Grundton (tiefer Gong ~120 Hz)
        this._addPartial(ctx, master, t, 120,  1.0);
        // Obertöne für Gong-Timbre
        this._addPartial(ctx, master, t, 280,  0.5);
        this._addPartial(ctx, master, t, 480,  0.25, 1.5); // schneller abklingen
        this._addPartial(ctx, master, t, 760,  0.15, 1.0);
        this._addPartial(ctx, master, t, 1100, 0.08, 0.8);

        // Percussiver Anschlag (weißes Rauschen, kurz)
        this._addAttackNoise(ctx, master, t);
    }

    _addPartial(ctx, destination, t, freq, gainMult, decayMult = 1) {
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.connect(gain);
        gain.connect(destination);

        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, t);
        // Leichtes Frequency-Wobble für lebendigen Klang
        osc.frequency.exponentialRampToValueAtTime(freq * 0.998, t + 0.5);

        gain.gain.setValueAtTime(0, t);
        gain.gain.linearRampToValueAtTime(gainMult, t + 0.01);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + 6.0 / decayMult);

        osc.start(t);
        osc.stop(t + 7.0);
    }

    _addAttackNoise(ctx, destination, t) {
        const bufSize  = ctx.sampleRate * 0.08;
        const buffer   = ctx.createBuffer(1, bufSize, ctx.sampleRate);
        const data     = buffer.getChannelData(0);
        for (let i = 0; i < bufSize; i++) data[i] = Math.random() * 2 - 1;

        const source = ctx.createBufferSource();
        source.buffer = buffer;

        const filter = ctx.createBiquadFilter();
        filter.type  = 'bandpass';
        filter.frequency.value = 500;
        filter.Q.value = 0.5;

        const gain = ctx.createGain();
        gain.gain.setValueAtTime(0.3, t);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.08);

        source.connect(filter);
        filter.connect(gain);
        gain.connect(destination);
        source.start(t);
        source.stop(t + 0.1);
    }

    /**
     * N Gongs spielen (2.5 Sekunden Abstand).
     * @param {number} count     Anzahl Gongs (1, 2 oder 3)
     * @param {number} delay     Optionaler Startverzögerung in Sekunden
     */
    play(count = 1, delay = 0) {
        const spacing = 2.5; // Sekunden zwischen Gongs
        for (let i = 0; i < count; i++) {
            this._strike(delay + i * spacing);
        }
    }

    /**
     * Theater-Glocken-Sequenz für Countdown.
     * Klassisch: 3 Gongs → 15min, 2 Gongs → 5min, 1 Gong → Start
     * Alternativ: 1 Gong → 10min, 2 Gongs → 5min, 3 Gongs → 1min
     *
     * @param {string} mode  'classic' | 'ascending'
     */
    scheduleFor(startsAt, mode = 'classic') {
        const schedule = mode === 'classic'
            ? [
                { minutesBefore: 15, count: 3 },
                { minutesBefore: 5,  count: 2 },
                { minutesBefore: 0,  count: 1 },
              ]
            : [
                { minutesBefore: 10, count: 1 },
                { minutesBefore: 5,  count: 2 },
                { minutesBefore: 1,  count: 3 },
              ];

        const start = new Date(startsAt).getTime();

        schedule.forEach(({ minutesBefore, count }) => {
            const triggerAt = start - minutesBefore * 60 * 1000;
            const msFromNow = triggerAt - Date.now();

            if (msFromNow < 0) return; // Verpasst

            setTimeout(() => {
                console.log(`🔔 Theater Gong ×${count} (${minutesBefore} Min vor Beginn)`);
                this.play(count);
            }, msFromNow);
        });
    }
}
