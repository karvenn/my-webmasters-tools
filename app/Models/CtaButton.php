<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CtaButton extends Model
{
    protected $fillable = [
        'user_id',
        'button_name',
        'embed_token',
        'is_active',
        'allowed_domains',
        'button_color',
        'button_text_color',
        'button_size',
        'button_position',
        'button_text',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_domains' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($button) {
            $button->embed_token = Str::random(32);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CtaButtonRule::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(CtaButtonAnalytic::class);
    }

    public function getActiveRulesAttribute()
    {
        return $this->rules()->where('is_active', true)->orderBy('priority', 'desc')->get();
    }

    public function getClicksCountAttribute(): int
    {
        return $this->analytics()->sum('click_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function isDomainAllowed(string $domain): bool
    {
        $allowedDomains = $this->allowed_domains ?? [];
        
        if (empty($allowedDomains)) {
            return false;
        }
        
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
