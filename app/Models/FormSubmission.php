<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'issue_description',
        'priority',
        'submitter_name',
        'submitter_email',
        'page_url',
        'status',
        'browser_name',
        'browser_version',
        'operating_system',
        'device_type',
        'screen_resolution',
        'viewport_size',
        'technical_metadata',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'technical_metadata' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FormSubmissionAttachment::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function markAsNew(): void
    {
        $this->update(['status' => 'new']);
    }

    public function markAsWip(): void
    {
        $this->update(['status' => 'wip']);
    }

    public function markAsAgencyReview(): void
    {
        $this->update(['status' => 'agency_review']);
    }

    public function markAsClientReview(): void
    {
        $this->update(['status' => 'client_review']);
    }

    public function markAsOnHold(): void
    {
        $this->update(['status' => 'on_hold']);
    }

    public function markAsConcluded(): void
    {
        $this->update(['status' => 'concluded']);
    }
}
