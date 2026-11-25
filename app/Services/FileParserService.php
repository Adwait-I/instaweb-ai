<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * FileParserService
 *
 * Responsibilities:
 * 1. Securely store and parse uploaded business documents (PDFs, price lists, photos).
 * 2. Extract plain text and structural data for ingestion into the AI pipeline.
 * 3. Inspect text for indirect prompt injection or adversarial override attempts hidden
 *    inside user-supplied files, neutralizing them before they can reach the AI model.
 */
class FileParserService
{
    /**
     * Regex patterns for detecting indirect prompt injection inside uploaded documents.
     * These patterns target common jailbreak commands, persona overrides, and system prompt tampering.
     */
    protected array $injectionPatterns = [
        '/(?:ignore|disregard|forget)\s+(?:all\s+)?(?:previous|prior|above)\s+(?:instructions|prompts|rules)/i',
        '/(?:system\s+prompt|developer\s+mode|jailbreak|DAN\s+mode)/i',
        '/(?:you\s+are\s+now|act\s+as\s+an?\s+unrestricted|bypass\s+all\s+filters)/i',
        '/(?:output\s+only|say\s+only|reply\s+with)\s*:\s*["\']?[^"\']{3,}/i',
        '/(?:send\s+all\s+data|exfiltrate|fetch\s+https?:\/\/)/i',
        '/<script[\s\S]*?>[\s\S]*?<\/script>/i',
    ];

    /**
     * Ingest an uploaded file, extract its text content, and check for security threats.
     *
     * @param UploadedFile $file The uploaded file from the web intake form
     * @return array Standardized file payload with extracted text and security flags
     */
    public function processFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $extractedText = '';
        $storedPath = null;
        $flags = [];
        $type = 'document';

        // Step 1: Route processing based on file type
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            // Business Photo: Save to public disk for the website's gallery & hero sections
            $type = 'photo';
            $storedPath = $file->store('public/business_photos');
            $extractedText = "[Uploaded Photo: {$originalName} (stored for gallery/hero)]";
        } elseif ($extension === 'pdf') {
            // PDF Document: Extract full text using pure-PHP PDF parser
            $type = 'pdf';
            $storedPath = $file->store('public/business_docs');
            $extractedText = $this->extractPdfText($file->getRealPath());
        } elseif (in_array($extension, ['txt', 'csv', 'tsv', 'json', 'md'])) {
            // Price List / Tabular Data: Parse line by line
            $type = 'price_list';
            $storedPath = $file->store('public/business_docs');
            $rawContent = file_get_contents($file->getRealPath());
            $extractedText = $this->parseStructuredText($rawContent, $extension);
        } else {
            // Generic Fallback Document
            $storedPath = $file->store('public/business_docs');
            $extractedText = "[Uploaded File: {$originalName}]";
        }

        // Step 2: Sanitize extracted text for indirect prompt injection
        $sanitizationResult = $this->sanitizeExtractedText($extractedText);
        $extractedText = $sanitizationResult['text'];

        if ($sanitizationResult['injection_detected']) {
            $flags[] = [
                'type' => 'prompt_injection_neutralized',
                'file' => $originalName,
                'message' => 'Suspicious override commands were detected in this file and neutralized to prevent prompt injection.',
                'matched' => $sanitizationResult['matches'],
            ];
        }

        return [
            'type' => $type,
            'filename' => $originalName,
            'stored_path' => $storedPath ? Storage::url($storedPath) : null,
            'extracted_text' => $extractedText,
            'flags' => $flags,
        ];
    }

    /**
     * Extract readable plain text from a PDF file using the Smalot PDF Parser.
     *
     * @param string $filePath Absolute path to the PDF on disk
     * @return string Extracted text or error description
     */
    protected function extractPdfText(string $filePath): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            return trim($pdf->getText());
        } catch (\Throwable $e) {
            return "[Error parsing PDF text: {$e->getMessage()}]";
        }
    }

    /**
     * Clean and normalize tabular or structured text files (CSV, TSV, JSON).
     *
     * @param string $content Raw file content
     * @param string $extension File extension
     * @return string Formatted string
     */
    protected function parseStructuredText(string $content, string $extension): string
    {
        if ($extension === 'csv' || $extension === 'tsv') {
            $lines = explode("\n", $content);
            $parsed = [];
            foreach (array_slice($lines, 0, 100) as $line) {
                if (trim($line)) {
                    $parsed[] = trim($line);
                }
            }
            return implode("\n", $parsed);
        }

        if ($extension === 'json') {
            $json = json_decode($content, true);
            if ($json) {
                return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        }

        return mb_substr(trim($content), 0, 15000);
    }

    /**
     * Defense against Prompt Injection:
     * Scans extracted text against known override patterns and neutralizes matching phrases.
     *
     * @param string $text Raw text extracted from file
     * @return array [text => sanitized_string, injection_detected => bool, matches => array]
     */
    public function sanitizeExtractedText(string $text): array
    {
        $injectionDetected = false;
        $matchesFound = [];

        foreach ($this->injectionPatterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                $injectionDetected = true;
                foreach ($matches[0] as $match) {
                    $matchesFound[] = substr($match, 0, 60);
                }
                // Replace any command directive with a safe notification badge
                $text = preg_replace($pattern, '[SAFEGUARD: Non-business instruction stripped]', $text);
            }
        }

        return [
            'text' => $text,
            'injection_detected' => $injectionDetected,
            'matches' => array_unique($matchesFound),
        ];
    }
}
