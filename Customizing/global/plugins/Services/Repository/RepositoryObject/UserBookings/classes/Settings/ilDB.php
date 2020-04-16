<?php
namespace CaT\Plugins\UserBookings\Settings;

/**
 * ILIAS implementation for settings db
 */
class ilDB implements DB
{
    const TABLE_NAME = "xubk_settings";

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
    public function create(
        int $obj_id,
        bool $superior_view = false,
        bool $local_evaluation = false,
        bool $recommendation_allowed = false
    ) : UserBookingsSettings {
        $settings = new UserBookingsSettings(
            $obj_id,
            $superior_view,
            $local_evaluation,
            $recommendation_allowed
        );

        $values = [
            "obj_id" => array("integer", $settings->getObjId()),
            "superior_view" => array("integer", $settings->getSuperiorView()),
            "local_evaluation" => array("integer", $settings->getLocalEvaluation()),
            "recommendation_allowed" => array("integer", $settings->getRecommendationAllowed())
        ];

        $this->getDB()->insert(self::TABLE_NAME, $values);
        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function update(UserBookingsSettings $settings)
    {
        $where = ["obj_id" => array("integer", $settings->getObjId())];
        $values = [
            "superior_view" => array("integer", $settings->getSuperiorView()),
            "local_evaluation" => array("integer", $settings->getLocalEvaluation()),
            "recommendation_allowed" => array("integer", $settings->getRecommendationAllowed())
        ];

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor(int $obj_id)
    {
        $query = "SELECT superior_view,local_evaluation, recommendation_allowed" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception("No settings found for object id " . $obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);
        return new UserBookingsSettings(
            $obj_id,
            (bool) $row["superior_view"],
            (bool) $row["local_evaluation"],
            (bool) $row["recommendation_allowed"]
        );
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
     *
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
     * Creates prmary keys
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
    }

    /**
     * Creates settings for all existing objects
     *
     * @return void
     */
    public function migrateExistingObjects()
    {
        require_once("Services/Object/classes/class.ilObjectFactory.php");
        $query = "SELECT object_data.obj_id, settings.obj_id AS settings" . PHP_EOL
                . " FROM object_data" . PHP_EOL
                . " JOIN object_reference" . PHP_EOL
                . "     ON object_reference.obj_id = object_data.obj_id" . PHP_EOL
                . " LEFT JOIN " . self::TABLE_NAME . " settings" . PHP_EOL
                . "     ON settings.obj_id = object_data.obj_id"
                . " WHERE object_data.type = 'xubk'" . PHP_EOL
                . "     AND object_reference.deleted IS NULL" . PHP_EOL
                . " HAVING settings IS NULL";

        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $this->create((int) $row["obj_id"]);
        }
    }

    /**
     * Get intance of db
     *
     * @throws \Exceptio
     *
     * @return \ilDBInterface
     */
    protected function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
