<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Config;

use CaT\Plugins\ScaledFeedback\Config\Sets\Set;
use CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;

class ilDB implements DB
{
    const TABLENAME_SETS = "xfbk_sets";
    const TABLENAME_DIMS = "xfbk_dimensions";
    const INTERIM_TABLE = "xfbk_setcontents";
    const TABLE_SETTINGS = "xfbk_settings";

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
    public function install()
    {
        $this->createSetsTable();
        $this->createDimensionsTable();
        $this->createInterimTable();
    }

    /**
     * @inheritdoc
     */
    public function createSet(
        string $title,
        bool $is_locked,
        int $min_submissions
    ) : Set {
        $next_id = (int) $this->getDB()->nextId(self::TABLENAME_SETS);

        $set = new Sets\Set(
            $next_id,
            $title,
            "",
            "",
            "",
            $is_locked,
            $min_submissions
        );

        $values = array(
            "set_id" => ["integer", $set->getSetId()],
            "title" => ["text", $set->getTitle()],
            "introtext" => ["text", $set->getIntrotext()],
            "extrotext" => ["text", $set->getExtrotext()],
            "repeattext" => ["text", $set->getRepeattext()],
            "is_locked" => ["integer", $set->getIsLocked()],
            "min_submissions" => ["integer", $set->getMinSubmissions()]
            );

        $this->getDB()->insert(self::TABLENAME_SETS, $values);

        return $set;
    }

    /**
     * @inheritdoc
     */
    public function updateSet(Set $set)
    {
        $set_id = $set->getSetId();
        $where = ["set_id" => ["integer", $set_id]];
        $values = array(
            "title" => ["text", $set->getTitle()],
            "introtext" => ["text", $set->getIntrotext()],
            "extrotext" => ["text", $set->getExtrotext()],
            "repeattext" => ["text", $set->getRepeattext()],
            "is_locked" => ["integer", $set->getIsLocked()],
            "min_submissions" => ["integer", $set->getMinSubmissions()]
            );
        $this->getDB()->update(self::TABLENAME_SETS, $values, $where);
        $this->updateInterim($set_id, $set->getDimensions());
    }

    /**
     * @inheritdoc
     */
    public function selectAllSets() : array
    {
        $sets = array();

        $query = "SELECT" . PHP_EOL
                . "    set_id," . PHP_EOL
                . "    title," . PHP_EOL
                . "    introtext," . PHP_EOL
                . "    extrotext," . PHP_EOL
                . "    repeattext," . PHP_EOL
                . "    is_locked," . PHP_EOL
                . "    min_submissions" . PHP_EOL
                . "FROM " . self::TABLENAME_SETS . PHP_EOL
                . "ORDER BY title ASC";
        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $sets[] = $this->getSetObject($row);
        }

