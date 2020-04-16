<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Category;

use CaT\Plugins\CourseClassification\Options\ilOptionsTableGUI;

class ilCategoriesTableGUI extends ilOptionsTableGUI
{
    /**
     * @var \ilOptionsGUI
     */
    protected $parent_object;

    /**
     * @var ilActions
     */
    protected $actions;


    protected $type;

    /**
     * @param string 	$type
     */
    public function __construct(\ilOptionsGUI $parent_object, ilActions $actions, string $type)
    {
        parent::__construct($parent_object, $actions, $type);

        $this->configurateTableExtras();
    }

    protected function configurateTableExtras()
    {
        $this->setRowTemplate("tpl.categories_row.html", $this->actions->getPlugin()->getDirectory());
        $this->addColumn($this->actions->getPlugin()->txt("topics"), "", "50%");
    }

    /**
     * @param Option 	$a_set
     */
    protected function fillRow($a_set)
    {
        parent::fillRow($a_set);
        /** @var Category $option */
        $option = $a_set["option"];

        if ($option->getTopics()) {
            $this->tpl->setVariable("TOPICS", $option->getTopicsTitleString());
        }
    }
}
