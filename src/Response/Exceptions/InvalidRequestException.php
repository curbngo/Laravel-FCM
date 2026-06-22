<?php

namespace LaravelFCM\Response\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

/**
 * Class InvalidRequestException.
 */
class InvalidRequestException extends Exception
{
    /**
     * @param \Illuminate\Http\Client\Response $response
     */
    public function __construct(Response $response)
    {
        parent::__construct($response->body(), $response->status());
    }
}
