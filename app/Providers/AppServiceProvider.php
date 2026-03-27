<?php


namespace App\Providers;


use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL; // <--- AGREGA ESTA LÍNEA


class AppServiceProvider extends ServiceProvider

{

    public function boot(): void

    {

        // Forzamos a que todas las URLs generadas sean HTTPS

        URL::forceScheme('https');

    }

}
