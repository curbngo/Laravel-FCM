<?php

namespace LaravelFCM\Response;

use Monolog\Logger;
use LaravelFCM\Message\Topics;
use Monolog\Handler\StreamHandler;
use Illuminate\Http\Client\Response;

/**
 * Class TopicResponse.
 */
class TopicResponse extends BaseResponse implements TopicResponseContract
{
    const LIMIT_RATE_TOPICS_EXCEEDED = 'TopicsMessageRateExceeded';

    /**
     * @internal
     *
     * @var string
     */
    protected $topic;

    /**
     * @internal
     *
     * @var string
     */
    protected $messageId;

    /**
     * @internal
     *
     * @var string
     */
    protected $error;

    /**
     * @internal
     *
     * @var bool
     */
    protected $needRetry = false;

    /**
     * TopicResponse constructor.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param Topics         $topic
     */
    public function __construct(Response $response, Topics $topic)
    {
        $this->topic = $topic;
        parent::__construct($response);
    }

    /**
     * Parse a v1 success response. Errors are thrown upstream (BaseResponse).
     *
     * @param $responseInJson
     */
    protected function parseResponse($responseInJson)
    {
        if (array_key_exists('name', $responseInJson)) {
            $this->messageId = $responseInJson['name'];
        }

        if ($this->logEnabled) {
            $this->logResponse();
        }
    }

    /**
     * Log the response.
     */
    protected function logResponse()
    {
        $logger = new Logger('Laravel-FCM');
        $logger->pushHandler(new StreamHandler(storage_path('logs/laravel-fcm.log')));

        $topic = $this->topic->build();

        $logMessage = "notification send to topic: ".json_encode($topic);
        if ($this->messageId) {
            $logMessage .= "with success (message-id : $this->messageId)";
        } else {
            $logMessage .= "with error (error : $this->error)";
        }

        $logger->info($logMessage);
    }

    /**
     * true if topic sent with success.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return (bool) $this->messageId;
    }

    /**
     * return error message
     * you should test if it's necessary to resent it.
     *
     * @return string error
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * return true if it's necessary resent it using exponential backoff.
     *
     * @return bool
     */
    public function shouldRetry()
    {
        return $this->needRetry;
    }
}
