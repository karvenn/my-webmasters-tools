<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Form extends Model
{
    protected $fillable = [
        'user_id',
        'website_name',
        'website_url',
        'embed_token',
        'is_active',
        'button_color',
        'button_text_color',
        'button_size',
        'button_position',
        'button_text',
        'allowed_domains',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_domains' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            $form->embed_token = Str::random(32);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function getSubmissionsCountAttribute(): int
    {
        return $this->submissions()->count();
    }

    public function getNewSubmissionsCountAttribute(): int
    {
        return $this->submissions()->where('status', 'new')->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function getAllowedDomainsAttribute($value)
    {
        if (empty($value)) {
            // Default to the domain from website_url
            $parsedUrl = parse_url($this->website_url);
            return [$parsedUrl['host'] ?? ''];
        }
        return $this->castAttribute('allowed_domains', $value);
    }

    public function isDomainAllowed(string $domain): bool
    {
        $allowedDomains = $this->allowed_domains ?? [];
        
        // Normalize domain (remove www.)
        $normalizedDomain = preg_replace('/^www\./', '', $domain);
        
        foreach ($allowedDomains as $allowed) {
            $normalizedAllowed = preg_replace('/^www\./', '', $allowed);
            
            // Check for wildcard subdomains
            if (strpos($normalizedAllowed, '*.') === 0) {
                $baseDomain = substr($normalizedAllowed, 2);
                if (preg_match('/\.' . preg_quote($baseDomain, '/') . '$/', $normalizedDomain) || $normalizedDomain === $baseDomain) {
                    return true;
                }
            } elseif ($normalizedAllowed === $normalizedDomain) {
                return true;
            }
        }
        
        // Allow localhost for development
        return in_array($domain, ['localhost', '127.0.0.1']);
    }
}
