@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

    <!-- Hero Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white p-8 sm:p-12 shadow-2xl mb-12">
        <div class="absolute -right-16 -top-16 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-semibold text-indigo-200 mb-4">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-400"></i>
                Turn Any Files & Links Into a Ready-to-Publish Website
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight font-heading leading-tight mb-4">
                Share what you have. <br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 via-purple-300 to-pink-300">
                    AI builds your website.
                </span>
            </h1>
            <p class="text-slate-300 text-base sm:text-lg mb-8 leading-relaxed">
                Feed your business name, Google Business page, social media links, PDF brochures, price lists, or photos. We extract the grounded facts, guard against prompt injection, resolve conflicting sources, and let you edit, regenerate sections, and publish to a public URL.
            </p>

            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('builder.create') }}" class="px-6 py-3.5 rounded-xl bg-indigo-500 hover:bg-indigo-400 text-white font-semibold shadow-lg shadow-indigo-500/30 hover:scale-[1.02] transition-all flex items-center gap-2.5">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Create New Website</span>
                </a>
                <div class="text-xs text-slate-400 flex items-center gap-3">
                    <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-emerald-400"></i> MySQL Backed</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-emerald-400"></i> Never Invents Facts</span>
                </div>
            </div>
        </div>

        <!-- Quick Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-10 pt-8 border-t border-white/10">
            <div class="bg-white/5 rounded-2xl p-4 backdrop-blur-sm border border-white/5">
                <div class="text-2xl sm:text-3xl font-bold font-heading">{{ $businesses->total() }}</div>
                <div class="text-xs text-slate-400 mt-1">Total Websites</div>
            </div>
            <div class="bg-white/5 rounded-2xl p-4 backdrop-blur-sm border border-white/5">
                <div class="text-2xl sm:text-3xl font-bold font-heading text-emerald-400">
                    {{ $businesses->where('status', 'published')->count() }}
                </div>
                <div class="text-xs text-slate-400 mt-1">Published Live</div>
            </div>
            <div class="bg-white/5 rounded-2xl p-4 backdrop-blur-sm border border-white/5">
                <div class="text-2xl sm:text-3xl font-bold font-heading text-purple-400">
                    {{ $businesses->sum('sources_count') }}
                </div>
                <div class="text-xs text-slate-400 mt-1">Grounded Sources</div>
            </div>
            <div class="bg-white/5 rounded-2xl p-4 backdrop-blur-sm border border-white/5">
                <div class="text-2xl sm:text-3xl font-bold font-heading text-pink-400">
                    {{ $businesses->sum('inquiries_count') }}
                </div>
                <div class="text-xs text-slate-400 mt-1">Visitor Inquiries</div>
            </div>
        </div>
    </div>

    <!-- Edge Cases Handled Banner -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-12">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 flex items-center gap-2">
            <i data-lucide="shield" class="w-4 h-4 text-indigo-600"></i>
            Architectural Edge Cases Strictly Handled
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 text-xs">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                    <i data-lucide="scale" class="w-3.5 h-3.5 text-amber-500"></i>
                    Golden Rule
                </div>
                <p class="text-slate-600">Zero hallucination. Only facts present in user uploads/links are used. Missing info triggers questions, not lies.</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                    <i data-lucide="git-compare" class="w-3.5 h-3.5 text-blue-500"></i>
                    Conflicting Sources
                </div>
                <p class="text-slate-600">If Facebook and PDF disagree on phone or hours, both are recorded and flagged for the user to pick.</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-purple-500"></i>
                    Social Site Scraper Blocks
                </div>
                <p class="text-slate-600">Instagram / Facebook login walls gracefully fall back with friendly user prompts rather than crashing.</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-rose-500"></i>
                    Hidden File Instructions
                </div>
                <p class="text-slate-600">Prompt injection attempts hidden in PDFs/price lists are sanitized before passing to AI.</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                    <i data-lucide="power" class="w-3.5 h-3.5 text-emerald-500"></i>
                    AI Downtime Fallback
                </div>
                <p class="text-slate-600">If Gemini API is unreachable or rate limited, a deterministic fact-engine builds the site seamlessly.</p>
            </div>
        </div>
    </div>

    <!-- Websites Grid -->
    <div class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold font-heading text-slate-900">Your Websites</h2>
                <p class="text-sm text-slate-500">Manage, edit, regenerate sections, and view public URLs.</p>
            </div>
            <a href="{{ route('builder.create') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                <span>+ Create Another</span>
            </a>
        </div>

        @if($businesses->isEmpty())
            <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="globe" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 font-heading mb-1">No websites created yet</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">
                    Enter a business name, drop a price list or brochure PDF, and watch the AI turn it into a publishable website.
                </p>
                <a href="{{ route('builder.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition-colors text-sm shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Build Your First Website
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($businesses as $b)
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group">
                        <div class="p-6">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <span class="inline-block text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 mb-1.5">
                                        {{ $b->category ?? 'Business' }}
                                    </span>
                                    <h3 class="text-xl font-bold text-slate-900 font-heading group-hover:text-indigo-600 transition-colors">
                                        {{ $b->name }}
                                    </h3>
                                    @if($b->city)
                                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                            <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                            {{ $b->city }}
                                        </p>
                                    @endif
                                </div>

                                <div>
                                    @if($b->status === 'published')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Draft
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Description -->
                            <p class="text-xs text-slate-600 line-clamp-2 mb-4 leading-relaxed">
                                {{ $b->description ?: ($b->tagline ?: 'Ready-to-publish website generated from verified business sources.') }}
                            </p>

                            <!-- Sources & Sections Badges -->
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 pt-3 border-t border-slate-100">
                                <span class="flex items-center gap-1 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    {{ $b->sections_count }} sections
                                </span>
                                <span class="flex items-center gap-1 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-purple-500"></i>
                                    {{ $b->sources_count }} sources
                                </span>
                                @if($b->inquiries_count > 0)
                                    <span class="flex items-center gap-1 bg-pink-50 text-pink-700 px-2 py-1 rounded-md border border-pink-100 font-semibold">
                                        <i data-lucide="inbox" class="w-3.5 h-3.5 text-pink-600"></i>
                                        {{ $b->inquiries_count }} leads
                                    </span>
                                @endif

                                @if(!empty($b->unresolved_conflicts) && count($b->unresolved_conflicts) > 0)
                                    <span class="flex items-center gap-1 bg-amber-50 text-amber-800 px-2 py-1 rounded-md border border-amber-200 font-semibold text-[10px]">
                                        <i data-lucide="alert-triangle" class="w-3 h-3 text-amber-600"></i>
                                        Conflict review
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="px-6 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-2">
                            <a href="{{ route('builder.edit', $b->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                Visual Editor & AI
                            </a>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('site.show', $b->slug) }}" target="_blank" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition-colors" title="View Public Website">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $businesses->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
