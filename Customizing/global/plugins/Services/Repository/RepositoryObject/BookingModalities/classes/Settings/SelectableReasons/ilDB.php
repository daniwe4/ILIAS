<?php

namespace CaT\Plugins\BookingModalities\Settings\SelectableReasons;

/**
 * Implementation of db interface of selectable reasons
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xbkm_select_reasons";

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
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function select()
    {
        $query = "SELECT id, reason, active" . PHP_EOL
                . " FROM " . self::TABLE_NAME;

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new SelectableReason((int) $row["id"], $row["reason"], (bool) $row["active"]);
        }

        return $ret;
    }

    /**
     * Get new emty selectable reason
     *
     * @return SelectableReason
     */
    public function newSelectableReason()
    {
        return new SelectableReason();
    }

    /**
     * Get a selectable reason object
     *
     * @param int 	$id
     * @param string 	$reason
     * @param bool 	$active
     */
    public function getSelectableReasonWith(int $id, string $reason, bool $active)
    {
        return new SelectableReason($id, $reason, $active);
    }

    /**
     * @inheritdoc
     */
    public function create(string $reason, bool $active)
    {
        $id = $this->getNextId();
        $reason = new SelectableReason($id, $reason, $active);

        $values = array("id" => array("integer", $reason->getId()),
            "reason" => array("text", $reason->getReason()),
            "active" => array("text", $reason->getActive())
        );
        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $reason;
    }

    /**
     * @inheritdoc
     */
    public function update(SelectableReason $selectable_reason)
    {
        $where = array("id" => array("integer", $selectable_reason->getId()));

        $values = array("reason" => array("text", $selectable_reason->getReason()),
            "active" => array("text", $selectable_reason->getActive())
        );
        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * Creates tables for this plugin
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields =
                array('id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'reason' => array(
                        'type' => 'text',
                        'length' => 255,
                        'notnull' => false
                    ),
                    'active' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Create primary key for member
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
    }

    /**
     * Create sequence
     *
     * @return void
     */
    public function createSequence()
    {
        $this->getDB()->createSequence(self::TABLE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function getReasonOptions($parent)
    {
        $query = "SELECT reason" . PHP_EOL
                . " FROM " . self::TABLE_NAME . PHP_EOL
                . " WHERE active = 1";

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[$row["reason"]] = $row["reason"];
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }

    /**
     * Get next id
     *
     * @return int
     */
    protected function getNextId()
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }
}
