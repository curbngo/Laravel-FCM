<?php

namespace LaravelFCM\Sender;

use Illuminate\Support\Facades\Http;
use LaravelFCM\Message\Topics;
use LaravelFCM\Request\Request;
use LaravelFCM\Message\Options;
use LaravelFCM\Message\PayloadData;
use LaravelFCM\Response\TopicResponse;
use LaravelFCM\Response\DownstreamResponse;
use LaravelFCM\Message\PayloadNotification;
use LaravelFCM\Response\Exceptions\ServerResponseException;
use LaravelFCM\Response\Exceptions\InvalidRequestException;
use LaravelFCM\Response\Exceptions\UnauthorizedRequestException;

/**
 * Class FCMSender.
 */
class FCMSender
{
    public const MAX_TOKEN_PER_REQUEST = 1;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var float
     */
    protected $timeout;

    public function __construct($url, $timeout = 30.0)
    {
        $this->url = $url;
        $this->timeout = $timeout;
    }

    /**
     * Send a downstream message to a single token (string) or many tokens (array).
     *
     * @return DownstreamResponse
     */
    public function sendTo($to, Options $options = null, PayloadNotification $notification = null, PayloadData $data = null)
    {
        if (is_array($to) && !empty($to)) {
            $response = DownstreamResponse::makeEmpty();
            $partialTokens = array_chunk($to, self::MAX_TOKEN_PER_REQUEST, false);

            foreach ($partialTokens as $tokens) {
                $request = new Request($tokens, $options, $notification, $data);
                $httpResponse = $this->post($request);

                try {
                    $response->merge(new DownstreamResponse($httpResponse, $tokens));
                } catch (InvalidRequestException | UnauthorizedRequestException | ServerResponseException $e) {
                    $response->addFailedToken(reset($tokens), $e);
                }
            }

            return $response;
        }

        $request = new Request($to, $options, $notification, $data);
        $httpResponse = $this->post($request);

        return new DownstreamResponse($httpResponse, $to);
    }

    /**
     * Send a message to one or more topics.
     *
     * @return TopicResponse
     */
    public function sendToTopic(Topics $topics, Options $options = null, PayloadNotification $notification = null, PayloadData $data = null)
    {
        $request = new Request(null, $options, $notification, $data, $topics);
        $httpResponse = $this->post($request);

        return new TopicResponse($httpResponse, $topics);
    }

    /**
     * @internal
     *
     * @param \LaravelFCM\Request\Request $request
     *
     * @return \Illuminate\Http\Client\Response
     */
    protected function post($request)
    {
        $built = $request->build();

        return Http::withHeaders($built['headers'])
            ->timeout($this->timeout)
            ->post($this->url, $built['json']);
    }
}
