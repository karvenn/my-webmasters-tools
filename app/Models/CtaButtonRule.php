<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CtaButtonRule extends Model
{
    protected $fillable = [
        'cta_button_id',
        'url_pattern',
        'destination_url',
        'pattern_description',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function ctaButton(): BelongsTo
    {
        return $this->belongsTo(CtaButton::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(CtaButtonAnalytic::class);
    }

    public function getClicksCountAttribute(): int
    {
        return $this->analytics()->sum('click_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Check if the URL matches this rule's pattern
     */
    public function matchesUrl(string $url): bool
    {
        $pattern = $this->url_pattern;
        
        // If the pattern starts with ^, it's already a regex pattern
        if (strpos($pattern, '^') === 0) {
            $regexPattern = '/' . $pattern . '/';
        } else {
            // Convert path pattern to regex manually to preserve regex chars inside parentheses
            $regexPattern = $this->convertPatternToRegex($pattern);
        }
        
        try {
            return (bool) preg_match($regexPattern, $url);
        } catch (\Exception $e) {
            \Log::error('Invalid regex pattern in CTA button rule', [
                'pattern' => $this->url_pattern,
                'regex' => $regexPattern,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Convert a URL pattern to a regex pattern
     */
    private function convertPatternToRegex(string $pattern): string
    {
        // Split the pattern on parentheses to separate literal parts from regex parts
        $parts = preg_split('/(\([^)]+\))/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $regexParts = [];
        foreach ($parts as $part) {
            if (preg_match('/^\([^)]+\)$/', $part)) {
                // This is a capture group - keep as is
                $regexParts[] = $part;
            } else {
                // This is a literal path part - escape special regex chars
                $escaped = preg_quote($part, '/');
                // But allow ? for optional characters
                $escaped = str_replace('\\?', '?', $escaped);
                $regexParts[] = $escaped;
            }
        }
        
        return '/^' . implode('', $regexParts) . '$/';
    }

    /**
     * Get the destination URL with parameters replaced
     */
    public function getDestinationUrl(string $sourceUrl): string
    {
        $pattern = $this->url_pattern;
        $destination = $this->destination_url;
        
        // Use the same logic as matchesUrl
        if (strpos($pattern, '^') === 0) {
            $regexPattern = '/' . $pattern . '/';
        } else {
            $regexPattern = $this->convertPatternToRegex($pattern);
        }
        
        try {
            // Extract matches from the source URL
            if (preg_match($regexPattern, $sourceUrl, $matches)) {
                // Replace $1, $2, etc. in the destination URL with captured groups
                for ($i = 1; $i < count($matches); $i++) {
                    $destination = str_replace('$' . $i, $matches[$i], $destination);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Invalid regex pattern in getDestinationUrl', [
                'pattern' => $this->url_pattern,
                'regex' => $regexPattern,
                'error' => $e->getMessage()
            ]);
        }
        
        return $destination;
    }
}
