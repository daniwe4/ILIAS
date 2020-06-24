<?php

namespace CaT\Plugins\CourseClassification\Settings;

use CaT\Plugins\CourseClassification\Helper;
use CaT\Plugins\CourseClassification\AdditionalLinks;

/**
 *
 */
class ilDB implements DB
{
    use Helper;

    const TABLE_NAME = "xccl_data";
    const TABLE_MULTI_TOPICS = "xccl_data_topics";
    const TABLE_MULTI_METHODS = "xccl_data_method";
    const TABLE_MULTI_MEDIA = "xccl_data_medias";
    const TABLE_MULTI_TARGET_GROUPS = "xccl_data_targetgroups";

    const TABLE_CATEGORY = "xccl_category";
    const TABLE_EDU_PROGRAM = "xccl_eduprogramme";
    const TABLE_MEDIA = "xccl_media";
    const TABLE_METHOD = "xccl_method";
    const TABLE_TARGET_GROUP = "xccl_target_group";
    const TABLE_TOPIC = "xccl_topic";
    const TABLE_TYPE = "xccl_type";
    const TABLE_TOPIC_ASSIGN = "xccl_category_topic";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var AdditionalLinks\DB | null
     */
    protected $links_db;

    public function __construct(\ilDBInterface $db, AdditionalLinks\DB $links_db = null)
    {
        $this->db = $db;
        $this->links_db = $links_db;
    }

    /**
     * @inhertidoc
     */
    public function update(CourseClassification $course_classification) : void
    {
        $obj_id = $course_classification->getObjId();
        $where = array("obj_id" => array("integer", $obj_id));

        $values = array("type" => array("integer", $course_classification->getType()),
                        "edu_program" => array("integer", $course_classification->getEduProgram()),
                        "content" => array("text", $course_classification->getContent()),
                        "goals" => array("text", $course_classification->getGoals()),
                        "preparation" => array("text", $course_classification->getPreparation()),
                        "target_group_description" => array("text", $course_classification->getTargetGroupDescription()),
                        "contact_name" => array("text", $course_classification->getContact()->getName()),
                        "contact_responsibility" => array("text", $course_classification->getContact()->getResponsibility()),
                        "contact_phone" => array("text", $course_classification->getContact()->getPhone()),
                        "contact_mail" => array("text", $course_classification->getContact()->getMail())
            );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);

