<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Storefront Launch Flyer — {{ $business->name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Space Grotesk', sans-serif; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .flyer-sheet { box-shadow: none !important; border: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8 flex flex-col items-center">

    <!-- Top Action Bar (hidden on print) -->
    <div class="no-print max-w-2xl w-full mb-6 flex items-center justify-between">
        <a href="{{ route('builder.edit', $business->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-sm transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Visual Editor
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/25 transition-all">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print Storefront Flyer
            </button>
        </div>
    </div>

    <!-- The Printable Storefront Flyer Sheet -->
    <div class="flyer-sheet bg-white w-full max-w-2xl rounded-3xl border border-slate-200/90 shadow-2xl p-10 sm:p-14 text-center relative overflow-hidden">
        
        <!-- Decorative Header Accents -->
        <div class="absolute top-0 inset-x-0 h-4 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

        <div class="mb-6">
            <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-bold text-xs uppercase tracking-wider mb-3">
                {{ $business->category ?? 'Official Business' }}
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold font-heading text-slate-900 tracking-tight mb-2">
                {{ $business->name }}
            </h1>
            @if($business->city)
                <p class="text-sm font-semibold text-slate-500 flex items-center justify-center gap-1">
                    <i data-lucide="map-pin" class="w-4 h-4 text-indigo-500"></i>
                    {{ $business->city }}
                </p>
            @endif
        </div>

        <p class="text-base sm:text-lg text-slate-600 max-w-lg mx-auto mb-8 leading-relaxed">
            {{ $business->tagline ?? $business->description }}
        </p>

        <!-- Big Centerpiece QR Code Box -->
        <div class="max-w-xs mx-auto p-6 rounded-3xl bg-slate-50 border-2 border-slate-900/10 shadow-inner mb-8">
            <div class="w-56 h-56 mx-auto bg-white p-3 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-center">
                {!! $qrCodeSvg !!}
            </div>

            <div class="mt-4">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full">
                    <i data-lucide="scan" class="w-3.5 h-3.5"></i>
                    Scan with Phone Camera
                </span>
                <p class="text-xs text-slate-500 mt-2 font-medium">
                    View our live menu, service offerings, and book online!
                </p>
            </div>
        </div>

        <!-- Verified Contact Details Grid -->
        <div class="grid grid-cols-2 gap-4 text-left max-w-lg mx-auto pt-6 border-t border-slate-100 text-xs text-slate-600">
            @if($business->phone)
                <div class="p-3 rounded-xl bg-slate-50">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Phone</div>
                    <div class="font-bold text-slate-900">{{ $business->phone }}</div>
                </div>
            @endif

            @if($business->address)
                <div class="p-3 rounded-xl bg-slate-50">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Location</div>
                    <div class="font-bold text-slate-900 truncate">{{ $business->address }}</div>
                </div>
            @endif

            @if($business->opening_hours)
                <div class="col-span-2 p-3 rounded-xl bg-slate-50">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Hours</div>
                    <div class="font-bold text-slate-900">
                        {{ is_array($business->opening_hours) ? ($business->opening_hours['schedule'] ?? json_encode($business->opening_hours)) : $business->opening_hours }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer Note -->
        <div class="mt-8 text-[11px] text-slate-400 font-mono">
            {{ $publicUrl }}
        </div>
    </div>

</body>
</html>
