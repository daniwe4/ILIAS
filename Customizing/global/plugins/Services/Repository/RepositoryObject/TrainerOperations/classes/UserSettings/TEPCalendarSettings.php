<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\UserSettings;

/**
 * TEP-specific settings for an ILIAS-Calendar
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class TEPCalendarSettings
{
    /**
     * @var int
     */
    protected $storage_id;

    /**
     * The TEP's object_id this setting is relvant for
     * @var int
     */
    protected $tep_obj_id;

    /**
     * The ilias category-id (table cal_categories)
     * @var int
     */
    protected $cat_id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var bool
     */
    protected $use;

    /**
     * @var bool
     */
    protected $hide_details;


    public function __construct(
        int $storage_id,
        int $tep_obj_id,
        int $cat_id,
        int $usr_id,
        bool $use,
        bool $hide_details
    ) {
        $this->storage_id = $storage_id;
        $this->tep_obj_id = $tep_obj_id;
        $this->cat_id = $cat_id;
        $this->usr_id = $usr_id;
        $this->use = $use;
        $this->hide_details = $hide_details;
    }

    public function getStorageId() : int
    {
        return $this->storage_id;
    }

    public function getTEPObjId() : int
    {
        return $this->tep_obj_id;
    }

    public function getCalCatId() : int
    {
        return $this->cat_id;
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function getUse() : bool
    {
        return $this->use;
    }

    public function getHideDetails() : bool
    {
        return $this->hide_details;
    }
}
