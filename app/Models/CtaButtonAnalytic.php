<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtaButtonAnalytic extends Model
{
    protected $fillable = [
        'cta_button_id',
        'cta_button_rule_id',
        'page_url',
        'referrer_domain',
        'click_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'click_count' => 'integer',
    ];

    public function ctaButton(): BelongsTo
    {
        return $this->belongsTo(CtaButton::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(CtaButtonRule::class, 'cta_button_rule_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }
}