        $this->assignTopics($obj_id, $course_classification->getTopics());
        $this->assignMethods($obj_id, $course_classification->getMethod());
        $this->assignMedia($obj_id, $course_classification->getMedia());
        $this->assignTargetGroups($obj_id, $course_classification->getTargetGroup());
        $this->assignAdditionalLinks($obj_id, $course_classification->getAdditionalLinks());
    }

    /**
     * @inhertidoc
     */
    public function create(
        int $obj_id,
        ?int $type = null,
        ?int $edu_program = null,
        array $topics = null,
        array $categories = null,
        ?int $content = null,
        ?string $goals = null,
        ?string $preparation = null,
        array $method = null,
        array $media = null,
        array $target_group = null,
        ?int $target_group_description = null,
        string $contact_name = "",
        string $contact_responsibility = "",
        string $contact_phone = "",
        string $contact_mail = ""
    ) : CourseClassification {
        assert($this->checkIntArray($topics));
        assert($this->checkIntArray($categories));
        assert($this->checkIntArray($method));
        assert($this->checkIntArray($media));
        assert($this->checkIntArray($target_group));

        $contact = new Contact($contact_name, $contact_name, $contact_phone, $contact_mail);

        $course_classification = new CourseClassification(
            $obj_id,
            $type,
            $edu_program,
            $topics,
            $categories,
            $content,
            $goals,
            $preparation,
            $method,
            $media,
            $target_group,
            $target_group_description,
            $contact
        );

        $values = array("obj_id" => array("integer", $course_classification->getObjId()),
                        "type" => array("integer", $course_classification->getType()),
                        "edu_program" => array("integer", $course_classification->getEduProgram()),
                        "content" => array("text", $course_classification->getContent()),
                        "goals" => array("text", $course_classification->getGoals()),
                        "preparation" => array("text", $course_classification->getPreparation()),
                        "target_group_description" => array("text", $course_classification->getTargetGroupDescription()),
                        "contact_name" => array("text", $course_classification->getContact()->getName()),
                        "contact_responsibility" => array("text", $course_classification->getContact()->getResponsibility()),
                        "contact_phone" => array("text", $course_classification->getContact()->getPhone()),
                        "contact_mail" => array("text", $course_classification->getContact()->getMail())
            );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        $this->assignTopics($obj_id, $topics);
        $this->assignMethods($obj_id, $method);
        $this->assignMedia($obj_id, $media);
        $this->assignTargetGroups($obj_id, $target_group);

        return $course_classification;
    }

    /**
     * @inhertidoc
     */
    public function selectFor(int $obj_id) : CourseClassification
    {
        $query = "SELECT A.obj_id, A.type, A.edu_program, A.content, A.goals, A.preparation, A.target_group_description,\n"
                . " A.contact_name, A.contact_responsibility, A.contact_phone, A.contact_mail,"
                . "     GROUP_CONCAT(DISTINCT B.option_id SEPARATOR ',') AS topics,\n"
                . "     GROUP_CONCAT(DISTINCT C.option_id SEPARATOR ',') AS methods,\n"
                . "     GROUP_CONCAT(DISTINCT D.option_id SEPARATOR ',') AS media,\n"
                . "     GROUP_CONCAT(DISTINCT E.option_id SEPARATOR ',') AS target_groups,\n"
                . "     GROUP_CONCAT(DISTINCT F.category_id SEPARATOR ',') AS categories\n"
                . " FROM " . self::TABLE_NAME . " A\n"
                . " LEFT JOIN " . self::TABLE_MULTI_TOPICS . " B\n"
                . "     ON A.obj_id = B.obj_id\n"
                . " LEFT JOIN " . self::TABLE_MULTI_METHODS . " C\n"
                . "     ON A.obj_id = C.obj_id\n"
                . " LEFT JOIN " . self::TABLE_MULTI_MEDIA . " D\n"
                . "     ON A.obj_id = D.obj_id\n"
                . " LEFT JOIN " . self::TABLE_MULTI_TARGET_GROUPS . " E\n"
                . "     ON A.obj_id = E.obj_id\n"
                . " LEFT JOIN " . self::TABLE_TOPIC_ASSIGN . " F\n"
                . "     ON B.option_id = F.topic_id\n"
                . " WHERE A.obj_id = " . $this->getDB()->quote($obj_id, "integer") . "\n"
                . " GROUP BY A.obj_id";
        $result = $this->getDB()->query($query);

        if ($this->getDB()->numRows($result) == 0) {
            throw new \LogicException("No settings entry for obj_id: $obj_id found");
        }

        $row = $this->getDB()->fetchAssoc($result);
        $contact = new Contact(
            (string) $row["contact_name"],
            (string) $row["contact_responsibility"],
            (string) $row["contact_phone"],
            (string) $row["contact_mail"]
        );

        $additional_links = $this->links_db->selectFor($obj_id);

        $course_classification = new CourseClassification(
            (int) $row["obj_id"],
            (int) $row["type"],
            (int) $row["edu_program"],
            $this->transformGroupConcatString($row["topics"]),
            $this->transformGroupConcatString($row["categories"]),
            $row["content"],
            $row["goals"],
            $row["preparation"],
            $this->transformGroupConcatString($row["methods"]),
            $this->transformGroupConcatString($row["media"]),
            $this->transformGroupConcatString($row["target_groups"]),
            $row["target_group_description"],
            $contact,
            $additional_links
        );

        return $course_classification;
    }

    protected function transformGroupConcatString(?string $group_concat_string) : ?array
    {
        if (
            is_null($group_concat_string) ||
            $group_concat_string == ""
        ) {
            return null;
        }

        return array_map(function ($part) {
            return (int) $part;
        }, explode(",", $group_concat_string));
    }

    /**
     * @inhertidoc
     */
    public function deleteFor(int $obj_id) : void
    {
        $this->assignTopics($obj_id, array());
        $this->assignMethods($obj_id, array());
        $this->assignMedia($obj_id, array());
        $this->assignTargetGroups($obj_id, array());

        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
        $this->links_db->deleteFor($obj_id);
    }

    /**
     * Get form options by table name
     *
     * @param string 	$table_name
     *
     * @return string[]
     */
    public function getFormOptionsByTableName(string $table_name) : array
    {
        //Can't quote the table name. SQL Statement will be broken because of '.
        $query = "SELECT id, caption\n"
                . " FROM " . $table_name . "\n";

        $ret = array();
        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[(int) $row["id"]] = $row["caption"];
        }

        return $ret;
    }

    public function getOptionsNameByTableName(string $table_name, array $option_ids) : array
    {
        //Can't quote the table name. SQL Statement will be broken because of '.
        $query = "SELECT id, caption\n"
                . " FROM " . $table_name . "\n"
                . " WHERE " . $this->getDB()->in("id", $option_ids, false, "integer");

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return array("-");
        }

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[$row["id"]] = $row["caption"];
        }

        return $ret;
    }

    /**
     * Get topic form options by category
     *
     * @return string[]
     */
    public function getTopicGroupOptions() : array
    {
        $query = "SELECT A.id, A.caption\n"
                . " FROM " . self::TABLE_TOPIC . " A\n";

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[(int) $row["id"]] = $row["caption"];
        }

        return $ret;
    }

    /**
     * Save assignes topics
     *
     * @param int 	$obj_id
     * @param int[] | null 	$topics
     *
     * @return void
     */
    protected function assignTopics(int $obj_id, array $topics = null) : void
    {
        $this->deassign(self::TABLE_MULTI_TOPICS, $obj_id);
        if ($topics !== null) {
            $this->assign(self::TABLE_MULTI_TOPICS, $obj_id, $topics);
        }
    }

    /**
     * Save assignees methods
     *
     * @param int 	$obj_id
     * @param int[] | null 	$methods
     *
     * @return void
     */
    protected function assignMethods(int $obj_id, array $methods = null) : void
    {
        $this->deassign(self::TABLE_MULTI_METHODS, $obj_id);
        if ($methods !== null) {
            $this->assign(self::TABLE_MULTI_METHODS, $obj_id, $methods);
        }
    }

    /**
     * Save assignes media
     *
     * @param int 	$obj_id
     * @param int[] | null 	$media
     *
     * @return void
     */
    protected function assignMedia(int $obj_id, array $media = null) : void
    {
        $this->deassign(self::TABLE_MULTI_MEDIA, $obj_id);
        if ($media !== null) {
            $this->assign(self::TABLE_MULTI_MEDIA, $obj_id, $media);
        }
    }

    /**
     * Save assignes target groups
     *
     * @param int 	$obj_id
     * @param int[] | null 	$target_groups
     *
     * @return void
     */
    protected function assignTargetGroups(int $obj_id, array $target_groups = null) : void
    {
        $this->deassign(self::TABLE_MULTI_TARGET_GROUPS, $obj_id);
        if ($target_groups !== null) {
            $this->assign(self::TABLE_MULTI_TARGET_GROUPS, $obj_id, $target_groups);
        }
    }

    /**
     * Assign options
     *
     * @param string 	$table_name
     * @param int 		$obj_id
     * @param int[] 	$options
     *
     * @return void
     */
    protected function assign(string $table_name, int $obj_id, array $options) : void
    {
        foreach ($options as $key => $option) {
            $values = array("obj_id" => array("integer", $obj_id),
                            "option_id" => array("integer", $option)
                );

            $this->getDB()->insert($table_name, $values);
        }
    }

    /**
     * Deassign options
     *
     * @param string 	$table_name
     * @param int 		$obj_id
     *
     * @return null
     */
    protected function deassign(string $table_name, int $obj_id) : void
    {
        $query = "DELETE FROM " . $table_name . "\n"
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Get categories ids by topic ids
     *
     * @param int[] | null
     *
     * @return int[] | null
     */
    public function getCategoriesByTopicIds(array $topic_ids = null) : ?array
    {
        $query = "SELECT DISTINCT category_id\n"
                . " FROM " . self::TABLE_TOPIC_ASSIGN . "\n"
                . " WHERE " . $this->getDB()->in("topic_id", $topic_ids, false, "integer");

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = (int) $row["category_id"];
        }

        return $ret;
    }

    public function assignAdditionalLinks(int $obj_id, array $links) : void
    {
        $this->links_db->storeFor($obj_id, $links);
    }
    public function readAdditionalLinks(int $obj_id) : void
    {
        $this->links_db->storeFor($obj_id, $links);
    }


    /**
     * Create tables
     *
     * @return void
     */
    public function createTables() : void
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'type' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'edu_program' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'category' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'content' => array(
                        'type' => 'clob',
                        'notnull' => false
                    ),
                    'goals' => array(
                        'type' => 'clob',
                        'notnull' => false
                    ),
                    'target_group_description' => array(
                        'type' => 'clob',
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(self::TABLE_NAME, $fields);
        }

        $multi_select_tables = array(self::TABLE_MULTI_TOPICS, self::TABLE_MULTI_METHODS, self::TABLE_MULTI_MEDIA, self::TABLE_MULTI_TARGET_GROUPS);

        foreach ($multi_select_tables as $table) {
            if (!$this->getDB()->tableExists($table)) {
                $fields =
                    array('obj_id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true
                        ),
                        'option_id' => array(
                            'type' => 'text',
                            'length' => 128,
                            'notnull' => true
                        )
                    );

                $this->getDB()->createTable($table, $fields);
            }
        }
    }

    /**
     * Create primary keys
     *
     * @return null
     */
    public function createPrimaryKeys() : void
    {
        $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
        $this->getDB()->addPrimaryKey(self::TABLE_MULTI_TOPICS, array("obj_id", "option_id"));
        $this->getDB()->addPrimaryKey(self::TABLE_MULTI_METHODS, array("obj_id", "option_id"));
        $this->getDB()->addPrimaryKey(self::TABLE_MULTI_MEDIA, array("obj_id", "option_id"));
        $this->getDB()->addPrimaryKey(self::TABLE_MULTI_TARGET_GROUPS, array("obj_id", "option_id"));
    }

    /**
     * DB Update step 1
     *
     * @return void
     */
    public function update1() : void
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "contact_name")) {
            $fields = array(
                        'type' => 'text',
                        'length' => 128,
                        'default' => "",
                        'notnull' => false
                    );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "contact_name", $fields);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "contact_responsibility")) {
            $fields = array(
                        'type' => 'text',
                        'length' => 128,
                        'default' => "",
                        'notnull' => false
                    );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "contact_responsibility", $fields);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "contact_phone")) {
            $fields = array(
                        'type' => 'text',
                        'length' => 64,
                        'default' => "",
                        'notnull' => false
                    );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "contact_phone", $fields);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "contact_mail")) {
            $fields = array(
                        'type' => 'clob',
                        'notnull' => false
                    );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "contact_mail", $fields);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "contact_consultation")) {
            $fields = array(
                        'type' => 'clob',
                        'notnull' => false
                    );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "contact_consultation", $fields);
        }
    }

    /**
     * Migrate categories to multi assignment
     *
     * @return void
     */
    public function update2() : void
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "category")) {
            $this->getDB()->dropTableColumn(self::TABLE_NAME, "category");
        }
    }

    /**
     * remove consultation
     *
     * @return void
     */
    public function update3() : void
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "contact_consultation")) {
            $this->getDB()->dropTableColumn(self::TABLE_NAME, "contact_consultation");
        }
    }

    /**
     * add table column preparation
     *
     * @return void
     */
    public function update4() : void
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "preparation")) {
            $fields = array(
                        'type' => 'clob',
                        'notnull' => false
                    );
            $this->getDB()->addTableColumn(self::TABLE_NAME, "preparation", $fields);
        }
    }

    /**
     * Update database
     *
     * @return void
     */
    public function update5() : void
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "learning_time")) {
            $this->getDB()->dropTableColumn(self::TABLE_NAME, "learning_time");
        }
    }

    /**
     * Get intance of db
     *
     * @throws \Exception
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
