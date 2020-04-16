<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\OverviewCertificate;

class ExistingCertificate
{
    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $minutes;

    public function __construct(int $usr_id, int $obj_id, int $minutes)
    {
        $this->usr_id = $usr_id;
        $this->obj_id = $obj_id;
        $this->minutes = $minutes;
    }

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getMinutes() : int
    {
        return $this->minutes;
    }
}
