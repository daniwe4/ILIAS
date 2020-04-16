<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use CaT\Plugins\TrainingProvider\Tags;
use CaT\Plugins\TrainingProvider\ilActions;

/**
 * GUI class for tags configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de
 */
class ilTagsGUI
{
    const CMD_SHOW_TAGS = "showTags";
    const CMD_SAVE_TAGS = "saveTags";

    /**
     * @var $ilCtrl
     */
    protected $gCtrl;

    /**
     * @var ilTemplate
     */
    protected $gTpl;

    /**
     * @var ilToolbarGUI
     */
    protected $gToolbar;

    public function __construct($parent_object, $plugin_object, $actions)
    {
        global $ilCtrl, $tpl, $ilToolbar;
        $this->gCtrl = $ilCtrl;
        $this->gTpl = $tpl;
        $this->gToolbar = $ilToolbar;

        $this->parent_object = $parent_object;
        $this->plugin_object = $plugin_object;
        $this->actions = $actions;
    }

    public function executeCommand()
    {
        $cmd = $this->gCtrl->getCmd(self::CMD_SHOW_TAGS);

        switch ($cmd) {
            case self::CMD_SHOW_TAGS:
            case self::CMD_SAVE_TAGS:
                $this->$cmd();
                break;
        }
    }

    protected function showTags($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
        }

        $this->gTpl->setContent($form->getHtml());
    }

    protected function saveTags()
    {
        $tags = $this->getTagsFromPost($_POST);
        $tags = $this->transformTagArray($tags);
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $values = $this->getFormValuesFromArray($tags);
            $form->setValuesByArray($values);
            $this->showTags($form);
            return;
        }

        $this->actions->saveTags($tags);
        \ilUtil::sendSuccess($this->txt('tags_saved'), true);
        $this->gCtrl->redirect($this);
    }

    protected function transformTagArray($tags)
    {
        $ret = array();

        $names = $tags["name"];
        $colors = $tags["color"];
        $ids = $tags["id"];

        foreach ($names as $key => $name) {
            $ret[] = array("id" => $ids[$key], "name" => $name, "color" => $colors[$key]);
        }

        return $ret;
    }

    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("tag_title"));
        $form->setFormAction($this->gCtrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_TAGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_TAGS, $this->txt("cancel"));

        $tags = $this->actions->getTagsRaw();
        $this->getTagFormItems($form, $tags);

        return $form;
    }

    protected function txt($code)
    {
        return $this->plugin_object->txt($code);
    }

    public function getTagFormItems($form, $tag_options)
    {
        require_once($this->plugin_object->getDirectory() . "/classes/Tags/class.ilSingleTagInputGUI.php");
        $tag = new \ilSingleTagInputGUI($this->plugin_object, $this->txt("tags"), ilActions::F_TAGS_TAGS);
        $tag->setValue($tag_options);
        $form->addItem($tag);
    }

    public function getTagsFromPost($post)
    {
        return $post[ilActions::F_TAGS_TAGS];
    }

    public function getFormValuesFromArray($tags)
    {
        return array(ilActions::F_TAGS_TAGS => $tags);
    }
}
