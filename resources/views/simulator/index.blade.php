<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NETPACK AI &bull; Cross-Platform Mobile App & PWA Simulator</title>
    <link rel="icon" type="image/png" href="/images/logo-icon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Realistic Device Frame Styling */
        .device-iphone {
            width: 393px;
            height: 852px;
            border-radius: 54px;
            box-shadow: 0 0 0 12px #1e293b, 0 0 0 14px #475569, 0 25px 60px -15px rgba(0,0,0,0.4);
            border: 4px solid #0f172a;
        }
        .device-samsung {
            width: 412px;
            height: 915px;
            border-radius: 38px;
            box-shadow: 0 0 0 10px #1e293b, 0 0 0 12px #334155, 0 25px 60px -15px rgba(0,0,0,0.4);
            border: 3px solid #0f172a;
        }
        .device-pixel {
            width: 412px;
            height: 892px;
            border-radius: 46px;
            box-shadow: 0 0 0 11px #1e293b, 0 0 0 13px #334155, 0 25px 60px -15px rgba(0,0,0,0.4);
            border: 3px solid #0f172a;
        }
        .device-landscape {
            transform: rotate(90deg);
        }
        .dynamic-island {
            width: 124px;
            height: 35px;
            background: #000000;
            border-radius: 20px;
            position: absolute;
            top: 11px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .camera-punchhole {
            width: 14px;
            height: 14px;
            background: #000000;
            border-radius: 50%;
            position: absolute;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
            border: 2px solid #1e293b;
        }
        .home-bar {
            width: 134px;
            height: 5px;
            background: #94a3b8;
            border-radius: 10px;
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col font-sans select-none overflow-x-hidden"
      x-data="{
          device: 'iphone',
          currentScreen: '/client/dashboard',
          isLandscape: false,
          scale: 0.9,
          networkOnline: true,
          battery: 98,
          carrier: 'NETPACK 5G',
          currentTime: '',
          swRegistered: false,
          pwaStatus: 'Ready (Standalone Mode)',

          init() {
              this.updateClock();
              setInterval(() => this.updateClock(), 1000);
              if ('serviceWorker' in navigator) {
                  navigator.serviceWorker.getRegistration().then(reg => {
                      this.swRegistered = !!reg;
                  });
              }
          },

          updateClock() {
              const now = new Date();
              let h = now.getHours();
              let m = now.getMinutes();
              if (m < 10) m = '0' + m;
              this.currentTime = h + ':' + m;
          },

          setScreen(url) {
              this.currentScreen = url;
              this.$refs.simFrame.src = url;
          },

          reloadFrame() {
              this.$refs.simFrame.src = this.currentScreen;
          },

          testPushAlert() {
              if ('Notification' in window) {
                  Notification.requestPermission().then(permission => {
                      if (permission === 'granted') {
                          new Notification('NETPACK AI Fleet Update', {
                              body: 'Consignment NP-9821 has reached Biratnagar Feeder Hub.',
                              icon: '/images/logo-icon.png'
                          });
                      } else {
                          alert('Please grant notification permission in your browser to test push alerts.');
                      }
                  });
              } else {
                  alert('Notifications API not supported in this browser.');
              }
          }
      }">

    <!-- Top Navigation Toolbar -->
    <header class="bg-slate-950/80 backdrop-blur-md border-b border-slate-800 px-4 py-3 flex items-center justify-between gap-4 sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <x-logo variant="white" size="sm" :href="route('client.dashboard')" />
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 tracking-wider uppercase">
                <i class="fas fa-mobile-screen text-teal-400"></i> App Simulator & Studio
            </span>
        </div>

        <!-- Center Controls: Device & Scale Selection -->
        <div class="hidden md:flex items-center gap-2 bg-slate-900 border border-slate-700/80 p-1 rounded-xl text-xs">
            <button type="button"
                    @click="device = 'iphone'"
                    :class="device === 'iphone' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <i class="fab fa-apple"></i> iPhone 16 Pro
            </button>
            <button type="button"
                    @click="device = 'samsung'"
                    :class="device === 'samsung' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <i class="fab fa-android"></i> Galaxy S24 Ultra
            </button>
            <button type="button"
                    @click="device = 'pixel'"
                    :class="device === 'pixel' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <i class="fab fa-google"></i> Pixel 9 Pro
            </button>
        </div>

        <!-- Right Quick Actions -->
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1 bg-slate-900 border border-slate-700/80 px-2 py-1 rounded-lg text-xs">
                <span class="text-[10px] text-slate-400">Zoom:</span>
                <button type="button" @click="scale = 0.8" :class="scale === 0.8 ? 'text-teal-400 font-bold' : 'text-slate-400'" class="px-1.5 hover:text-white">80%</button>
                <button type="button" @click="scale = 0.9" :class="scale === 0.9 ? 'text-teal-400 font-bold' : 'text-slate-400'" class="px-1.5 hover:text-white">90%</button>
                <button type="button" @click="scale = 1.0" :class="scale === 1.0 ? 'text-teal-400 font-bold' : 'text-slate-400'" class="px-1.5 hover:text-white">100%</button>
            </div>
            <a href="{{ route('client.dashboard') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg transition border border-slate-700 flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-[10px]"></i> Back to Portal
            </a>
        </div>
    </header>

    <!-- Main Simulator Area -->
    <div class="flex-1 flex flex-col lg:flex-row items-center justify-center p-4 sm:p-8 gap-8 overflow-y-auto">

        <!-- Left / Center: Interactive Phone Frame -->
        <div class="flex flex-col items-center">

            <!-- Device Shell Container -->
            <div class="relative transition-all duration-300 transform origin-top"
                 :style="'transform: scale(' + scale + ')'"
                 :class="{
                     'device-iphone': device === 'iphone',
                     'device-samsung': device === 'samsung',
                     'device-pixel': device === 'pixel'
                 }">

                <!-- Hardware Speaker / Island / Notch -->
                <template x-if="device === 'iphone'">
                    <div class="dynamic-island cursor-pointer group" title="iOS Dynamic Island &bull; NETPACK Live Tracking Active">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-ping"></span>
                        <span class="text-[9px] font-mono font-bold text-teal-300 truncate">NP-AI Fleet Live</span>
                        <div class="w-3 h-3 rounded-full bg-slate-800 border border-slate-700"></div>
                    </div>
                </template>

                <template x-if="device === 'samsung' || device === 'pixel'">
                    <div class="camera-punchhole"></div>
                </template>

                <!-- Phone Internal Screen (Iframe Container) -->
                <div class="w-full h-full overflow-hidden relative bg-white"
                     :class="{
                         'rounded-[48px]': device === 'iphone',
                         'rounded-[32px]': device === 'samsung',
                         'rounded-[40px]': device === 'pixel'
                     }">

                    <!-- Mobile Status Bar Overlay -->
                    <div class="absolute top-0 left-0 right-0 h-10 px-6 flex items-center justify-between text-[11px] font-semibold text-slate-800 bg-white/90 backdrop-blur-xs z-40 pointer-events-none select-none">
                        <span x-text="currentTime">9:41</span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-teal-700 font-bold" x-text="carrier"></span>
                            <i class="fas fa-wifi text-[10px]"></i>
                            <div class="flex items-center gap-0.5">
                                <span class="text-[10px]" x-text="battery + '%'"></span>
                                <i class="fas fa-battery-full text-xs text-emerald-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- The Live App Iframe -->
                    <iframe x-ref="simFrame"
                            :src="currentScreen"
                            class="w-full h-full border-0 pt-10 pb-6 bg-slate-50"
                            allow="geolocation; microphone; camera"></iframe>

                    <!-- iOS Home Bar / Android Gesture Pill -->
                    <div class="home-bar"></div>
                </div>
            </div>

            <!-- Device Action Pill beneath phone -->
            <div class="mt-4 flex items-center gap-2 bg-slate-950/80 border border-slate-800 px-4 py-2 rounded-2xl text-xs text-slate-400 shadow-md">
                <button type="button" @click="reloadFrame()" class="hover:text-teal-400 transition flex items-center gap-1.5" title="Reload Phone Screen">
                    <i class="fas fa-rotate"></i> Reload
                </button>
                <span class="text-slate-700">|</span>
                <span class="text-[11px] text-slate-500">Viewport: <span x-text="device === 'iphone' ? '393 x 852 px' : '412 x 915 px'" class="font-mono text-slate-300"></span></span>
            </div>
        </div>

        <!-- Right Side: Simulator Studio & Testing Telemetry -->
        <div class="w-full lg:max-w-md space-y-4">

            <!-- 1. Screen Switcher Console -->
            <div class="bg-slate-950/90 border border-slate-800 rounded-2xl p-4 shadow-sm">
                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2.5 flex items-center gap-2">
                    <i class="fas fa-compass text-teal-400"></i>
                    <span>Quick Screen Switcher</span>
                </h3>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button"
                            @click="setScreen('{{ route('client.dashboard') }}')"
                            :class="currentScreen.includes('client/dashboard') ? 'bg-teal-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800'"
                            class="p-2.5 rounded-xl border border-slate-700/60 text-left transition flex items-center gap-2">
                        <i class="fas fa-chart-pie text-teal-400"></i>
                        <span>Client Dashboard</span>
                    </button>

                    <button type="button"
                            @click="setScreen('{{ route('shipments.create') }}')"
                            :class="currentScreen.includes('shipments/create') ? 'bg-teal-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800'"
                            class="p-2.5 rounded-xl border border-slate-700/60 text-left transition flex items-center gap-2">
                        <i class="fas fa-box-archive text-amber-400"></i>
                        <span>3-Tap Dispatch</span>
                    </button>

                    <button type="button"
                            @click="setScreen('{{ route('tracking.page') }}')"
                            :class="currentScreen.includes('tracking') ? 'bg-teal-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800'"
                            class="p-2.5 rounded-xl border border-slate-700/60 text-left transition flex items-center gap-2">
                        <i class="fas fa-radar text-sky-400"></i>
                        <span>Live Radar Map</span>
                    </button>

                    <button type="button"
                            @click="setScreen('{{ route('ai.assistant') }}')"
                            :class="currentScreen.includes('ai-assistant') ? 'bg-teal-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800'"
                            class="p-2.5 rounded-xl border border-slate-700/60 text-left transition flex items-center gap-2">
                        <i class="fas fa-robot text-teal-400"></i>
                        <span>Chanda AI Assistant</span>
                    </button>

                    <button type="button"
                            @click="setScreen('/offline.html')"
                            :class="currentScreen.includes('offline.html') ? 'bg-teal-600 text-white font-bold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800'"
                            class="p-2.5 rounded-xl border border-slate-700/60 text-left transition flex items-center gap-2 col-span-2">
                        <i class="fas fa-wifi-slash text-rose-400"></i>
                        <span>Test PWA Offline Fallback Screen</span>
                    </button>
                </div>
            </div>

            <!-- 2. PWA & Native Hardware Inspector -->
            <div class="bg-slate-950/90 border border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-microchip text-emerald-400"></i>
                    <span>PWA & Hardware Telemetry</span>
                </h3>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-400">Service Worker</span>
                        <span class="font-mono font-bold text-emerald-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Registered (v2.1.0)
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-400">PWA Manifest</span>
                        <span class="font-mono font-bold text-teal-400">Standalone (Light Theme)</span>
                    </div>

                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-400">Push Notifications</span>
                        <button type="button" @click="testPushAlert()" class="px-2 py-0.5 bg-teal-600 hover:bg-teal-500 text-white font-bold text-[10px] rounded-md transition shadow-2xs">
                            <i class="fas fa-bell mr-1"></i> Trigger Test Push
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-400">Background Sync</span>
                        <span class="font-mono font-bold text-amber-400">IndexedDB Queue Active</span>
                    </div>
                </div>
            </div>

            <!-- 3. Physical Device Testing (Live LAN Link) -->
            <div class="bg-gradient-to-br from-teal-950/50 via-slate-950 to-slate-950 border border-teal-500/30 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold text-teal-300 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-qrcode text-teal-400"></i>
                        <span>Test on Real Phone</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono">Wi-Fi / LAN</span>
                </div>
                <p class="text-[11px] text-slate-400 leading-snug mb-3">
                    Connect your iPhone or Android to the same Wi-Fi network and open the app on your physical device:
                </p>
                <div class="p-2.5 rounded-xl bg-slate-900 border border-teal-500/40 flex items-center justify-between text-xs font-mono text-teal-300 select-all">
                    <span>http://192.168.1.7:8000</span>
                    <button type="button" onclick="navigator.clipboard.writeText('http://192.168.1.7:8000'); alert('LAN address copied!');" class="text-slate-400 hover:text-white" title="Copy LAN URL">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            <!-- 4. Native App Compilation Instructions (Android APK & iOS) -->
            <div class="bg-slate-950/90 border border-slate-800 rounded-2xl p-4 shadow-sm text-xs">
                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="fab fa-android text-emerald-400"></i>
                    <i class="fab fa-apple text-slate-200"></i>
                    <span>Native Mobile Compilation</span>
                </h3>
                <p class="text-[11px] text-slate-400 mb-2 leading-relaxed">
                    Build native binaries for Google Play & App Store via Capacitor:
                </p>
                <div class="p-2 rounded-xl bg-slate-900 border border-slate-800 font-mono text-[10px] text-teal-300 space-y-1">
                    <p class="text-slate-400"># Run on Android Studio Emulator:</p>
                    <p>npx cap run android</p>
                    <p class="text-slate-400 pt-1"># Run on iOS Xcode Simulator:</p>
                    <p>npx cap run ios</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
