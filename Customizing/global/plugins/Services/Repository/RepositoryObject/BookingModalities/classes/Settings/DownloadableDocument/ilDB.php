<?php

namespace CaT\Plugins\BookingModalities\Settings\DownloadableDocument;

/**
 * Implementation of db interface for role-specific documents
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_DOCS = "xbkm_roledocs";

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
    public function createRoleSetting($role_id)
    {
        assert('is_int($role_id)');
        $setting = new Relevance($role_id, '');
        $values = array("role_id" => array("int", $setting->getRoleId()));
        $this->getDB()->insert(self::TABLE_DOCS, $values);
        return $setting;
    }

    /**
     * @inheritdoc
     */
    public function selectRoleSetting($role_id)
    {
        assert('is_int($role_id)');
        $query = "SELECT filename\n"
            . " FROM " . self::TABLE_DOCS . " \n"
            . " WHERE role_id = " . $this->getDB()->quote($role_id, "integer");
        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) > 0) {
            $row = $this->getDB()->fetchAssoc($res);
            return new Relevance($role_id, $row['filename']);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function select()
    {
        $ret = array();

        $roles = $this->getGlobalRoles();
        $roles[0] = 'default';
        foreach ($roles as $role_id => $title) {
            //get or create setting
            $setting = $this->selectRoleSetting($role_id);
            if (!$setting) {
                $setting = $this->createRoleSetting($role_id);
            }
            $ret[] = $setting;
        }
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function updateRoleSetting(Relevance $relevance)
    {
        $where = array("role_id" => array("integer", $relevance->getRoleId()));
        $values = array("filename" => array("text", $relevance->getFileName()));
        $this->getDB()->update(self::TABLE_DOCS, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($role_id)
    {
        assert('is_int($role_id)');
        $query = "DELETE FROM " . self::TABLE_DOCS . "\n"
            . " WHERE role_id = " . $this->getDB()->quote($role_id, "integer");
        $this->getDB()->manipulate($query);
    }

    /**
     * Get all global roles
     *
     * @return array<int, string>
     */
    public function getGlobalRoles()
    {
        require_once('Services/AccessControl/classes/class.ilObjRole.php');
        global $DIC;
        $rbacreview = $DIC->rbac()->review();
        $role_ids = $rbacreview->getGlobalRoles();
        $ret = array();
        foreach ($role_ids as $id) {
            $role = new \ilObjRole((int) $id);
            $title = $role->getPresentationTitle();
            $ret[$id] = $title;
        }
        return $ret;
    }

    /**
     * Get  global roles for a user
     *
     * @param int $usr_id
     * @return int[]
     */
    public function getGlobalRoleIdsForUser($usr_id)
    {
        global $DIC;
        $rbacreview = $DIC->rbac()->review();
        $global_roles = $this->getGlobalRoles();
        $role_ids = array_filter(
            array_keys($global_roles),
            function ($role_id) use ($usr_id, $rbacreview) {
                return $rbacreview->isAssigned($usr_id, $role_id);
            }
        );

        return $role_ids;
    }

    /**
     * Get intance of db
     *
     * @throws \Exception
     * @return \ilDBInterface
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }

    /**
     * Creates table
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_DOCS)) {
            $fields =
                array(
                    'role_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'filename' => array(
                        'type' => 'text',
                        'length' => 255,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(self::TABLE_DOCS, $fields);
        }
    }

    /**
     * Create primary key
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(self::TABLE_DOCS, array("role_id"));
    }

    /**
     * Create table and primary key
     *
     * @return void
     */
    public function install()
    {
        $this->createTable();
        $this->createPrimaryKey();
    }
}
