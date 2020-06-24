<?php

namespace CaT\Plugins\Webinar\Settings;

require_once("Services/Calendar/classes/class.ilDateTime.php");

/**
 * Implementation for DB handle of additional setting values
 */
class ilDB implements DB
{
    const TABLE_NAME = "xwbr_data";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inhertidoc
     */
    public function update(Webinar $webinar)
    {
        $start = $webinar->getBeginning();
        if ($start !== null) {
            $start = $start->get(IL_CAL_DATETIME);
        }

        $end = $webinar->getEnding();
        if ($end !== null) {
            $end = $end->get(IL_CAL_DATETIME);
        }

        $where = array("obj_id" => array("integer", $webinar->getObjId()));

        $values = array("beginning" => array("string", $start),
            "vc_type" => array("string", $webinar->getVCType()),
            "ending" => array("string", $end),
            "admission" => array("string", $webinar->getAdmission()),
            "url" => array("string", $webinar->getUrl()),
            "is_online" => array("integer", $webinar->getOnline()),
            "lp_mode" => array("integer", $webinar->getLPMode()),
            "is_finished" => array("integer", $webinar->isFinished())
        );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inhertidoc
     */
    public function create(
        int $obj_id,
        string $vc_type,
        ?\ilDateTime $beginning = null,
        ?\ilDateTime $ending = null,
        ?string $admission = null,
        ?string $url = null,
        bool $online = false,
        int $lp_mode = 0
    ) {
        $webinar = new Webinar(
            $obj_id,
            $vc_type,
            $beginning,
            $ending,
            $admission,
            $url,
            $online,
            $lp_mode
        );

        $start = $webinar->getBeginning();
        if ($start !== null) {
            $start = $start->get(IL_CAL_DATETIME);
        }

        $end = $webinar->getEnding();
        if ($end !== null) {
            $end = $end->get(IL_CAL_DATETIME);
        }
        $values = array("obj_id" => array("integer", $webinar->getObjId()),
            "vc_type" => array("string", $webinar->getVCType()),
            "beginning" => array("string", $start),
            "ending" => array("string", $end),
            "admission" => array("string", $webinar->getAdmission()),
            "url" => array("string", $webinar->getUrl()),
            "is_online" => array("integer", $webinar->getOnline()),
            "lp_mode" => array("integer", $webinar->getLPMode()),
            "is_finished" => array("integer", false)
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $webinar;
    }

    /**
     * @inhertidoc
     */
    public function selectFor($obj_id)
    {
        $query = "SELECT obj_id, vc_type, beginning, ending, admission, url, is_online, lp_mode," . PHP_EOL
                . " is_finished" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            var_dump($this->getDB()->query($query));
            throw new \Exception(__METHOD__ . " no settings found for object id: " . $obj_id);
        }

        $row = $this->getDB()->fetchAssoc($res);
        if ($row["beginning"] !== null) {
            $row["beginning"] = new \ilDateTime($row["beginning"], IL_CAL_DATETIME);
        }

        if ($row["ending"] !== null) {
            $row["ending"] = new \ilDateTime($row["ending"], IL_CAL_DATETIME);
        }

        return new Webinar(
            (int) $row["obj_id"],
            $row["vc_type"],
            $row["beginning"],
            $row["ending"],
            $row["admission"],
            $row["url"],
            (bool) $row["is_online"],
            (int) $row["lp_mode"],
            (bool) $row["is_finished"]
        );
    }

    /**
     * @inhertidoc
     */
    public function deleteFor($obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Create tables
     *
     * @return null
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
                    'vc_type' => array(
                        'type' => 'text',
                        'length' => 16,
                        'notnull' => true
                    ),
                    'beginning' => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ),
                    'ending' => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ),
                    'admission' => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ),
                    'url' => array(
                        'type' => 'text',
                        'length' => 256,
                        'notnull' => false
                    ),
                    'is_online' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Create primary key
     *
     * @return null
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
    }

    /**
     * Update table with new column
     *
     * @return null
     */
    public function tableUpdate1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, 'lp_mode')) {
            $this->getDB()->addTableColumn(self::TABLE_NAME, 'lp_mode', array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ));
        }
    }

    /**
     * Update table with new column
     *
     * @return void
     */
    public function tableUpdate2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, 'is_finished')) {
            $this->getDB()->addTableColumn(self::TABLE_NAME, 'is_finished', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            ));
        }
    }

    /**
     * Update table with new column
     *
     * @return null
     */
    public function tableUpdate3()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, 'upload_required')) {
            $this->getDB()->addTableColumn(self::TABLE_NAME, 'upload_required', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            ));
        }
    }

    /**
     * Update table with new column
     *
     * @return null
     */
    public function tableUpdate4()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, 'upload_required')) {
            $this->getDB()->dropTableColumn(self::TABLE_NAME, 'upload_required');
        }
    }

    /**
     * Get intance of db
     *
     * @throws \Exception
     *
     * @return \ilDBInterface
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
