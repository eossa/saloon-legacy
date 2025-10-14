<?php

namespace Saloon\Tests\Helpers;

use DateTime;
use DateInterval;
use DateTimeImmutable;

final class Date
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Constructor
     */
    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Construct
     *
     * @return $this
     */
    public static function now()
    {
        return new self(new DateTime);
    }

    /**
     * Add seconds
     *
     * @param int $seconds
     *
     * @return $this
     */
    public function addSeconds($seconds)
    {
        $this->dateTime->add(
            DateInterval::createFromDateString($seconds . ' seconds')
        );

        return $this;
    }

    /**
     * Subtract minutes
     *
     * @param int $minutes
     *
     * @return $this
     */
    public function subMinutes($minutes)
    {
        $this->dateTime->sub(
            DateInterval::createFromDateString($minutes . ' minutes')
        );

        return $this;
    }

    /**
     * Get the datetime instance
     *
     * @return DateTimeImmutable
     */
    public function toDateTime()
    {
        return DateTimeImmutable::createFromMutable($this->dateTime);
    }
}
