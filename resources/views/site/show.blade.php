<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $business->name }} — {{ $business->tagline ?? ($business->city ? 'Official Website in ' . $business->city : 'Official Website') }}</title>

    <meta name="description" content="{{ $business->description ?? 'Official website for ' . $business->name }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Space Grotesk', sans-serif; }

        @php
            $colors = [
                'indigo' => ['primary' => '#4f46e5', 'primary_hover' => '#4338ca', 'light' => '#eef2ff', 'ring' => 'rgba(79, 70, 229, 0.2)'],
                'emerald' => ['primary' => '#059669', 'primary_hover' => '#047857', 'light' => '#ecfdf5', 'ring' => 'rgba(5, 150, 105, 0.2)'],
                'rose' => ['primary' => '#e11d48', 'primary_hover' => '#be123c', 'light' => '#fff1f2', 'ring' => 'rgba(225, 29, 72, 0.2)'],
                'amber' => ['primary' => '#d97706', 'primary_hover' => '#b45309', 'light' => '#fffbeb', 'ring' => 'rgba(217, 119, 6, 0.2)'],
                'cyan' => ['primary' => '#0891b2', 'primary_hover' => '#0e7490', 'light' => '#ecfeff', 'ring' => 'rgba(8, 145, 178, 0.2)'],
                'slate' => ['primary' => '#334155', 'primary_hover' => '#1e293b', 'light' => '#f8fafc', 'ring' => 'rgba(51, 65, 85, 0.2)'],
            ];
            $theme = $colors[$business->theme_color] ?? $colors['indigo'];
        @endphp

        :root {
            --brand-primary: {{ $theme['primary'] }};
            --brand-hover: {{ $theme['primary_hover'] }};
            --brand-light: {{ $theme['light'] }};
            --brand-ring: {{ $theme['ring'] }};
        }

        .bg-brand { background-color: var(--brand-primary); }
        .bg-brand:hover { background-color: var(--brand-hover); }
        .text-brand { color: var(--brand-primary); }
        .border-brand { border-color: var(--brand-primary); }
        .bg-brand-light { background-color: var(--brand-light); }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased selection:bg-slate-900 selection:text-white">

    <!-- Preview Notification Banner (If preview mode or draft) -->
    @if(request()->has('preview') || $business->status === 'draft')
        <div class="bg-slate-900 text-white text-xs py-2 px-4 sticky top-0 z-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $business->status === 'published' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                <span>Mode: <strong>{{ strtoupper($business->status) }}</strong> — Live Website Preview</span>
            </div>
            <a href="{{ route('builder.edit', $business->slug) }}" class="underline hover:text-slate-200">Open Visual Editor & AI</a>
        </div>
    @endif

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#hero" class="flex items-center gap-2.5">
                <span class="text-xl sm:text-2xl font-black font-heading tracking-tight text-slate-900">
                    {{ $business->name }}
                </span>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#hero" class="hover:text-slate-900 transition-colors">Home</a>
                <a href="#about" class="hover:text-slate-900 transition-colors">About</a>
                <a href="#services" class="hover:text-slate-900 transition-colors">Services</a>
                <a href="#gallery" class="hover:text-slate-900 transition-colors">Gallery</a>
                <a href="#contact" class="hover:text-slate-900 transition-colors">Contact</a>
            </nav>

            <div class="flex items-center gap-3">
                @if($business->phone)
                    <a href="tel:{{ preg_replace('/[^0-9]/', '', $business->phone) }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-800 text-xs font-bold hover:bg-slate-50 transition-colors">
                        <i data-lucide="phone" class="w-3.5 h-3.5 text-brand"></i>
                        <span>{{ $business->phone }}</span>
                    </a>
                @endif
                <a href="#contact" class="px-5 py-2.5 rounded-xl bg-brand text-white text-xs font-bold shadow-md hover:opacity-95 transition-all">
                    Get in Touch
                </a>
            </div>
        </div>
    </header>

    <!-- Sections Renderer -->
    <main>
        @foreach($business->sections as $sec)
            @if($sec->section_key === 'hero')
                <!-- HERO SECTION -->
                <section id="hero" class="relative overflow-hidden pt-20 pb-28 bg-gradient-to-b from-slate-50/80 via-white to-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                        @if(!empty($sec->content['badge']))
                            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-light text-brand text-xs font-bold mb-6 shadow-sm border border-brand/10">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                {{ $sec->content['badge'] }}
                            </div>
                        @endif

                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black font-heading text-slate-900 tracking-tight max-w-4xl mx-auto leading-tight mb-6">
                            {{ $sec->title }}
                        </h1>

                        <p class="text-lg sm:text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed font-normal">
                            {{ $sec->subtitle }}
                        </p>

                        <div class="flex flex-wrap items-center justify-center gap-4">
                            @if(!empty($sec->content['cta_primary_text']))
                                <a href="{{ $sec->content['cta_primary_link'] ?? '#contact' }}" class="px-8 py-4 rounded-xl bg-brand text-white font-bold text-sm shadow-xl shadow-slate-900/10 hover:scale-105 transition-all">
                                    {{ $sec->content['cta_primary_text'] }}
                                </a>
                            @endif

                            @if(!empty($sec->content['cta_secondary_text']))
                                <a href="{{ $sec->content['cta_secondary_link'] ?? '#services' }}" class="px-8 py-4 rounded-xl border border-slate-300 bg-white text-slate-800 font-bold text-sm hover:bg-slate-50 transition-all">
                                    {{ $sec->content['cta_secondary_text'] }}
                                </a>
                            @endif
                        </div>
                    </div>
                </section>

            @elseif($sec->section_key === 'about')
                <!-- ABOUT SECTION -->
                <section id="about" class="py-24 bg-slate-50 border-y border-slate-100">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="max-w-3xl mb-16">
                            <span class="text-xs font-bold uppercase tracking-wider text-brand mb-2 block">Our Foundation</span>
                            <h2 class="text-3xl sm:text-4xl font-bold font-heading text-slate-900 mb-4">{{ $sec->title }}</h2>
                            <p class="text-base sm:text-lg text-slate-600 leading-relaxed">{{ $sec->subtitle }}</p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                            <div class="lg:col-span-7 bg-white p-8 sm:p-10 rounded-3xl border border-slate-200/80 shadow-sm leading-relaxed text-slate-700 text-base">
                                <p class="mb-4 whitespace-pre-line">{{ $sec->content['story'] ?? $business->description }}</p>
                                <div class="pt-6 border-t border-slate-100 flex items-center gap-4 text-xs font-semibold text-slate-500">
                                    <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Verified Information</span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4 text-brand"></i> {{ $business->city ?? 'Local' }}</span>
                                </div>
                            </div>

                            <div class="lg:col-span-5 space-y-4">
                                @if(!empty($sec->content['highlights']))
                                    @foreach($sec->content['highlights'] as $highlight)
                                        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-brand-light text-brand flex items-center justify-center shrink-0 mt-0.5">
                                                <i data-lucide="{{ $highlight['icon'] ?? 'star' }}" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-900 font-heading mb-1">{{ $highlight['title'] }}</h4>
                                                <p class="text-xs text-slate-500 leading-relaxed">{{ $highlight['description'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

            @elseif($sec->section_key === 'services')
                <!-- SERVICES / PRICING SECTION -->
                <section id="services" class="py-24 bg-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center max-w-2xl mx-auto mb-16">
                            <span class="text-xs font-bold uppercase tracking-wider text-brand mb-2 block">Offerings & Menu</span>
                            <h2 class="text-3xl sm:text-4xl font-bold font-heading text-slate-900 mb-4">{{ $sec->title }}</h2>
                            <p class="text-slate-600 text-sm sm:text-base">{{ $sec->subtitle }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @if(!empty($sec->content['items']))
                                @foreach($sec->content['items'] as $item)
                                    <div class="p-6 rounded-2xl border border-slate-200/90 bg-white hover:border-slate-300 hover:shadow-lg transition-all flex flex-col justify-between group">
                                        <div>
                                            <div class="flex items-start justify-between gap-2 mb-2">
                                                <h3 class="text-base font-bold text-slate-900 font-heading group-hover:text-brand transition-colors">
                                                    {{ $item['name'] }}
                                                </h3>
                                                @if(!empty($item['price']))
                                                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-slate-100 text-slate-900 shrink-0">
                                                        {{ $item['price'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                                                {{ $item['description'] ?? 'Crafted with top quality and care.' }}
                                            </p>
                                        </div>

                                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                                            <span class="text-[11px] text-slate-400 font-medium">{{ $item['category'] ?? 'Service' }}</span>
                                            <a href="#contact" class="text-brand font-semibold hover:underline flex items-center gap-1">
                                                Inquire <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </section>

            @elseif($sec->section_key === 'gallery')
                <!-- GALLERY SECTION -->
                <section id="gallery" class="py-24 bg-slate-50 border-t border-slate-100">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center max-w-2xl mx-auto mb-16">
                            <span class="text-xs font-bold uppercase tracking-wider text-brand mb-2 block">Our Craft</span>
                            <h2 class="text-3xl sm:text-4xl font-bold font-heading text-slate-900 mb-4">{{ $sec->title }}</h2>
                            <p class="text-slate-600 text-sm sm:text-base">{{ $sec->subtitle }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @if(!empty($sec->content['items']))
                                @foreach($sec->content['items'] as $photo)
                                    <div class="rounded-2xl overflow-hidden shadow-sm bg-white border border-slate-200 group">
                                        <div class="h-60 overflow-hidden bg-slate-100 relative">
                                            <img src="{{ $photo['url'] }}" alt="{{ $photo['caption'] ?? 'Photo' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                        </div>
                                        @if(!empty($photo['caption']))
                                            <div class="p-3 text-xs font-medium text-slate-600 text-center bg-white">
                                                {{ $photo['caption'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </section>

            @elseif($sec->section_key === 'contact')
                <!-- CONTACT & OPERATING HOURS SECTION -->
                <section id="contact" class="py-24 bg-white" x-data="contactFormHandler()">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                            <!-- Left: Contact Details & Hours -->
                            <div class="lg:col-span-5 space-y-8">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-brand mb-2 block">Connect With Us</span>
                                    <h2 class="text-3xl sm:text-4xl font-bold font-heading text-slate-900 mb-4">{{ $sec->title }}</h2>
                                    <p class="text-slate-600 text-sm leading-relaxed">{{ $sec->subtitle }}</p>
                                </div>

                                <div class="space-y-4">
                                    @if(!empty($sec->content['phone']))
                                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                            <div class="w-10 h-10 rounded-xl bg-brand-light text-brand flex items-center justify-center shrink-0">
                                                <i data-lucide="phone" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-400">Direct Phone</div>
                                                <a href="tel:{{ preg_replace('/[^0-9]/', '', $sec->content['phone']) }}" class="text-sm font-bold text-slate-900 hover:text-brand">
                                                    {{ $sec->content['phone'] }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($sec->content['email']))
                                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                            <div class="w-10 h-10 rounded-xl bg-brand-light text-brand flex items-center justify-center shrink-0">
                                                <i data-lucide="mail" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-400">Email Address</div>
                                                <a href="mailto:{{ $sec->content['email'] }}" class="text-sm font-bold text-slate-900 hover:text-brand">
                                                    {{ $sec->content['email'] }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($sec->content['address']))
                                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                            <div class="w-10 h-10 rounded-xl bg-brand-light text-brand flex items-center justify-center shrink-0">
                                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-400">Location</div>
                                                <div class="text-sm font-bold text-slate-900">{{ $sec->content['address'] }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($sec->content['hours']))
                                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                            <div class="w-10 h-10 rounded-xl bg-brand-light text-brand flex items-center justify-center shrink-0">
                                                <i data-lucide="clock" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-400">Hours of Operation</div>
                                                <div class="text-sm font-bold text-slate-900">{{ $sec->content['hours'] }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Right: Contact Inquiry Form (Saves to MySQL) -->
                            <div class="lg:col-span-7">
                                <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200/90 shadow-xl">
                                    <h3 class="text-xl font-bold font-heading text-slate-900 mb-2">Send a Message</h3>
                                    <p class="text-xs text-slate-500 mb-6">Leave your details and business staff will get back to you promptly.</p>

                                    <!-- Success Notice -->
                                    <div x-show="submitted" class="p-4 mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-3">
                                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                                        <div>
                                            <h4 class="font-bold">Message Delivered!</h4>
                                            <p class="text-xs mt-0.5" x-text="responseMsg"></p>
                                        </div>
                                    </div>

                                    <form @submit.prevent="submitInquiry($event)" x-show="!submitted" class="space-y-4 text-xs">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Your Name <span class="text-rose-500">*</span></label>
                                                <input type="text" name="name" required placeholder="John Doe" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-1 focus:ring-brand">
                                            </div>
                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Your Email <span class="text-rose-500">*</span></label>
                                                <input type="email" name="email" required placeholder="john@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-1 focus:ring-brand">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block font-bold text-slate-700 mb-1">Phone Number (Optional)</label>
                                            <input type="text" name="phone" placeholder="(555) 000-0000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-1 focus:ring-brand">
                                        </div>

                                        <div>
                                            <label class="block font-bold text-slate-700 mb-1">Message or Inquiries <span class="text-rose-500">*</span></label>
                                            <textarea name="message" rows="4" required placeholder="How can we help you?" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-1 focus:ring-brand"></textarea>
                                        </div>

                                        <button type="submit" :disabled="loading" class="w-full py-3 rounded-xl bg-brand text-white font-bold text-xs shadow-md hover:opacity-95 transition-all flex items-center justify-center gap-2">
                                            <span x-show="!loading">Send Message</span>
                                            <span x-show="loading">Sending...</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endforeach
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white py-12 border-t border-slate-800 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                <h4 class="text-base font-bold font-heading mb-1">{{ $business->name }}</h4>
                <p class="text-slate-400 text-xs">
                    {{ $business->city ? 'Proudly serving ' . $business->city : 'Dedicated to quality service' }}
                </p>
            </div>

            @if(!empty($business->social_links))
                <div class="flex items-center gap-4 text-slate-400">
                    @foreach($business->social_links as $platform => $link)
                        <a href="{{ $link }}" target="_blank" rel="noopener" class="hover:text-white transition-colors" title="{{ $platform }}">
                            <span class="capitalize">{{ $platform }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="text-slate-500 text-[11px] flex flex-col sm:flex-row items-center gap-2">
                <span>© {{ date('Y') }} {{ $business->name }}. All rights reserved.</span>
                <span class="hidden sm:inline">•</span>
                <span class="inline-flex items-center gap-1 text-emerald-400">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    TruthLens™ Verified Facts
                </span>
            </div>
        </div>
    </footer>

    <script>
    function contactFormHandler() {
        return {
            loading: false,
            submitted: false,
            responseMsg: '',

            async submitInquiry(event) {
                this.loading = true;
                const form = event.target;
                const formData = new FormData(form);

                try {
                    const res = await fetch("{{ route('site.inquire', $business->slug) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            name: formData.get('name'),
                            email: formData.get('email'),
                            phone: formData.get('phone'),
                            message: formData.get('message'),
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.submitted = true;
                        this.responseMsg = data.message;
                    } else {
                        alert('Error sending message. Please try again.');
                    }
                } catch (e) {
                    alert('Submission failed.');
                } finally {
                    this.loading = false;
                }
            }
        }
    }
    </script>
</body>
</html>
