<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMailing\Invites;

class Invite
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $added_by;

    /**
     * @var \DateTime
     */
    protected $added_at;

    /**
     * @return string[]
     */
    protected $udf_fields;

    /**
     * @var \DateTime | null
     */
    protected $rejected_at;

    /**
     * @var int | null
     */
    protected $rejected_by;

    /**
     * @var \DateTime | null
     */
    protected $invite_at;

    /**
     * @var int | null
     */
    protected $invite_by;

    public function __construct(
        int $id,
        int $usr_id,
        string $lastname,
        string $firstname,
        int $obj_id,
        int $added_by,
        \DateTime $added_at,
        array $udf_fields,
        int $invite_by = null,
        \DateTime $invite_at = null,
        int $rejected_by = null,
        \DateTime $rejected_at = null
    ) {
        $this->id = $id;
        $this->usr_id = $usr_id;
        $this->lastname = $lastname;
        $this->firstname = $firstname;
        $this->obj_id = $obj_id;
        $this->added_by = $added_by;
        $this->added_at = $added_at;
        $this->udf_fields = $udf_fields;
        $this->invite_by = $invite_by;
        $this->invite_at = $invite_at;
        $this->rejected_by = $rejected_by;
        $this->rejected_at = $rejected_at;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    /**
     * @return string
     */
    public function getLastname() : string
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getFirstname() : string
    {
        return $this->firstname;
    }

    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return int
     */
    public function getAddedBy() : int
    {
        return $this->added_by;
    }

    /**
     * @return \DateTime
     */
    public function getAddedAt() : \DateTime
    {
        return $this->added_at;
    }

    /**
     * @return array
     */
    public function getUdfFields() : array
    {
        return $this->udf_fields;
    }

    /**
     * @return \DateTime|null
     */
    public function getRejectedAt()
    {
        return $this->rejected_at;
    }

    /**
     * @return int|null
     */
    public function getRejectedBy()
    {
        return $this->rejected_by;
    }

    /**
     * @return \DateTime|null
     */
    public function getInviteAt()
    {
        return $this->invite_at;
    }

    /**
     * @return int|null
     */
    public function getInviteBy()
    {
        return $this->invite_by;
    }
}
