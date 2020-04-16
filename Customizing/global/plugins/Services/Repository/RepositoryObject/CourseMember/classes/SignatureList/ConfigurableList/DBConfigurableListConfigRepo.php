<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types = 1);

namespace CaT\Plugins\CourseMember\SignatureList\ConfigurableList;

use ILIAS\FileDelivery\FileDeliveryTypes\PHP;

class DBConfigurableListConfigRepo implements ConfigurableListConfigRepo
{
    const TABLE_MAIN = 'xcmb_siglstcfg';
    const TABLE_FIELDS = 'xcmb_siglstflds';
    const TABLE_COURSES = 'xcmb_siglstcrs';

    const FIELD_ID = 'id';
    const FIELD_ROW_ID = 'row_id';
    const FIELD_CRS_ID = 'crs_id';
    const FIELD_NAME = 'name';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_TYPE = 'type';
    const FIELD_VALUE = 'value';
    const FIELD_DEFAULT = 'is_default';
    const FIELD_MAIL_TEMPLATE = 'mail_template_id';

    const TYPE_STANDARD = 'standard';
    const TYPE_UDF = 'udf';
    const TYPE_LP = 'lp';
    const TYPE_ROLES = 'roles';
    const TYPE_ADDITIONAL = 'additional';

    protected $db;
    protected $af;

    public function __construct(
        \ilDBInterface $db,
        AvailableFields $af
    ) {
        $this->db = $db;
        $this->af = $af;
    }

    /**
     * @inheritDoc
     */
    public function create(
        string $name,
        string $description,
        array $standard_fields,
        array $lp_fields,
        array $udf_fields,
        array $roles,
        array $additional,
        string $mail_template
    ) : ConfigurableListConfig {
        if ($this->exists($name)) {
            throw new \Exception('may not overwrite name ' . $name);
        }
        $id = $this->getNextId();
        $cfg = new ConfigurableListConfig(
            $id,
            $name,
            $description,
            array_intersect($standard_fields, array_keys($this->af->getStandardFields())),
            array_intersect($lp_fields, array_keys($this->af->getLpFields())),
            array_intersect($udf_fields, array_keys($this->af->getUdfFields())),
            array_intersect($roles, array_keys($this->af->getRoles())),
            $additional,
            false,
            $mail_template
        );
        $this->insertMain($cfg);
        $this->insertFields($cfg);
        return $cfg;
    }

    protected function getNextId() : int
    {
        return (int) $this->db->nextId(self::TABLE_MAIN);
    }

