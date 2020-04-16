<?php

declare(strict_types=1);

namespace CaT\Plugins\EmployeeBookingOverview\Settings;

class DBSettingsRepository implements SettingsRepository
{
    protected $db;

    const TABLE = 'xebo_settings';
    const COL_OBJ_ID = 'obj_id';
    const COL_ONLINE = 'online';
    const COL_GLOBAL = 'global';
    const COL_INVISIBLE_COURSE_TOPICS = "invisible_crs_topics";

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings)
    {
        $this->db->update(
            self::TABLE,
            [
                self::COL_ONLINE => ['integer',$settings->isOnline()],
                self::COL_GLOBAL => ['integer',$settings->isGlobal()],
                self::COL_INVISIBLE_COURSE_TOPICS => [
                    'text',
                    serialize(
                        $settings->getInvisibleCourseTopics()
                    )
                ]
            ],
            [
                self::COL_OBJ_ID => ['integer',$settings->objId()]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function create(int $obj_id) : Settings
    {
        $settings = new Settings($obj_id);
        $this->db->insert(
            self::TABLE,
            [
                self::COL_ONLINE => ['integer',$settings->isOnline()],
                self::COL_GLOBAL => ['integer',$settings->isGlobal()],
                self::COL_OBJ_ID => ['integer',$settings->objId()],
                self::COL_INVISIBLE_COURSE_TOPICS => [
                    'text',
                    serialize($settings->getInvisibleCourseTopics())
                ]
            ]
        );
        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function load(int $obj_id) : Settings
    {
        $res = $this->db->query(
            'SELECT ' . self::COL_ONLINE . ', ' . self::COL_GLOBAL . ', ' . PHP_EOL
            . self::COL_INVISIBLE_COURSE_TOPICS . PHP_EOL
            . 'FROM ' . self::TABLE . PHP_EOL
            . 'WHERE ' . self::COL_OBJ_ID . ' = ' . $this->db->quote($obj_id, 'integer')
        );
        if ($rec = $this->db->fetchAssoc($res)) {
            $online = (string) $rec[self::COL_ONLINE] === '1';
            $global = (string) $rec[self::COL_GLOBAL] === '1';

            $vals = [];
            if (!is_null($rec[self::COL_INVISIBLE_COURSE_TOPICS])) {
                $vals = unserialize($rec[self::COL_INVISIBLE_COURSE_TOPICS]);
            }

            return new Settings(
                $obj_id,
                $online,
                $global,
                $vals
            );
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function delete(Settings $settings)
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::TABLE
            . '	WHERE ' . self::COL_OBJ_ID . ' = ' . $this->db->quote($settings->objId(), 'integer')
        );
    }

    public function getHistoricCourseTopics() : array
    {
        $ret = [];

        $query = "SELECT DISTINCT list_data FROM hhd_crs_topics";
        $res = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($res)) {
            $ret[$row["list_data"]] = $row["list_data"];
        }

        return $ret;
    }
}
