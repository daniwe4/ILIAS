<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Topic;

use CaT\Plugins\CourseClassification\Options\Category\Category;
use CaT\Plugins\CourseClassification\Options\ilDB as OptionDB;
use CaT\Plugins\CourseClassification\Options\Option;

class ilDB extends OptionDB
{
    const TABLE_NAME = 'xccl_topic';
    const TABLE_CATEGORY = 'xccl_category';
    const TABLE_TOPIC_ASSIGN = 'xccl_category_topic';
    const TABLE_MULTI_TOPICS = 'xccl_data_topics';

    /**
     * @inheritdoc
     */
    public function create(string $caption) : Option
    {
        $id = $this->getNextId();
        $option = new Topic($id, $caption);

        $values = [
            'id' => ['integer', $option->getId()],
            'caption' => ['text', $option->getCaption()]
        ];

        $this->getDB()->insert(static::TABLE_NAME, $values);

        return $option;
    }

    public function assignCategory(int $topic_id, int $category_id)
    {
        $values = [
            'topic_id' => ['integer', $topic_id],
            'category_id' => ['integer', $category_id]
        ];

        $this->getDB()->insert(self::TABLE_TOPIC_ASSIGN, $values);
    }

    public function deleteAssignmentsBy(int $topic_id)
    {
        $query = 'DELETE FROM ' . self::TABLE_TOPIC_ASSIGN . PHP_EOL
                . ' WHERE topic_id = ' . $this->getDB()->quote($topic_id, 'integer');

        $this->getDB()->manipulate($query);
    }

    /**
     * @inheritdoc
     */
    public function select() : array
    {
        $query = 'SELECT A.id, A.caption, C.id AS category_id, C.caption AS category_caption' . PHP_EOL
            . ' FROM ' . self::TABLE_NAME . ' A' . PHP_EOL
            . ' LEFT JOIN ' . self::TABLE_TOPIC_ASSIGN . ' B' . PHP_EOL
            . '     ON A.id = B.topic_id' . PHP_EOL
            . ' LEFT JOIN ' . self::TABLE_CATEGORY . ' C' . PHP_EOL
            . '     ON B.category_id = C.id';

        $result = $this->getDB()->query($query);

        $ret = [];
        while ($row = $this->getDB()->fetchAssoc($result)) {
            $category = null;
            if ($row['category_id'] !== null) {
                $category = new Category((int) $row['category_id'], $row['category_caption'], ...[]);
            }

            $topic = new Topic((int) $row['id'], $row['caption'], $category);
            $ret[] = $topic;
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    protected function createTable()
    {
        parent::createTable();

        if (!$this->getDB()->tableExists(self::TABLE_TOPIC_ASSIGN)) {
            $fields =
                array('topic_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'category_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    )
                );

            $this->getDB()->createTable(static::TABLE_TOPIC_ASSIGN, $fields);
            $this->getDB()->addPrimaryKey(static::TABLE_TOPIC_ASSIGN, array('topic_id', 'category_id'));
        }
    }

    /**
     * @return string[]
     */
    public function getCategoriesForForm() : array
    {
        $query = 'SELECT id, caption FROM ' . self::TABLE_CATEGORY;
        $res = $this->getDB()->query($query);

        $ret = [];
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[$row['id']] = $row['caption'];
        }

        return $ret;
    }

    /**
     * @return Option | null
     */
    public function getCategoryById(int $category_id)
    {
        $query = 'SELECT id, caption FROM ' . self::TABLE_CATEGORY . PHP_EOL
                . ' WHERE id = ' . $this->getDB()->quote($category_id, 'integer');

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            return null;
        }

        $row = $this->getDB()->fetchAssoc($res);

        return new Category((int) $row['id'], $row['caption'], ...[]);
    }

    /**
     * @return int[]
     */
    public function getAssignedCategories(int $topic_id) : array
    {
        $query = 'SELECT category_id FROM' . PHP_EOL
                . ' ' . self::TABLE_TOPIC_ASSIGN . PHP_EOL
                . ' WHERE topic_id = ' . $this->getDB()->quote($topic_id, 'integer');

        $res = $this->getDB()->query($query);
        $ret = [];
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = (int) $row['category_id'];
        }

        return $ret;
    }

    /**
     * @return int[]
     */
    public function getAffectedCCObjectObjIds(int $id) : array
    {
        $ret = [];
        $query = 'SELECT obj_id' . PHP_EOL
            . ' FROM ' . self::TABLE_MULTI_TOPICS . PHP_EOL
            . ' WHERE option_id = ' . $this->getDB()->quote($id, 'integer');

        $res = $this->getDB()->query($query);
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = (int) $row['obj_id'];
        }

        return $ret;
    }
}
