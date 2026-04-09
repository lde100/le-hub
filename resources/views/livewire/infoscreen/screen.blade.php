<div
    class="infoscreen-root w-screen h-screen flex flex-col bg-black text-white overflow-hidden"
    x-data="infoscreen(@js($slides))"
    x-init="start()"
>
    {{-- LE Branding Header --}}
    <div class="infoscreen-header flex items-center justify-between px-8 py-4 border-b border-white/10">
        <div class="le-logo flex items-center gap-3">
            <span class="text-3xl font-black tracking-tight text-white">LE</span>
            <div class="flex flex-col leading-none">
                <span class="text-xs font-semibold text-white/60 uppercase tracking-widest">Lucas</span>
                <span class="text-xs font-semibold text-white/60 uppercase tracking-widest">Entertainment</span>
            </div>
        </div>
        <div class="text-white/40 text-sm" x-text="currentTime"></div>
    </div>

    {{-- Slide Content --}}
    <div class="infoscreen-content flex-1 relative overflow-hidden">

        {{-- now_playing --}}
        <template x-if="currentSlide?.type === 'now_playing'">
            <div class="slide-now-playing absolute inset-0 flex items-center justify-center p-12">
                <template x-if="currentSlide.data?.empty">
                    <div class="text-center text-white/30 text-2xl">Keine laufende Vorstellung</div>
                </template>
                <template x-if="!currentSlide.data?.empty">
                    <div class="flex gap-12 items-center">
                        <div class="text-8xl">🎬</div>
                        <div>
                            <div class="text-white/50 text-sm uppercase tracking-widest mb-2">Jetzt läuft</div>
                            <div class="text-5xl font-bold mb-4" x-text="currentSlide.data.title"></div>
                            <div class="flex gap-6 text-white/60 text-lg">
                                <span x-text="'⏱ ' + currentSlide.data.duration"></span>
                                <span x-text="currentSlide.data.rating"></span>
                                <span x-text="'Seit ' + currentSlide.data.started + ' Uhr'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- upcoming --}}
        <template x-if="currentSlide?.type === 'upcoming'">
            <div class="slide-upcoming absolute inset-0 flex flex-col justify-center p-12">
                <div class="text-white/50 text-sm uppercase tracking-widest mb-8">Demnächst</div>
                <div class="flex gap-8">
                    <template x-for="item in currentSlide.data" :key="item.title">
                        <div class="flex-1 border border-white/10 rounded-2xl p-6">
                            <div class="text-4xl mb-3">🎥</div>
                            <div class="text-xl font-bold mb-2" x-text="item.title"></div>
                            <div class="text-white/50" x-text="item.date + ' · ' + item.time + ' Uhr'"></div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- menu_category --}}
        <template x-if="currentSlide?.type === 'menu_category'">
            <div class="slide-menu absolute inset-0 flex flex-col justify-center p-12">
                <div class="flex items-center gap-4 mb-8">
                    <span class="text-5xl" x-text="currentSlide.data.icon"></span>
                    <span class="text-4xl font-bold" x-text="currentSlide.data.name"></span>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <template x-for="product in currentSlide.data.products" :key="product.name">
                        <div class="border border-white/10 rounded-xl p-4 flex justify-between items-center">
                            <span class="text-lg" x-text="product.name"></span>
                            <span class="text-white/60 text-sm" x-text="product.price || 'inklusive'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- paypal_qr --}}
        <template x-if="currentSlide?.type === 'paypal_qr'">
            <div class="slide-paypal absolute inset-0 flex flex-col items-center justify-center gap-8">
                <div class="text-white/50 text-sm uppercase tracking-widest">Bezahlen via PayPal</div>
                <div class="bg-white p-4 rounded-2xl">
                    <img
                        :src="'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent('https://paypal.me/' + currentSlide.data.paypal_me)"
                        width="280" height="280" alt="PayPal QR"
                    >
                </div>
                <div class="text-xl font-semibold" x-text="'paypal.me/' + currentSlide.data.paypal_me"></div>
            </div>
        </template>

    </div>

    {{-- Slide Progress Bar --}}
    <div class="infoscreen-footer px-8 py-3">
        <div class="flex gap-2 items-center">
            <template x-for="(slide, i) in slides" :key="i">
                <div
                    class="h-1 rounded-full transition-all duration-300"
                    :class="i === currentIndex ? 'bg-white flex-1' : 'bg-white/20 w-6'"
                ></div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.infoscreen = function(slides) {
    return {
        slides,
        currentIndex: 0,
        currentSlide: null,
        currentTime: '',
        timer: null,

        start() {
            if (!this.slides.length) return;
            this.currentSlide = this.slides[0];
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.scheduleNext();
        },

        scheduleNext() {
            const duration = (this.currentSlide?.duration ?? 10) * 1000;
            this.timer = setTimeout(() => this.nextSlide(), duration);
        },

        nextSlide() {
            this.currentIndex = (this.currentIndex + 1) % this.slides.length;
            this.currentSlide = this.slides[this.currentIndex];
            this.scheduleNext();
        },

        updateClock() {
            this.currentTime = new Date().toLocaleTimeString('de-DE', {
                hour: '2-digit', minute: '2-digit'
            });
        }
    }
}
</script>
@endpush
