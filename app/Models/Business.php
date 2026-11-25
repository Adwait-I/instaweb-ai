<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'social_links' => 'array',
            'unresolved_conflicts' => 'array',
            'verification_flags' => 'array',
            'facts_summary' => 'array',
        ];
    }

    public function sources(): HasMany
    {
        return $this->hasMany(BusinessSource::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(WebsiteSection::class)->orderBy('order_index');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(ContactInquiry::class)->latest();
    }

    public static function generateUniqueSlug(string $name, ?string $city = null): string
    {
        $base = Str::slug($name . ($city ? ' ' . $city : ''));
        if (empty($base)) {
            $base = 'business';
        }
        $slug = $base;
        $counter = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-" . (++$counter);
        }
        return $slug;
    }
}
