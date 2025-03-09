<?php

namespace App\Providers;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class CustomEncryptionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('encrypter', function ($app) {
            $config = $app->make('config')->get('app');
            
            // If no key is set, generate a temporary one
            if (empty($config['key'])) {
                $key = base64_encode(random_bytes(32));
                $this->app->make('config')->set('app.key', $key);
                $config['key'] = $key;
            }

            return new Encrypter($this->parseKey($config), $config['cipher']);
        });
    }

    protected function parseKey(array $config)
    {
        if (empty($config['key'])) {
            $key = base64_encode(random_bytes(32));
            $this->app->make('config')->set('app.key', $key);
            return base64_decode($key);
        }

        if (Str::startsWith($key = $config['key'], $prefix = 'base64:')) {
            return base64_decode(Str::after($key, $prefix));
        }

        return base64_decode($key);
    }
} 