{{-- resources/views/components/ai-copilot-widget.blade.php --}}
@php
    $userName = auth()->check() ? auth()->user()->name : 'Valued Client';
    $firstName = auth()->check() ? explode(' ', trim(auth()->user()->name))[0] : 'Client';
    $userRole = auth()->check() ? (auth()->user()->user_type_label ?? auth()->user()->user_type) : 'Guest';
@endphp

<div id="netpack-ai-copilot" 
     x-data="aiCopilotWidget({
         userName: '{{ addslashes($userName) }}',
         firstName: '{{ addslashes($firstName) }}',
         userRole: '{{ addslashes($userRole) }}',
         csrfToken: '{{ csrf_token() }}'
     })"
     x-init="initWidget()"
     x-cloak
     class="fixed bottom-6 right-6 z-50 font-sans select-none print:hidden">

    <!-- FLOATING AVATAR LAUNCH BUTTON -->
    <div x-show="!isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-75 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="relative flex items-center group">

        <!-- Proactive Speech Bubble / Tooltip -->
        <div x-show="showGreetingPill"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute right-20 bottom-1 hidden sm:flex items-center gap-2.5 bg-slate-900/95 text-white px-4 py-2.5 rounded-2xl shadow-2xl border border-teal-500/40 backdrop-blur-md whitespace-nowrap">
            <span class="text-base animate-bounce" x-text="currentGestureIcon">👋</span>
            <div class="flex flex-col text-left">
                <span class="text-xs font-bold text-teal-300 flex items-center gap-1.5">
                    <span>Namaste, <span x-text="firstName"></span>!</span>
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                </span>
                <span class="text-[11px] text-slate-300" x-text="pillText">How can I assist your deliveries today?</span>
            </div>
            <button @click.stop="showGreetingPill = false" class="text-slate-400 hover:text-white ml-1 text-xs">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- The Glowing Master Bubble -->
        <button @click="toggleDrawer()" 
                type="button"
                aria-label="Open AI Logistics Copilot"
                class="relative flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-tr from-slate-950 via-teal-900 to-teal-600 text-white shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 ring-4 ring-teal-500/20 group-hover:ring-teal-400/40 focus:outline-none">
            
            <!-- Animated Aura Glow -->
            <span class="absolute inset-0 rounded-full bg-teal-400 opacity-20 blur-md group-hover:opacity-40 transition animate-pulse"></span>

            <!-- Center Avatar / Gesture Icon -->
            <div class="relative flex items-center justify-center">
                <template x-if="isSpeaking">
                    <!-- Equalizer wave bars when speaking -->
                    <div class="flex items-center gap-0.5 h-6">
                        <span class="w-1 bg-teal-300 rounded-full animate-[soundWave_0.8s_ease-in-out_infinite]"></span>
                        <span class="w-1 bg-emerald-300 rounded-full animate-[soundWave_0.6s_ease-in-out_infinite_0.2s]"></span>
                        <span class="w-1 bg-teal-200 rounded-full animate-[soundWave_0.9s_ease-in-out_infinite_0.4s]"></span>
                        <span class="w-1 bg-teal-400 rounded-full animate-[soundWave_0.7s_ease-in-out_infinite_0.1s]"></span>
                    </div>
                </template>
                <template x-if="!isSpeaking && isListening">
                    <!-- Microphone listening radar -->
                    <div class="flex items-center justify-center text-rose-400 animate-pulse">
                        <i class="fas fa-microphone-lines text-2xl"></i>
                    </div>
                </template>
                <template x-if="!isSpeaking && !isListening">
                    <!-- Default AI Sparkle / Robot Icon -->
                    <div class="flex items-center justify-center text-teal-200 group-hover:text-white transition">
                        <i class="fas fa-robot text-2xl group-hover:rotate-12 transition transform duration-300"></i>
                    </div>
                </template>
            </div>

            <!-- Online Badge -->
            <span class="absolute bottom-0 right-0 w-4 h-4 bg-emerald-500 border-2 border-slate-900 rounded-full shadow-xs flex items-center justify-center">
                <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
            </span>

            <!-- Occasion / Notification Alert Dot -->
            <template x-if="activeOccasion">
                <span class="absolute -top-1 -right-1 flex h-5 w-5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-5 w-5 bg-gradient-to-r from-amber-500 to-rose-500 text-white text-[10px] font-black items-center justify-center shadow-xs">
                        <i class="fas fa-bell text-[9px]"></i>
                    </span>
                </span>
            </template>
        </button>
    </div>

    <!-- EXPANDABLE GLASSMORPHISM AI CHAT DRAWER -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-95"
         class="w-[94vw] sm:w-[440px] max-h-[85vh] h-[640px] flex flex-col bg-slate-900/95 text-slate-100 rounded-3xl shadow-2xl border border-teal-500/40 backdrop-blur-xl overflow-hidden ring-1 ring-white/10">

        <!-- HEADER -->
        <div class="px-5 py-3.5 bg-gradient-to-r from-slate-950 via-teal-950 to-slate-900 border-b border-teal-900/60 flex items-center justify-between gap-3 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Dynamic Gesture Avatar Circle -->
                <div class="relative w-10 h-10 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-400 p-0.5 shadow-md flex-shrink-0">
                    <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-lg">
                        <span x-text="currentGestureIcon">🤖</span>
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 border-2 border-slate-950 rounded-full"></span>
                </div>

                <div class="flex flex-col min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-black text-white tracking-tight truncate">NETPACK AI Copilot</h3>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 uppercase tracking-wider">
                            Voice & Chat
                        </span>
                    </div>
                    <p class="text-[11px] text-teal-400/80 font-medium truncate flex items-center gap-1.5">
                        <span x-text="statusMessage">Namaste, ready to assist</span>
                    </p>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <!-- Voice Audio Toggle Button -->
                <button @click="toggleVoice()" 
                        type="button"
                        :title="voiceEnabled ? 'Voice responses active (Click to mute)' : 'Voice muted (Click to enable speech)'"
                        class="p-2 rounded-xl text-xs transition"
                        :class="voiceEnabled ? 'bg-teal-500/20 text-teal-300 hover:bg-teal-500/30' : 'bg-slate-800 text-slate-400 hover:text-slate-200'">
                    <i class="fas" :class="voiceEnabled ? (isSpeaking ? 'fa-volume-high text-emerald-400 animate-pulse' : 'fa-volume-high') : 'fa-volume-xmark'"></i>
                </button>

                <!-- Full Page Link -->
                <a href="{{ route('ai.assistant') }}" 
                   title="Open Fullscreen Logistics Command Center"
                   class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs transition">
                    <i class="fas fa-up-right-and-down-left-from-center"></i>
                </a>

                <!-- Minimize Button -->
                <button @click="toggleDrawer()" 
                        type="button"
                        title="Minimize"
                        class="p-2 rounded-xl bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-300 text-xs transition">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>

        <!-- FESTIVAL & LOGISTICS SCHEDULE ALERT RIBBON (Proactive Notice) -->
        <template x-if="activeOccasion">
            <div class="bg-gradient-to-r from-amber-500/15 via-rose-500/15 to-teal-500/15 border-b border-amber-500/30 px-4 py-2 flex items-center justify-between text-xs text-amber-200 flex-shrink-0">
                <div class="flex items-center gap-2 truncate">
                    <span class="text-sm">🗓️</span>
                    <span class="font-bold truncate text-[11px]" x-text="activeOccasion.name"></span>
                    <span class="text-[10px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded uppercase font-semibold" x-text="activeOccasion.impact_level"></span>
                </div>
                <button @click="askQuestion('Tell me about ' + activeOccasion.name + ' shipping schedules and cutoffs')" 
                        class="text-[10px] text-teal-300 hover:underline font-bold whitespace-nowrap ml-2">
                    View Cutoff &rarr;
                </button>
            </div>
        </template>

        <!-- CHAT MESSAGE FEED -->
        <div id="ai-chat-feed" class="flex-1 p-4 overflow-y-auto space-y-3.5 scroll-smooth text-xs select-text">
            
            <template x-for="(msg, idx) in messages" :key="idx">
                <div class="flex flex-col" :class="msg.role === 'user' ? 'items-end' : 'items-start'">
                    <!-- Message Bubble -->
                    <div class="max-w-[88%] rounded-2xl p-3.5 transition-all shadow-sm"
                         :class="msg.role === 'user' 
                             ? 'bg-gradient-to-r from-teal-600 to-teal-700 text-white rounded-tr-xs' 
                             : 'bg-slate-800/90 text-slate-100 rounded-tl-xs border border-slate-700/60'">
                        
                        <!-- Assistant Header with Gesture & Speaker -->
                        <template x-if="msg.role === 'assistant'">
                            <div class="flex items-center justify-between gap-2 mb-1.5 pb-1 border-b border-slate-700/40 text-[10px] text-teal-400 font-semibold">
                                <span class="flex items-center gap-1">
                                    <span x-text="msg.gestureIcon || '✨'"></span>
                                    <span>NETPACK Logistics AI</span>
                                </span>
                                <button @click="speakText(msg.speechText || msg.content)" 
                                        type="button" 
                                        title="Listen to this message"
                                        class="hover:text-teal-200 p-0.5 transition">
                                    <i class="fas fa-volume-high"></i>
                                </button>
                            </div>
                        </template>

                        <!-- Content Rendered as HTML or Formatted Text -->
                        <div class="prose prose-invert prose-xs max-w-none leading-relaxed break-words" x-html="renderMessageHtml(msg.content)"></div>

                        <!-- Action Buttons (if attached) -->
                        <template x-if="msg.actions && msg.actions.length > 0">
                            <div class="mt-2.5 pt-2 border-t border-slate-700/50 flex items-center gap-2 flex-wrap">
                                <template x-for="(act, actIdx) in msg.actions" :key="actIdx">
                                    <a :href="act.url" 
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-500/20 hover:bg-teal-500/30 text-teal-300 border border-teal-500/30 font-bold text-[10px] transition">
                                        <span x-text="act.label"></span>
                                        <i class="fas fa-arrow-right text-[8px]"></i>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>

                    <span class="text-[9px] text-slate-500 mt-1 px-1" x-text="msg.time"></span>
                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex items-center gap-2 text-slate-400 p-2">
                <div class="w-2 h-2 rounded-full bg-teal-400 animate-ping"></div>
                <span class="text-[11px] font-medium" x-text="loadingStatusText">Analyzing logistics network...</span>
            </div>
        </div>

        <!-- QUICK ACTION CHIPS -->
        <div class="px-4 py-2 bg-slate-950/60 border-t border-slate-800/80 flex items-center gap-1.5 overflow-x-auto no-scrollbar flex-shrink-0">
            <template x-for="(chip, idx) in suggestionChips" :key="idx">
                <button @click="askQuestion(chip.prompt)" 
                        type="button"
                        class="px-2.5 py-1 rounded-full bg-slate-800 hover:bg-teal-900/60 hover:text-teal-200 border border-slate-700/60 text-slate-300 text-[10px] font-medium whitespace-nowrap transition flex items-center gap-1">
                    <span x-text="chip.label"></span>
                </button>
            </template>
        </div>

        <!-- VOICE EQUALIZER BAR (Visible when listening or speaking) -->
        <div x-show="isListening || isSpeaking" 
             x-transition
             class="px-4 py-1.5 bg-teal-950/50 border-t border-teal-800/40 flex items-center justify-between text-[11px] text-teal-300 flex-shrink-0">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2" :class="isListening ? 'bg-rose-500' : 'bg-emerald-400'"></span>
                </span>
                <span class="font-bold text-[10px]" x-text="isListening ? 'Listening to your voice... Speak clearly' : 'Speaking response...'"></span>
            </div>

            <!-- Equalizer Animation -->
            <div class="flex items-center gap-1">
                <span class="w-1 bg-teal-400 rounded-full animate-[soundWave_0.6s_ease-in-out_infinite]"></span>
                <span class="w-1 bg-emerald-400 rounded-full animate-[soundWave_0.4s_ease-in-out_infinite_0.1s]"></span>
                <span class="w-1 bg-teal-300 rounded-full animate-[soundWave_0.7s_ease-in-out_infinite_0.2s]"></span>
                <span class="w-1 bg-teal-500 rounded-full animate-[soundWave_0.5s_ease-in-out_infinite_0.3s]"></span>
                <template x-if="isSpeaking">
                    <button @click="stopSpeaking()" class="ml-2 text-[10px] text-slate-400 hover:text-white underline">
                        Stop Voice
                    </button>
                </template>
            </div>
        </div>

        <!-- INPUT BAR (Voice Microphone + Written Text) -->
        <div class="p-3 bg-slate-950 border-t border-slate-800/80 flex items-end gap-2 flex-shrink-0">
            <!-- Voice Input Button (Speech-to-Text) -->
            <button @click="toggleSpeechRecognition()" 
                    type="button"
                    :title="isListening ? 'Stop Listening' : 'Click to Speak (Voice Input)'"
                    class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all duration-200 flex-shrink-0 focus:outline-none"
                    :class="isListening 
                        ? 'bg-rose-500 text-white shadow-lg ring-4 ring-rose-500/30 animate-pulse' 
                        : 'bg-slate-800 hover:bg-teal-600/30 hover:text-teal-300 text-slate-400'">
                <i class="fas" :class="isListening ? 'fa-stop text-sm' : 'fa-microphone text-base'"></i>
            </button>

            <!-- Text Input Area -->
            <div class="flex-1 bg-slate-900 border border-slate-700/80 rounded-2xl px-3 py-2 flex items-center gap-2 focus-within:border-teal-500 focus-within:ring-1 focus-within:ring-teal-500 transition">
                <textarea x-model="inputText" 
                          @keydown.enter.prevent="handleEnterPress($event)"
                          placeholder="Ask about door-to-door delivery, tracking, rates, or OTP..." 
                          rows="1"
                          class="w-full bg-transparent text-white placeholder-slate-400 text-xs focus:outline-none resize-none max-h-20 leading-relaxed"></textarea>
            </div>

            <!-- Send Button -->
            <button @click="sendUserMessage()" 
                    :disabled="!inputText.trim() || isLoading"
                    type="button"
                    title="Send message"
                    class="w-10 h-10 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-slate-950 font-bold flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed shadow-md transition flex-shrink-0 focus:outline-none">
                <i class="fas fa-paper-plane text-xs"></i>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes soundWave {
    0%, 100% { height: 4px; }
    50% { height: 18px; }
}
/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>

