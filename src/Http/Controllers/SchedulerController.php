<?php

namespace BerkayKaradeniz\LaravelDbScheduler\Http\Controllers;

use BerkayKaradeniz\LaravelDbScheduler\Models\ScheduledJob;
use BerkayKaradeniz\LaravelDbScheduler\Models\JobHistory;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SchedulerController extends Controller
{
    public function run(Request $request)
    {
        // API anahtarını kontrol et
        if ($request->header('X-Scheduler-Key') !== config('scheduler.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $jobs = ScheduledJob::query()
            ->where('is_active', true)
            ->where('next_run_at', '<=', Carbon::now())
            ->get();

        $results = [];

        foreach ($jobs as $job) {
            $results[] = $this->runJob($job);
        }

        return response()->json([
            'message' => 'Jobs processed',
            'jobs_count' => count($results),
            'results' => $results
        ]);
    }

    protected function runJob(ScheduledJob $job)
    {
        $history = JobHistory::create([
            'scheduled_job_id' => $job->id,
            'started_at' => Carbon::now(),
            'status' => 'running'
        ]);

        try {
            // Komutu çalıştır
            $output = [];
            $exitCode = 0;
            
            // PHP fonksiyonu çağır veya HTTP isteği yap
            if (str_starts_with($job->command, 'function:')) {
                // PHP fonksiyonu çağır
                $functionName = str_replace('function:', '', $job->command);
                if (is_callable($functionName)) {
                    $result = call_user_func($functionName, json_decode($job->parameters ?? '[]', true));
                    $exitCode = $result === false ? 1 : 0;
                    $output[] = is_string($result) ? $result : json_encode($result);
                } else {
                    throw new \Exception("Function {$functionName} not found");
                }
            } elseif (str_starts_with($job->command, 'http:') || str_starts_with($job->command, 'https:')) {
                // HTTP isteği yap
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $job->command);
                $exitCode = $response->getStatusCode() >= 400 ? 1 : 0;
                $output[] = (string) $response->getBody();
            } else {
                throw new \Exception("Invalid command type. Use 'function:' or 'http(s):'");
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

            return [
                'job_id' => $job->id,
                'status' => 'success',
                'output' => $output
            ];

        } catch (\Exception $e) {
            $history->update([
                'finished_at' => Carbon::now(),
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);

            return [
                'job_id' => $job->id,
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
} 