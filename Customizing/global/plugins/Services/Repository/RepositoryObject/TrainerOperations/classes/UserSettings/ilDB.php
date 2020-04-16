<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\UserSettings;

/**
 * Implementation of the db interface.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xtep_cal_settings";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $tep_obj_id) : array
    {
        return $this->doSelectFor($tep_obj_id);
    }

    /**
     * @inheritdoc
     */
    public function selectForUser(int $tep_obj_id, int $usr_id) : array
    {
        return $this->doSelectFor($tep_obj_id, $usr_id);
    }

    /**
     * @inheritdoc
     */
    public function store(array $settings)
    {
        foreach ($settings as $s) {
            if ($s->getStorageId() === -1 && $s->getUse()) {
                $this->create($s);
            }
            if ($s->getStorageId() > -1 && $s->getUse()) {
                $this->update($s);
            }
            if ($s->getStorageId() > -1 && $s->getUse() === false) {
                $this->delete($s);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $tep_obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE tep_obj_id = " . $tep_obj_id;
        $this->getDB()->manipulate($query);
    }

    public function deleteUserSettings(int $cal_catid)
    {
        $query =
             "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE cal_cat_id = " . $cal_catid . PHP_EOL
        ;
        $this->getDB()->manipulate($query);
    }

    /**
     * @return TEPCalendarSettings[]
     */
    protected function doSelectFor(int $tep_obj_id, int $usr_id = null) : array
    {
        $query = 'SELECT' . PHP_EOL
            . 'id, tep_obj_id, cal_cat_id, usr_id, hide_details' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE tep_obj_id = ' . $tep_obj_id . PHP_EOL;

        if (!is_null($usr_id)) {
            $query .= 'AND usr_id = ' . $usr_id;
        }

        $result = $this->getDB()->query($query);
        $ret = [];
        while ($row = $result->fetchObject()) {
            $set = new TEPCalendarSettings(
                (int) $row->id,
                (int) $row->tep_obj_id,
                (int) $row->cal_cat_id,
                (int) $row->usr_id,
                true,
                (bool) $row->hide_details
            );
            $ret[] = $set;
        }
        return $ret;
    }

    protected function update(TEPCalendarSettings $setting)
    {
        $values = array(
            "hide_details" => array("integer", $setting->getHideDetails())
        );
        $where = array("id" => ["integer", $setting->getStorageId()]);

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    protected function delete(TEPCalendarSettings $setting)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE id = " . $setting->getStorageId();
        $this->getDB()->manipulate($query);
    }

    protected function create(TEPCalendarSettings $setting)
    {
        $next_id = $this->getNextId(self::TABLE_NAME);
        $values = array(
            "id" => array("integer", $next_id),
            "tep_obj_id" => array("integer", $setting->getTEPObjId()),
            "cal_cat_id" => array("integer", $setting->getCalCatId()),
            "usr_id" => array("integer", $setting->getUserId()),
            "hide_details" => array("integer", $setting->getHideDetails())
        );
        $this->getDB()->insert(self::TABLE_NAME, $values);
    }

    protected function getDB() : \ilDBInterface
    {
        return $this->db;
    }

    protected function getNextId() : int
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }

    public function createTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME)) {
            $fields = [
                'id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'tep_obj_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'cal_cat_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                ],
                'usr_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'hide_details' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ]
            ];
            $this->getDB()->createTable(static::TABLE_NAME, $fields);
        }
    }

    public function createPrimaryKeys()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, ['id']);
    }

    public function createSequenceTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME . '_seq')) {
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }
}
