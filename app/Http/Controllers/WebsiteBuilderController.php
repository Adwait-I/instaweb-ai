<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\WebsiteSection;
use App\Services\TruthLensService;
use App\Services\WebsiteBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * WebsiteBuilderController
 *
 * Handles:
 * 1. Business intake wizard & multi-source processing
 * 2. Visual live website editor with responsive viewport previews
 * 3. Per-section AI regeneration and inline content editing
 * 4. Conflict resolution (Golden Rule: Never invent facts. Ask user.)
 * 5. TruthLens Fact Grounding Audits
 * 6. Storefront QR Launch Flyer generation
 * 7. Publishing & Unpublishing sites
 */
class WebsiteBuilderController extends Controller
{
    public function __construct(
        protected WebsiteBuilderService $builderService,
        protected TruthLensService $truthLens
    ) {}

    /**
     * Dashboard: List all businesses created by user.
     */
    public function index(): View
    {
        $businesses = Business::withCount(['sections', 'sources', 'inquiries'])
            ->latest()
            ->paginate(12);

        return view('builder.index', compact('businesses'));
    }

    /**
     * Creation Wizard: Business input form.
     */
    public function create(): View
    {
        return view('builder.create');
    }

    /**
     * Ingest inputs, process files & links, detect conflicts, generate website.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'city' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'opening_hours' => 'nullable|string|max:255',
            'google_business_url' => 'nullable|string',
            'social_links' => 'nullable|string',
            'theme_color' => 'nullable|string|max:30',
            'files.*' => 'nullable|file|max:20480', // 20MB max per file
        ]);

        $files = $request->file('files', []);
        $business = $this->builderService->buildWebsite($validated, $files);

        return redirect()->route('builder.edit', $business->slug)
            ->with('success', "Website generated for {$business->name}! Review grounded facts below.");
    }

    /**
     * Visual Editor: Live editing, section regeneration, conflict resolution.
     */
    public function edit(string $slug): View
    {
        $business = Business::with(['sections', 'sources', 'inquiries'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Run TruthLens Fact Audit
        $audit = $this->truthLens->auditBusiness($business);

        return view('builder.edit', compact('business', 'audit'));
    }

    /**
     * TruthLens Fact Audit API endpoint.
     */
    public function auditFacts(string $slug): JsonResponse
    {
        $business = Business::with(['sections', 'sources'])
            ->where('slug', $slug)
            ->firstOrFail();

        $audit = $this->truthLens->auditBusiness($business);

        return response()->json([
            'success' => true,
            'audit' => $audit,
        ]);
    }

    /**
     * Printable Storefront Launch Flyer with live QR Code!
     */
    public function flyer(string $slug): View
    {
        $business = Business::with(['sections', 'sources'])
            ->where('slug', $slug)
            ->firstOrFail();

        $publicUrl = route('site.show', $business->slug);
        $qrCodeSvg = $this->truthLens->generateQrCodeSvg($publicUrl);

        return view('builder.flyer', compact('business', 'publicUrl', 'qrCodeSvg'));
    }

    /**
     * Update section content inline from the editor.
     */
    public function updateSection(Request $request, string $slug, int $sectionId): JsonResponse
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        $section = $business->sections()->findOrFail($sectionId);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'content' => 'nullable|array',
            'is_visible' => 'nullable|boolean',
        ]);

        $section->update(array_filter([
            'title' => $validated['title'] ?? $section->title,
            'subtitle' => $validated['subtitle'] ?? $section->subtitle,
            'content' => isset($validated['content']) ? $validated['content'] : $section->content,
            'is_visible' => $validated['is_visible'] ?? $section->is_visible,
        ], fn($v) => !is_null($v)));

