<?php

namespace CaT\Plugins\CourseClassification\Options\Topic;

use CaT\Plugins\CourseClassification\Options\ilOptionsTableGUI;

class ilTopicTableGUI extends ilOptionsTableGUI
{
    const F_CATEGORY = "category";

    /**
     * @param string 	$type
     */
    public function __construct(\ilOptionsGUI $parent_object, ilActions $actions, $type)
    {
        parent::__construct($parent_object, $actions, $type);

        $this->configurateTableExtras();
    }

    /**
     * Extra configurations for this special of options table
     *
     * @return null
     */
    protected function configurateTableExtras()
    {
        $this->setRowTemplate("tpl.topic_row.html", $this->actions->getPlugin()->getDirectory());
        $this->addColumn($this->actions->getPlugin()->txt("category"), "", "50%");
    }

    /**
     * @param Option 	$a_set
     */
    protected function fillRow($a_set)
    {
        $option = $a_set["option"];
        $errors = $a_set["errors"];

        require_once("Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new \ilSelectInputGUI("", self::F_CATEGORY . "[" . $this->counter . "]");
        $categories_options = array("-" => $this->actions->getPlugin()->txt("pls_select")) + $this->actions->getCategoriesForForm();
        $si->setOptions($categories_options);
        if ($option->getCategory()) {
            $si->setValue($option->getCategory()->getId());
        }

        $this->tpl->setVariable("CATEGORY", $si->render());
        if (array_key_exists("category", $errors)) {
            $category_errors = $errors["category"];
            $category_errors = array_map(function ($err) {
                return $this->actions->getPlugin()->txt($err);
            }, $category_errors);
            $this->tpl->setCurrentBlock("category_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->actions->getPlugin()->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $category_errors));
            $this->tpl->parseCurrentBlock();
        }

        parent::fillRow($a_set);
    }
}