    protected function insertMain(ConfigurableListConfig $cfg)
    {
        $this->db->insert(
            self::TABLE_MAIN,
            [
                self::FIELD_ID => ['integer',$cfg->getId()],
                self::FIELD_NAME => ['text',$cfg->getName()],
                self::FIELD_DESCRIPTION => ['text',$cfg->getDescription()],
                self::FIELD_DEFAULT => ['integer',$cfg->isDefault()],
                self::FIELD_MAIL_TEMPLATE => ['text',$cfg->getMailTemplateId()]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function exists(string $name) : bool
    {
        $q = 'SELECT ' . self::FIELD_ID . ' FROM ' . self::TABLE_MAIN
            . '	WHERE ' . self::FIELD_NAME . ' = ' . $this->db->quote($name, 'text');
        if ($this->db->fetchAssoc($this->db->query($q))) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function existsId(int $id) : bool
    {
        $q = 'SELECT ' . self::FIELD_ID . ' FROM ' . self::TABLE_MAIN
            . '	WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($id, 'integer');
        if ($this->db->fetchAssoc($this->db->query($q))) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function idByName(string $name) : int
    {
        $q = 'SELECT ' . self::FIELD_ID . ' FROM ' . self::TABLE_MAIN
            . '	WHERE ' . self::FIELD_NAME . ' = ' . $this->db->quote($name, 'text');
        if ($rec = $this->db->fetchAssoc($this->db->query($q))) {
            return (int) $rec[self::FIELD_ID];
        }
        return self::NONE_INT;
    }

    /**
     * @inheritDoc
     */
    public function load(int $id) : ConfigurableListConfig
    {
        $q_main = 'SELECT ' . self::FIELD_NAME . ', ' . self::FIELD_DESCRIPTION
            . ', ' . self::FIELD_DEFAULT . ', ' . self::FIELD_MAIL_TEMPLATE . PHP_EOL
            . 'FROM ' . self::TABLE_MAIN . PHP_EOL
            . 'WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($id, 'integer')
        ;

        if ($rec = $this->db->fetchAssoc($this->db->query($q_main))) {
            $description = $rec[self::FIELD_DESCRIPTION];
            $name = $rec[self::FIELD_NAME];
            $default = (bool) $rec[self::FIELD_DEFAULT];
            $mail_template_id = (string) $rec[self::FIELD_MAIL_TEMPLATE];
        } else {
            throw new \Exception("no record exists for id " . $id);
        }
        $q_fields = 'SELECT ' . self::FIELD_TYPE
                . '		,' . self::FIELD_VALUE
                . '	FROM ' . self::TABLE_FIELDS
                . '	WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($id, 'integer');
        $res = $this->db->query($q_fields);
        $standard = [];
        $lp = [];
        $udf = [];
        $roles = [];
        $additional = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            switch ($rec[self::FIELD_TYPE]) {
                case self::TYPE_STANDARD:
                    $standard[] = $rec[self::FIELD_VALUE];
                    break;
                case self::TYPE_UDF:
                    $udf[] = $rec[self::FIELD_VALUE];
                    break;
                case self::TYPE_LP:
                    $lp[] = $rec[self::FIELD_VALUE];
                    break;
                case self::TYPE_ROLES:
                    $roles[] = $rec[self::FIELD_VALUE];
                    break;
                case self::TYPE_ADDITIONAL:
                    $additional[] = $rec[self::FIELD_VALUE];
                    break;
            }
        }
        return new ConfigurableListConfig(
            $id,
            $name,
            $description,
            array_intersect($standard, array_keys($this->af->getStandardFields())),
            array_intersect($lp, array_keys($this->af->getLpFields())),
            array_intersect($udf, array_keys($this->af->getUdfFields())),
            array_intersect($roles, array_keys($this->af->getRoles())),
            $additional,
            $default,
            $mail_template_id
        );
    }

    /**
     * @inheritDoc
     */
    public function save(ConfigurableListConfig $cfg)
    {
        if (!$this->existsId($cfg->getId())) {
            throw new \Exception('unkknown id ' . $cfg->getId());
        }
        $aux_id = $this->idByName($cfg->getName());
        if ($aux_id !== $cfg->getId() && $aux_id !== self::NONE_INT) {
            throw new \Exception('may not overwrite ' . $cfg->getName());
        }
        $this->updateMain($cfg);
        $this->updateFields($cfg);
    }

    /**
     * @inheritDoc
     */
    public function inUse(int $id) : bool
    {
        $q = 'SELECT ' . self::FIELD_CRS_ID . ' FROM ' . self::TABLE_COURSES . PHP_EOL
            . 'WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($id, 'integer')
        ;
        if ($rec = $this->db->fetchAssoc($this->db->query($q))) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id)
    {
        $this->deleteMain($id);
        $this->deleteFields($id);
    }

    /**
     * @inheritDoc
     */
    public function updateDefault(int $default_id)
    {
        $this->resetDefault();
        $this->setDefault($default_id);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultTeplateId()
    {
        $q = 'SELECT ' . self::FIELD_ID . PHP_EOL
            . 'FROM ' . self::TABLE_MAIN . PHP_EOL
            . 'WHERE ' . self::FIELD_DEFAULT . ' = 1';

        $res = $this->db->query($q);
        if ($this->db->numRows($res) == 0) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);
        return (int) $row[self::FIELD_ID];
    }

    /**
     * @inheritDoc
     */
    public function getSelectedCourseTemplate(int $crs_id)
    {
        $q = 'SELECT ' . self::FIELD_ID . ' FROM ' . self::TABLE_COURSES . PHP_EOL
            . 'WHERE ' . self::FIELD_CRS_ID . ' = ' . $this->db->quote($crs_id, 'integer')
        ;

        $res = $this->db->query($q);
        if ($this->db->numRows($res) == 0) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);
        return (int) $row[self::FIELD_ID];
    }

    /**
     * @inheritDoc
     */
    public function setSelectedCourseTemplate(int $crs_id, int $tpl_id)
    {
        $q = 'DELETE FROM ' . self::TABLE_COURSES . PHP_EOL
            . ' WHERE ' . self::FIELD_CRS_ID . ' = ' . $this->db->quote($crs_id, 'integer');

        $this->db->manipulate($q);

        $values = [
            self::FIELD_ID => [
                'integer',
                $tpl_id
            ],
            self::FIELD_CRS_ID => [
                'integer',
                $crs_id
            ]
        ];
        $this->db->insert(self::TABLE_COURSES, $values);
    }

    protected function updateMain(ConfigurableListConfig $cfg)
    {
        $where = [
            self::FIELD_ID => [
                "integer",
                $cfg->getId()
            ]
        ];

        $values = [
            self::FIELD_DESCRIPTION => [
                "text",
                $cfg->getDescription()
            ],
            self::FIELD_NAME => [
                "text",
                $cfg->getName()
            ],
            self::FIELD_MAIL_TEMPLATE => [
                "text",
                $cfg->getMailTemplateId()
            ]
        ];
        $this->db->update(self::TABLE_MAIN, $values, $where);
    }

    protected function updateFields(ConfigurableListConfig $cfg)
    {
        $this->deleteFields($cfg->getId());
        $this->insertFields($cfg);
    }

    protected function insertFields(ConfigurableListConfig $cfg)
    {
        $this->insertFieldType(
            $cfg->getId(),
            self::TYPE_STANDARD,
            array_intersect($cfg->getStandardFields(), array_keys($this->af->getStandardFields()))
        );
        $this->insertFieldType(
            $cfg->getId(),
            self::TYPE_UDF,
            array_intersect($cfg->getUdfFields(), array_keys($this->af->getUdfFields()))
        );
        $this->insertFieldType(
            $cfg->getId(),
            self::TYPE_LP,
            array_intersect($cfg->getLpFields(), array_keys($this->af->getLpFields()))
        );
        $this->insertFieldType(
            $cfg->getId(),
            self::TYPE_ROLES,
            array_intersect($cfg->getRoleFields(), array_keys($this->af->getRoles()))
        );
        $this->insertFieldType(
            $cfg->getId(),
            self::TYPE_ADDITIONAL,
            $cfg->getAdditionalFields()
        );
    }

    protected function insertFieldType(int $id, string $type, array $fields)
    {
        if (count($fields) > 0) {
            $q = 'INSERT INTO ' . self::TABLE_FIELDS . PHP_EOL
                . '(' . self::FIELD_ROW_ID . ', ' . self::FIELD_ID . ', '
                . self::FIELD_TYPE . ',' . self::FIELD_VALUE . ')' . PHP_EOL
                . 'VALUES'
            ;
            $rows = [];
            foreach ($fields as $field) {
                $row = [];
                $row[] = $this->db->quote($this->db->nextId(self::TABLE_FIELDS), 'integer');
                $row[] = $this->db->quote($id, 'integer');
                $row[] = $this->db->quote($type, 'text');
                $row[] = $this->db->quote($field, 'text');
                $rows[] = '(' . join(', ', $row) . ')';
            }
            $q .= join(', ', $rows);
            $this->db->manipulate($q);
        }
    }

    public function tableData() : array
    {
        $q = 'SELECT ' . self::FIELD_ID . ', ' . self::FIELD_NAME . ', ' . self::FIELD_DESCRIPTION . PHP_EOL
        . ', ' . self::FIELD_DEFAULT . ', ' . self::FIELD_MAIL_TEMPLATE . PHP_EOL
        . ' FROM ' . self::TABLE_MAIN;

        $res = $this->db->query($q);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $rec[self::FIELD_ID] = (int) $rec[self::FIELD_ID];
            $rec[self::FIELD_NAME] = (string) $rec[self::FIELD_NAME];
            $rec[self::FIELD_DESCRIPTION] = (string) $rec[self::FIELD_DESCRIPTION];
            $rec[self::FIELD_DEFAULT] = (bool) $rec[self::FIELD_DEFAULT];
            $rec[self::FIELD_MAIL_TEMPLATE] = (string) $rec[self::FIELD_MAIL_TEMPLATE];
            $return[] = $rec;
        }
        return $return;
    }

    public function getAvailablePlaceholders() : array
    {
        $q = "SELECT " . self::FIELD_MAIL_TEMPLATE . PHP_EOL
            . " FROM " . self::TABLE_MAIN . PHP_EOL
            . " WHERE (" . self::FIELD_MAIL_TEMPLATE . " IS NOT NULL" . PHP_EOL
            . "    AND " . self::FIELD_MAIL_TEMPLATE . " != '')"
        ;

        $ret = [];
        $res = $this->db->query($q);
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $row[self::FIELD_MAIL_TEMPLATE];
        }
        return $ret;
    }

    public function getTemplateByMailId(string $template_mail_id)
    {
        $q = 'SELECT ' . self::FIELD_ID . PHP_EOL
            . 'FROM ' . self::TABLE_MAIN . PHP_EOL
            . ' WHERE ' . self::FIELD_MAIL_TEMPLATE . ' = ' . $this->db->quote($template_mail_id, "text");

        $res = $this->db->query($q);
        if ($this->db->numRows($res) == 0) {
            return null;
        }
        $row = $this->db->fetchAssoc($res);
        return (int) $row[self::FIELD_ID];
    }

    protected function deleteMain(int $id)
    {
        $q = 'DELETE FROM ' . self::TABLE_MAIN . PHP_EOL
            . ' WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($id, 'integer');
        $this->db->manipulate($q);
    }

    protected function deleteFields(int $id)
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::TABLE_FIELDS . PHP_EOL
            . ' WHERE ' . self::FIELD_ID . ' = ' . $this->db->quote($id, 'integer')
        );
    }

    protected function resetDefault()
    {
        $this->db->manipulate(
            'UPDATE ' . self::TABLE_MAIN . PHP_EOL
            . 'SET ' . self::FIELD_DEFAULT . ' = 0'
        );
    }

    protected function setDefault(int $id)
    {
        $where = [
            self::FIELD_ID => [
                "integer",
                $id
            ]
        ];
        $values = [
            self::FIELD_DEFAULT => [
                "integer",
                1
            ]
        ];

        $this->db->update(self::TABLE_MAIN, $values, $where);
    }
}