        return response()->json([
            'success' => true,
            'message' => "Section '{$section->title}' updated.",
            'section' => $section->fresh(),
        ]);
    }

    /**
     * Regenerate a single section using AI guidance.
     */
    public function regenerateSection(Request $request, string $slug, int $sectionId): JsonResponse
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        $section = $business->sections()->findOrFail($sectionId);

        $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        $updatedSection = $this->builderService->regenerateSection(
            $business,
            $section,
            $request->input('prompt')
        );

        return response()->json([
            'success' => true,
            'message' => "Section '{$updatedSection->title}' regenerated successfully.",
            'section' => $updatedSection,
        ]);
    }

    /**
     * Resolve a detected conflict (Golden Rule: Never invent facts. Ask user.)
     */
    public function resolveConflict(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        $request->validate([
            'conflict_id' => 'required|string',
            'chosen_value' => 'required|string',
        ]);

        $business = $this->builderService->resolveConflict(
            $business,
            $request->input('conflict_id'),
            $request->input('chosen_value')
        );

        return response()->json([
            'success' => true,
            'message' => 'Conflict resolved and saved.',
            'business' => $business,
        ]);
    }

    /**
     * Publish or unpublish website.
     */
    public function togglePublish(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        $business->status = ($business->status === 'published') ? 'draft' : 'published';
        $business->save();

        $publicUrl = route('site.show', $business->slug);

        return response()->json([
            'success' => true,
            'status' => $business->status,
            'public_url' => $publicUrl,
            'message' => $business->status === 'published' 
                ? "Website is now LIVE at {$publicUrl}" 
                : 'Website unpublished and saved as draft.',
        ]);
    }

    /**
     * Reorder sections.
     */
    public function reorderSections(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        $orders = $request->input('orders', []); // [section_id => order_index]

        foreach ($orders as $sectionId => $orderIndex) {
            $business->sections()->where('id', $sectionId)->update(['order_index' => $orderIndex]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get current Gemini API Key status.
     */
    public function getApiKeyStatus(): JsonResponse
    {
        $key = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $isSet = !empty($key) && strlen($key) > 5;
        $masked = $isSet ? substr($key, 0, 6) . '...' . substr($key, -4) : null;

        return response()->json([
            'configured' => $isSet,
            'masked_key' => $masked,
            'model' => config('services.gemini.model', 'gemini-2.5-flash'),
        ]);
    }

    /**
     * Update and test Gemini API Key.
     */
    public function saveApiKey(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => 'required|string|min:15',
            'model' => 'nullable|string',
        ]);

        $newKey = trim($validated['api_key']);
        $model = $validated['model'] ?? config('services.gemini.model', 'gemini-2.5-flash');

        // Test key with a quick ping to Gemini API
        $testUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$newKey}";
        try {
            $testRes = \Illuminate\Support\Facades\Http::timeout(10)->post($testUrl, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => 'ping']]]
                ]
            ]);

            if ($testRes->failed()) {
                $err = $testRes->json()['error']['message'] ?? 'API responded with status ' . $testRes->status();
                return response()->json([
                    'success' => false,
                    'message' => "Gemini API verification failed: {$err}",
                ], 422);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Connection error during API key verification: {$e->getMessage()}",
            ], 422);
        }

        // Key is verified! Update .env file
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (preg_match('/^GEMINI_API_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^GEMINI_API_KEY=.*$/m', "GEMINI_API_KEY={$newKey}", $envContent);
            } else {
                $envContent .= "\nGEMINI_API_KEY={$newKey}";
            }

            if (!empty($model)) {
                if (preg_match('/^GEMINI_MODEL=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^GEMINI_MODEL=.*$/m', "GEMINI_MODEL={$model}", $envContent);
                } else {
                    $envContent .= "\nGEMINI_MODEL={$model}";
                }
            }

            file_put_contents($envPath, $envContent);
        }

        config(['services.gemini.api_key' => $newKey]);
        config(['services.gemini.model' => $model]);

        return response()->json([
            'success' => true,
            'message' => 'Gemini API key verified and saved successfully! AI generation is active.',
            'model' => $model,
        ]);
    }
}
