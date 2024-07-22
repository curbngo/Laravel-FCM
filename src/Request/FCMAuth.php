<?php

namespace LaravelFCM\Request;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Illuminate\Support\Facades\Cache;

class FCMAuth
{
    private const AUTH_KEY = "__fcm_access_key";

    protected $config;

    /**
     * BaseRequest constructor.
     */
    public function __construct()
    {
        $this->config = app('config')->get('fcm.http', []);
    }

    public function getAccessToken()
    {
        $access_key = $this->config['fcm_cache_access_key'] ?? static::AUTH_KEY;

        $token = Cache::get($access_key);

        if (!$token) {
            $d = $this->getToken();

            if (!isset($d['access_token'])) {
                throw new \Exception("Failed to fetch new token please check FCM setup");
            }

            $expires_in = isset($d['expires_in']) ? $d['expires_in'] : 60;

            $token = Cache::remember($access_key, $expires_in, function () use ($d) {
                return $d['access_token'];
            });
        }

        return $token;
    }

    protected function getToken()
    {
        $scopes = [
            'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $serviceAccountPath = base_path() . "/" . $this->config['fcm_admin_sdk_file'];
        $file = @file_get_contents($serviceAccountPath);
        if ($file) {
            $serviceAccount = json_decode($file, true);
            $credentials = new ServiceAccountCredentials($scopes, $serviceAccount);
            return $credentials->fetchAuthToken();
        }
        throw new \Exception("Failed to open FCM config file");
    }
}
