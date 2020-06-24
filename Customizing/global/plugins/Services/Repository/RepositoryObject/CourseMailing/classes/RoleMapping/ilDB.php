<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMailing\RoleMapping;

use Box\Spout\Reader\CSV\Reader;

class ilDB implements DB
{
    const TABLE_NAME = "xcml_rolemappings";
    const TABLE_NAME_ATTACHMENTS = "xcml_attachment_maps";
    const TABLE_NAME_ATTACHMENTS_DEPRECATED = "xcml_attachments";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Get next id for mapping entry
     */
    public function getNextId()
    {
        return (int) $this->db->nextId(static::TABLE_NAME);
    }

    public function upsert(RoleMapping $role_mapping)
    {
        if ($role_mapping->getId() == -1) {
            return $this->create($role_mapping);
        }
        return $this->update($role_mapping);
    }

    protected function create(RoleMapping $mapping)
    {
        $id = $this->getNextId();
        $mapping = new RoleMapping(
            $id,
            $mapping->getObjectId(),
            $mapping->getRoleId(),
            $mapping->getTemplateId(),
            $mapping->getAttachmentIds()
        );

        $values = array(
            "id" => array("integer", $mapping->getId()),
            "obj_id" => array("integer", $mapping->getObjectId()),
            "role_id" => array("integer", $mapping->getRoleId()),
            "mail_template_id" => array("integer", $mapping->getTemplateId())
        );
        $this->db->insert(static::TABLE_NAME, $values);
        $this->insertAttachmentsFor($mapping->getId(), $mapping->getAttachmentIds());
        return $mapping;
    }

    protected function update(RoleMapping $mapping)
    {
        $where = array("id" => array("integer", $mapping->getId()));
        $values = array(
            "obj_id" => array("integer", $mapping->getObjectId()),
            "role_id" => array("integer", $mapping->getRoleId()),
            "mail_template_id" => array("integer", $mapping->getTemplateId())
        );
        $this->db->update(static::TABLE_NAME, $values, $where);

        $mapping_id = $mapping->getId();
        $attachment_ids = $mapping->getAttachmentIds();
        $table = static::TABLE_NAME_ATTACHMENTS;

        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock($table);
        $ilAtomQuery->addQueryCallable(
            function (\ilDBInterface $db) use ($table, $mapping_id, $attachment_ids) {
                $this->deleteAttachmentsFor($mapping_id);
                $this->insertAttachmentsFor($mapping_id, $attachment_ids, $db);
            }

        );
        $ilAtomQuery->run();
        return $mapping;
    }


    /**
     * Insert attachment-id.
     *
     * @param 	int 	$id
     * @param 	string[] 	$attachment_ids
     * @return 	void
     */
    private function insertAttachmentsFor($id, array $attachment_ids)
    {
        foreach ($attachment_ids as $aid) {
            $values = array(
                "mapping_id" => array("integer", $id),
                "attachment_id" => array("text", $aid)
            );
            $this->db->insert(static::TABLE_NAME_ATTACHMENTS, $values);
        }
    }


    /**
     * Delete all attachment settings for this mapping.
     *
     * @param 	int 	$id
     * @return 	void
     */
    private function deleteAttachmentsFor($id)
    {
        $query = "DELETE FROM " . static::TABLE_NAME_ATTACHMENTS . PHP_EOL
            . " WHERE mapping_id = " . $this->db->quote($id, "integer");
        $this->db->manipulate($query);
    }


    /**
     *@inheritdoc
     */
    public function selectForObjectId(int $obj_id) : array
    {
        $query = "SELECT id, obj_id, role_id, mail_template_id" . PHP_EOL
                . " FROM " . static::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->db->quote($obj_id, "integer");

        $ret = array();
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $ret[] = $this->getMappingObject(
                (int) $row["id"],
                (int) $row["obj_id"],
                (int) $row["role_id"],
                (int) $row["mail_template_id"],
                $this->selectAttachmentsFor((int) $row["id"])
            );
        }

