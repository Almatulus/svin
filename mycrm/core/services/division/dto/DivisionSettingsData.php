<?php

namespace core\services\division\dto;

class DivisionSettingsData
{
    /** @var string */
    private $notification_time_before_lunch;
    /** @var string */
    private $notification_time_after_lunch;

    /**
     * DivisionSettingsDto constructor.
     * @param string $notification_time_before_lunch
     * @param string $notification_time_after_lunch
     */
    public function __construct(
        string $notification_time_before_lunch = null,
        string $notification_time_after_lunch = null
    ) {
        $this->notification_time_before_lunch = $notification_time_before_lunch;
        $this->notification_time_after_lunch = $notification_time_after_lunch;
    }

    /**
     * @return string
     */
    public function getNotificationTimeBeforeDelimiter(): string
    {
        return $this->notification_time_before_lunch;
    }

    /**
     * @return string
     */
    public function getNotificationTimeAfterDelimiter(): string
    {
        return $this->notification_time_after_lunch;
    }
}