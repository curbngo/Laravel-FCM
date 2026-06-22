<?php

namespace LaravelFCM\Response;

use Illuminate\Http\Client\Response;
use LaravelFCM\Response\Exceptions\ServerResponseException;
use LaravelFCM\Response\Exceptions\InvalidRequestException;
use LaravelFCM\Response\Exceptions\UnauthorizedRequestException;

/**
 * Class BaseResponse.
 */
abstract class BaseResponse
{
    const SUCCESS = 'success';
    const FAILURE = 'failure';
    const ERROR = 'error';
    const MESSAGE_ID = 'message_id';

    /**
     * @var bool
     */
    protected $logEnabled = false;

    /**
     * BaseResponse constructor.
     *
     * @param \Illuminate\Http\Client\Response $response
     */
    public function __construct(Response $response)
    {
        $this->isJsonResponse($response);
        $this->logEnabled = app('config')->get('fcm.log_enabled', false);
        $responseInJson = $response->json() ?? [];
        $this->parseResponse($responseInJson);
    }

    /**
     * @param \Illuminate\Http\Client\Response $response
     *
     * @throws InvalidRequestException
     * @throws ServerResponseException
     * @throws UnauthorizedRequestException
     */
    private function isJsonResponse(Response $response)
    {
        $status = $response->status();

        if ($status == 200) {
            return;
        }

        if ($status == 400) {
            throw new InvalidRequestException($response);
        }

        if ($status == 401) {
            throw new UnauthorizedRequestException($response);
        }

        throw new ServerResponseException($response);
    }

    /**
     * parse the response.
     *
     * @param array $responseInJson
     */
    abstract protected function parseResponse($responseInJson);

    /**
     * Log the response.
     */
    abstract protected function logResponse();
}
