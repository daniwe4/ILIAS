<?php

namespace CaT\Plugins\MaterialList\Lists;

/**
 * Interface for repo object material list entries
 */
class ilDB implements DB
{
    const TABLE_NAME = "xmat_data";

    /**
     * @var \ilDB
     */
    protected $db;

    public function __construct($db)
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
    public function create($obj_id, $number_per_participant, $number_per_course, $article_number, $title)
    {
        $next_id = $this->getNextId();
        $list_entry = new ListEntry(
            $next_id,
            $obj_id,
            $number_per_participant,
            $number_per_course,
            $article_number,
            $title
        );

        $values = array( "id" => array("integer", $list_entry->getId())
                    , "obj_id" => array("integer", $list_entry->getObjId())
                    , "number_per_participant" => array("integer", $list_entry->getNumberPerParticipant())
                    , "number_per_course" => array("integer", $list_entry->getNumberPerCourse())
                    , "article_number" => array("text", $list_entry->getArticleNumber())
                    , "title" => array("text", $list_entry->getTitle())
                );

        $this->getDB()->insert(self::TABLE_NAME, $values);
    }

    /**
     * @inheritdoc
     */
    public function update(ListEntry $list_entry)
    {
        $where = array( "id" => array("integer", $list_entry->getId())
                    , "obj_id" => array("integer", $list_entry->getObjId())
                );

        $values = array( "number_per_participant" => array("integer", $list_entry->getNumberPerParticipant())
                    , "number_per_course" => array("integer", $list_entry->getNumberPerCourse())
                    , "article_number" => array("text", $list_entry->getArticleNumber())
                    , "title" => array("text", $list_entry->getTitle())
                );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteId(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function deleteForObjId(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function selectForObjId(int $obj_id)
    {
        $query = "SELECT id, obj_id, number_per_participant, number_per_course, article_number, title\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);
        $ret = array();

        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new ListEntry(
                (int) $row["id"],
                (int) $row["obj_id"],
                (int) $row["number_per_participant"],
                (int) $row["number_per_course"],
                $row["article_number"],
                $row["title"]
            );
        }

        return $ret;
    }

    /**
     * Get article number and title by id
     *
     * @param int 	$id
     *
     * @return string
     */
    public function getArtTitleById(int $id)
    {
        $query = "SELECT article_number, title\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        return $row["article_number"] . $row["title"];
    }

    /**
     * Create required tables
     *
     * @return null
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields =
                array('id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'number_per_participant' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'number_per_course' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'article_number' => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => true
                    ),
                    'title' => array(
                        'type' => 'text',
                        'length' => 256,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id", "obj_id"));
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    /**
     * Get next db id
     *
     * @return int
     */
    public function getNextId()
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }

    /**
     * Get the db handler
     *
     * @throws \Exception
     *
     * @return \ilDB
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
