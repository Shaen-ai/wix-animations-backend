<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $assetsLink = public_path('assets');
        $assetsTarget = dirname(base_path()) . '/assets';
        if (!file_exists($assetsLink) && is_dir($assetsTarget)) {
            symlink($assetsTarget, $assetsLink);
        }
    }
}
