<?php

use LaravelFCM\Request\FCMAuth;
use Orchestra\Testbench\TestCase;

abstract class FCMTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \LaravelFCM\FCMServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('fcm.driver', 'http');
        $app['config']->set('fcm.http.timeout', 20);
        $app['config']->set('fcm.http.server_send_url', 'http://test.test');
        $app['config']->set('fcm.http.sender_id', 'SENDER_ID');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $auth = Mockery::mock(FCMAuth::class);
        $auth->shouldReceive('getAccessToken')->andReturn('fake-access-token');
        $this->app->instance(FCMAuth::class, $auth);
    }
}