        return $sets;
    }

    /**
     * @inheritdoc
     */
    public function selectSetById(int $id) : Set
    {
        $where = "WHERE set_id = " . $this->getDB()->quote($id, "integer");
        $query = "SELECT" . PHP_EOL
                . "    set_id," . PHP_EOL
                . "    title," . PHP_EOL
                . "    introtext," . PHP_EOL
                . "    extrotext," . PHP_EOL
                . "    repeattext," . PHP_EOL
                . "    is_locked," . PHP_EOL
                . "    min_submissions" . PHP_EOL
                . "FROM " . self::TABLENAME_SETS . "" . PHP_EOL;
        $result = $this->getDB()->query($query . $where);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \LogicException(__METHOD__ . " no Set found for set_id " . $id);
        }

        return $this->getSetObject($this->getDB()->fetchAssoc($result));
    }

    /**
     * @inheritdoc
     */
    public function deleteSets(array $ids)
    {
        foreach ($ids as $id) {
            $query = "DELETE FROM " . self::TABLENAME_SETS . PHP_EOL
                    . "WHERE set_id = " . $this->getDB()->quote($id, "integer");
            $this->getDB()->manipulate($query);
            $this->deleteInterimBySetId((int) $id);
        }
    }

    protected function getSetObject(array $row) : Set
    {
        $dims = $this->getDimensionsBySetId((int) $row['set_id']);

        return new Set(
            (int) $row['set_id'],
            $row['title'],
            $row['introtext'],
            $row['extrotext'],
            $row['repeattext'],
            (bool) $row['is_locked'],
            (int) $row['min_submissions'],
            $this->isSetInUse((int) $row['set_id']),
            $dims
        );
    }

    public function isValidSetId(int $id) : bool
    {
        $query = "SELECT set_id" . PHP_EOL
                . "FROM " . self::TABLENAME_SETS . PHP_EOL
                . "WHERE set_id = " . $this->getDB()->quote($id, "integer");
        $result = $this->getDB()->query($query);

        return $this->getDB()->numRows($result) != 0;
    }

    protected function isSetInUse(int $set_id) : bool
    {
        $query = "SELECT count(set_id) AS set_id" . PHP_EOL
                . "FROM " . self::TABLE_SETTINGS . PHP_EOL
                . "WHERE set_id = " . $this->getDB()->quote($set_id, "integer");

        $result = $this->getDB()->query($query);

        $num = $this->getDB()->fetchAssoc($result);

        return $num['set_id'] != 0;
    }

    public function getQuestionSetValues() : array
    {
        $ret = array();
        $query = "SELECT" . PHP_EOL
                . "    set_id," . PHP_EOL
                . "    title" . PHP_EOL
                . "FROM " . self::TABLENAME_SETS . PHP_EOL
                . "WHERE is_locked = 0" . PHP_EOL;
        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $ret[$row['set_id']] = $row['title'];
        }

        uasort($ret, function ($a, $b) {
            return strcasecmp($a, $b);
        });

        return $ret;
    }

    public function getMinSubmissionsBySetId(int $set_id) : int
    {
        $query = "SELECT" . PHP_EOL
                . "    min_submissions" . PHP_EOL
                . "FROM " . self::TABLENAME_SETS . PHP_EOL
                . "WHERE set_id = " . $this->getDB()->quote($set_id, "integer") . PHP_EOL;

        $result = $this->getDB()->query($query);

        return (int) $this->getDB()->fetchAssoc($result)['min_submissions'];
    }

    protected function createSetsTable()
    {
        if (!$this->getDB()->tableExists(self::TABLENAME_SETS)) {
            $fields = array(
                'set_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'title' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'introtext' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'extrotext' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'repeattext' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'is_locked' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                    ),
                'min_submissions' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLENAME_SETS, $fields);
        }
    }

    public function createPrimaryKeyForSets()
    {
        $this->createPrimaryKey(self::TABLENAME_SETS, array("set_id"));
    }

    public function createSequenceForSets()
    {
        $this->getDB()->createSequence(self::TABLENAME_SETS);
    }

    /**
     * @inheritdoc
     */
    public function createDimension(string $title, string $displayed_title) : Dimension
    {
        $next_id = (int) $this->getDB()->nextId(self::TABLENAME_DIMS);
        $dimension = new Dimension($next_id, $title, $displayed_title);
        $values = array(
            "dim_id" => ["integer", $next_id],
            "title" => ["text", $dimension->getTitle()],
            "displayed_title" => ["text", $dimension->getDisplayedTitle()]
        );
        $this->getDB()->insert(self::TABLENAME_DIMS, $values);

        return $dimension;
    }

    public function updateDimension(Dimension $dimension)
    {
        $where = ["dim_id" => ["integer", $dimension->getDimId()]];
        $values = array(
            "title" => ["text", $dimension->getTitle()],
            "displayed_title" => ["text", $dimension->getDisplayedTitle()],
            "info" => ["text", $dimension->getInfo()],
            "label1" => ["text", $dimension->getLabel1()],
            "label2" => ["text", $dimension->getLabel2()],
            "label3" => ["text", $dimension->getLabel3()],
            "label4" => ["text", $dimension->getLabel4()],
            "label5" => ["text", $dimension->getLabel5()],
            "enable_comment" => ["integer", (int) $dimension->getEnableComment()],
            "only_textual_feedback" => ["integer", (int) $dimension->getOnlyTextualFeedback()],
            "is_locked" => ["integer", (int) $dimension->getIsLocked()]
            );

        $this->getDB()->update(self::TABLENAME_DIMS, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectAllDimensions(string $filter = "") : array
    {
        $dimensions = array();
        $where = "";

        if ($filter != "" && $filter != "all") {
            switch ($filter) {
                case "locked":
                    $filter = 1;
                    break;
                case "unlocked":
                    $filter = 0;
            }

            $where = " WHERE is_locked = " . $this->getDB()->quote($filter, "integer");
        }

        $query = "SELECT" . PHP_EOL
                . "    dim_id," . PHP_EOL
                . "    title," . PHP_EOL
                . "    displayed_title," . PHP_EOL
                . "    info," . PHP_EOL
                . "    label1," . PHP_EOL
                . "    label2," . PHP_EOL
                . "    label3," . PHP_EOL
                . "    label4," . PHP_EOL
                . "    label5," . PHP_EOL
                . "    enable_comment," . PHP_EOL
                . "    only_textual_feedback," . PHP_EOL
                . "    is_locked" . PHP_EOL
                . "FROM " . self::TABLENAME_DIMS . PHP_EOL
                 . $where . PHP_EOL
                . "ORDER BY title ASC";

        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $dimensions[] = $this->getDimensionObject($row);
        }

        return $dimensions;
    }

    /**
     * @inheritdoc
     */
    public function selectDimensionById(int $id) : Dimension
    {
        $where = "WHERE dim_id = " . $this->getDB()->quote($id, "integer");
        $query = "SELECT" . PHP_EOL
                . "    dim_id," . PHP_EOL
                . "    title," . PHP_EOL
                . "    displayed_title," . PHP_EOL
                . "    info," . PHP_EOL
                . "    label1," . PHP_EOL
                . "    label2," . PHP_EOL
                . "    label3," . PHP_EOL
                . "    label4," . PHP_EOL
                . "    label5," . PHP_EOL
                . "    enable_comment," . PHP_EOL
                . "    only_textual_feedback," . PHP_EOL
                . "    is_locked" . PHP_EOL
                . "FROM " . self::TABLENAME_DIMS . "" . PHP_EOL;
        $result = $this->getDB()->query($query . $where);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \LogicException(__METHOD__ . " no Dimension found for dim_id " . $id);
        }

        return $this->getDimensionObject($this->getDB()->fetchAssoc($result));
    }

    /**
     * @inheritdoc
     */
    public function deleteDimensions(array $ids)
    {
        foreach ($ids as $id) {
            $query = "DELETE FROM " . self::TABLENAME_DIMS . PHP_EOL
                    . "WHERE dim_id = " . $this->getDB()->quote($id, "integer");
            $this->getDB()->manipulate($query);
        }
    }

    public function isValidDimId(int $id) : bool
    {
        $query = "SELECT dim_id" . PHP_EOL
                . "FROM " . self::TABLENAME_DIMS . PHP_EOL
                . "WHERE dim_id = " . $this->getDB()->quote($id, "integer");

        $result = $this->getDB()->query($query);
        return $this->getDB()->numRows($result) != 0;
    }

    protected function getDimensionObject(array $row) : Dimension
    {
        return new Dimension(
            (int) $row['dim_id'],
            $row['title'],
            $row['displayed_title'],
            $row['info'],
            $row['label1'],
            $row['label2'],
            $row['label3'],
            $row['label4'],
            $row['label5'],
            (bool) $row['enable_comment'],
            (bool) $row['only_textual_feedback'],
            (bool) $row['is_locked'],
            $this->isDimensionInUse((int) $row['dim_id'])
        );
    }

    public function isDimensionTitleInUse(string $title, bool $save = true) : bool
    {
        $query = "SELECT title" . PHP_EOL
                . "FROM " . self::TABLENAME_DIMS . PHP_EOL
                . "WHERE title = " . $this->getDB()->quote($title, "text") . PHP_EOL
                . "AND is_locked = 0";
        $result = $this->getDB()->query($query);

        if ($save) {
            return $this->getDB()->numRows($result) != 0;
        }

        return $this->getDB()->numRows($result) > 1;
    }

    /**
     * Create new table xfbk_dimensions ifn't exists.
     */
    protected function createDimensionsTable()
    {
        if (!$this->getDB()->tableExists(self::TABLENAME_DIMS)) {
            $fields = array(
                'dim_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'title' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'info' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'label1' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'label2' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'label3' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'label4' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'label5' => array(
                    'type' => 'text',
                    'length' => 255
                    ),
                'enable_comment' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                    ),
                'is_locked' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true
                    )
                );
            $this->getDB()->createTable(self::TABLENAME_DIMS, $fields);
        }
    }

    /**
     * Create primary key for table xfbk_dimensions.
     *
     * @return 	void
     */
    public function createPrimaryKeyForDimensions()
    {
        $this->createPrimaryKey(self::TABLENAME_DIMS, array("dim_id"));
    }

    /**
     * Create sequence for table xfbk_dimensions.
     *
     * @return 	void
     */
    public function createSequenceForDimensions()
    {
        $this->getDB()->createSequence(self::TABLENAME_DIMS);
    }


    /**
     * @param 	int 			$set_id
     * @param 	Dimension[] 	$dimensions
     */
    protected function updateInterim(int $set_id, array $dimensions)
    {
        $query = "DELETE FROM " . self::INTERIM_TABLE . PHP_EOL
                . "WHERE set_id = " . $this->getDB()->quote($set_id, "integer");
        $this->getDB()->manipulate($query);

        foreach ($dimensions as $dimension) {
            $values = array(
                "set_id" => ["integer", $set_id],
                "dim_id" => ["integer", $dimension->getDimId()],
                "ordernumber" => ["integer", $dimension->getOrdernumber()]
                );
            $this->getDB()->insert(self::INTERIM_TABLE, $values);
        }
    }

    protected function deleteInterimBySetId(int $set_id)
    {
        $query = "DELETE FROM " . self::INTERIM_TABLE . PHP_EOL
                . "WHERE set_id = " . $this->getDB()->quote($set_id, "integer");
        $this->getDB()->manipulate($query);
    }

    protected function isDimensionInUse(int $dim_id) : bool
    {
        $query = "SELECT count(dim_id) AS dim" . PHP_EOL
                . "FROM " . self::INTERIM_TABLE . PHP_EOL
                . "WHERE dim_id = " . $this->getDB()->quote($dim_id, "integer");
        $result = $this->getDB()->query($query);
        $num = $this->getDB()->fetchAssoc($result);

        return $num['dim'] != 0;
    }

    /**
     * @param 	int 	$set_id
     * @return 	Dimension[]
     */
    protected function getDimensionsBySetId(int $set_id) : array
    {
        $ret = array();
        $where = "WHERE interim.set_id = " . $this->getDB()->quote($set_id, "integer") . PHP_EOL;
        $order = "ORDER BY interim.ordernumber ASC" . PHP_EOL;
        $query = "SELECT" . PHP_EOL
                . "    interim.ordernumber," . PHP_EOL
                . "    dim.dim_id," . PHP_EOL
                . "    dim.title," . PHP_EOL
                . "    dim.displayed_title," . PHP_EOL
                . "    dim.info," . PHP_EOL
                . "    dim.label1," . PHP_EOL
                . "    dim.label2," . PHP_EOL
                . "    dim.label3," . PHP_EOL
                . "    dim.label4," . PHP_EOL
                . "    dim.label5," . PHP_EOL
                . "    dim.enable_comment," . PHP_EOL
                 . "    dim.only_textual_feedback," . PHP_EOL
                 . "    dim.is_locked" . PHP_EOL
                 . "FROM " . self::INTERIM_TABLE . " AS interim" . PHP_EOL
                . "JOIN " . self::TABLENAME_DIMS . " AS dim" . PHP_EOL
                . "    ON dim.dim_id = interim.dim_id" . PHP_EOL;

        $result = $this->getDB()->query($query . $where . $order);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $dim = $this->getDimensionObject($row)
                   ->withOrdernumber((int) $row['ordernumber']);
            $ret[] = $dim;
        }

        return $ret;
    }

    public function getHighestOrdernumber(int $set_id) : int
    {
        $where = "WHERE set_id = " . $this->getDB()->quote($set_id, "integer");
        $query = "SELECT MAX(ordernumber) AS orn" . PHP_EOL
                . "FROM " . self::INTERIM_TABLE . PHP_EOL;
        $res = $this->getDB()->query($query . $where);

        return (int) $this->getDB()->fetchAssoc($res)['orn'];
    }

    /**
     * @throws \Exception
     */
    public function getDimensionOrdernumber(int $set_id, int $dim_id) : int
    {
        $query = "SELECT" . PHP_EOL
                . "    ordernumber" . PHP_EOL
                . "FROM " . self::INTERIM_TABLE . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    set_id = " . $this->getDB()->quote($set_id, "integer") . PHP_EOL
                . "    AND" . PHP_EOL
                . "    dim_id = " . $this->getDB()->quote($dim_id, "integer") . PHP_EOL;
        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \LogicException(__METHOD__ . " no Dimension found for dim_id " . $dim_id);
        }

        return (int) $this->getDB()->fetchAssoc($result)['ordernumber'];
    }

    protected function createInterimTable()
    {
        if (!$this->getDB()->tableExists(self::INTERIM_TABLE)) {
            $fields = array(
                'set_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'dim_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'ordernumber' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::INTERIM_TABLE, $fields);
        }
    }

    public function createPrimaryKeyForInterim()
    {
        $this->createPrimaryKey(self::INTERIM_TABLE, array("set_id", "dim_id"));
    }

    public function createSequenceForInterim()
    {
        $this->getDB()->createSequence(self::INTERIM_TABLE);
    }

    public function update1()
    {
        if ($this->getDB()->tableColumnExists(self::TABLENAME_DIMS, "info")) {
            $this->getDB()->modifyTableColumn(
                self::TABLENAME_DIMS,
                "info",
                array(
                    "type" => 'clob'
                )
            );
        }
    }

    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLENAME_DIMS, "only_textual_feedback")) {
            $this->getDB()->addTableColumn(
                self::TABLENAME_DIMS,
                "only_textual_feedback",
                [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => false
                ]
            )
            ;
        }
    }

    public function update3()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLENAME_DIMS, "displayed_title")) {
            $this->getDB()->addTableColumn(
                self::TABLENAME_DIMS,
                "displayed_title",
                [
                    'type' => 'text',
                    'length' => 255
                ]
            );

            $query = "SELECT dim_id, title FROM " . self::TABLENAME_DIMS;
            $result = $this->getDB()->query($query);

            while ($row = $result->fetchAssoc($result)) {
                $query =
                     "UPDATE " . self::TABLENAME_DIMS . PHP_EOL
                    . "SET displayed_title = " . $this->getDB()->quote($row["title"], "text") . PHP_EOL
                    . "WHERE dim_id = " . $this->getDB()->quote($row["dim_id"], "integer") . PHP_EOL
                ;

                $this->getDB()->manipulate($query);
            }
        }
    }

    protected function createPrimaryKey(string $tablename, array $primary_keys)
    {
        $this->getDB()->addPrimaryKey($tablename, $primary_keys);
    }

    /**
     * @throws \Exception
     */
    private function getDB() : \ilDBInterface
    {
        if (!$this->db) {
            throw new \Exception("no database");
        }
        return $this->db;
    }
}