<script>
function aiCopilotWidget(config) {
    return {
        isOpen: false,
        showGreetingPill: true,
        inputText: '',
        messages: [],
        isLoading: false,
        isSpeaking: false,
        isListening: false,
        voiceEnabled: true,
        speechSynthesis: null,
        recognition: null,
        currentGestureIcon: '👋',
        pillText: 'How can I assist your door-to-door deliveries today?',
        statusMessage: 'Ready to assist',
        loadingStatusText: 'Analyzing logistics network...',
        firstName: config.firstName || 'Client',
        userName: config.userName || 'Valued Client',
        userRole: config.userRole || 'Guest',
        csrfToken: config.csrfToken,
        activeOccasion: null,
        suggestionChips: [
            { label: '🚪 Door-to-Door Guide', prompt: 'Explain the door-to-door delivery workflow with secret Pickup OTP and Delivery OTP.' },
            { label: '📍 Track Parcel', prompt: 'How can I track my shipment using HAWB or Consignment code?' },
            { label: '💰 Domestic Tariffs', prompt: 'What are the delivery rates from Kathmandu to Pokhara, Biratnagar, and other districts?' },
            { label: '🗓️ Dashain & Festive Cutoffs', prompt: 'What are the upcoming festival dates and shipping cutoff deadlines?' },
            { label: '💵 COD Limits & Cash', prompt: 'How does Cash on Delivery (COD) collection and tiered rider limits work?' }
        ],

        initWidget() {
            // Setup Speech Synthesis
            if ('speechSynthesis' in window) {
                this.speechSynthesis = window.speechSynthesis;
            }

            // Setup Speech Recognition (Browser Native Web Speech API)
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (SpeechRecognition) {
                this.recognition = new SpeechRecognition();
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.lang = 'en-US';

                this.recognition.onstart = () => {
                    this.isListening = true;
                    this.statusMessage = 'Listening to your voice...';
                };

                this.recognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript;
                    if (transcript) {
                        this.inputText = transcript;
                        this.sendUserMessage();
                    }
                };

                this.recognition.onerror = (event) => {
                    console.log('Speech recognition event:', event.error);
                    this.isListening = false;
                    this.statusMessage = 'Ready to assist';
                };

                this.recognition.onend = () => {
                    this.isListening = false;
                    if (!this.isLoading) {
                        this.statusMessage = 'Ready to assist';
                    }
                };
            }

            // Fetch dynamic greeting and occasions from backend
            this.fetchGreeting();

            // Auto-hide greeting pill after 14 seconds if not opened
            setTimeout(() => {
                this.showGreetingPill = false;
            }, 14000);
        },

        toggleDrawer() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.showGreetingPill = false;
                this.scrollToBottom();
                if (this.messages.length === 0) {
                    this.fetchGreeting();
                }
            }
        },

        fetchGreeting() {
            fetch('/ai/greeting')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data) {
                        const d = data.data;
                        this.pillText = d.time_of_day ? `${d.time_of_day}! Need delivery help?` : this.pillText;
                        
                        if (d.festive_notice) {
                            this.activeOccasion = {
                                name: d.festive_notice.occasion,
                                impact_level: d.festive_notice.impact,
                                notice: d.festive_notice.notice,
                                cutoff: d.festive_notice.cutoff
                            };
                            this.currentGestureIcon = '🎉';
                        }

                        if (d.quick_suggestions && d.quick_suggestions.length > 0) {
                            this.suggestionChips = d.quick_suggestions;
                        }

                        // Add Initial Greeting Message if empty
                        if (this.messages.length === 0) {
                            const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            this.messages.push({
                                role: 'assistant',
                                content: d.greeting,
                                speechText: d.greeting,
                                gestureIcon: d.gesture === 'celebrating' ? '🎉' : '🙏',
                                time: nowTime,
                                actions: [
                                    { label: 'Door-to-Door Guide', url: '/shipments/create' },
                                    { label: 'Rate Calculator', url: '/rates/inquiry' }
                                ]
                            });

                            // Optionally speak greeting if user interacts
                        }
                    }
                })
                .catch(err => {
                    console.log('AI Greeting fetch error:', err);
                    if (this.messages.length === 0) {
                        this.messages.push({
                            role: 'assistant',
                            content: `Namaste ${this.firstName}! 🙏 I am your NETPACK AI Logistics Copilot. How may I assist your door-to-door deliveries, cargo tariffs, or tracking today?`,
                            speechText: `Namaste ${this.firstName}! How may I assist your deliveries today?`,
                            gestureIcon: '👋',
                            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                        });
                    }
                });
        },

        sendUserMessage() {
            const query = this.inputText.trim();
            if (!query || this.isLoading) return;

            const timeNow = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Add user message to conversation
            this.messages.push({
                role: 'user',
                content: query,
                time: timeNow
            });

            this.inputText = '';
            this.isLoading = true;
            this.statusMessage = 'Searching logistics knowledge base...';
            this.scrollToBottom();

            // Prepare history payload for context
            const historyPayload = this.messages.slice(-6).map(m => ({
                role: m.role,
                content: m.content
            }));

            fetch('/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: query,
                    history: historyPayload
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isLoading = false;
                this.statusMessage = 'Ready to assist';

                if (data && data.response) {
                    const gestureIcon = data.gesture === 'celebrating' ? '🎉' 
                        : (data.gesture === 'alerting' ? '⚠️' 
                        : (data.gesture === 'thinking' ? '💡' : '🎙️'));

                    this.currentGestureIcon = gestureIcon;

                    this.messages.push({
                        role: 'assistant',
                        content: data.response,
                        speechText: data.speech_text || data.response,
                        gestureIcon: gestureIcon,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        actions: data.actions || []
                    });

                    this.scrollToBottom();

                    // Speak response if voice enabled
                    if (this.voiceEnabled && (data.speech_text || data.response)) {
                        this.speakText(data.speech_text || data.response);
                    }
                } else {
                    this.messages.push({
                        role: 'assistant',
                        content: 'Namaste! I am currently unable to fetch that data. Please try asking again or check our universal tracking page.',
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    });
                }
            })
            .catch(err => {
                this.isLoading = false;
                this.statusMessage = 'Ready to assist';
                console.error('AI Chat Error:', err);
                this.messages.push({
                    role: 'assistant',
                    content: 'Namaste! I encountered a temporary connection glitch. Please try again or explore our rate calculator and tracking radar.',
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                });
            });
        },

        askQuestion(prompt) {
            this.inputText = prompt;
            this.sendUserMessage();
        },

        handleEnterPress(event) {
            if (!event.shiftKey) {
                this.sendUserMessage();
            }
        },

        toggleSpeechRecognition() {
            if (!this.recognition) {
                alert('Voice speech recognition is supported in Google Chrome, Microsoft Edge, Safari, and other modern browsers.');
                return;
            }

            if (this.isListening) {
                this.recognition.stop();
                this.isListening = false;
            } else {
                // If assistant is currently speaking, stop it first
                this.stopSpeaking();
                try {
                    this.recognition.start();
                } catch(e) {
                    console.log('Recognition start error:', e);
                }
            }
        },

        toggleVoice() {
            this.voiceEnabled = !this.voiceEnabled;
            if (!this.voiceEnabled) {
                this.stopSpeaking();
            }
        },

        speakText(text) {
            if (!this.voiceEnabled || !('speechSynthesis' in window)) return;

            // Stop any ongoing speech
            window.speechSynthesis.cancel();

            // Sanitize text from markdown characters for speech
            const cleanText = text
                .replace(/[#*`_~[\]()]/g, ' ')
                .replace(/https?:\/\/\S+/g, '')
                .replace(/\s+/g, ' ')
                .trim();

            if (!cleanText) return;

            const utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.rate = 1.0;
            utterance.pitch = 1.0;

            // Choose natural English voice if available
            const voices = window.speechSynthesis.getVoices();
            const preferredVoice = voices.find(v => v.lang.startsWith('en') && (v.name.includes('Natural') || v.name.includes('Google') || v.name.includes('Samantha')));
            if (preferredVoice) {
                utterance.voice = preferredVoice;
            }

            utterance.onstart = () => {
                this.isSpeaking = true;
                this.statusMessage = 'Speaking...';
            };

            utterance.onend = () => {
                this.isSpeaking = false;
                this.statusMessage = 'Ready to assist';
            };

            utterance.onerror = () => {
                this.isSpeaking = false;
                this.statusMessage = 'Ready to assist';
            };

            window.speechSynthesis.speak(utterance);
        },

        stopSpeaking() {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
            this.isSpeaking = false;
            this.statusMessage = 'Ready to assist';
        },

        renderMessageHtml(markdown) {
            if (!markdown) return '';
            
            // Safe, lightweight markdown-to-HTML parser for chat bubbles
            let html = markdown
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                // Headers
                .replace(/^### (.*$)/gim, '<h4 class="text-xs font-bold text-teal-300 mt-2 mb-1">$1</h4>')
                .replace(/^#### (.*$)/gim, '<h5 class="text-[11px] font-bold text-amber-300 mt-1 mb-0.5">$1</h5>')
                // Bold & Italic
                .replace(/\*\*(.*?)\*\*/gim, '<strong class="font-bold text-white">$1</strong>')
                .replace(/\*(.*?)\*/gim, '<em class="italic text-teal-200">$1</em>')
                // Inline Code
                .replace(/`([^`]+)`/gim, '<code class="px-1 py-0.5 rounded bg-slate-900 text-teal-300 font-mono text-[10px]">$1</code>')
                // Bullet points
                .replace(/^\s*\*\s+(.*$)/gim, '<li class="ml-3 list-disc text-slate-200 my-0.5">$1</li>')
                .replace(/^\s*-\s+(.*$)/gim, '<li class="ml-3 list-disc text-slate-200 my-0.5">$1</li>')
                // Line breaks
                .replace(/\n\n/gim, '<br/><br/>')
                .replace(/\n/gim, '<br/>');

            return html;
        },

        scrollToBottom() {
            setTimeout(() => {
                const feed = document.getElementById('ai-chat-feed');
                if (feed) {
                    feed.scrollTop = feed.scrollHeight;
                }
            }, 80);
        }
    };
}
</script>
