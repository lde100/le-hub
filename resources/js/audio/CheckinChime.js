/**
 * CheckinChime — Kino-Einlass Signature Sound
 *
 * Zwei warme aufsteigende Töne (wie ein Concierge-Bell),
 * gefolgt von einem kurzen Shimmer. Klar unterscheidbar
 * vom Theater-Gong (der ist tiefer + länger).
 *
 * Usage:
 *   import CheckinChime from './audio/CheckinChime.js';
 *   const chime = new CheckinChime();
 *   chime.play();           // Standard (Erfolg)
 *   chime.playError();      // Dissonant (Fehler)
 *   chime.playWarning();    // Einzelton (Warnung)
 */
export default class CheckinChime {
    constructor() {
        this._ctx = null;
    }

    _getCtx() {
        if (!this._ctx) {
            this._ctx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (this._ctx.state === 'suspended') this._ctx.resume();
        return this._ctx;
    }

    _bell(ctx, freq, startOffset, duration = 1.2, volume = 0.4) {
        const master = ctx.createGain();
        master.connect(ctx.destination);
        const t = ctx.currentTime + startOffset;

        // Schneller Attack, sanftes Ausklingen
        master.gain.setValueAtTime(0, t);
        master.gain.linearRampToValueAtTime(volume, t + 0.008);
        master.gain.exponentialRampToValueAtTime(0.001, t + duration);

        // Grundton
        [1, 2.756, 5.404].forEach((harmonic, i) => {
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain); gain.connect(master);
            osc.type = 'sine';
            osc.frequency.value = freq * harmonic;
            gain.gain.setValueAtTime(1 / (i + 1), t);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + duration / (i + 1));
            osc.start(t); osc.stop(t + duration + 0.1);
        });

        // Kurzes Anschlag-Geräusch
        const bufSize = ctx.sampleRate * 0.015;
        const buf = ctx.createBuffer(1, bufSize, ctx.sampleRate);
        const data = buf.getChannelData(0);
        for (let i = 0; i < bufSize; i++) data[i] = (Math.random() * 2 - 1) * (1 - i / bufSize);
        const noise = ctx.createBufferSource();
        noise.buffer = buf;
        const nGain = ctx.createGain();
        nGain.gain.setValueAtTime(0.12, t);
        nGain.gain.exponentialRampToValueAtTime(0.0001, t + 0.015);
        noise.connect(nGain); nGain.connect(master);
        noise.start(t); noise.stop(t + 0.02);
    }

    /** Erfolg: zwei aufsteigende Töne (C5 → E5) + Shimmer */
    play() {
        const ctx = this._getCtx();
        this._bell(ctx, 523.25, 0.0,  1.4, 0.35);  // C5
        this._bell(ctx, 659.25, 0.18, 1.6, 0.4);   // E5
        // Shimmer: sanfter hoher Ton
        this._bell(ctx, 1046.5, 0.36, 0.8, 0.15);  // C6 leise
    }

    /** Warnung: einzelner mittlerer Ton */
    playWarning() {
        const ctx = this._getCtx();
        this._bell(ctx, 440, 0, 1.0, 0.3);          // A4
    }

    /** Fehler: zwei absteigende Töne (dissonant) */
    playError() {
        const ctx = this._getCtx();
        this._bell(ctx, 349.23, 0.0,  0.8, 0.3);   // F4
        this._bell(ctx, 311.13, 0.15, 0.9, 0.25);  // Eb4
    }
}
