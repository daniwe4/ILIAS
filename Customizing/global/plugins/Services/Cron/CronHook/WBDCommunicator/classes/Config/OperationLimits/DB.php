<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\OperationLimits;

interface DB
{
    public function getOffsetForRequest() : int;
    public function setOffsetForRequest(int $offset);
    public function getLimitForRequest() : int;
    public function setLimitForRequest(int $limit);
    public function getMaxNumberOfAnnouncemence() : int;
    public function setMaxNumberOfAnnouncemence(int $max);
    public function getMaxNumberOfCancellations() : int;
    public function setMaxNumberOfCancellations(int $max);
    public function setStartDateForAnnouncement(\DateTime $date);
    public function getStartDateForAnnouncement() : \DateTime;
}
