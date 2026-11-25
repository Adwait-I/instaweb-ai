@extends('layouts.app', ['title' => 'Build My Website — InstaWeb AI'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8" x-data="websiteWizard()">

    <!-- Header Breadcrumb -->
    <div class="mb-6 flex items-center gap-2 text-xs text-slate-500">
        <a href="{{ route('builder.index') }}" class="hover:text-indigo-600">Websites</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold">New Website Generator</span>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden mb-12">
        <!-- Wizard Banner -->
        <div class="p-8 bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 text-white relative">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold backdrop-blur-sm mb-3">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    Multimodal Intake
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold font-heading mb-2">
                    Share What You Have
                </h1>
                <p class="text-indigo-100 text-sm leading-relaxed">
                    Any mix of inputs works. Provide whatever you have: business name, social links, Google listing, a price list file, brochure PDF, or photos.
                </p>
            </div>
        </div>

        <!-- 1-Click Quick Fill Edge Case Demos -->
        <div class="px-8 py-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
            <span class="font-bold text-slate-600 flex items-center gap-1.5">
                <i data-lucide="play-circle" class="w-4 h-4 text-indigo-600"></i>
                Try Edge Case Demo Presets:
            </span>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="fillPreset('pizzeria')" class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:border-indigo-500 hover:text-indigo-600 font-medium transition-colors">
                    🍕 Pizzeria (Conflicting Phones Demo)
                </button>
                <button type="button" @click="fillPreset('autocare')" class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:border-indigo-500 hover:text-indigo-600 font-medium transition-colors">
                    🚗 Auto Care (Social Scraper Block Demo)
                </button>
                <button type="button" @click="fillPreset('injection_test')" class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:border-indigo-500 hover:text-indigo-600 font-medium transition-colors">
                    🛡️ Prompt Injection Safeguard Demo
                </button>
            </div>
        </div>

        <!-- Form Form -->
        <form action="{{ route('builder.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
            @csrf

            <!-- Section 1: Business Identity -->
            <div>
                <h2 class="text-base font-bold font-heading text-slate-900 mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs flex items-center justify-center font-bold">1</span>
                    Business Name & Location
                </h2>
                <p class="text-xs text-slate-500 mb-4 ml-8">Who are you and where do you operate?</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 ml-8">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Business Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" required placeholder="e.g. Bella Napoli Trattoria" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City / Region</label>
                        <input type="text" name="city" x-model="form.city" placeholder="e.g. Austin, TX" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Business Category / Industry</label>
                        <input type="text" name="category" x-model="form.category" placeholder="e.g. Italian Restaurant, Roofing Contractor" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Website Color Accent</label>
                        <select name="theme_color" x-model="form.theme_color" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                            <option value="indigo">Indigo / Royal Blue</option>
                            <option value="emerald">Emerald / Forest Green</option>
                            <option value="rose">Rose / Crimson</option>
                            <option value="amber">Amber / Warm Terracotta</option>
                            <option value="cyan">Cyan / Oceanic</option>
                            <option value="slate">Slate / Modern Minimal</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Section 2: Online Links (Social & Google Business) -->
            <div>
                <h2 class="text-base font-bold font-heading text-slate-900 mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs flex items-center justify-center font-bold">2</span>
                    Online Presence & Links
                </h2>
                <p class="text-xs text-slate-500 mb-4 ml-8">We will read public data and handle anti-bot login walls gracefully.</p>

                <div class="space-y-4 ml-8">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Google Business Listing / Maps Link</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                            </div>
                            <input type="url" name="google_business_url" x-model="form.google_business_url" placeholder="https://maps.google.com/?cid=..." class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Social Media Links (One per line)</label>
                        <textarea name="social_links" x-model="form.social_links" rows="3" placeholder="https://instagram.com/mybusiness&#10;https://facebook.com/mybusinesspage" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm font-mono text-xs"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Accepts Instagram, Facebook, LinkedIn, TikTok, Twitter/X, Yelp, etc.</p>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Section 3: Direct Contact & Hours (Optional) -->
            <div>
                <h2 class="text-base font-bold font-heading text-slate-900 mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs flex items-center justify-center font-bold">3</span>
                    Contact & Hours (Optional if in files/links)
                </h2>
                <p class="text-xs text-slate-500 mb-4 ml-8">If unsure, leave blank and our extractor will parse them from your files.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 ml-8">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" x-model="form.phone" placeholder="(555) 234-5678" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" x-model="form.email" placeholder="contact@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Physical Street Address</label>
                        <input type="text" name="address" x-model="form.address" placeholder="124 Main Street, Suite 200" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Operating Hours</label>
                        <input type="text" name="opening_hours" x-model="form.opening_hours" placeholder="Mon-Fri: 9am-6pm, Sat: 10am-4pm, Sun: Closed" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm">
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Section 4: File Uploads -->
            <div>
                <h2 class="text-base font-bold font-heading text-slate-900 mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs flex items-center justify-center font-bold">4</span>
                    Upload Files (PDF, Price List, Photos)
                </h2>
                <p class="text-xs text-slate-500 mb-4 ml-8">Drop your menus, service brochures, price sheets (CSV/TXT), or business photos.</p>

                <div class="ml-8">
                    <div class="border-2 border-dashed border-slate-300 hover:border-indigo-400 rounded-2xl p-6 text-center bg-slate-50/50 hover:bg-indigo-50/20 transition-all cursor-pointer relative" @click="$refs.fileInput.click()">
                        <input type="file" name="files[]" multiple x-ref="fileInput" @change="handleFiles($event)" class="hidden" accept=".pdf,.txt,.csv,.json,.jpg,.jpeg,.png,.webp">
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 mb-1">Click to browse or drag and drop files here</p>
                        <p class="text-xs text-slate-400">PDF brochures, Price Lists (.csv, .txt, .json), Photos (.jpg, .png)</p>
                    </div>

                    <!-- Selected Files List -->
                    <div x-show="selectedFiles.length > 0" class="mt-4 space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Selected Files (<span x-text="selectedFiles.length"></span>)</h4>
                        <template x-for="(file, index) in selectedFiles" :key="index">
                            <div class="flex items-center justify-between p-2.5 bg-slate-100 rounded-xl text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <i data-lucide="file" class="w-4 h-4 text-slate-500 shrink-0"></i>
                                    <span class="font-medium text-slate-800 truncate" x-text="file.name"></span>
                                    <span class="text-[10px] text-slate-400 shrink-0" x-text="'(' + formatSize(file.size) + ')'"></span>
                                </div>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-white text-indigo-600 font-semibold border border-slate-200" x-text="file.name.split('.').pop().toUpperCase()"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-200 flex items-center justify-between">
                <a href="{{ route('builder.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                    Cancel
                </a>

                <button type="submit" class="px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-lg shadow-indigo-500/25 hover:scale-[1.02] transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Generate Website with AI</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function websiteWizard() {
    return {
        form: {
            name: '',
            city: '',
            category: '',
            theme_color: 'indigo',
            google_business_url: '',
            social_links: '',
            phone: '',
            email: '',
            address: '',
            opening_hours: '',
        },
        selectedFiles: [],
        handleFiles(e) {
            this.selectedFiles = Array.from(e.target.files);
        },
        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },
        fillPreset(type) {
            if (type === 'pizzeria') {
                this.form.name = 'Bella Napoli Trattoria & Pizzeria';
                this.form.city = 'Austin, TX';
                this.form.category = 'Authentic Neapolitan Pizza & Italian Dining';
                this.form.theme_color = 'amber';
                this.form.phone = '(512) 555-0199'; // Note: differs from social link to trigger conflict detector!
                this.form.email = 'ciao@bellanapoliaustin.com';
                this.form.address = '408 Congress Ave, Austin, TX 78701';
                this.form.opening_hours = 'Tue-Sun: 11:30 AM – 10:00 PM (Closed Monday)';
                this.form.google_business_url = 'https://maps.google.com/?q=Bella+Napoli+Trattoria+Austin';
                this.form.social_links = "https://instagram.com/bellanapoliaustin\nhttps://facebook.com/bellanapoliaustin";
            } else if (type === 'autocare') {
                this.form.name = 'Apex Precision Auto Care';
                this.form.city = 'Denver, CO';
                this.form.category = 'Auto Repair, Tuning & Diagnostics';
                this.form.theme_color = 'emerald';
                this.form.phone = '(303) 555-8821';
                this.form.email = 'service@apexprecisionauto.com';
                this.form.address = '2100 Broadway, Denver, CO 80205';
                this.form.opening_hours = 'Mon-Fri: 7:30 AM – 6:00 PM';
                this.form.google_business_url = 'https://maps.google.com/?q=Apex+Auto+Denver';
                this.form.social_links = "https://instagram.com/apexprecisionauto\nhttps://linkedin.com/company/apex-auto-repair";
            } else if (type === 'injection_test') {
                this.form.name = 'SecureTrust Legal Partners';
                this.form.city = 'Chicago, IL';
                this.form.category = 'Corporate & Intellectual Property Law';
                this.form.theme_color = 'slate';
                this.form.phone = '(312) 555-3344';
                this.form.email = 'inquiries@securetrustlegal.com';
                this.form.address = '100 N LaSalle St, Chicago, IL 60602';
                this.form.opening_hours = 'Mon-Fri: 8:30 AM – 5:30 PM';
                this.form.google_business_url = 'https://maps.google.com/?q=SecureTrust+Legal+Chicago';
                this.form.social_links = "https://linkedin.com/company/securetrustlegal";
            }
        }
    }
}
</script>
@endpush
@endsection
