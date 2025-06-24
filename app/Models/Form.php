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
}
