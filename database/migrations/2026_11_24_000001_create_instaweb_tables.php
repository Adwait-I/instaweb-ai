<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->json('opening_hours')->nullable();
            $table->json('social_links')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->string('theme_color')->default('indigo');
            $table->string('font_family')->default('sans');
            $table->string('status')->default('draft'); // draft, published
            $table->json('unresolved_conflicts')->nullable(); // flagged discrepancies (e.g. 2 phone numbers)
            $table->json('verification_flags')->nullable(); // security/scraper flags
            $table->json('facts_summary')->nullable(); // structured grounded facts
            $table->timestamps();
        });

        Schema::create('business_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // file, social_url, google_business_url, web_search, manual_entry
            $table->string('title_or_filename');
            $table->text('original_path_or_url')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('status')->default('success'); // success, scrape_restricted, discrepancy_found, injection_sanitized, failed
            $table->text('status_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('website_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('section_key'); // hero, about, services, pricing, gallery, reviews, contact, footer
            $table->string('section_type'); // hero, about, services_grid, pricing_table, gallery_grid, reviews_list, contact_form, footer
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->json('content')->nullable(); // specific section content items
            $table->json('styling')->nullable(); // layout variants, background color/image
            $table->integer('order_index')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->text('ai_prompt')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->string('status')->default('new'); // new, read, replied
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
        Schema::dropIfExists('website_sections');
        Schema::dropIfExists('business_sources');
        Schema::dropIfExists('businesses');
    }
};
