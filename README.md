# InstaWeb AI — AI Builds My Website

> **Turn any business files, price lists, social links, and Google Business profiles into a ready-to-publish website in seconds.**

Built with **Laravel 12**, **MySQL**, **Google Gemini 2.5 Flash**, **Symfony DomCrawler**, **Tailwind CSS v4**, and **Alpine.js**.

---

## 🌟 Overview

InstaWeb AI allows small business owners to input whatever they have on hand:
- **Business Name & City** (e.g. "Bella Napoli Trattoria", Austin, TX)
- **Social Media Links** (Instagram, Facebook, LinkedIn, TikTok)
- **Google Business / Maps Listings**
- **Files**: PDF menus, CSV/TXT price lists, photo galleries

The system reads and parses all inputs, queries the web, strictly cross-references verified facts, and generates a structured, mobile-responsive website stored in MySQL with a split-screen visual editor and 1-click publishing.

---

## 🛡️ Core Rules & Edge-Case Safeguards

1. **The Golden Rule: Never Invent Facts**
   - Hallucination temperature is set to `0.2` on Gemini models.
   - If information is missing or uncertain, the app explicitly asks the business owner rather than guessing.
2. **Ambiguous Business Names (Disambiguation)**
   - When multiple businesses share the same name (e.g. multi-location branches), DuckDuckGo search results flag candidate locations for user selection.
3. **Conflicting Sources**
   - Automatically compares extracted phone numbers, emails, addresses, and hours. Discrepancies are flagged in the visual editor for 1-click resolution.
4. **Social Sites & Anti-Bot Walls**
   - Handles login walls and Cloudflare challenges gracefully (`scrape_restricted`) without crashing, prompting the user to verify facts.
   - Dual-mode scraping: Native HTTP or proxy scraper APIs (ScraperAPI / ScrapingBee / ZenRows).
5. **Prompt Injection Shield**
   - Neutralizes malicious prompts hidden in customer-uploaded PDFs (e.g., `IGNORE PREVIOUS INSTRUCTIONS`) using regular expression safeguards before passing text to the AI.
6. **Graceful Offline Fallback**
   - If the Gemini API is down, unconfigured, or rate-limited, a deterministic rule-based facts engine generates complete websites without interruption.

---

## 🚀 Unique Features

### 1. TruthLens™ Fact Grounding & Source Citation Audit
* Every generated claim is mapped back to the exact source file or link that supplied it.
* Generates a real-time **Fact Integrity Score (0–100%)** so business owners know their site is 100% grounded in verified truth.

### 2. Storefront QR Launch Flyer
* Generates an instant, printable **Storefront Window Flyer** with an inline SVG QR code pointing directly to the live public site (`/site/{slug}`).
* Customers can scan the physical flyer to browse menus, services, and submit contact inquiries.

---

## 🛠️ Tech Stack

* **Backend**: Laravel 12 (PHP 8.6)
* **Database**: MySQL 8+
* **AI Model**: Google Gemini 2.5 Flash (`gemini-2.5-flash`) via official Google GenAI REST API
* **Web Scraping**: Native `Symfony\Component\DomCrawler` + optional proxy API
* **File Parsing**: `smalot/pdfparser` for PDFs, native regex for CSV/TXT price lists
* **Frontend**: Laravel Blade, Tailwind CSS v4, Alpine.js, Lucide Icons
* **Testing**: PHPUnit / Laravel Feature & Unit Tests (24 automated tests, 93 assertions)

---

## 💻 Quickstart Installation

### Prerequisites
* PHP 8.2+
* Composer 2+
* MySQL 8+
* Node.js & NPM (for asset compilation)

### Setup Instructions

```bash
# 1. Clone repository
git clone https://github.com/Adwait-I/instaweb-ai.git
cd instaweb-ai

# 2. Install dependencies
composer install
npm install && npm run build

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Configure Database in .env
# DB_DATABASE=instaweb
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Run Database Migrations & Demo Seeder
php artisan migrate --seed

# 6. Create Storage Symlink
php artisan storage:link

# 7. Start the Development Server
php artisan serve --port=8000
```

Open your browser at **http://127.0.0.1:8000/builder**.

---

## 🧪 Running Automated Tests

```bash
php artisan test
```

Includes 24 feature and unit tests covering:
* Intake creation with mixed inputs (PDF + links + photos)
* Prompt injection neutralization in uploaded files
* Conflicting source detection and resolution
* Social platform scrape block handling
* Deterministic offline AI fallback
* Fact integrity scoring and SVG QR generation
* Public site rendering and lead inquiry capture

---

## 📄 License
Open source under the [MIT License](LICENSE).
