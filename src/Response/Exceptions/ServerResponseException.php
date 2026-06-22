<?php

namespace LaravelFCM\Response\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

/**
 * Class ServerResponseException.
 */
class ServerResponseException extends Exception
{
    /**
     * retry after.
     *
     * @var string|null
     */
    public $retryAfter;

    /**
     * @param \Illuminate\Http\Client\Response $response
     */
    public function __construct(Response $response)
    {
        $this->retryAfter = $response->header('Retry-After') ?: null;

        parent::__construct($response->body(), $response->status());
    }
}
