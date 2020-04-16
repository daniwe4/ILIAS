<?php
declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\AdditionalLinks;

class ilDB implements DB
{
    const TABLE_ADDITIONAL_LINKS = "xccl_data_links";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return AdditionalLink[]
     */
    public function selectFor(int $obj_id) : array
    {
        $query = 'SELECT label, url' . PHP_EOL
            . 'FROM ' . static::TABLE_ADDITIONAL_LINKS . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($obj_id, "integer")
        ;

        $result = $this->db->query($query);
        $ret = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $link = new AdditionalLink($row['label'], $row['url']);
            $ret[] = $link;
        }

        return $ret;
    }

    public function deleteFor(int $obj_id)
    {
        $query = 'DELETE FROM ' . static::TABLE_ADDITIONAL_LINKS . PHP_EOL
            . 'WHERE obj_id=' . $this->db->quote($obj_id, "integer")
        ;
        $this->db->manipulate($query);
    }

    protected function insertFor(int $obj_id, AdditionalLink $link)
    {
        $id = $this->db->nextId(static::TABLE_ADDITIONAL_LINKS);
        $values = array(
            'id' => ['integer', $id],
            'obj_id' => ['integer', $obj_id],
            'label' => ['text', $link->getLabel()],
            'url' => ['text', $link->getUrl()]
        );
        $this->db->insert(static::TABLE_ADDITIONAL_LINKS, $values);
    }

    /**
     * @param int $obj_id
     * @param AdditionalLink[] $links
     */
    public function storeFor(int $obj_id, array $links)
    {
        $atom_query = $this->db->buildAtomQuery();
        $atom_query->addTableLock(static::TABLE_ADDITIONAL_LINKS);
        $atom_query->addTableLock(static::TABLE_ADDITIONAL_LINKS . '_seq');

        $atom_query->addQueryCallable(
            function (\ilDBInterface $db) use ($obj_id, $links) {
                $this->deleteFor($obj_id);
                foreach ($links as $link) {
                    $this->insertFor($obj_id, $link);
                }
            }
        );
        $atom_query->run();
    }


    public function install()
    {
        $this->createTable();
        $this->createPrimaryKey();
    }

    protected function createTable()
    {
        if (!$this->db->tableExists(static::TABLE_ADDITIONAL_LINKS)) {
            $fields =
                array(
                    'id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'label' => array(
                        'type' => 'text',
                        'length' => 128,
                        'notnull' => false,
                        'default' => ''
                    ),
                    'url' => array(
                        'type' => 'clob',
                        'notnull' => true
                    )
                );

            $this->db->createTable(static::TABLE_ADDITIONAL_LINKS, $fields);
            $this->db->createSequence(static::TABLE_ADDITIONAL_LINKS);
        }
    }

    protected function createPrimaryKey()
    {
        $this->db->addPrimaryKey(static::TABLE_ADDITIONAL_LINKS, array("id", "obj_id"));
    }
}
