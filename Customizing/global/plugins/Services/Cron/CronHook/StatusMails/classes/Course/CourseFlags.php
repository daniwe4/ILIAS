<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Course;

/**
 * CourseFlags hold relevant information concerning status-mails.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class CourseFlags
{
    /**
     * @var int
     */
    protected $crs_obj_id;

    /**
     * @var bool
     */
    protected $prevent_mails;

    /**
     * @var bool
     */
    protected $overnights;

    public function __construct(
        int $crs_obj_id,
        bool $prevent_mails = true,
        bool $overnights = false
    ) {
        $this->crs_obj_id = $crs_obj_id;
        $this->prevent_mails = $prevent_mails;
        $this->overnights = $overnights;
    }

    public function courseObjId() : int
    {
        return $this->crs_obj_id;
    }

    public function preventMailEntirely() : bool
    {
        return $this->prevent_mails;
    }

    public function outlineOvernights() : bool
    {
        return $this->overnights;
    }
}
