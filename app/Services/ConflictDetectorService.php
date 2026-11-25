<?php

namespace App\Services;

class ConflictDetectorService
{
    /**
     * Analyze extracted data from multiple sources and find disagreements.
     */
    public function detectConflicts(array $sourcesData, array $initialInputs): array
    {
        $conflicts = [];
        $extractedPhones = [];
        $extractedEmails = [];
        $extractedHours = [];
        $extractedAddresses = [];

        // Check initial inputs
        if (!empty($initialInputs['phone'])) {
            $extractedPhones[] = [
                'value' => $this->normalizePhone($initialInputs['phone']),
                'display' => $initialInputs['phone'],
                'source' => 'Direct User Input',
            ];
        }
        if (!empty($initialInputs['email'])) {
            $extractedEmails[] = [
                'value' => strtolower(trim($initialInputs['email'])),
                'display' => trim($initialInputs['email']),
                'source' => 'Direct User Input',
            ];
        }

        // Parse through each source's extracted text and meta
        foreach ($sourcesData as $source) {
            $title = $source['title_or_filename'] ?? 'Source';
            $text = $source['extracted_text'] ?? '';
            $meta = $source['meta'] ?? [];

            // Detect phone numbers
            if (!empty($meta['phone'])) {
                $extractedPhones[] = [
                    'value' => $this->normalizePhone($meta['phone']),
                    'display' => $meta['phone'],
                    'source' => $title,
                ];
            } else {
                $foundPhones = $this->extractPhonesFromText($text);
                foreach ($foundPhones as $p) {
                    $extractedPhones[] = [
                        'value' => $this->normalizePhone($p),
                        'display' => $p,
                        'source' => $title,
                    ];
                }
            }

            // Detect email addresses
            $foundEmails = $this->extractEmailsFromText($text);
            foreach ($foundEmails as $e) {
                $extractedEmails[] = [
                    'value' => strtolower(trim($e)),
                    'display' => trim($e),
                    'source' => $title,
                ];
            }

            // Detect opening hours
            $hours = $this->extractHoursFromText($text);
            if (!empty($hours)) {
                $extractedHours[] = [
                    'value' => $hours,
                    'display' => $hours,
                    'source' => $title,
                ];
            }
        }

        // Evaluate Phone Disagreements
        $uniquePhones = $this->deduplicateByValue($extractedPhones);
        if (count($uniquePhones) > 1) {
            $conflicts[] = [
                'id' => 'conflict_phone',
                'field' => 'phone',
                'title' => 'Conflicting Phone Numbers Found',
                'description' => 'Different sources provided different phone numbers. Please choose the official number for your website:',
                'options' => $uniquePhones,
                'resolved' => false,
                'resolved_value' => null,
            ];
        }

        // Evaluate Email Disagreements
        $uniqueEmails = $this->deduplicateByValue($extractedEmails);
        if (count($uniqueEmails) > 1) {
            $conflicts[] = [
                'id' => 'conflict_email',
                'field' => 'email',
                'title' => 'Multiple Email Addresses Found',
                'description' => 'Multiple emails were detected across your files and links. Select which one customer inquiries should reach:',
                'options' => $uniqueEmails,
                'resolved' => false,
                'resolved_value' => null,
            ];
        }

        // Evaluate Hours Disagreements
        $uniqueHours = $this->deduplicateByValue($extractedHours);
        if (count($uniqueHours) > 1) {
            $conflicts[] = [
                'id' => 'conflict_hours',
                'field' => 'hours',
                'title' => 'Conflicting Operating Hours Detected',
                'description' => 'Operating hours differ between your sources. Which schedule should be published on the site?',
                'options' => $uniqueHours,
                'resolved' => false,
                'resolved_value' => null,
            ];
        }

        return $conflicts;
    }

    protected function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    protected function deduplicateByValue(array $items): array
    {
        $unique = [];
        $seen = [];
        foreach ($items as $item) {
            $val = $item['value'];
            if (!empty($val) && !isset($seen[$val])) {
                $seen[$val] = true;
                $unique[] = $item;
            }
        }
        return $unique;
    }

    protected function extractPhonesFromText(string $text): array
    {
        $phones = [];
        if (preg_match_all('/(?:\+?1[-.\s]?)?\(?[0-9]{3}\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}/', $text, $matches)) {
            $phones = array_unique($matches[0]);
        }
        return array_slice($phones, 0, 3);
    }

    protected function extractEmailsFromText(string $text): array
    {
        $emails = [];
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            $emails = array_unique($matches[0]);
        }
        return array_slice($emails, 0, 3);
    }

    protected function extractHoursFromText(string $text): ?string
    {
        if (preg_match('/(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun|Hours|Open)[^.\n]{5,60}(?:am|pm|AM|PM)/i', $text, $matches)) {
            return trim($matches[0]);
        }
        return null;
    }
}
