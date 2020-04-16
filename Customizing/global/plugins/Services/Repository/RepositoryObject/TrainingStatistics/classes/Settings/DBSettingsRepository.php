<?php
declare(strict_types=1);

namespace CaT\Plugins\TrainingStatistics\Settings;

class DBSettingsRepository implements SettingsRepository
{
    protected $db;


    const TABLE = 'xrts_settings';
    const COL_OBJ_ID = 'obj_id';
    const COL_AGGREGATE_ID = 'aggregate_id';
    const COL_ONLINE = 'online';
    const COL_GLOBAL = 'global';

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings)
    {
        $this->db->update(self::TABLE, [
            self::COL_AGGREGATE_ID => ['text',$settings->aggregateId()],
            self::COL_ONLINE => ['integer',$settings->online()],
            self::COL_GLOBAL => ['integer',$settings->global()]
        ], [
            self::COL_OBJ_ID => ['integer',$settings->objId()]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function create(int $obj_id) : Settings
    {
        $settings = new Settings($obj_id);
        $this->db->insert(self::TABLE, [
            self::COL_AGGREGATE_ID => ['text',$settings->aggregateId()],
            self::COL_ONLINE => ['integer',$settings->online()],
            self::COL_GLOBAL => ['integer',$settings->global()],
            self::COL_OBJ_ID => ['integer',$settings->objId()]
        ]);
        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function load(int $obj_id) : Settings
    {
        $res = $this->db->query(
            'SELECT ' . self::COL_AGGREGATE_ID
            . '	,' . self::COL_ONLINE
            . '	,' . self::COL_GLOBAL
            . '	FROM ' . self::TABLE
            . '	WHERE ' . self::COL_OBJ_ID . ' = ' . $this->db->quote($obj_id, 'integer')
        );
        if ($rec = $this->db->fetchAssoc($res)) {
            $online = (string) $rec[self::COL_ONLINE] === '1';
            $global = (string) $rec[self::COL_GLOBAL] === '1';
            return new Settings(
                $obj_id,
                $rec[self::COL_AGGREGATE_ID],
                $online,
                $global
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
}
