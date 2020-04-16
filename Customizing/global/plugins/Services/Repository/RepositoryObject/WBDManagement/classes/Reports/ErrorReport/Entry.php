<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\WBDManagement\Reports\ErrorReport;

/**
 * Contains error data caused by user requests.
 */
class Entry
{
    const STATUS_OPEN = "open";
    const STATUS_RESOLVED = "resolved";
    const STATUS_NOT_RESOLVABLE = "not_resolvable";

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var string | null
     */
    protected $login;

    /**
     * @var string | null
     */
    protected $firstname;

    /**
     * @var string | null
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $gutberaten_id;

    /**
     * @var int
     */
    protected $crs_id;

    /**
     * @var string
     */
    protected $crs_title;

    /**
     * minutes
     *
     * @var int
     */
    protected $learning_time;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \DateTime
     */
    protected $request_date;

    /**
     * @var string
     */
    protected $status;

    public function __construct(
        int $id,
        int $usr_id,
        string $gutberaten_id,
        int $crs_id,
        string $crs_title,
        int $learning_time,
        string $message,
        \DateTime $request_date,
        string $status,
        string $login = null,
        string $firstname = null,
        string $lastname = null
    ) {
        if (!$this->validStatus($status)) {
            throw new \InvalidArgumentException("no valid status in argument " . $status);
        }

        $this->id = $id;
        $this->usr_id = $usr_id;
        $this->gutberaten_id = $gutberaten_id;
        $this->crs_id = $crs_id;
        $this->crs_title = $crs_title;
        $this->learning_time = $learning_time;
        $this->message = $message;
        $this->request_date = $request_date;
        $this->login = $login;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->status = $status;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function getGuteratenId() : string
    {
        return $this->gutberaten_id;
    }

    public function getCrsId() : int
    {
        return $this->crs_id;
    }

    public function getCrsTitle() : string
    {
        return $this->crs_title;
    }

    public function getLearningTime() : int
    {
        return $this->learning_time;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getRequestDate() : \DateTime
    {
        return $this->request_date;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    protected function validStatus(string $status) : bool
    {
        $valid = [
            self::STATUS_OPEN,
            self::STATUS_RESOLVED,
            self::STATUS_NOT_RESOLVABLE
        ];

        return in_array($status, $valid);
    }

    /**
     * @return string | null
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string | null
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string | null
     */
    public function getLastname()
    {
        return $this->lastname;
    }
}
