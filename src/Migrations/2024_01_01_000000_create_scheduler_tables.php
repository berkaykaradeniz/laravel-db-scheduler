<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scheduled_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // İşin adı
            $table->text('description')->nullable(); // İş açıklaması
            $table->string('command');              // Çalıştırılacak komut (function: veya http:)
            $table->string('parameters')->nullable();// Komut parametreleri (JSON)
            $table->string('frequency_type');        // once, everyMinutes, hourly, daily, etc.
            $table->integer('frequency_value')->nullable(); // Frekans değeri
            $table->timestamp('next_run_at');       // Bir sonraki çalışma zamanı
            $table->timestamp('last_run_at')->nullable(); // Son çalışma zamanı
            $table->nullableMorphs('related');      // İlişkili model (user_id veya başka bir model)
            $table->boolean('is_active')->default(true); // İş aktif mi?
            $table->timestamps();

            // İndeksler
            $table->index('next_run_at');
            $table->index('is_active');
            $table->index('name');
        });

        Schema::create('job_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_job_id')->constrained('scheduled_jobs')->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status'); // running, success, failed
            $table->text('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            // İndeksler
            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_histories');
        Schema::dropIfExists('scheduled_jobs');
    }
}; 