<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\CourseClassification\Options\Category;
use CaT\Plugins\CourseClassification\TableProcessing\TableProcessor;

require_once(__DIR__ . "/../class.ilOptionsGUI.php");

class ilCategoriesGUI extends ilOptionsGUI
{
    /**
     * @inheritdoc
     */
    protected function getTableGUI()
    {
        return new Category\ilCategoriesTableGUI($this, $this->actions, $this->type);
    }

    protected function saveOptions()
    {
        $options = $this->getProcessingOptionsFromPost();
        $options = $this->table_processor->process($options, array(TableProcessor::ACTION_SAVE));

        foreach ($options as $key => $option) {
            $id = $option["option"]->getId();
            if ($id != -1) {
                $this->actions->raiseUpdateEventFor($id);
            }
        }
        $this->showOptions($options);
    }

    /**
     * @inheritdoc
     */
    protected function getOptionsFromPost()
    {
        $ret = array();
        $post = $_POST;

        if ($post["caption"] && count($post["caption"]) > 0) {
            foreach ($post["caption"] as $key => $value) {
                $topics = $this->actions->getTopicsById((int) $post["hidden_id"][$key]);
                $ret[$key] = new Category\Category((int) $post["hidden_id"][$key], $value, ...$topics);
            }
        }

        return $ret;
    }
}
