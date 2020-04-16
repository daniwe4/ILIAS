<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Settings;

/**
 * ILIAS implementation for settings db
 *
 * @author  Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xbka_settings";

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
    public function create(int $obj_id, bool $superior_view = false) : BookingApprovals
    {
        $settings = new BookingApprovals($obj_id, $superior_view);
        $values = array("obj_id" => array("integer", $settings->getObjId())
                    , "superior_view" => array("integer", $settings->getSuperiorView())
                );
        $this->getDB()->insert(self::TABLE_NAME, $values);
        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id) : BookingApprovals
    {
        $query = "SELECT superior_view" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception("No settings found for object id " . $obj_id);
        }
        $row = $this->getDB()->fetchAssoc($res);
        return new BookingApprovals($obj_id, (bool) $row["superior_view"]);
    }

    /**
     * @inheritdoc
     */
    public function update(BookingApprovals $settings)
    {
        $where = array("obj_id" => array("integer", $settings->getObjId()));
        $values = array("superior_view" => array("integer", $settings->getSuperiorView()));
        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * Creates database tables
     * @return void
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'superior_view' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Creates primary keys
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
    }

    /**
     * Get intance of db
     * @throws \Exceptiom
     */
    protected function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no database");
        }
        return $this->db;
    }
}
