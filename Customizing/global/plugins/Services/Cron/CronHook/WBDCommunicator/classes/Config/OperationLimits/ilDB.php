<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\OperationLimits;

class ilDB implements DB
{
    const PREFIX = 'wbd_ols_';
    const INFIX_OFFSET = 'offset_';
    const INFIX_LIMIT = 'limit_';
    const START_DATE = 'start_date_';

    const KEY_REQUEST = 'request';
    const KEY_ANNOUNCE = 'announce';
    const KEY_CANCEL = 'cancel';

    /**
     * @var \ilSetting
     */
    protected $settings;

    public function __construct(\ilSetting $set)
    {
        $this->settings = $set;
    }

    public function getOffsetForRequest() : int
    {
        return (int) $this->settings->get(self::PREFIX . self::INFIX_OFFSET . self::KEY_REQUEST);
    }

    public function setOffsetForRequest(int $offset)
    {
        $this->settings->set(self::PREFIX . self::INFIX_OFFSET . self::KEY_REQUEST, $offset);
    }

    public function getLimitForRequest() : int
    {
        return (int) $this->settings->get(self::PREFIX . self::INFIX_LIMIT . self::KEY_REQUEST);
    }

    public function setLimitForRequest(int $limit)
    {
        $this->settings->set(self::PREFIX . self::INFIX_LIMIT . self::KEY_REQUEST, $limit);
    }

    public function getMaxNumberOfAnnouncemence() : int
    {
        return (int) $this->settings->get(
            self::PREFIX . self::INFIX_LIMIT . self::KEY_ANNOUNCE,
            0
        );
    }

    public function setMaxNumberOfAnnouncemence(int $max)
    {
        $this->settings->set(self::PREFIX . self::INFIX_LIMIT . self::KEY_ANNOUNCE, $max);
    }

    public function getMaxNumberOfCancellations() : int
    {
        return (int) $this->settings->get(
            self::PREFIX . self::INFIX_LIMIT . self::KEY_CANCEL,
            0
        );
    }

    public function setMaxNumberOfCancellations(int $max)
    {
        $this->settings->set(self::PREFIX . self::INFIX_LIMIT . self::KEY_CANCEL, $max);
    }

    public function setStartDateForAnnouncement(\DateTime $date)
    {
        $this->settings->set(
            self::PREFIX . self::START_DATE . self::KEY_ANNOUNCE,
            $date->format('Y-m-d')
        );
    }

    public function getStartDateForAnnouncement() : \DateTime
    {
        $date = $this->settings->get(
            self::PREFIX . self::START_DATE . self::KEY_ANNOUNCE,
            null
        );

        if (is_null($date)) {
            throw new \LogicException("No start date defined");
        }

        return \DateTime::createFromFormat("Y-m-d", $date);
    }
}
