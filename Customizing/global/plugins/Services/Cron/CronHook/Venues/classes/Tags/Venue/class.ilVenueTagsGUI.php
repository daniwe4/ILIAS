<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */


declare(strict_types=1);

use CaT\Plugins\Venues\Tags\Venue;
use CaT\Plugins\Venues\Tags\Tag;
use CaT\Plugins\Venues\ilActions;

/**
 * GUI class for tags configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de
 */
class ilVenueTagsGUI
{
    const CMD_SHOW_TAGS = "showTags";
    const CMD_SAVE_TAGS = "saveTags";

    const F_TAGS_TAGS = "tags";

    /**
     * @var $ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var Venue\DB
     */
    protected $db;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        Venue\DB $db,
        Closure $txt,
        string $plugin_directory
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;

        $this->db = $db;
        $this->txt = $txt;
        $this->plugin_directory = $plugin_directory;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_TAGS:
                $this->showTags();
                break;
            case self::CMD_SAVE_TAGS:
                $this->saveTags();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showTags(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
        }

        $this->tpl->setContent($form->getHtml());
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

        $show_message = $this->createOrUpdateTags($tags);
        $this->deleteTags($tags);

        if ($show_message) {
            \ilUtil::sendSuccess($this->txt("tags_saved"), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_TAGS);
    }

    protected function createOrUpdateTags(array &$tags) : bool
    {
        $ret = false;
        foreach ($tags as $key => $tag) {
            if (($tag["name"] === null || $tag["name"] == "") && ($tag["color"] === null || $tag["color"] == "")) {
                continue;
            }

            if (isset($tag["id"]) && $tag["id"] != "" && $tag["id"] !== null) {
                $tag = new Tag((int) $tag["id"], $tag["name"], $tag["color"]);
                $this->db->update($tag);
            } else {
                $tag = $this->db->create($tag["name"], $tag["color"]);
                $tags[$key]["id"] = $tag->getId();
            }
            $ret = true;
        }
        return $ret;
    }

    protected function deleteTags(array $tags)
    {
        $exist_tags = $this->db->getTagsRaw();
        $exist_tags = array_map(function ($tag) {
            return $tag["id"];
        }, $exist_tags);

        $tags = array_map(function ($tag) {
            return $tag["id"];
        }, $tags);

        $deleted_tags = array_diff($exist_tags, $tags);

        foreach ($deleted_tags as $key => $tag_id) {
            $this->db->deallocation((int) $tag_id);
            $this->db->delete((int) $tag_id);
        }
    }

    protected function transformTagArray(array $tags) : array
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

    protected function initForm() : ilPropertyFormGUI
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("tag_title"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_TAGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_TAGS, $this->txt("cancel"));

        $tags = $this->db->getTagsRaw();
        $this->addTagFormItem($form, $tags);

        return $form;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    public function addTagFormItem(ilPropertyFormGUI $form, array $tag_options)
    {
        require_once $this->plugin_directory . "/classes/Tags/class.ilVenueTagInputGUI.php";
        $tag = new \ilVenueTagInputGUI($this->plugin_directory, $this->txt, $this->txt("tags"), self::F_TAGS_TAGS);
        $tag->setValue($tag_options);
        $form->addItem($tag);
    }

    public function getTagsFromPost(array $post) : array
    {
        return $post[self::F_TAGS_TAGS];
    }

    public function getFormValuesFromArray(array $tags) : array
    {
        return array(self::F_TAGS_TAGS => $tags);
    }
}
