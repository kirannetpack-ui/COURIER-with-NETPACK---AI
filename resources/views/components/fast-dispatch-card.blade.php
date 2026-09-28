<!-- 3-Tap Fast Dispatch (Zero-Form Booking & 5 Major Services Showcase) -->
<div x-data="{
        query: '',
        loading: false,
        listening: false,
        recognition: null,
        activeService: 'domestic',
        result: null,
        error: null,
        playingAudio: false,

        init() {
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                this.recognition = new SpeechRec();
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.lang = 'en-US';

                this.recognition.onstart = () => {
                    this.listening = true;
                };
                this.recognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript;
                    this.query = transcript;
                    this.listening = false;
                    this.parseDispatch();
                };
                this.recognition.onerror = () => {
                    this.listening = false;
                };
                this.recognition.onend = () => {
                    this.listening = false;
                };
            }
        },

        toggleVoice() {
            if (!this.recognition) {
                alert('Voice recognition is not supported in this browser. You can type directly.');
                return;
            }
            if (this.listening) {
                this.recognition.stop();
            } else {
                this.recognition.start();
            }
        },

        setPreset(text) {
            this.query = text;
            this.parseDispatch();
        },

        selectService(serviceKey) {
            this.activeService = serviceKey;
            if (!this.query) {
                const map = {
                    domestic: 'Send 2kg document parcel to Pokhara',
                    international: 'Air cargo 10kg export to Sydney Australia',
                    ecommerce: 'E-commerce delivery to Biratnagar with Rs 3500 COD to Sita 9841000000',
                    hyperlocal: 'Urgent 2-hour documents delivery inside Kathmandu Valley',
                    cold_chain: 'Temperature controlled medicine delivery to Bharatpur Chitwan'
                };
                this.query = map[serviceKey] || 'Send 2kg parcel to Pokhara';
            }
            this.parseDispatch();
        },

        async parseDispatch() {
            if (!this.query.trim()) return;
            this.loading = true;
            this.error = null;

            try {
                const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                const res = await fetch('{{ route('ai.fast_dispatch_parse') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ query: this.query })
                });

                const data = await res.json();
                if (data.success) {
                    this.result = data;
                    this.activeService = data.service_tier;
                } else {
                    this.error = data.message || 'Unable to parse dispatch details.';
                }
            } catch (err) {
                console.error(err);
                this.error = 'Network error while parsing dispatch.';
            } finally {
                this.loading = false;
            }
        },

        playChandaSpeech() {
            if (!this.result?.speech_summary) return;
            this.playingAudio = true;
            const audioUrl = '{{ route('ai.speech.stream') }}?accent=en-IN&text=' + encodeURIComponent(this.result.speech_summary);
            const audio = new Audio(audioUrl);
            audio.onended = () => { this.playingAudio = false; };
            audio.onerror = () => { this.playingAudio = false; };
            audio.play();
        }
     }"
     class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs relative overflow-hidden transition">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-teal-50 text-teal-700 border border-teal-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-600 animate-pulse"></span>
                    3-Tap Dispatch &bull; Zero-Form AI
                </span>
                <span class="text-[11px] font-semibold text-slate-400">Minimalist Client Input</span>
            </div>
            <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                Fast AI Dispatch Console
            </h2>
            <p class="text-xs text-slate-500">
                Speak or paste a single sentence. Chanda AI instantly extracts route, rates, and triggers fleet pickup.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-[11px] font-mono font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-200">
                <i class="fas fa-shield-halved text-xs mr-1"></i> Dual-OTP Guaranteed
            </span>
        </div>
    </div>

    <!-- 1. The 5 Major Services Showcase Cards -->
    <div class="mb-5">
        <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-2.5">
            Select Service Corridor
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
            <!-- 1. Domestic Express -->
            <button type="button"
                    @click="selectService('domestic')"
                    :class="activeService === 'domestic' ? 'border-teal-600 bg-teal-50/70 text-teal-950 ring-2 ring-teal-500/20 shadow-xs' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50 text-slate-700'"
                    class="p-3 rounded-xl border text-left transition flex flex-col justify-between group focus:outline-none">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center text-sm shadow-xs">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <span class="text-[9px] font-bold text-teal-700 uppercase">77 Districts</span>
                </div>
                <div>
                    <h3 class="text-xs font-bold leading-tight">Domestic Express</h3>
                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">Door-to-door Nepal</p>
                </div>
            </button>

            <!-- 2. Global Air Cargo -->
            <button type="button"
                    @click="selectService('international')"
                    :class="activeService === 'international' ? 'border-sky-600 bg-sky-50/70 text-sky-950 ring-2 ring-sky-500/20 shadow-xs' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50 text-slate-700'"
                    class="p-3 rounded-xl border text-left transition flex flex-col justify-between group focus:outline-none">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-600 text-white flex items-center justify-center text-sm shadow-xs">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                    <span class="text-[9px] font-bold text-sky-700 uppercase">190+ Countries</span>
                </div>
                <div>
                    <h3 class="text-xs font-bold leading-tight">Global Air Cargo</h3>
                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">TIA Export Gateway</p>
                </div>
            </button>

            <!-- 3. E-Commerce & COD -->
            <button type="button"
                    @click="selectService('ecommerce')"
                    :class="activeService === 'ecommerce' ? 'border-emerald-600 bg-emerald-50/70 text-emerald-950 ring-2 ring-emerald-500/20 shadow-xs' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50 text-slate-700'"
                    class="p-3 rounded-xl border text-left transition flex flex-col justify-between group focus:outline-none">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-sm shadow-xs">
                        <i class="fas fa-coins"></i>
                    </div>
                    <span class="text-[9px] font-bold text-emerald-700 uppercase">Auto Escrow</span>
                </div>
                <div>
                    <h3 class="text-xs font-bold leading-tight">E-Commerce & COD</h3>
                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">Instant Payouts</p>
                </div>
            </button>

            <!-- 4. Hyperlocal Same-Day -->
            <button type="button"
                    @click="selectService('hyperlocal')"
                    :class="activeService === 'hyperlocal' ? 'border-purple-600 bg-purple-50/70 text-purple-950 ring-2 ring-purple-500/20 shadow-xs' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50 text-slate-700'"
                    class="p-3 rounded-xl border text-left transition flex flex-col justify-between group focus:outline-none">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center text-sm shadow-xs">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <span class="text-[9px] font-bold text-purple-700 uppercase">2-4 Hours</span>
                </div>
                <div>
                    <h3 class="text-xs font-bold leading-tight">Hyperlocal City</h3>
                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">Valley & Hubs</p>
                </div>
            </button>

            <!-- 5. Pharma & Cold Chain -->
            <button type="button"
                    @click="selectService('cold_chain')"
                    :class="activeService === 'cold_chain' ? 'border-cyan-600 bg-cyan-50/70 text-cyan-950 ring-2 ring-cyan-500/20 shadow-xs' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50 text-slate-700'"
                    class="p-3 rounded-xl border text-left transition flex flex-col justify-between group focus:outline-none col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-cyan-600 text-white flex items-center justify-center text-sm shadow-xs">
                        <i class="fas fa-snowflake"></i>
                    </div>
                    <span class="text-[9px] font-bold text-cyan-700 uppercase">Temp Monitored</span>
                </div>
                <div>
                    <h3 class="text-xs font-bold leading-tight">Cold Chain & Pharma</h3>
                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">Secure Medical</p>
                </div>
            </button>
        </div>
    </div>

    <!-- 2. Minimalist Input Box (Voice + Text) -->
    <div class="space-y-3">
        <form @submit.prevent="parseDispatch" class="relative">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text"
                           x-model="query"
                           placeholder="Speak or type: 'Send 2kg tea to Pokhara to Hari 9841234567'..."
                           class="w-full pl-10 pr-24 py-3 bg-slate-50 border border-slate-200/90 rounded-xl text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition shadow-2xs">
                    <i class="fas fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>

                    <!-- Voice Mic Button inside input -->
                    <button type="button"
                            @click="toggleVoice()"
                            :class="listening ? 'bg-red-500 text-white animate-pulse' : 'bg-teal-50 text-teal-700 hover:bg-teal-100'"
                            class="absolute right-2 top-2 px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 focus:outline-none shadow-2xs"
                            :title="listening ? 'Listening... click to stop' : 'Click to speak dispatch details'">
                        <i class="fas fa-microphone"></i>
                        <span class="text-[10px]" x-text="listening ? 'Listening...' : 'Voice'"></span>
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        :disabled="loading || !query.trim()"
                        class="px-5 py-3 bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition flex items-center gap-2 flex-shrink-0">
                    <i x-show="!loading" class="fas fa-wand-magic-sparkles"></i>
                    <i x-show="loading" class="fas fa-circle-notch fa-spin"></i>
                    <span class="hidden sm:inline">AI Parse & Estimate</span>
                    <span class="sm:hidden">Parse</span>
                </button>
            </div>
        </form>

        <!-- Quick 1-Tap Presets -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px] text-slate-500 scrollbar-none">
            <span class="font-bold text-slate-400 uppercase tracking-wider text-[9px] flex-shrink-0">Try:</span>
            <button type="button" @click="setPreset('Send 1kg documents to Pokhara to Hari 9841234567')" class="px-2.5 py-1 bg-slate-100 hover:bg-teal-50 hover:text-teal-800 rounded-lg text-slate-600 transition flex-shrink-0">
                📄 1kg Docs to Pokhara
            </button>
            <button type="button" @click="setPreset('Air cargo 5kg tea to Sydney Australia to John 61412000000')" class="px-2.5 py-1 bg-slate-100 hover:bg-sky-50 hover:text-sky-800 rounded-lg text-slate-600 transition flex-shrink-0">
                ✈️ 5kg Cargo to Sydney
            </button>
            <button type="button" @click="setPreset('Deliver fashion clothes with Rs 2800 COD to Biratnagar to Sita 9801000000')" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-800 rounded-lg text-slate-600 transition flex-shrink-0">
                💰 COD Parcel to Biratnagar
            </button>
        </div>
    </div>

    <!-- 3. AI Generated Review & 1-Tap Confirmation Card (Tap 2 & Tap 3) -->
    <template x-if="result">
        <div class="mt-5 p-4 sm:p-5 rounded-2xl bg-teal-50/50 border border-teal-200/80 transition animate-fade-in">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-teal-200/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-black text-slate-900" x-text="result.service_name"></h4>
                        <p class="text-[11px] text-teal-800">Route & Tariff verified by Chanda AI</p>
                    </div>
                </div>

                <!-- Chanda Voice Audio Playback -->
                <button type="button"
                        @click="playChandaSpeech()"
                        :disabled="playingAudio"
                        class="px-3 py-1.5 bg-white hover:bg-teal-100 text-teal-800 border border-teal-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i :class="playingAudio ? 'fas fa-volume-high animate-bounce text-teal-600' : 'fas fa-volume-low text-teal-600'"></i>
                    <span x-text="playingAudio ? 'Chanda Speaking...' : 'Listen to Voice Brief'"></span>
                </button>
            </div>

            <!-- Parameters Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-4 text-xs">
                <div class="bg-white p-2.5 rounded-xl border border-slate-200/70 shadow-2xs">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Destination</span>
                    <p class="font-black text-slate-900 text-xs mt-0.5 truncate" x-text="result.destination"></p>
                </div>
                <div class="bg-white p-2.5 rounded-xl border border-slate-200/70 shadow-2xs">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Consignee</span>
                    <p class="font-black text-slate-900 text-xs mt-0.5 truncate" x-text="result.recipient_name"></p>
                    <p class="text-[10px] text-slate-500 font-mono" x-text="result.recipient_phone"></p>
                </div>
                <div class="bg-white p-2.5 rounded-xl border border-slate-200/70 shadow-2xs">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Weight & Transit</span>
                    <p class="font-black text-slate-900 text-xs mt-0.5" x-text="result.weight + ' kg'"></p>
                    <p class="text-[10px] text-teal-700 font-semibold" x-text="result.transit_days"></p>
                </div>
                <div class="bg-white p-2.5 rounded-xl border border-slate-200/70 shadow-2xs">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Estimated Tariff</span>
                    <p class="font-black text-emerald-700 text-sm mt-0.5" x-text="'Rs. ' + result.estimated_cost"></p>
                    <p class="text-[10px] text-slate-400">Doorstep Collection Incl.</p>
                </div>
            </div>

            <!-- Final 1-Tap Action -->
            <div class="flex items-center justify-between gap-3 pt-2">
                <p class="text-[11px] text-slate-600 hidden sm:block">
                    <i class="fas fa-fingerprint text-teal-600 mr-1"></i> Tap confirm to generate Pickup OTP and dispatch rider fleet.
                </p>
                <a :href="'{{ route('shipments.create') }}?mode=' + result.service_tier + '&receiver_city=' + encodeURIComponent(result.destination) + '&receiver_name=' + encodeURIComponent(result.recipient_name) + '&receiver_phone=' + encodeURIComponent(result.recipient_phone) + '&weight=' + result.weight"
                   class="w-full sm:w-auto px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    <span>Confirm & Dispatch Rider (1-Tap)</span>
                </a>
            </div>
        </div>
    </template>
</div>
