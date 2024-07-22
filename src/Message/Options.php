<?php

namespace LaravelFCM\Message;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Options.
 */
class Options implements Arrayable
{
    /**
     * @internal
     *
     * @var null|string
     */
    protected $collapseKey;

    /**
     * @internal
     *
     * @var null|string
     */
    protected $priority;

    /**
     * @internal
     *
     * @var null|string
     */
    protected $android_priority;

    /**
     * @internal
     *
     * @var null|string
     */
    protected $ios_priority;

    /**
     * @internal
     *
     * @var bool
     */
    protected $contentAvailable;

    /**
     * @internal
     *
     * @var bool
     */
    protected $isMutableContent = false;

    /**
     * @internal
     *
     * @var bool
     */
    protected $delayWhileIdle;

    /**
     * @internal
     *
     * @var int|null
     */
    protected $timeToLive;

    /**
     * @internal
     *
     * @var null|string
     */
    protected $restrictedPackageName;

    /**
     * @internal
     *
     * @var bool
     */
    protected $isDryRun = false;

    /**
     * @internal
     *
     * @var string
     */
    protected $iosImage = null;

    /**
     * @internal
     *
     * @var string
     */
    protected $iosAnalyticsLabel = null;

    /**
     * @internal
     *
     * @var array
     */
    protected $apns_options = [];
    /**
     * @internal
     *
     * @var array
     */
    protected $android_options = [];

    /**
     * Options constructor.
     *
     * @param OptionsBuilder $builder
     */
    public function __construct(OptionsBuilder $builder)
    {
        $this->collapseKey = $builder->getCollapseKey();
        $this->priority = $builder->getPriority();
        $this->android_priority = $builder->getAndroidPriority();
        $this->ios_priority = $builder->getIosPriority();
        $this->contentAvailable = $builder->isContentAvailable();
        $this->isMutableContent = $builder->isMutableContent();
        $this->delayWhileIdle = $builder->isDelayWhileIdle();
        $this->timeToLive = $builder->getTimeToLive();
        $this->restrictedPackageName = $builder->getRestrictedPackageName();
        $this->isDryRun = $builder->isDryRun();


        $this->android_options = $builder->getAndroidOptions();
        $this->apns_options = $builder->getAPNSOptions();
    }

    /**
     * Transform Option to array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(['android' => $this->android_options, 'apns' => $this->apns_options]);
    }
}
