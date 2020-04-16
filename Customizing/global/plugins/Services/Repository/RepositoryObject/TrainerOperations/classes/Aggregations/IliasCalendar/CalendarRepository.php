<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Aggregations\IliasCalendar;

/**
 * Read the ILIAS-Calendar
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class CalendarRepository
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }


    public function getTypeGlobal() : int
    {
        return 	\ilCalendarCategory::TYPE_GLOBAL;
    }
    public function getTypeUser() : int
    {
        return 	\ilCalendarCategory::TYPE_USR;
    }

    /**
     * @return CalendarCategory[]
     */
    public function getCategories() : array
    {
        return $this->getCategoriesFromDB(
            [],//not restricted to certain ids
            [],//not restricted to certain users
            [
                $this->getTypeGlobal(),
                $this->getTypeUser()
            ],
            [] //no special location
        );
    }

    /**
     * @return CalendarCategory[]
     */
    public function getCategoriesByObjIds(array $ids) : array
    {
        return $this->getCategoriesFromDB(
            [],//not restricted to certain ids
            $ids,//not restricted to certain users
            [
                $this->getTypeGlobal(),
                $this->getTypeUser()
            ],
            [] //no special location
        );
    }



    public function getCategoryById(int $cat_id) : CalendarCategory
    {
        $cats = $this->getCategoriesFromDB(
            [$cat_id],//restricted to id
            [],//not restricted to certain users
            [
                \ilCalendarCategory::TYPE_GLOBAL,
                \ilCalendarCategory::TYPE_USR
            ],
            [] //no special location
        );
        return array_shift($cats);
    }

    protected function getCategoriesFromDB(
        array $category_ids = [],
        array $obj_ids = [],
        array $types = [],
        array $location = []
    ) : array {
        $db = $this->getDB();
        $query = "SELECT * FROM cal_categories WHERE 1";

        if (count($category_ids) > 0) {
            $query .= " AND " . $db->in('cat_id', $category_ids, false, 'integer');
        }
        if (count($obj_ids) > 0) {
            $query .= " AND " . $db->in('obj_id', $obj_ids, false, 'integer');
        }
        if (count($types) > 0) {
            $query .= " AND " . $db->in('type', $types, false, 'integer');
        }
        if (count($location) > 0) {
            $query .= " AND " . $db->in('loc_type', $location, false, 'integer');
        }

        $cats = [];
        $res = $db->query($query);
        while ($row = $res->fetchObject()) {
            $cal_ids = $this->getCalIdsForCategory([$row->cat_id]);
            $cats[] = $this->buildCategory(
                (int) $row->cat_id,
                $row->title,
                (int) $row->type,
                (int) $row->obj_id,
                (string) $row->remote_url,
                (string) $row->remote_user,
                (string) $row->remote_pass,
                $cal_ids,
                (int) $row->loc_type,
                (string) $row->remote_sync,
                (string) $row->color
            );
        }

        return $cats;
    }

    public function buildCategory(
        int $cat_id,
        string $title,
        int $type,
        int $obj_id,
        string $remote_url,
        string $remote_user,
        string $remote_pass,
        array $cal_ids = [],
        int $location_type = 2, //remote
        $remote_sync = '',
        string $color = '#ffffff'
    ) : CalendarCategory {
        return new CalendarCategory(
            $cat_id,
            $cal_ids,
            $obj_id,
            $title,
            $color,
            $type,
            $location_type,
            $remote_url,
            $remote_user,
            $remote_pass,
            $remote_sync
        );
    }

    public function storeCategory(CalendarCategory $cat)
    {
        $id = max([$cat->getCategoryId(), 0]);

        $il_cat = new \ilCalendarCategory($id);
        $il_cat->setTitle($cat->getTitle());
        if (!$il_cat->getColor()) {
            $il_cat->setColor($cat->getColor());
        }
        $il_cat->setType($cat->getType());
        $il_cat->setObjId($cat->getObjId());
        $il_cat->setLocationType($cat->getLocationType());
        $il_cat->setRemoteUrl($cat->getRemoteUrl());
        $il_cat->setRemoteUser($cat->getRemoteUser());
        $il_cat->setRemotePass($cat->getRemotePass());

        if ($cat->getCategoryId() < 0) {
            $il_cat->add();
        } else {
            $il_cat->update();
        }
    }

    public function deleteCatagory(int $cal_catid)
    {
        $il_cat = new \ilCalendarCategory($cal_catid);
        $il_cat->delete();
    }

    public function synchronize(CalendarCategory $category)
    {
        $il_cat = \ilCalendarCategory::getInstanceByCategoryId($category->getCategoryId());
        $remote = new \ilCalendarRemoteReader($il_cat->getRemoteUrl());

        $remote->setUser($il_cat->getRemoteUser());
        $remote->setPass($il_cat->getRemotePass());
        $remote->read();
        $remote->import($il_cat);
    }

    protected function getCalIdsForCategory(array $category_ids) : array
    {
        $db = $this->getDB();
        $query = "SELECT cal_id FROM cal_cat_assignments WHERE "
            . $db->in('cat_id', $category_ids, false, 'integer');
        $res = $db->query($query);

        $ret = [];
        while ($row = $res->fetchObject()) {
            $ret[] = (int) $row->cal_id;
        }

        return $ret;
    }

    /**
     * @return Entry[]
     */
    public function getEvents(
        array $category_ids
    ) : array {
        $db = $this->getDB();

        $cal_ids = $this->getCalIdsForCategory($category_ids);

        $query = "SELECT * FROM cal_entries WHERE "
            . $db->in('cal_id', $cal_ids, false, 'integer');

        $entries = [];
        $res = $db->query($query);
        while ($row = $res->fetchObject()) {
            $entries[] = new Entry(
                (int) $row->cal_id,
                new \DateTime($row->starta),
                new \DateTime($row->enda),
                $row->last_update,
                (string) $row->title,
                (string) $row->subtitle,
                (string) $row->description,
                (string) $row->location,
                (string) $row->informations,
                (bool) $row->fullday,
                (int) $row->auto_generated,
                (int) $row->context_id,
                (int) $row->translation_type,
                (int) $row->completion,
                (bool) $row->is_milestone,
                (bool) $row->notification
            );
        }
        return $entries;
    }

    protected function getDB() : \ilDBInterface
    {
        return $this->db;
    }
}
