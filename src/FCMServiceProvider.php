<?php

namespace LaravelFCM;

use Illuminate\Support\Str;
use LaravelFCM\Request\FCMAuth;
use LaravelFCM\Sender\FCMSender;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class FCMServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        if (Str::contains($this->app->version(), 'Lumen')) {
            $this->app->configure('fcm');
        } else {
            $this->publishes([
                __DIR__.'/../config/fcm.php' => config_path('fcm.php'),
            ]);
        }
    }

    public function register(): void
    {
        if (!Str::contains($this->app->version(), 'Lumen')) {
            $this->mergeConfigFrom(__DIR__.'/../config/fcm.php', 'fcm');
        }

        $this->app->singleton(FCMAuth::class, function () {
            return new FCMAuth();
        });

        $this->app->bind('fcm.sender', function ($app) {
            $config = $app['config']->get('fcm.http');

            return new FCMSender($config['server_send_url'], $config['timeout'] ?? 30.0);
        });
    }

    public function provides()
    {
        return [FCMAuth::class, 'fcm.sender'];
    }
}
