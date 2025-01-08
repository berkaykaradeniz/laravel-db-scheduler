<?php

use BerkayKaradeniz\LaravelDbScheduler\Http\Controllers\SchedulerController;
use Illuminate\Support\Facades\Route;

Route::post('/scheduler/run', [SchedulerController::class, 'run'])
    ->name('scheduler.run'); 