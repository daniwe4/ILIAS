<?php

namespace CaT\Plugins\MaterialList\Materials;

/**
 * Implementation for DB handle of material values
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xmat_stock";

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
    public function update(Material $material)
    {
        $where = array("id" => array("integer", $material->getId()));

        $values = array("article_number" => array("text", $material->getArticleNumber())
                ,"title" => array("text", $material->getTitle())
                );
        $this->getDB()->update(self::TABLE_NAME, $values, $where);

        return $material;
    }

    /**
     * @inheritdoc
     */
    public function create($article_number, $title)
    {
        $next_id = $this->getNextId();
        $material = new Material($next_id, $article_number, $title);

        $values = array( "id" => array("integer", $material->getId())
                , "article_number" => array("text", $material->getArticleNumber())
                , "title" => array("text", $material->getTitle())
                );
        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $material;
    }

    /**
     * @inheritdoc
     */
    public function selectAll()
    {
        $query = "SELECT id, article_number, title\n"
                . " FROM " . self::TABLE_NAME . "\n";

        $res = $this->getDB()->query($query);
        $ret = array();

        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new Material((int) $row["id"], $row["article_number"], $row["title"]);
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function deleteAll()
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n";

        $this->getDB()->manipulate($query);
    }

    /**
     * Get all article entries starting with $start_with
     * on article_number or title
     *
     * @param string 	$start_with
     *
     * @return array<string, string>
     */
    public function getOptionsStartingWith($start_with)
    {
        $query = "SELECT DISTINCT article_number, title,\n"
                . $this->getDB()->locate($this->getDB()->quote($start_with, "text"), 'article_number') . " as loc_article_number,\n"
                . $this->getDB()->locate($this->getDB()->quote($start_with, "text"), 'title') . " as loc_title\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " HAVING loc_article_number != 0 OR loc_title != 0";

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = array($row["article_number"], $row["title"]);
        }

        return $ret;
    }

    /**
     * Check selected article number
     *
     * @param string 	$article_number
     *
     * @return boolean
     */
    public function checkArticleNumber($article_number)
    {
        $query = "SELECT COUNT(article_number) as cnt\n"
                . " FROM " . self::TABLE_NAME . "\n"
                . " WHERE article_number = " . $this->getDB()->quote($article_number, "text");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        return $row["cnt"] > 0;
    }

    /**
     * Create table for materials
     *
     * @return null
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
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
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
