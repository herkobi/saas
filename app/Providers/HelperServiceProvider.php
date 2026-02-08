<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class HelperServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $helperDir = app_path('Helpers');

        if (! is_dir($helperDir)) {
            return;
        }

        // İstersen sadece *_helpers.php gibi isimlendirmeyle de sınırlayabilirsin:
        // $pattern = $helperDir.'/*_helpers.php';
        $pattern = $helperDir.'/*_helpers.php';

        // Dosya listesinin "değişip değişmediğini" anlamak için imza üret.
        $signature = $this->helpersSignature($pattern);

        $cacheKey = "helper_files:{$signature}";

        $files = Cache::rememberForever($cacheKey, function () use ($pattern) {
            return File::glob($pattern) ?: [];
        });

        foreach ($files as $file) {
            require_once $file;
        }
    }

    private function helpersSignature(string $pattern): string
    {
        $files = File::glob($pattern) ?: [];

        // Aynı listeyi aynı imzaya dönüştürmek için sıralayalım.
        sort($files);

        // Dosya adı + son değiştirilme zamanı => imza
        $bits = [];
        foreach ($files as $file) {
            $bits[] = $file.'|'.@filemtime($file);
        }

        return md5(implode(';', $bits));
    }
}
