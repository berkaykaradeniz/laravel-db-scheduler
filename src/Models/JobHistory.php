<?php

namespace BerkayKaradeniz\LaravelDbScheduler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobHistory extends Model
{
    protected $fillable = [
        'scheduled_job_id',
        'started_at',
        'finished_at',
        'status',
        'output',
        'error'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime'
    ];

    public function scheduledJob(): BelongsTo
    {
        return $this->belongsTo(ScheduledJob::class);
    }
} 