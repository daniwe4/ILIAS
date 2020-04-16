<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Feedback;

use CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;

class ilDB implements DB
{
    const TABLE_FEEDBACK = "xfbk_feedbacks";
    const TABLE_SET_CONTENTS = "xfbk_setcontents";
    const TABLE_DIMENSIONS = "xfbk_dimensions";

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
        $this->createTable();
    }

    /**
     * @inheritdoc
     */
    public function create(
        int $obj_id,
        int $set_id,
        int $usr_id,
        int $dim_id
    ) : Feedback {
        $feedback = new Feedback(
            $obj_id,
            $set_id,
            $usr_id,
            $dim_id,
            0,
            "",
            null,
            null
            );

        $values = array(
            'obj_id' => ['integer', $feedback->getObjId()],
            'set_id' => ['integer', $feedback->getSetId()],
            'usr_id' => ['integer', $feedback->getUsrId()],
            'dim_id' => ['integer', $feedback->getDimId()],
            'rating' => ['integer', $feedback->getRating()],
            'commenttext' => ['text', $feedback->getCommenttext()]
            );

        $this->getDB()->insert(self::TABLE_FEEDBACK, $values);

        return $feedback;
    }

    /**
     * @inheritdoc
     */
    public function update(Feedback $feedback)
    {
        $where = array(
            'obj_id' => array('integer', $feedback->getObjId()),
            'set_id' => array('integer', $feedback->getSetId()),
            'dim_id' => array('integer', $feedback->getDimId()),
            'usr_id' => array('integer', $feedback->getUsrId())
            );

        $values = array(
            'parent_obj_id' => ['integer', $feedback->getParentObjId()],
            'parent_ref_id' => ['integer', $feedback->getParentRefId()],
            'rating' => ['integer', $feedback->getRating()],
            'commenttext' => ['text', $feedback->getCommenttext()]
            );

        $this->getDB()->update(self::TABLE_FEEDBACK, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectAll() : array
    {
        $feedbacks = array();

        $query = "SELECT" . PHP_EOL
                . "    parent_obj_id," . PHP_EOL
                . "    parent_ref_id," . PHP_EOL
                . "    obj_id," . PHP_EOL
                . "    set_id," . PHP_EOL
                . "    usr_id," . PHP_EOL
                . "    dim_id," . PHP_EOL
                . "    rating," . PHP_EOL
                . "    commenttext" . PHP_EOL
                . "FROM " . self::TABLE_FEEDBACK;

        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $feedbacks[] = $this->getFeedbackObject($row);
        }

        return $feedbacks;
    }

    /**
     * @inheritdoc
     */
    public function selectByIds(int $obj_id, int $set_id) : array
    {
        $feedbacks = array();

        $query = "SELECT" . PHP_EOL
                . "    parent_obj_id," . PHP_EOL
                . "    parent_ref_id," . PHP_EOL
                . "    obj_id," . PHP_EOL
                . "    set_id," . PHP_EOL
                . "    usr_id," . PHP_EOL
                . "    dim_id," . PHP_EOL
                . "    rating," . PHP_EOL
                . "    commenttext" . PHP_EOL
                . "FROM " . self::TABLE_FEEDBACK . PHP_EOL
                . "WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . "    AND set_id = " . $this->getDB()->quote($set_id, "integer") . PHP_EOL;

        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            return $feedbacks;
        }

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $feedbacks[] = $this->getFeedbackObject($row);
        }

        return $feedbacks;
    }

    /**
     * @inheritdoc
     */
    public function getAmountOfFeedbacks(int $obj_id, int $set_id) : int
    {
        $query = "SELECT COUNT(DISTINCT usr_id) AS cnt" . PHP_EOL
                . " FROM " . self::TABLE_FEEDBACK . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . "    AND set_id = " . $this->getDB()->quote($set_id, "integer") . PHP_EOL;

        $result = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($result);

        return (int) $row["cnt"];
    }

    /**
     * Get all user id that allready give a feedback.
     * @return 	int[]
     */
    public function getUsrIds(int $obj_id, int $set_id) : array
    {
        $arr = array();

        $query = "SELECT DISTINCT" . PHP_EOL
                . "    usr_id" . PHP_EOL
                . "FROM " . self::TABLE_FEEDBACK . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . "    AND" . PHP_EOL
                . "    set_id = " . $this->getDB()->quote($set_id, "integer") . PHP_EOL;
        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            return $arr;
        }

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $arr[] = $row['usr_id'];
        }

        return $arr;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_FEEDBACK . "\n"
                . "WHERE parent_obj_id = " . $this->getDB()->quote($id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * Get dimension title for dim_id.
     */
    public function getDimensionTitleById(int $dim_id) : string
    {
        $query = "SELECT" . PHP_EOL
                . "    title" . PHP_EOL
                . "FROM " . self::TABLE_DIMENSIONS . PHP_EOL
                . "WHERE dim_id = " . $this->getDB()->quote($dim_id, "integer") . PHP_EOL;
        $result = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($result);

        return $row['title'];
    }

    /**
     * Get dimension title for dim_id.
     */
    public function getDimensionDisplayedTitleById(int $dim_id) : string
    {
        $query = "SELECT" . PHP_EOL
                 . "    displayed_title" . PHP_EOL
                 . "FROM " . self::TABLE_DIMENSIONS . PHP_EOL
                 . "WHERE dim_id = " . $this->getDB()->quote($dim_id, "integer") . PHP_EOL;
        $result = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($result);

        return $row['displayed_title'];
    }

    /**
     * Check whether the user wants to repeat a feedback.
     */
    public function checkRepeat(int $obj_id, int $usr_id) : bool
    {
        $query = "SELECT" . PHP_EOL
                . "    set_id" . PHP_EOL
                . "FROM " . self::TABLE_FEEDBACK . PHP_EOL
                . "WHERE" . PHP_EOL
                . "    obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL
                . "    AND" . PHP_EOL
                . "    usr_id = " . $this->getDB()->quote($usr_id, "integer") . PHP_EOL
                ;

        $result = $this->getDB()->query($query);

        return $this->getDB()->numRows($result) != 0;
    }

    protected function getFeedbackObject(array $row) : Feedback
    {
        if ($row['parent_obj_id'] == "NULL") {
            $parent_obj_id = null;
        } else {
            $parent_obj_id = (int) $row['parent_obj_id'];
        }

        if ($row['parent_ref_id'] == "NULL") {
            $parent_ref_id = null;
        } else {
            $parent_ref_id = (int) $row['parent_ref_id'];
        }

        return new Feedback(
            (int) $row['obj_id'],
            (int) $row['set_id'],
            (int) $row['usr_id'],
            (int) $row['dim_id'],
            (int) $row['rating'],
            $row['commenttext'],
            $parent_obj_id,
            $parent_ref_id
            );
    }

    /**
     * Get all dimensions for a given set id.
     * @return 	Dimension[]
     */
    public function getDimensionsForSetId(int $set_id) : array
    {
        $dimensions = array();
        $query = "SELECT" . PHP_EOL
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
                . "FROM " . self::TABLE_DIMENSIONS . " AS dim" . PHP_EOL
                . "JOIN " . self::TABLE_SET_CONTENTS . " AS sc" . PHP_EOL
                . "    ON sc.dim_id = dim.dim_id" . PHP_EOL
                . "WHERE sc.set_id = " . $this->getDB()->quote($set_id, "integer") . PHP_EOL
                . "ORDER BY sc.ordernumber";

        $result = $this->getDB()->query($query);

        while ($row = $this->getDB()->fetchAssoc($result)) {
            $dimensions[] = $this->getDimensionObject($row);
        }

        return $dimensions;
    }

    /**
     * Generate a dimension object using a database tuple.
     */
    protected function getDimensionObject(array $row) : Dimension
    {
        return new Dimension(
            (int) $row['dim_id'],
            $row['title'],
            $row["displayed_title"],
            $row['info'],
            $row['label1'],
            $row['label2'],
            $row['label3'],
            $row['label4'],
            $row['label5'],
            (bool) $row['enable_comment'],
            (bool) $row['only_textual_feedback'],
            (bool) $row['is_locked']
            );
    }

    /**
     * @throws \Exception
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_FEEDBACK)) {
            $fields = array(
                'parent_obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'parent_ref_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'set_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'usr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'dim_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'rating' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                    ),
                'commenttext' => array(
                    'type' => 'text',
                    'length' => 255
                    )
                );
            $this->getDB()->createTable(self::TABLE_FEEDBACK, $fields);
        }
    }

    public function createPrimaryKeyForFeedback()
    {
        $this->createPrimaryKey(
            self::TABLE_FEEDBACK,
            array("obj_id", "set_id", "usr_id", "dim_id")
        );
    }

    /**
     * Create a primary key for tablename
     * @param 	string[]	$primary_keys
     */
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

    /**
     * @throws \Exception
     */
    public function update1()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_FEEDBACK, "parent_obj_id")) {
            $this->getDB()->modifyTableColumn(
                self::TABLE_FEEDBACK,
                "parent_obj_id",
                array("notnull" => false)
            );
        }

        if ($this->getDB()->tableColumnExists(self::TABLE_FEEDBACK, "parent_ref_id")) {
            $this->getDB()->modifyTableColumn(
                self::TABLE_FEEDBACK,
                "parent_ref_id",
                array("notnull" => false)
            );
        }
    }

    /**
     * @throws \Exception
     */
    public function update2()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_FEEDBACK, "commenttext")) {
            $this->getDB()->modifyTableColumn(
                self::TABLE_FEEDBACK,
                "commenttext",
                array(
                    "type" => 'clob'
                )
            );
        }
    }
}
