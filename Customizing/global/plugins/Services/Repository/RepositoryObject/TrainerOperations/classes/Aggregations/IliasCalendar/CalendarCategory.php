<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Aggregations\IliasCalendar;

/**
 * Calendar Category
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class CalendarCategory
{
    public function __construct(
        int $cat_id,
        array $cal_ids,
        int $obj_id,
        string $title,
        string $color,
        int $type,
        int $location_type,
        string $remote_url,
        string $remote_user,
        string $remote_pass,
        $remote_sync
    ) {
        $this->cat_id = $cat_id;
        $this->cal_ids = $cal_ids;
        $this->obj_id = $obj_id;
        $this->title = $title;
        $this->color = $color;
        $this->type = $type;
        $this->location_type = $location_type;
        $this->remote_url = $remote_url;
        $this->remote_user = $remote_user;
        $this->remote_pass = $remote_pass;
        $this->remote_sync = $remote_sync; //TODO: should be DateTime
    }

    public function getCategoryId() : int
    {
        return $this->cat_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getColor() : string
    {
        return $this->color;
    }

    public function getType() : int
    {
        return $this->type;
    }

    public function getLocationType() : int
    {
        return $this->location_type;
    }

    public function getRemoteUrl() : string
    {
        return $this->remote_url;
    }

    public function getRemoteUser() : string
    {
        return $this->remote_user;
    }

    public function getRemotePass() : string
    {
        return $this->remote_pass;
    }

    public function getRemoteSyncLastExecution() : \DateTime
    {
        //TODO
    }

    public function getCalIds() : array
    {
        return $this->cal_ids;
    }
}
