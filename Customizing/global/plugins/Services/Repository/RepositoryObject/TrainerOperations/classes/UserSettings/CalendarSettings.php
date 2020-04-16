<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\UserSettings;

/**
 * Calendar(-settings) as used by the TEP
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class CalendarSettings
{
    const TYPE_PERSONAL = 'personal';
    const TYPE_GENERAL = 'general';

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
     * The usr_id this setting is for; user is also the owner of this setting.
     * @var int
     */
    protected $usr_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var bool
     */
    protected $show_here;

    /**
     * @var bool
     */
    protected $hide_details;


    public function __construct(
        int $storage_id,
        int $tep_obj_id,
        int $cat_id,
        int $usr_id,
        string $title,
        string $url,
        string $username,
        string $password,
        bool $use,
        bool $hide_details
    ) {
        $this->storage_id = $storage_id;
        $this->tep_obj_id = $tep_obj_id;
        $this->cat_id = $cat_id;
        $this->usr_id = $usr_id;
        $this->title = $title;
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->show_here = $use;
        $this->hide_details = $hide_details;
    }

    public function getType() : string
    {
        if ($this->getUserId() === 0) {
            return self::TYPE_GENERAL;
        }
        return self::TYPE_PERSONAL;
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

    public function getShow() : bool
    {
        return $this->show_here;
    }

    public function withShow(bool $show) : CalendarSettings
    {
        $clone = clone $this;
        $clone->show_here = $show;
        return $clone;
    }

    public function getHideDetails() : bool
    {
        return $this->hide_details;
    }

    public function withHideDetails(bool $hide_details) : CalendarSettings
    {
        $clone = clone $this;
        $clone->hide_details = $hide_details;
        return $clone;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function withTitle(string $title) : CalendarSettings
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getURL() : string
    {
        return $this->url;
    }

    public function withURL(string $url) : CalendarSettings
    {
        $clone = clone $this;
        $clone->url = $url;
        return $clone;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function withUsername(string $username) : CalendarSettings
    {
        $clone = clone $this;
        $clone->username = $username;
        return $clone;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function withPassword(string $password) : CalendarSettings
    {
        $clone = clone $this;
        $clone->password = $password;
        return $clone;
    }
}