        return $ret;
    }

    /**
     *@inheritdoc
     */
    public function deleteForObject(int $obj_id)
    {
        $query = "SELECT id" . PHP_EOL
                . " FROM " . static::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->db->quote($obj_id, "integer");
        $result = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($result)) {
            $this->deleteAttachmentsFor((int) $row['id']);
        }

        $query = "DELETE FROM " . static::TABLE_NAME . PHP_EOL
            . " WHERE obj_id = " . $this->db->quote($obj_id, "integer");
        $this->db->manipulate($query);
    }


    /**
     *@inheritdoc
     */
    public function selectFor(int $id) : RoleMapping
    {
        $query = "SELECT id, obj_id, role_id, mail_template_id" . PHP_EOL
                . " FROM " . static::TABLE_NAME . PHP_EOL
                . " WHERE id = " . $this->db->quote($id, "integer");

        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        return $this->getMappingObject(
            (int) $row["id"],
            (int) $row["obj_id"],
            (int) $row["role_id"],
            (int) $row["mail_template_id"],
            $this->selectAttachmentsFor((int) $row["id"])
        );
    }


    /**
     * Get all attachment settings for this mapping.
     *
     * @param 	int 	$id
     * @return 	string[]
     */
    private function selectAttachmentsFor($id)
    {
        $query = "SELECT attachment_id" . PHP_EOL
                . " FROM " . static::TABLE_NAME_ATTACHMENTS . PHP_EOL
                . " WHERE mapping_id = " . $this->db->quote($id, "integer");

        $result = $this->db->query($query);
        $attachments = array();
        while ($row = $this->db->fetchAssoc($result)) {
            $attachments[] = $row['attachment_id'];
        }
        return $attachments;
    }


    /**
     * @inheritdoc
     */
    public function deleteFor(int $id)
    {
        $this->deleteAttachmentsFor($id);

        $query = "DELETE FROM " . static::TABLE_NAME . PHP_EOL
            . " WHERE id = " . $this->db->quote($id, "integer");
        $this->db->manipulate($query);
    }

    public function deleteRoleEntriesById(int $role_id)
    {
        $q = "SELECT id FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE role_id = " . $this->db->quote($role_id, 'integer');

        $res = $this->db->query($q);
        while ($row = $this->db->fetchAssoc($res)) {
            $id = (int) $row['id'];
            $this->deleteAttachmentsFor($id);
            $query = "DELETE FROM " . static::TABLE_NAME . PHP_EOL
                . " WHERE id = " . $this->db->quote($id, "integer");
            $this->db->manipulate($query);
        }
    }

    /**
     * create table
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->db->tableExists(static::TABLE_NAME)) {
            $fields = array(
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
                'role_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'role_title' => array(
                    'type' => 'text',
                    'length' => 256,
                    'notnull' => true
                ),
                'mail_template_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => false
                )
            );
            $this->db->createTable(static::TABLE_NAME, $fields);
        }
    }

    /**
     * Configure primary key on table
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->db->addPrimaryKey(static::TABLE_NAME, array("id"));
    }

    /**
     * Create sequences for ids
     *
     * @return void
     */
    public function createSequence()
    {
        $this->db->createSequence(static::TABLE_NAME);
    }


    /**
     * create table
     *
     * @return void
     */
    public function createAttachmentTable()
    {
        if (!$this->db->tableExists(static::TABLE_NAME_ATTACHMENTS)) {
            $fields = array(
                'mapping_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'attachment_id' => array(
                    'type' => 'text',
                    'length' => 128,
                    'notnull' => true
                )
            );
            $this->db->createTable(static::TABLE_NAME_ATTACHMENTS, $fields);
        }
    }

    /**
     * Configure primary key on table
     *
     * @return void
     */
    public function createPrimaryKeyForAttachments()
    {
        $this->db->addPrimaryKey(static::TABLE_NAME_ATTACHMENTS, array("mapping_id", "attachment_id"));
    }

    /**
     * Configure primary key on table
     *
     * @return void
     */
    public function singulateAttachmentData()
    {
        if ($this->db->tableExists(static::TABLE_NAME_ATTACHMENTS_DEPRECATED)) {
            $query = "INSERT INTO " . static::TABLE_NAME_ATTACHMENTS . PHP_EOL
                . " SELECT * FROM " . static::TABLE_NAME_ATTACHMENTS_DEPRECATED . PHP_EOL
                . " GROUP BY mapping_id, attachment_id";

            $this->db->manipulate($query);
        }
    }

    public function getMappingObject(
        int $mapping_id,
        int $obj_id,
        int $role_id,
        int $template_id = null,
        array $attachments = []
    ) : RoleMapping {
        return new RoleMapping(
            $mapping_id,
            $obj_id,
            $role_id,
            $template_id,
            $attachments
        );
    }
}
