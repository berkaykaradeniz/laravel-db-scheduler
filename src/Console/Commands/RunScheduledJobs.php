<?php

namespace BerkayKaradeniz\LaravelDbScheduler\Console\Commands;

use BerkayKaradeniz\LaravelDbScheduler\Models\ScheduledJob;
use BerkayKaradeniz\LaravelDbScheduler\Models\JobHistory;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RunScheduledJobs extends Command
{
    protected $signature = 'db-scheduler:run';
    protected $description = 'Zamanlanmış işleri çalıştır';

    public function handle()
    {
        $this->info('Zamanlanmış işler kontrol ediliyor...');

        $jobs = ScheduledJob::query()
            ->where('is_active', true)
            ->where('next_run_at', '<=', Carbon::now())
            ->get();

        foreach ($jobs as $job) {
            $this->runJob($job);
        }

        $this->info('İşlem tamamlandı.');
    }

    protected function runJob(ScheduledJob $job)
    {
        $history = JobHistory::create([
            'scheduled_job_id' => $job->id,
            'started_at' => Carbon::now(),
            'status' => 'running'
        ]);

        try {
            $this->info("İş çalıştırılıyor: {$job->command}");
            
            // Komutu çalıştır
            $output = [];
            $exitCode = 0;
            
            if (str_contains($job->command, 'artisan')) {
                $command = str_replace('php artisan ', '', $job->command);
                $exitCode = $this->call($command, json_decode($job->parameters ?? '[]', true));
            } else {
                exec($job->command, $output, $exitCode);
            }

            $history->update([
                'finished_at' => Carbon::now(),
                'status' => $exitCode === 0 ? 'success' : 'failed',
                'output' => implode("\n", $output),
            ]);

            // Bir sonraki çalışma zamanını hesapla
            $job->last_run_at = Carbon::now();
            $job->calculateNextRunTime();
            $job->save();

        } catch (\Exception $e) {
            $history->update([
                'finished_at' => Carbon::now(),
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);

            $this->error("Hata: {$e->getMessage()}");
        }
    }
} 