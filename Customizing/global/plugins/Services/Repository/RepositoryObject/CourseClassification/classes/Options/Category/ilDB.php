<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Category;

use CaT\Plugins\CourseClassification\Options;

class ilDB extends Options\ilDB
{
    const TABLE_NAME = "xccl_category";
    const TABLE_TOPIC = "xccl_topic";
    const TABLE_TOPIC_ASSIGN = "xccl_category_topic";

    /**
     * @inheritdoc
     */
    public function create(string $caption) : Options\Option
    {
        $id = $this->getNextId();
        $option = new Category($id, $caption, ...[]);

        $values = [
            "id" => ["integer", $option->getId()],
            "caption" => ["text", $option->getCaption()]
        ];

        $this->getDB()->insert(static::TABLE_NAME, $values);

        return $option;
    }

    /**
     * @inheritdoc
     */
    public function select() : array
    {
        $query = 'SELECT A.id, A.caption, C.id AS topic_id, C.caption AS topic_caption' . PHP_EOL
            . ' FROM ' . self::TABLE_NAME . ' A' . PHP_EOL
            . ' LEFT JOIN ' . self::TABLE_TOPIC_ASSIGN . ' B' . PHP_EOL
            . '    ON A.id = B.category_id' . PHP_EOL
            . ' LEFT JOIN ' . self::TABLE_TOPIC . ' C' . PHP_EOL
            . '    ON B.topic_id = C.id' . PHP_EOL
            . ' ORDER BY A.id' . PHP_EOL
        ;

        $result = $this->getDB()->query($query);
        $ret = array();
        $category_id = null;
        $category = null;
        $topics = array();
        while ($row = $this->getDB()->fetchAssoc($result)) {
            if ($category_id != $row["id"]) {
                if ($category != null) {
                    $ret[] = $category->withTopics($topics);
                    $topics = array();
                }
                $category_id = (int) $row["id"];
                $category = new Category($category_id, $row["caption"], ...[]);
            }

            if ($row["topic_id"]) {
                $topics[] = new Options\Topic\Topic((int) $row["topic_id"], $row["topic_caption"], null);
            }
        }

        if ($category) {
            $ret[] = $category->withTopics(...$topics);
        }

        return $ret;
    }

    /**
     * @return Options\Option[]
     */
    public function getTopicsById(int $category_id)
    {
        $query = 'SELECT id, caption' . PHP_EOL
            . ' FROM ' . self::TABLE_TOPIC . ' A' . PHP_EOL
            . ' JOIN ' . self::TABLE_TOPIC_ASSIGN . ' B' . PHP_EOL
            . '     ON B.topic_id = A.id' . PHP_EOL
            . ' WHERE B.category_id = ' . $this->getDB()->quote($category_id, 'integer')
        ;

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new Options\Topic\Topic((int) $row["id"], $row["caption"], null);
        }

        return $ret;
    }
}
