<?php

namespace LaravelFCM\Response\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

/**
 * Class UnauthorizedRequestException.
 */
class UnauthorizedRequestException extends Exception
{
    /**
     * @param \Illuminate\Http\Client\Response $response
     */
    public function __construct(Response $response)
    {
        parent::__construct('FCM service account credentials are invalid or unauthorized', $response->status());
    }
}
