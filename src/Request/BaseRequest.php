<?php

namespace LaravelFCM\Request;

use LaravelFCM\Request\FCMAuth;

/**
 * Class BaseRequest.
 */
abstract class BaseRequest
{
    /**
     * @internal
     *
     * @var array
     */
    protected $config;
    protected $fcm_auth;

    /**
     * BaseRequest constructor.
     */
    public function __construct()
    {
        $this->config = app('config')->get('fcm.http', []);
        $this->fcm_auth = app(FCMAuth::class);
    }

    /**
     * Build the header for the request.
     *
     * @return array
     */
    protected function buildRequestHeader()
    {
        return [
            'Authorization' => 'Bearer ' . $this->fcm_auth->getAccessToken(),
        ];
    }

    /**
     * Build the body of the request.
     *
     * @return mixed
     */
    abstract protected function buildBody();

    /**
     * Return the request in array form.
     *
     * @return array
     */
    public function build()
    {
        return [
            'headers' => $this->buildRequestHeader(),
            'json' => $this->buildBody(),
        ];
    }
}
