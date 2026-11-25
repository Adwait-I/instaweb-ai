<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\WebsiteSection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebsiteBuilderService
{
    public function __construct(
        protected FileParserService $fileParser,
        protected WebScraperService $webScraper,
        protected ConflictDetectorService $conflictDetector,
        protected GeminiService $gemini
    ) {}

    /**
     * Ingest all inputs, process edge cases, build website with AI/fallback, and save to MySQL.
     */
    public function buildWebsite(array $inputs, array $files = []): Business
    {
        return DB::transaction(function () use ($inputs, $files) {
            $name = trim($inputs['name'] ?? 'My Business');
            $city = trim($inputs['city'] ?? '');
            $category = trim($inputs['category'] ?? 'Local Business');
            $slug = Business::generateUniqueSlug($name, $city);

            $business = Business::create([
                'name' => $name,
                'city' => $city,
                'slug' => $slug,
                'category' => $category,
                'phone' => $inputs['phone'] ?? null,
                'email' => $inputs['email'] ?? null,
                'address' => $inputs['address'] ?? null,
                'google_maps_url' => $inputs['google_business_url'] ?? null,
                'theme_color' => $inputs['theme_color'] ?? 'indigo',
                'status' => 'draft',
                'social_links' => [],
                'verification_flags' => [],
                'unresolved_conflicts' => [],
                'facts_summary' => [],
            ]);

            $verificationFlags = [];
            $sourcesSummary = [];
            $extractedTextCollector = [];
            $photosCollector = [];
            $socialLinks = [];

            // 1. Edge Case: Search the web and detect if multiple businesses share the same name
            $searchResult = $this->webScraper->searchBusiness($name, $city);
            if ($searchResult['is_ambiguous']) {
                $verificationFlags[] = [
                    'type' => 'multiple_businesses_found',
                    'title' => 'Multiple Businesses with Similar Name Detected',
                    'message' => 'We detected more than one business with this name. Please verify your exact address and contact details in the review panel.',
                    'candidates' => $searchResult['candidates'],
                ];
            }

            // 2. Process Social Links & Scrape Restrictions
            if (!empty($inputs['social_links'])) {
                $linksList = is_array($inputs['social_links']) ? $inputs['social_links'] : explode("\n", $inputs['social_links']);
                foreach ($linksList as $link) {
                    $link = trim($link);
                    if (empty($link)) continue;

                    $scrape = $this->webScraper->scrapeUrl($link);
                    $platform = $scrape['platform'] ?? 'Web';
                    $socialLinks[$platform] = $link;

                    $source = BusinessSource::create([
                        'business_id' => $business->id,
                        'type' => 'social_url',
                        'title_or_filename' => "{$platform} Link",
                        'original_path_or_url' => $link,
                        'extracted_text' => $scrape['text'],
                        'status' => $scrape['status'],
                        'status_message' => $scrape['message'],
                        'meta' => $scrape['meta'] ?? [],
                    ]);

                    $sourcesSummary[] = $source->toArray();
                    $extractedTextCollector[] = $scrape['text'];

                    if ($scrape['status'] === 'scrape_restricted') {
                        $verificationFlags[] = [
                            'type' => 'scrape_restricted',
                            'platform' => $platform,
                            'url' => $link,
                            'title' => "{$platform} Scraping Blocked by Provider",
                            'message' => $scrape['message'],
                        ];
                    }
                }
            }

            // 3. Process Google Business URL if provided
            if (!empty($inputs['google_business_url'])) {
                $gUrl = trim($inputs['google_business_url']);
                $scrape = $this->webScraper->scrapeUrl($gUrl);
                
                $source = BusinessSource::create([
                    'business_id' => $business->id,
                    'type' => 'google_business_url',
                    'title_or_filename' => 'Google Business Listing',
                    'original_path_or_url' => $gUrl,
                    'extracted_text' => $scrape['text'],
                    'status' => $scrape['status'],
                    'status_message' => $scrape['message'],
                    'meta' => $scrape['meta'] ?? [],
                ]);

                $sourcesSummary[] = $source->toArray();
                $extractedTextCollector[] = $scrape['text'];
            }

            // 4. Process Uploaded Files (PDFs, Price Lists, Photos)
            foreach ($files as $file) {
                if (!$file instanceof UploadedFile) continue;

                $parsed = $this->fileParser->processFile($file);

                $source = BusinessSource::create([
                    'business_id' => $business->id,
                    'type' => 'file',
                    'title_or_filename' => $parsed['filename'],
                    'original_path_or_url' => $parsed['stored_path'],
                    'extracted_text' => $parsed['extracted_text'],
                    'status' => !empty($parsed['flags']) ? 'injection_sanitized' : 'success',
                    'status_message' => !empty($parsed['flags']) ? 'Safe file parsed with security sanitization' : 'Parsed successfully',
                    'meta' => ['file_type' => $parsed['type']],
                ]);

                $sourcesSummary[] = $source->toArray();
                $extractedTextCollector[] = $parsed['extracted_text'];

                if ($parsed['type'] === 'photo' && $parsed['stored_path']) {
                    $photosCollector[] = [
                        'url' => $parsed['stored_path'],
                        'caption' => pathinfo($parsed['filename'], PATHINFO_FILENAME),
                    ];
                }

                // Check for injection flags
                foreach ($parsed['flags'] as $flag) {
                    $verificationFlags[] = [
                        'type' => 'prompt_injection_neutralized',
                        'title' => 'Hidden Instructions Neutralized in Uploaded File',
                        'file' => $flag['file'],
                        'message' => $flag['message'],
                        'matched' => $flag['matched'] ?? [],
                    ];
                }
            }

            // 5. Detect Conflicting Sources (Golden Rule: Never invent facts. Ask user if unsure.)
            $conflicts = $this->conflictDetector->detectConflicts($sourcesSummary, [
                'phone' => $inputs['phone'] ?? null,
                'email' => $inputs['email'] ?? null,
                'address' => $inputs['address'] ?? null,
            ]);

            // Set resolved/preferred initial contact info
            $preferredPhone = $inputs['phone'] ?? null;
            $preferredEmail = $inputs['email'] ?? null;
            $preferredAddress = $inputs['address'] ?? null;

            // Prepare facts dictionary for Gemini
            $facts = [
                'name' => $name,
                'city' => $city,
                'category' => $category,
                'phone' => $preferredPhone,
                'email' => $preferredEmail,
                'address' => $preferredAddress,
                'hours' => $inputs['opening_hours'] ?? null,
                'social_links' => $socialLinks,
                'google_maps_url' => $inputs['google_business_url'] ?? null,
                'extracted_summary' => implode("\n\n---\n\n", $extractedTextCollector),
            ];

            // 6. Generate Website via Gemini or Fallback
            $generationResult = $this->gemini->generateWebsite($facts, $photosCollector);

            // Update Business with facts & metadata
            $business->update([
                'tagline' => $generationResult['tagline'] ?? null,
                'description' => $generationResult['description'] ?? null,
                'social_links' => $socialLinks,
                'verification_flags' => $verificationFlags,
                'unresolved_conflicts' => $conflicts,
                'facts_summary' => [
                    'used_ai' => $generationResult['used_ai'],
                    'generation_note' => $generationResult['note'],
                    'sources_count' => count($sourcesSummary),
                    'photos_count' => count($photosCollector),
                ],
            ]);

            // 7. Save Website Sections to MySQL
            foreach ($generationResult['sections'] as $index => $sec) {
                WebsiteSection::create([
                    'business_id' => $business->id,
                    'section_key' => $sec['section_key'] ?? ('section_' . $index),
                    'section_type' => $sec['section_type'] ?? 'generic',
                    'title' => $sec['title'] ?? null,
                    'subtitle' => $sec['subtitle'] ?? null,
                    'content' => $sec['content'] ?? [],
                    'styling' => $sec['styling'] ?? [],
                    'order_index' => $sec['order_index'] ?? ($index + 1),
                    'is_visible' => $sec['is_visible'] ?? true,
                ]);
            }

            return $business->load(['sections', 'sources']);
        });
    }

    /**
     * Resolve a conflict chosen by the user.
     */
    public function resolveConflict(Business $business, string $conflictId, string $chosenValue): Business
    {
        $conflicts = $business->unresolved_conflicts ?? [];
        $updatedConflicts = [];

        foreach ($conflicts as $c) {
            if ($c['id'] === $conflictId) {
                $c['resolved'] = true;
                $c['resolved_value'] = $chosenValue;

                // Apply to business model
                if ($c['field'] === 'phone') {
                    $business->phone = $chosenValue;
                    // Also update contact section content
                    $this->updateSectionField($business, 'contact', 'phone', $chosenValue);
                } elseif ($c['field'] === 'email') {
                    $business->email = $chosenValue;
                    $this->updateSectionField($business, 'contact', 'email', $chosenValue);
                } elseif ($c['field'] === 'hours') {
                    $this->updateSectionField($business, 'contact', 'hours', $chosenValue);
                }
            }
            $updatedConflicts[] = $c;
        }

        $business->unresolved_conflicts = $updatedConflicts;
        $business->save();

        return $business;
    }

    /**
     * Update a field inside a section's JSON content.
     */
    protected function updateSectionField(Business $business, string $sectionKey, string $field, mixed $value): void
    {
        $section = $business->sections()->where('section_key', $sectionKey)->first();
        if ($section) {
            $content = $section->content ?? [];
            $content[$field] = $value;
            $section->content = $content;
            $section->save();
        }
    }

    /**
     * Regenerate a single section using Gemini AI with user guidance.
     */
    public function regenerateSection(Business $business, WebsiteSection $section, string $userPrompt): WebsiteSection
    {
        $facts = [
            'name' => $business->name,
            'city' => $business->city,
            'category' => $business->category,
            'phone' => $business->phone,
            'email' => $business->email,
            'address' => $business->address,
            'hours' => $business->opening_hours ? json_encode($business->opening_hours) : null,
            'extracted_summary' => implode("\n", $business->sources->pluck('extracted_text')->toArray()),
        ];

        $result = $this->gemini->regenerateSection(
            $section->section_key,
            $section->section_type,
            $section->content ?? [],
            $userPrompt,
            $facts
        );

        $section->update([
            'title' => $result['title'] ?? $section->title,
            'subtitle' => $result['subtitle'] ?? $section->subtitle,
            'content' => $result['content'],
            'ai_prompt' => $userPrompt,
        ]);

        return $section;
    }
}
