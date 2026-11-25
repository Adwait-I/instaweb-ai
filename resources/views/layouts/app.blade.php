<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'InstaWeb AI — AI Builds My Website' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased selection:bg-indigo-500 selection:text-white" x-data="globalApp()">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('builder.index') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-slate-900 tracking-tight font-heading flex items-center gap-1.5">
                            InstaWeb <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 font-semibold border border-indigo-200/60">AI</span>
                        </span>
                        <p class="text-[10px] text-slate-500 -mt-1 font-medium">AI Builds My Website</p>
                    </div>
                </a>
            </div>

            <nav class="flex items-center gap-2 sm:gap-4">
                <!-- API Key Manager Trigger Button -->
                <button type="button" @click="showKeyModal = true" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 flex items-center gap-1.5 transition-colors" title="Manage Gemini API Key">
                    <i data-lucide="key" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span class="hidden sm:inline">Gemini Key</span>
                    <span class="w-2 h-2 rounded-full {{ config('services.gemini.api_key') ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
                </button>

                <a href="{{ route('builder.index') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-lg hover:bg-slate-100 transition-colors flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">My Websites</span>
                </a>
                <a href="{{ route('builder.create') }}" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm hover:shadow-indigo-500/25 transition-all flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Build Website</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="p-4 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm" role="alert">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm" role="alert">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <div class="font-semibold flex items-center gap-2 mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    Please correct the following errors:
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 pb-16">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-700">InstaWeb AI</span>
                <span>•</span>
                <span>Laravel + MySQL + Gemini AI</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span class="flex items-center gap-1"><i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i> Zero Hallucination Policy</span>
                <span>•</span>
                <span>Prompt Injection Safeguard</span>
            </div>
        </div>
    </footer>

    <!-- Gemini API Key Settings Modal -->
    <div x-show="showKeyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl overflow-hidden" @click.outside="showKeyModal = false">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="key" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold font-heading text-slate-900">Gemini API Key</h3>
                        <p class="text-xs text-slate-500">Configure your Google Gemini AI key.</p>
                    </div>
                </div>
                <button type="button" @click="showKeyModal = false" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-600">
                    <p class="mb-2">
                        Get a <strong>free API key</strong> with zero credit card required at 
                        <a href="https://aistudio.google.com/" target="_blank" class="text-indigo-600 font-semibold underline">Google AI Studio</a>.
                    </p>
                    <p class="text-[11px] text-slate-500">
                        When configured, InstaWeb AI uses live Gemini multimodal models. When unconfigured or offline, it seamlessly uses the deterministic zero-hallucination fact engine.
                    </p>
                </div>

                @if(config('services.gemini.api_key'))
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] flex items-center justify-between">
                        <span class="flex items-center gap-1.5 font-semibold">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                            Key Configured: {{ substr(config('services.gemini.api_key'), 0, 6) }}...{{ substr(config('services.gemini.api_key'), -4) }}
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 font-bold uppercase">{{ config('services.gemini.model', 'gemini-2.5-flash') }}</span>
                    </div>
                @endif

                <form @submit.prevent="saveApiKey($event)" class="space-y-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Enter Gemini API Key (starts with AIzaSy...)</label>
                        <input type="password" name="api_key" placeholder="AIzaSy..." required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Model Selection</label>
                        <select name="model" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-1 focus:ring-indigo-500">
                            <option value="gemini-2.5-flash">Gemini 2.5 Flash (Recommended - Fast & Grounded)</option>
                            <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
                            <option value="gemini-2.0-flash">Gemini 2.0 Flash</option>
                        </select>
                    </div>

                    <div x-show="keyError" class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px]" x-text="keyError"></div>
                    <div x-show="keySuccess" class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px]" x-text="keySuccess"></div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showKeyModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold">
                            Cancel
                        </button>
                        <button type="submit" :disabled="verifyingKey" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold disabled:opacity-50 flex items-center gap-1.5">
                            <span x-show="!verifyingKey">Verify & Save Key</span>
                            <span x-show="verifyingKey">Verifying with Google...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function globalApp() {
        return {
            showKeyModal: false,
            verifyingKey: false,
            keyError: '',
            keySuccess: '',

            async saveApiKey(event) {
                this.verifyingKey = true;
                this.keyError = '';
                this.keySuccess = '';
                const form = event.target;
                const formData = new FormData(form);

                try {
                    const res = await fetch("{{ route('builder.settings.api_key.save') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            api_key: formData.get('api_key'),
                            model: formData.get('model'),
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.keySuccess = data.message;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    } else {
                        this.keyError = data.message || 'Verification failed.';
                    }
                } catch (e) {
                    this.keyError = 'Network error verifying API key.';
                } finally {
                    this.verifyingKey = false;
                }
            }
        }
    }
    </script>

    @stack('scripts')
</body>
</html>
