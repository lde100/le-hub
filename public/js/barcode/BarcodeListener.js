/**
 * BarcodeListener — Global HID Barcode/RFID Scanner Handler
 *
 * HID-Scanner verhält sich wie Tastatur: tippt schnell Zeichen + Enter.
 * Unterscheidung zu normaler Tastatureingabe über Eingabe-Geschwindigkeit.
 *
 * Usage:
 *   import BarcodeListener from './barcode/BarcodeListener';
 *   const scanner = new BarcodeListener((code) => handleScan(code));
 *   scanner.destroy(); // cleanup
 */

export default class BarcodeListener {
    constructor(onScan, options = {}) {
        this.onScan = onScan;
        this.buffer = '';
        this.lastKeyTime = 0;

        this.options = {
            minLength: 3,           // Mindestlänge eines gültigen Codes
            maxGap: 50,             // Max. ms zwischen Zeichen (Scanner tippt ~5ms)
            prefix: '',             // Optionales Präfix-Zeichen (z.B. STX)
            suffix: 'Enter',        // Abschlusszeichen
            captureInput: true,     // Input-Felder vom Scan ausschließen
            ...options,
        };

        this._handler = this._onKeyDown.bind(this);
        document.addEventListener('keydown', this._handler, true);
    }

    _onKeyDown(e) {
        const now = Date.now();
        const gap = now - this.lastKeyTime;

        // Wenn Gap zu groß: neuer Input-Versuch, Buffer leeren
        if (gap > this.options.maxGap && this.buffer.length > 0) {
            this.buffer = '';
        }

        this.lastKeyTime = now;

        // Enter = Scan abgeschlossen
        if (e.key === 'Enter') {
            const code = this.buffer.trim();
            this.buffer = '';

            if (code.length >= this.options.minLength) {
                // Aus Input-Feldern heraus: nicht intercepten wenn langsame Eingabe
                if (this.options.captureInput || !this._isFocusedOnInput()) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.onScan(code);
                }
            }
            return;
        }

        // Nur druckbare Zeichen buffern
        if (e.key.length === 1) {
            this.buffer += e.key;
        }
    }

    _isFocusedOnInput() {
        const tag = document.activeElement?.tagName?.toLowerCase();
        return ['input', 'textarea', 'select'].includes(tag);
    }

    destroy() {
        document.removeEventListener('keydown', this._handler, true);
    }
}
