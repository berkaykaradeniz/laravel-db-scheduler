# Laravel DB Scheduler

Laravel için veritabanı tabanlı iş zamanlayıcı paketi. Bu paket, geleneksel cron jobs ve Laravel Queue sistemine alternatif olarak geliştirilmiştir.

## Özellikler

- Veritabanı tabanlı iş zamanlaması
- Herhangi bir cron yapılandırması gerektirmez
- Kolay kurulum ve kullanım
- Her iş için özel zamanlama seçenekleri
- İşlerin durumunu takip etme
- User veya diğer model ilişkileri ile entegrasyon
- Web arayüzü ile iş yönetimi

## Kurulum

Composer ile paketi projenize ekleyin:

```bash
composer require berkaykaradeniz/laravel-db-scheduler
```

Servis sağlayıcısını `config/app.php` dosyasına ekleyin:

```php
'providers' => [
    // ...
    BerkayKaradeniz\LaravelDbScheduler\SchedulerServiceProvider::class,
];
```

Migrationları çalıştırın:

```bash
php artisan migrate
```

## Kullanım

### İş Tanımlama

```php
use BerkayKaradeniz\LaravelDbScheduler\Facades\DbScheduler;

// Basit bir iş zamanlamak
DbScheduler::create()
    ->command('emails:send')
    ->everyMinutes(30)
    ->save();

// User ile ilişkili iş
DbScheduler::create()
    ->command('user:notify')
    ->relatedModel('user', 1)
    ->runAt('2024-01-01 00:00:00')
    ->save();
```

### İş Çalıştırma

Tek bir komut ile tüm zamanlanmış işleri çalıştırın:

```bash
php artisan db-scheduler:run
```

## Veritabanı Yapısı

Paket aşağıdaki tabloları oluşturur:

- `scheduled_jobs`: Zamanlanmış işlerin ana tablosu
- `job_histories`: İş çalışma geçmişi

## Gereksinimler

- PHP >= 8.0
- Laravel >= 9.0

## Lisans

MIT

## Yazar

**Berkay Karadeniz**
- GitHub: [@berkaykaradeniz](https://github.com/berkaykaradeniz)
- LinkedIn: [Berkay Karadeniz](https://www.linkedin.com/in/berkay-karadeniz-60126b12b/)

## Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun 