<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmissionAttachment extends Model
{
    protected $fillable = [
        'form_submission_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'url',
    ];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }
}