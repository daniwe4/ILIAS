<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\History;

/**
 * A UserActivity is the typed interaction of a user with a course.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class UserActivity
{
    const ACT_TYPE_BOOKED = 1;
    const ACT_TYPE_BOOKED_WAITING = 2;
    const ACT_TYPE_CANCELLED = 3;
    const ACT_TYPE_COMPLETED = 4;
    const ACT_TYPE_FAILED = 5;
    const ACT_TYPE_CANCELLED_WAITING = 6;
    const ACT_TYPE_REQUEST_PENDING = 7;
    const ACT_TYPE_REQUEST_DECLINED = 8;
    const ACT_TYPE_REQUEST_APPROVED = 9;

    protected static $valid_types = [
        self::ACT_TYPE_BOOKED,
        self::ACT_TYPE_BOOKED_WAITING,
        self::ACT_TYPE_CANCELLED,
        self::ACT_TYPE_COMPLETED,
        self::ACT_TYPE_FAILED,
        self::ACT_TYPE_CANCELLED_WAITING,
        self::ACT_TYPE_REQUEST_PENDING,
        self::ACT_TYPE_REQUEST_DECLINED,
        self::ACT_TYPE_REQUEST_APPROVED
    ];

    /**
     * @var int
     */
    protected $activity_type;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int
     */
    protected $crs_id;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $crs_type;

    /**
     * @var string
     */
    protected $crs_title;

    /**
     * @var string
     */
    protected $usr_idd_time;

    /**
     * @var \DateTime|null
     */
    protected $crs_start_date;

    /**
     * @var \DateTime|null
     */
    protected $crs_end_date;

    /**
     * @var string
     */
    protected $crs_idd_time;

    /**
     * @var \DateTime[]
     */
    protected $usr_overnights;

    /**
     * @var bool
     */
    protected $prior_night;

    /**
     * @var bool
     */
    protected $following_night;

    public function __construct(
        int $activity_type,
        int $usr_id,
        int $crs_id,
        string $firstname,
        string $lastname,
        string $login,
        string $crs_type,
        string $crs_title,
        ?\DateTime $crs_start_date,
        ?\DateTime $crs_end_date,
        string $usr_idd_time,
        string $crs_idd_time,
        array $usr_overnights,
        bool $prior_night,
        bool $following_night
    ) {
        if (!in_array($activity_type, self::$valid_types)) {
            throw new \InvalidArgumentException("The provides activity type is not valid: " . $activity_type);
        }

        $this->activity_type = $activity_type;
        $this->usr_id = $usr_id;
        $this->crs_id = $crs_id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->login = $login;
        $this->crs_type = $crs_type;
        $this->crs_title = $crs_title;
        $this->usr_idd_time = $usr_idd_time;
        $this->crs_start_date = $crs_start_date;
        $this->crs_end_date = $crs_end_date;
        $this->crs_idd_time = $crs_idd_time;
        $this->usr_overnights = $usr_overnights;
        $this->prior_night = $prior_night;
        $this->following_night = $following_night;
    }

    public function getActivityType() : int
    {
        return $this->activity_type;
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function getUserFirstName() : string
    {
        return $this->firstname;
    }

    public function getUserLastName() : string
    {
        return $this->lastname;
    }

    public function getUserLogin() : string
    {
        return $this->login;
    }

    public function getUserIDDTime() : string
    {
        return $this->usr_idd_time;
    }

    public function getUserOvernights() : array
    {
        return $this->usr_overnights;
    }

    public function getCourseObjId() : int
    {
        return $this->crs_id;
    }

    public function getCourseType() : string
    {
        return $this->crs_type;
    }

    public function getCourseTitle() : string
    {
        return $this->crs_title;
    }

    public function getCourseStartDate() : ?\DateTime
    {
        return $this->crs_start_date;
    }

    public function getCourseEndDate() : ?\DateTime
    {
        return $this->crs_end_date;
    }

    public function getCourseIDDTime() : string
    {
        return $this->crs_idd_time;
    }

    public function getPriorNight() : bool
    {
        return $this->prior_night;
    }

    public function getFollowingNight() : bool
    {
        return $this->following_night;
    }
}
