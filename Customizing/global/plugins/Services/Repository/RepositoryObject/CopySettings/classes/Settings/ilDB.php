<?php

declare(strict_types=1);

namespace CaT\Plugins\CopySettings\Settings;

require_once(__DIR__ . "/class.ilCopySettingsGUI.php");

class ilDB implements DB
{
    const TABLE_NAME = "xcps_settings";

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
    public function create(int $obj_id) : Settings
    {
        $settings = new Settings(
            $obj_id,
            false,
            false,
            false,
            false,
            false,
            false,
            [],
            false,
            false,
            false,
            false,
            false,
            false,
            true,
            null,
            null
        );

        $values = array(
            "obj_id" => array("integer", $settings->getObjId()),
            "edit_title" => array("integer", $settings->getEditTitle()),
            "edit_target_groups" => array("integer", $settings->getEditTargetGroups()),
            "edit_target_group_desc" => array("integer", $settings->getEditTargetGroupDescription()),
            "edit_content" => array("integer", $settings->getEditContent()),
            "edit_benefits" => array("integer", $settings->getEditBenefits()),
            "edit_idd_learningtime" => array("integer", $settings->getEditIDDLearningTime()),
            "role_ids" => array("text", serialize($settings->getRoleIds())),
            "time_mode" => array("text", $settings->getTimeMode()),
            "min_days_in_future" => array("integer", $settings->getMinDaysInFuture()),
            "edit_venue" => array("integer", $settings->getEditVenue()),
            "edit_provider" => array("integer", $settings->getEditProvider()),
            "additional_infos" => array("integer", $settings->getAdditionalInfos()),
            "no_mail" => array("integer", $settings->getNoMail()),
            "edit_gti" => array("integer", $settings->isEditGti()),
            "suppress_mail_delivery" => array("integer", $settings->getSuppressMailDelivery()),
            "edit_memberlimits" => array("integer", $settings->getEditMemberlimits())
        );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function update(Settings $settings)
    {
        $where = array("obj_id" => array("integer", $settings->getObjId()));

        $values = array(
            "edit_title" => array("integer", $settings->getEditTitle()),
            "edit_target_groups" => array("integer", $settings->getEditTargetGroups()),
            "edit_target_group_desc" => array("integer", $settings->getEditTargetGroupDescription()),
            "edit_content" => array("integer", $settings->getEditContent()),
            "edit_benefits" => array("integer", $settings->getEditBenefits()),
            "edit_idd_learningtime" => array("integer", $settings->getEditIDDLearningTime()),
            "role_ids" => array("text",  serialize($settings->getRoleIds())),
            "time_mode" => array("text", $settings->getTimeMode()),
            "min_days_in_future" => array("integer", $settings->getMinDaysInFuture()),
            "edit_venue" => array("integer", $settings->getEditVenue()),
            "edit_provider" => array("integer", $settings->getEditProvider()),
            "additional_infos" => array("integer", $settings->getAdditionalInfos()),
            "no_mail" => array("integer", $settings->getNoMail()),
            "edit_gti" => array("integer", $settings->isEditGti()),
            "suppress_mail_delivery" => array("integer", $settings->getSuppressMailDelivery()),
            "edit_memberlimits" => array("integer", $settings->getEditMemberlimits())
        );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function select(int $obj_id) : Settings
    {
        $query = "SELECT obj_id, edit_title, edit_target_groups, edit_target_group_desc, edit_content," . PHP_EOL
            . " edit_benefits, edit_idd_learningtime, role_ids," . PHP_EOL
            . " time_mode, min_days_in_future, edit_venue, edit_provider, additional_infos," . PHP_EOL
            . " no_mail, suppress_mail_delivery, edit_gti, edit_memberlimits" . PHP_EOL
            . " FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        $min_days_in_future = $row["min_days_in_future"];
        if (!is_null($min_days_in_future)) {
            $min_days_in_future = (int) $min_days_in_future;
        }

        $time_mode = $row["time_mode"];
        if (is_null($time_mode)) {
            $time_mode = \ilCopySettingsGUI::EVERYTIME;
        }

        return new Settings(
            (int) $row["obj_id"],
            (bool) $row["edit_title"],
            (bool) $row["edit_target_groups"],
            (bool) $row["edit_target_group_desc"],
            (bool) $row["edit_content"],
            (bool) $row["edit_benefits"],
            (bool) $row["edit_idd_learningtime"],
            unserialize($row["role_ids"]),
            (bool) $row["edit_venue"],
            (bool) $row["edit_provider"],
            (bool) $row["additional_infos"],
            (bool) $row["no_mail"],
            (bool) $row["edit_gti"],
            (bool) $row["edit_memberlimits"],
            (bool) $row["suppress_mail_delivery"],
            $time_mode,
            $min_days_in_future
        );
    }

    /**
     * @inheritdoc
     */
    public function delete(int $obj_id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . PHP_EOL
            . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");

        $this->getDB()->manipulate($query);
    }

    public function createTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME)) {
            $fields =
                array('obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'edit_title' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    ),
                    'edit_target_groups' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'edit_content' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(static::TABLE_NAME, $fields);
        }
    }

    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "edit_benefits")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'edit_benefits', $field);
        }
    }

    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "role_id")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'role_id', $field);
        }
    }

    public function update3()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "time_mode")) {
            $field = array(
                'type' => 'text',
                'length' => 50,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'time_mode', $field);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "min_days_in_future")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'min_days_in_future', $field);
        }
    }

    public function update4()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "edit_idd_learningtime")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 0
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'edit_idd_learningtime', $field);
        }
    }

    public function update5()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "additional_infos")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 0
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'additional_infos', $field);
        }
    }

    public function update6()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "no_mail")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 0
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'no_mail', $field);
        }
    }

    public function update7()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "role_id")) {
            $field = array(
                'type' => 'clob',
                'notnull' => false
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, 'role_id', $field);
        }
    }

    public function update8()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "role_id")) {
            $this->getDB()->renameTableColumn(self::TABLE_NAME, 'role_id', "role_ids");
        }
    }

    public function update9()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "suppress_mail_delivery")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 1
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'suppress_mail_delivery', $field);
        }
    }

    public function update10()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "edit_venue")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'edit_venue', $field);
        }

        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "edit_provider")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'edit_provider', $field);
        }
    }

    public function update11()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "edit_gti")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 0
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'edit_gti', $field);
        }
    }
    public function update12()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "edit_target_group_desc")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false,
                'default' => 0
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'edit_target_group_desc', $field);
        }
    }
    public function update13()
    {
        if (!$this->getDB()->tableColumnExists(self::TABLE_NAME, "edit_memberlimits")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            );

            $this->getDB()->addTableColumn(self::TABLE_NAME, 'edit_memberlimits', $field);
        }
    }

    public function createPrimaryKey()
    {
        if ($this->getDB()->tableExists(self::TABLE_NAME)) {
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
        }
    }

    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }
}
