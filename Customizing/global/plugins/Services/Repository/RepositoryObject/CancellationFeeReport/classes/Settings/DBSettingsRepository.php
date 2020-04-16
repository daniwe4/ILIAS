<?php declare(strict_types = 1);
namespace CaT\Plugins\CancellationFeeReport\Settings;

class DBSettingsRepository implements SettingsRepository
{
    const TABLE = 'xcfr_settings';
    const ROW_ID = 'id';
    const ROW_ONLINE = 'is_online';
    const ROW_GLOBAL = 'is_global';

    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings)
    {
        if (!$this->exists($settings->id())) {
            throw new \InvalidArgumentException('A dataset with id ' . $settings->id() . ' doesn\'t exist');
        }
        $this->updateDB($settings->id(), $settings->online(), $settings->isGlobal());
    }

    /**
     * @inheritdoc
     */
    public function create(int $obj_id) : Settings
    {
        if ($this->exists($obj_id)) {
            throw new \InvalidArgumentException('A dataset with id ' . $obj_id . ' allready exists');
        }
        $this->insertDB($obj_id, false, false);
        return new Settings($obj_id);
    }

    /**
     * @inheritdoc
     */
    public function load(int $obj_id) : Settings
    {
        return $this->loadDB($obj_id);
    }

    /**
     * @inheritdoc
     */
    public function delete(Settings $settings)
    {
        if (!$this->exists($settings->id())) {
            throw new \InvalidArgumentException('A dataset with id ' . $settings->id() . ' doesn\'t exist');
        }
        $this->deleteDB($settings->id());
    }

    protected function updateDB(int $obj_id, bool $online, bool $global)
    {
        $this->db->update(
            self::TABLE,
            [self::ROW_ONLINE => ['integer',$online ? 1 : 0]
            ,self::ROW_GLOBAL => ['integer',$global ? 1 : 0]],
            [self::ROW_ID => ['integer',$obj_id]]
        );
    }

    protected function deleteDB(int $obj_id)
    {
        $this->db->manipulate('DELETE FROM ' . self::TABLE
                            . '	WHERE ' . self::ROW_ID . ' = ' . $this->db->quote($obj_id, 'integer'));
    }

    protected function loadDB(int $obj_id) : Settings
    {
        $res = $this->db->query('SELECT ' . self::ROW_ONLINE . ',' . self::ROW_GLOBAL
                                . '	FROM ' . self::TABLE
                                . '	WHERE ' . self::ROW_ID . ' = ' . $this->db->quote($obj_id, 'integer'));
        while ($rec = $this->db->fetchAssoc($res)) {
            return new Settings($obj_id, (bool) $rec[self::ROW_ONLINE], (bool) $rec[self::ROW_GLOBAL]);
        }
        throw new \InvalidArgumentException('Invalid Id:' . $obj_id);
    }

    protected function insertDB(int $obj_id, bool $online, bool $global)
    {
        $this->db->insert(
            self::TABLE,
            [self::ROW_ID => ['integer', $obj_id]
            ,self::ROW_ONLINE => ['integer',$online ? 1 : 0]
            ,self::ROW_GLOBAL => ['integer',$global ? 1 : 0]]
        );
    }

    /**
     * @inheritdoc
     */
    public function exists(int $obj_id) : bool
    {
        $res = $this->db->query('SELECT ' . self::ROW_ID
                                . '	FROM ' . self::TABLE
                                . '	WHERE ' . self::ROW_ID . ' = ' . $this->db->quote($obj_id, 'integer'));
        return $this->db->numRows($res) > 0;
    }
}
