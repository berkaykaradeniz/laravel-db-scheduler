<?php

namespace BerkayKaradeniz\LaravelDbScheduler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduledJob extends Model
{
    protected $fillable = [
        'name',
        'description',
        'command',
        'parameters',
        'frequency_type',
        'frequency_value',
        'next_run_at',
        'last_run_at',
        'related_type',
        'related_id',
        'is_active'
    ];

    protected $casts = [
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
        'is_active' => 'boolean',
        'parameters' => 'array'
    ];

    public function histories(): HasMany
    {
        return $this->hasMany(JobHistory::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function calculateNextRunTime(): void
    {
        try {
            $now = Carbon::now();
            
            switch ($this->frequency_type) {
                case 'once':
                    // Do nothing, next_run_at is already set
                    break;
                case 'everyMinutes':
                    $this->next_run_at = $now->addMinutes($this->frequency_value);
                    break;
                case 'hourly':
                    $this->next_run_at = $now->addHours($this->frequency_value ?? 1);
                    break;
                case 'daily':
                    $this->next_run_at = $now->addDays($this->frequency_value ?? 1);
                    break;
                case 'weekly':
                    $this->next_run_at = $now->addWeeks($this->frequency_value ?? 1);
                    break;
                case 'monthly':
                    $this->next_run_at = $now->addMonths($this->frequency_value ?? 1);
                    break;
                default:
                    Log::warning("Invalid frequency type: {$this->frequency_type} for job: {$this->name}");
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Error calculating next run time for job {$this->name}: " . $e->getMessage());
        }
    }

    public function isRunning(): bool
    {
        return $this->histories()
            ->where('status', 'running')
            ->exists();
    }

    public function getLastStatus(): ?string
    {
        $lastHistory = $this->histories()
            ->latest()
            ->first();

        return $lastHistory ? $lastHistory->status : null;
    }

    public function getLastOutput(): ?string
    {
        $lastHistory = $this->histories()
            ->latest()
            ->first();

        return $lastHistory ? $lastHistory->output : null;
    }

    public function getLastError(): ?string
    {
        $lastHistory = $this->histories()
            ->latest()
            ->where('status', 'failed')
            ->first();

        return $lastHistory ? $lastHistory->error : null;
    }
} 