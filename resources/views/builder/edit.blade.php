@extends('layouts.app', ['title' => 'Visual Editor — ' . $business->name])

@section('content')
<div class="h-[calc(100vh-4rem)] flex flex-col bg-slate-100 overflow-hidden" x-data="visualEditor()">

    <!-- Top Action & Device Bar -->
    <div class="bg-white border-b border-slate-200 px-4 py-2.5 flex items-center justify-between gap-4 z-20 shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('builder.index') }}" class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors" title="Back to All Websites">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 font-heading truncate max-w-xs sm:max-w-md">
                        {{ $business->name }}
                    </h1>
                    <span id="statusBadge" class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $business->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ $business->status }}
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span>{{ $business->category ?? 'Website' }}</span>
                    <span>•</span>
                    <a href="{{ route('site.show', $business->slug) }}" target="_blank" class="text-indigo-600 hover:underline flex items-center gap-1 font-mono text-[11px]">
                        /site/{{ $business->slug }}
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Viewport Switcher -->
        <div class="hidden md:flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs text-slate-600">
            <button type="button" @click="viewport = 'desktop'" :class="viewport === 'desktop' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'hover:text-slate-900'" class="px-2.5 py-1 rounded-lg flex items-center gap-1.5 transition-all">
                <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
                <span>Desktop</span>
            </button>
            <button type="button" @click="viewport = 'tablet'" :class="viewport === 'tablet' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'hover:text-slate-900'" class="px-2.5 py-1 rounded-lg flex items-center gap-1.5 transition-all">
                <i data-lucide="tablet" class="w-3.5 h-3.5"></i>
                <span>Tablet</span>
            </button>
            <button type="button" @click="viewport = 'mobile'" :class="viewport === 'mobile' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'hover:text-slate-900'" class="px-2.5 py-1 rounded-lg flex items-center gap-1.5 transition-all">
                <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                <span>Mobile</span>
            </button>
        </div>

        <!-- Publish & Storefront Flyer Actions -->
        <div class="flex items-center gap-2">
            <a href="{{ route('builder.flyer', $business->slug) }}" target="_blank" class="px-3 py-1.5 rounded-xl border border-indigo-200 bg-indigo-50/50 hover:bg-indigo-100/70 text-indigo-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Storefront QR Flyer</span>
            </a>

            <button type="button" @click="copyPublicUrl()" class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                <span x-text="copied ? 'Copied URL!' : 'Copy Link'"></span>
            </button>

            <button type="button" @click="togglePublish()" :class="isPublished ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'" class="px-4 py-1.5 rounded-xl text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                <i :data-lucide="isPublished ? 'globe-2' : 'upload-cloud'" class="w-3.5 h-3.5"></i>
                <span x-text="isPublished ? 'Unpublish Site' : 'Publish Live'"></span>
            </button>
        </div>
    </div>

    <!-- Workspace Body: Split View -->
    <div class="flex-1 flex overflow-hidden">

        <!-- Left Column: Controls, TruthLens & Section Editors -->
        <div class="w-full md:w-[480px] lg:w-[520px] bg-white border-r border-slate-200 flex flex-col shrink-0 overflow-y-auto">

            <!-- TruthLens™ Real-Time Fact Citation & Audit Card -->
            <div class="p-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white shrink-0 shadow-inner">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/30 flex items-center justify-center text-indigo-400">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold font-heading tracking-wide uppercase text-indigo-200">
                                TruthLens™ Fact Audit
                            </span>
                            <div class="text-[10px] text-slate-300">Golden Rule Zero-Hallucination Shield</div>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-lg font-black font-heading text-emerald-400">{{ $audit['fact_integrity_score'] }}%</div>
                        <div class="text-[9px] uppercase tracking-wider text-slate-400">Grounded Score</div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-white/10 text-[11px]">
                    <span class="text-slate-300">
                        Risk: <strong class="text-emerald-400">{{ $audit['hallucination_risk'] }}</strong>
                        ({{ $audit['verified_claims_count'] }} verified claims)
                    </span>

                    <button type="button" @click="showAuditModal = true" class="text-indigo-300 hover:text-white font-semibold underline flex items-center gap-1 text-[11px]">
                        Inspect Citations <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>

            <!-- Edge Cases & Verification Center -->
            <div class="p-4 border-b border-slate-200 bg-slate-50/60 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-indigo-600"></i>
                        Source Discrepancies & Safeguards
                    </span>
                    <span class="text-[10px] text-slate-400">Verified Sources</span>
                </div>

                <!-- 1. Conflicting Sources Resolution Card -->
                @if(!empty($business->unresolved_conflicts))
                    @foreach($business->unresolved_conflicts as $conflict)
                        <div class="p-3.5 rounded-xl border {{ $conflict['resolved'] ?? false ? 'bg-emerald-50/80 border-emerald-200' : 'bg-amber-50 border-amber-200' }} text-xs">
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="git-branch" class="w-4 h-4 {{ $conflict['resolved'] ?? false ? 'text-emerald-600' : 'text-amber-600' }}"></i>
                                    {{ $conflict['title'] }}
                                </div>
                                @if($conflict['resolved'] ?? false)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">Resolved</span>
                                @else
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold">Needs Selection</span>
                                @endif
                            </div>
                            <p class="text-slate-600 mb-2">{{ $conflict['description'] }}</p>

                            @if(!($conflict['resolved'] ?? false))
                                <div class="space-y-1.5 mt-2">
                                    @foreach($conflict['options'] as $opt)
                                        <button type="button" @click="resolveConflict('{{ $conflict['id'] }}', '{{ $opt['display'] }}')" class="w-full text-left p-2 rounded-lg bg-white border border-amber-300 hover:border-indigo-600 hover:bg-indigo-50/40 transition-colors flex items-center justify-between group">
                                            <div>
                                                <div class="font-bold text-slate-900 group-hover:text-indigo-600">{{ $opt['display'] }}</div>
                                                <div class="text-[10px] text-slate-400">Found in: {{ $opt['source'] }}</div>
                                            </div>
                                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">Select This</span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-[11px] font-semibold text-emerald-800 flex items-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    Official value set to: <span class="font-bold">{{ $conflict['resolved_value'] }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif

                <!-- 2. Scrape Limitations and Safeguards Notices -->
                @if(!empty($business->verification_flags))
                    @foreach($business->verification_flags as $flag)
                        @if($flag['type'] === 'scrape_restricted')
                            <div class="p-3 rounded-xl bg-purple-50 border border-purple-200 text-xs">
                                <div class="font-bold text-purple-900 flex items-center gap-1.5 mb-1">
                                    <i data-lucide="lock" class="w-3.5 h-3.5 text-purple-600"></i>
                                    {{ $flag['title'] }}
                                </div>
                                <p class="text-purple-800 text-[11px] leading-relaxed">{{ $flag['message'] }}</p>
                            </div>
                        @elseif($flag['type'] === 'prompt_injection_neutralized')
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs">
                                <div class="font-bold text-rose-900 flex items-center gap-1.5 mb-1">
                                    <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-rose-600"></i>
                                    {{ $flag['title'] }}
                                </div>
                                <p class="text-rose-800 text-[11px] leading-relaxed">
                                    File <strong>{{ $flag['file'] }}</strong> contained instructions attempting to override system behavior. These were safely neutralized.
                                </p>
                            </div>
                        @elseif($flag['type'] === 'multiple_businesses_found')
                            <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-xs">
                                <div class="font-bold text-blue-900 flex items-center gap-1.5 mb-1">
                                    <i data-lucide="info" class="w-3.5 h-3.5 text-blue-600"></i>
                                    {{ $flag['title'] }}
                                </div>
                                <p class="text-blue-800 text-[11px] leading-relaxed">{{ $flag['message'] }}</p>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Sections Accordion -->
            <div class="p-4 space-y-4 flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Website Sections ({{ $business->sections->count() }})
                    </h3>
                    <span class="text-[11px] text-slate-400">Click to expand & edit</span>
                </div>

                <div class="space-y-3">
                    @foreach($business->sections as $sec)
                        <div class="border border-slate-200 rounded-2xl bg-white overflow-hidden shadow-sm" x-data="{ open: false, regenPrompt: '', loadingRegen: false, saving: false }">
                            <!-- Section Header -->
                            <div class="p-3.5 bg-slate-50/70 hover:bg-slate-100/70 flex items-center justify-between cursor-pointer transition-colors" @click="open = !open">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 text-xs flex items-center justify-center font-bold">
                                        {{ $sec->order_index }}
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800">{{ $sec->title ?: ucfirst($sec->section_key) }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">{{ $sec->section_type }}</div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5" @click.stop>
                                    <!-- Visibility Toggle -->
                                    <button type="button" @click="toggleVisibility({{ $sec->id }}, {{ $sec->is_visible ? 'false' : 'true' }})" class="p-1 rounded text-slate-400 hover:text-slate-700" title="Toggle Visibility">
                                        <i data-lucide="{{ $sec->is_visible ? 'eye' : 'eye-off' }}" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Chevron -->
                                    <button type="button" @click="open = !open" class="p-1 text-slate-400">
                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Section Body (Open) -->
                            <div x-show="open" class="p-4 border-t border-slate-100 space-y-4 text-xs">

                                <!-- Quick "Regenerate with AI" Action Box -->
                                <div class="p-3 rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100/80">
                                    <div class="font-bold text-indigo-900 mb-1 flex items-center gap-1.5">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-600"></i>
                                        Regenerate Section with AI
                                    </div>
                                    <p class="text-[11px] text-indigo-800/80 mb-2">
                                        Give custom instructions (e.g. <em>"Make headline punchier"</em>, <em>"Add our 10% senior discount"</em>, <em>"Highlight fast turnaround"</em>).
                                    </p>
                                    <div class="flex gap-2">
                                        <input type="text" x-model="regenPrompt" placeholder="What should AI change in this section?" class="flex-1 px-3 py-1.5 rounded-lg border border-indigo-200 text-xs bg-white focus:ring-1 focus:ring-indigo-500">
                                        <button type="button" :disabled="loadingRegen || !regenPrompt.trim()" @click="regenerateSection({{ $sec->id }}, regenPrompt)" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold disabled:opacity-50 text-xs shrink-0 flex items-center gap-1">
                                            <span x-show="!loadingRegen">Regenerate</span>
                                            <span x-show="loadingRegen">Generating...</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Direct Form Inputs for Content -->
                                <form @submit.prevent="saveSectionChanges({{ $sec->id }}, $event)" class="space-y-3">
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">Section Title</label>
                                        <input type="text" name="title" value="{{ $sec->title }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">Subtitle / Summary</label>
                                        <textarea name="subtitle" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-indigo-500">{{ $sec->subtitle }}</textarea>
                                    </div>

                                    @if(isset($sec->content['story']))
                                        <div>
                                            <label class="block font-bold text-slate-700 mb-1">Story / About Text</label>
                                            <textarea name="content[story]" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-indigo-500">{{ $sec->content['story'] }}</textarea>
                                        </div>
                                    @endif

                                    @if(isset($sec->content['badge']))
                                        <div>
                                            <label class="block font-bold text-slate-700 mb-1">Hero Badge Text</label>
                                            <input type="text" name="content[badge]" value="{{ $sec->content['badge'] }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-1 focus:ring-indigo-500">
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-end pt-2">
                                        <button type="submit" :disabled="saving" class="px-4 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center gap-1.5 transition-colors">
                                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                            <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Ingested Sources Drawer -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 text-xs">
                <div class="font-bold text-slate-700 mb-2 flex items-center gap-1.5">
                    <i data-lucide="file-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                    Grounded Files & Sources Ingested ({{ $business->sources->count() }})
                </div>
                <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                    @foreach($business->sources as $source)
                        <div class="p-2 rounded-lg bg-white border border-slate-200/80 flex items-center justify-between text-[11px]">
                            <div class="truncate mr-2">
                                <span class="font-semibold text-slate-800">{{ $source->title_or_filename }}</span>
                                <div class="text-[10px] text-slate-400 truncate">{{ $source->original_path_or_url }}</div>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase {{ $source->status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $source->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Right Column: Live Responsive Preview Frame -->
        <div class="flex-1 bg-slate-200/80 p-4 sm:p-6 flex flex-col items-center justify-start overflow-hidden relative">
            <div class="w-full flex items-center justify-between mb-3 text-xs text-slate-500 max-w-5xl">
                <span class="flex items-center gap-1.5 font-medium">
                    <i data-lucide="play" class="w-3.5 h-3.5 text-emerald-500"></i>
                    Live Preview Frame
                </span>
                <button type="button" @click="refreshIframe()" class="hover:text-slate-800 flex items-center gap-1">
                    <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                    Reload Preview
                </button>
            </div>

            <div class="flex-1 w-full flex items-center justify-center overflow-hidden">
                <div class="h-full bg-white rounded-2xl shadow-2xl border border-slate-300 overflow-hidden transition-all duration-300"
                     :style="viewport === 'desktop' ? 'width: 100%; max-width: 1200px;' : (viewport === 'tablet' ? 'width: 768px;' : 'width: 375px;')">
                    <iframe id="previewFrame" src="{{ route('site.show', $business->slug) }}?preview=1" class="w-full h-full border-0"></iframe>
                </div>
            </div>
        </div>

    </div>

    <!-- TruthLens™ Audit Modal -->
    <div x-show="showAuditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl max-h-[85vh] flex flex-col overflow-hidden" @click.outside="showAuditModal = false">
            <div class="flex items-start justify-between gap-4 mb-4 pb-4 border-b border-slate-100">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">
                        TruthLens™ Grounding Report
                    </span>
                    <h3 class="text-xl font-bold font-heading text-slate-900 mt-2">Fact Citation & Integrity Audit</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Every claim matched directly against your uploaded files and links.</p>
                </div>
                <button type="button" @click="showAuditModal = false" class="text-slate-400 hover:text-slate-700 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 space-y-4 text-xs">
                <!-- Verified Contact Claims -->
                <div>
                    <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px] mb-2 flex items-center gap-1.5">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
                        Business Identity & Contacts
                    </h4>
                    <div class="space-y-2">
                        @foreach($audit['contact_claims'] as $claim)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-semibold text-slate-400">{{ $claim['type'] }}</span>
                                    <div class="font-bold text-slate-900">{{ $claim['value'] }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        {{ $claim['status'] }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1">Source: {{ $claim['citation'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Section Claims -->
                <div>
                    <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px] mb-2 flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-4 h-4 text-indigo-500"></i>
                        Section Citations & Menu Items
                    </h4>
                    <div class="space-y-3">
                        @foreach($audit['section_breakdown'] as $key => $secData)
                            <div class="border border-slate-200 rounded-xl p-3 bg-white">
                                <div class="font-bold text-slate-900 mb-2 capitalize flex items-center justify-between">
                                    <span>Section: {{ $secData['title'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $key }}</span>
                                </div>
                                <div class="space-y-1.5">
                                    @foreach($secData['claims'] as $c)
                                        <div class="p-2 rounded-lg bg-slate-50 flex items-center justify-between gap-2 text-[11px]">
                                            <span class="font-medium text-slate-800">{{ $c['value'] }}</span>
                                            <span class="text-[10px] text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200">
                                                {{ $c['citation'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Hallucination Risk: <strong class="text-emerald-600">{{ $audit['hallucination_risk'] }}</strong></span>
                <button type="button" @click="showAuditModal = false" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-semibold">
                    Done
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function visualEditor() {
    return {
        viewport: 'desktop',
        isPublished: {{ $business->status === 'published' ? 'true' : 'false' }},
        copied: false,
        showAuditModal: false,
        publicUrl: "{{ route('site.show', $business->slug) }}",

        refreshIframe() {
            const frame = document.getElementById('previewFrame');
            if (frame) {
                frame.src = frame.src;
            }
        },

        copyPublicUrl() {
            navigator.clipboard.writeText(this.publicUrl);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        },

        async togglePublish() {
            try {
                const res = await fetch("{{ route('builder.publish', $business->slug) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.isPublished = (data.status === 'published');
                    const badge = document.getElementById('statusBadge');
                    if (badge) {
                        badge.textContent = data.status;
                        badge.className = data.status === 'published'
                            ? 'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : 'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200';
                    }
                    this.refreshIframe();
                }
            } catch (err) {
                alert('Error updating publication status.');
            }
        },

        async resolveConflict(conflictId, chosenValue) {
            try {
                const res = await fetch("{{ route('builder.conflict.resolve', $business->slug) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ conflict_id: conflictId, chosen_value: chosenValue })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (e) {
                alert('Error resolving conflict.');
            }
        },

        async regenerateSection(sectionId, prompt) {
            this.loadingRegen = true;
            try {
                const res = await fetch(`/builder/{{ $business->slug }}/sections/${sectionId}/regenerate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ prompt: prompt })
                });
                const data = await res.json();
                if (data.success) {
                    this.refreshIframe();
                    window.location.reload();
                }
            } catch (e) {
                alert('Error regenerating section.');
            } finally {
                this.loadingRegen = false;
            }
        },

        async saveSectionChanges(sectionId, event) {
            const formData = new FormData(event.target);
            const dataObj = {
                title: formData.get('title'),
                subtitle: formData.get('subtitle'),
                content: {}
            };

            for (let [key, val] of formData.entries()) {
                if (key.startsWith('content[')) {
                    const cleanKey = key.replace('content[', '').replace(']', '');
                    dataObj.content[cleanKey] = val;
                }
            }

            try {
                const res = await fetch(`/builder/{{ $business->slug }}/sections/${sectionId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(dataObj)
                });
                const data = await res.json();
                if (data.success) {
                    this.refreshIframe();
                }
            } catch (e) {
                alert('Error saving section changes.');
            }
        },

        async toggleVisibility(sectionId, visible) {
            try {
                await fetch(`/builder/{{ $business->slug }}/sections/${sectionId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ is_visible: visible })
                });
                this.refreshIframe();
                window.location.reload();
            } catch (e) {
                alert('Error changing visibility.');
            }
        }
    }
}
</script>
@endpush
@endsection
