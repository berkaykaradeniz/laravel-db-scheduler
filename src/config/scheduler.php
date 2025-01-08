<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | API anahtarı, HTTP endpoint'ini korumak için kullanılır.
    |
    */
    'api_key' => env('SCHEDULER_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, zamanlayıcının varsayılan davranışını belirler.
    |
    */

    // Bir işin maksimum çalışma süresi (saniye)
    'timeout' => 3600,

    // Başarısız işler için yeniden deneme sayısı
    'max_attempts' => 3,

    // İş geçmişi kayıtlarının saklanma süresi (gün)
    'history_retention_days' => 30,

    // Varsayılan frekans tipi
    'default_frequency' => 'daily',

    // Kullanılabilir frekans tipleri
    'frequency_types' => [
        'once' => 'Bir kez',
        'everyMinutes' => 'Her X dakika',
        'hourly' => 'Saatlik',
        'daily' => 'Günlük',
        'weekly' => 'Haftalık',
        'monthly' => 'Aylık',
    ],
]; 