<?php

namespace LaravelFCM\Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Http\Client\Response;

/**
 * Class DownstreamResponse.
 */
class DownstreamResponse extends BaseResponse implements DownstreamResponseContract
{
    public const NAME = 'name';
    public const UNREGISTERED = 'UNREGISTERED';
    public const INVALID_ARGUMENT = 'INVALID_ARGUMENT';
    public const SENDER_ID_MISMATCH = 'SENDER_ID_MISMATCH';

    /**
     * @internal
     *
     * @var int
     */
    protected $numberTokensSuccess = 0;

    /**
     * @internal
     *
     * @var int
     */
    protected $numberTokensFailure = 0;

    /**
     * @internal
     *
     * @var int
     */
    protected $numberTokenModify = 0;

    /**
     * @internal
     *
     * @var
     */
    protected $messageId;

    /**
     * @internal
     *
     * @var array
     */
    protected $tokensToDelete = [];

    /**
     * @internal
     *
     * @var array
     */
    protected $tokensToModify = [];
    /**
     * @internal
     *
     * @var array
     */
    protected $tokensToRetry = [];

    /**
     * @internal
     *
     * @var array
     */
    protected $tokensWithError = [];

    /**
     * @internal
     *
     * @var bool
     */
    protected $hasMissingToken = false;

    /**
     * @internal
     *
     * @var array
     */
    private $tokens;

    /**
     * DownstreamResponse constructor.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param                $tokens
     */
    public function __construct(Response $response, $tokens)
    {
        $this->tokens = is_string($tokens) ? [$tokens] : $tokens;

        parent::__construct($response);
    }

    /**
     * Build an empty accumulator response (used when sending to many tokens).
     *
     * @return self
     */
    public static function makeEmpty()
    {
        $instance = (new \ReflectionClass(self::class))->newInstanceWithoutConstructor();
        $instance->tokens = [];

        return $instance;
    }

    /**
     * Record a token whose v1 send failed, classifying it from the exception.
     *
     * @param string     $token
     * @param \Exception $e
     */
    public function addFailedToken($token, \Exception $e)
    {
        $this->numberTokensFailure++;

        $status = null;
        $decoded = json_decode($e->getMessage(), true);
        if (is_array($decoded) && isset($decoded['error']['status'])) {
            $status = $decoded['error']['status'];
        }
        $code = $e->getCode();

        if (in_array($status, ['UNREGISTERED', 'INVALID_ARGUMENT', 'SENDER_ID_MISMATCH']) || in_array($code, [400, 403, 404])) {
            $this->tokensToDelete[] = $token;

            return;
        }

        if (in_array($status, ['UNAVAILABLE', 'INTERNAL', 'QUOTA_EXCEEDED']) || in_array($code, [429, 500, 503])) {
            $this->tokensToRetry[] = $token;

            return;
        }

        $this->tokensWithError[$token] = $status ?? (string) $code;
    }

    /**
     * Parse a v1 success response. Errors are thrown upstream (BaseResponse).
     *
     * @param array $responseInJson
     */
    protected function parseResponse($responseInJson)
    {
        $this->numberTokensSuccess = count($this->tokens);

        if (array_key_exists(self::NAME, $responseInJson)) {
            $this->messageId = $responseInJson[self::NAME];
        }

        if ($this->logEnabled) {
            $this->logResponse();
        }
    }

    /**
     * @internal
     */
    protected function logResponse()
    {
        $logger = new Logger('Laravel-FCM');
        $logger->pushHandler(new StreamHandler(storage_path('logs/laravel-fcm.log')));

        $logMessage = 'notification send to '.count($this->tokens).' devices'.PHP_EOL;
        $logMessage .= 'success: '.$this->numberTokensSuccess.PHP_EOL;
        $logMessage .= 'failures: '.$this->numberTokensFailure.PHP_EOL;
        $logMessage .= 'number of modified token : '.$this->numberTokenModify.PHP_EOL;

        $logger->info($logMessage);
    }

    /**
     * Merge two response.
     *
     * @param DownstreamResponse $response
     */
    public function merge(DownstreamResponse $response)
    {
        $this->numberTokensSuccess += $response->numberSuccess();
        $this->numberTokensFailure += $response->numberFailure();
        $this->numberTokenModify += $response->numberModification();

        $this->tokensToDelete = array_merge($this->tokensToDelete, $response->tokensToDelete());
        $this->tokensToModify = array_merge($this->tokensToModify, $response->tokensToModify());
        $this->tokensToRetry = array_merge($this->tokensToRetry, $response->tokensToRetry());
        $this->tokensWithError = array_merge($this->tokensWithError, $response->tokensWithError());
    }

    /**
     * Get the number of device reached with success.
     *
     * @return int
     */
    public function numberSuccess()
    {
        return $this->numberTokensSuccess;
    }

    /**
     * Get the number of device which thrown an error.
     *
     * @return int
     */
    public function numberFailure()
    {
        return $this->numberTokensFailure;
    }

    /**
     * Get the number of device that you need to modify their token.
     *
     * @return int
     */
    public function numberModification()
    {
        return $this->numberTokenModify;
    }

    /**
     * get token to delete.
     *
     * remove all tokens returned by this method in your database
     *
     * @return array
     */
    public function tokensToDelete()
    {
        return $this->tokensToDelete;
    }

    /**
     * get token to modify.
     *
     * key: oldToken
     * value: new token
     *
     * find the old token in your database and replace it with the new one
     *
     * @return array
     */
    public function tokensToModify()
    {
        return $this->tokensToModify;
    }

    /**
     * Get tokens that you should resend using exponential backoff.
     *
     * @return array
     */
    public function tokensToRetry()
    {
        return $this->tokensToRetry;
    }

    /**
     * Get tokens that thrown an error.
     *
     * key : token
     * value : error
     *
     * In production, remove these tokens from you database
     *
     * @return array
     */
    public function tokensWithError()
    {
        return $this->tokensWithError;
    }

    /**
     * check if missing tokens was given to the request
     * If true, remove all the empty token in your database.
     *
     * @return bool
     */
    public function hasMissingToken()
    {
        return $this->hasMissingToken;
    }
}
