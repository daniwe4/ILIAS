<?php
namespace CaT\Plugins\MaterialList\Materials;

use \CaT\Plugins\MaterialList\ilPluginActions;

require_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Table for materials
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilMaterialsTableGUI extends \ilTable2GUI
{
    /**
     * @var \ilMaterialListConfigGUI
     */
    protected $parent_gui;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var ilPluginActions
     */
    protected $plugin_actions;

    public function __construct(\ilMaterialListConfigGUI $parent_gui, \Closure $txt, ilPluginActions $plugin_actions)
    {
        $this->parent_gui = $parent_gui;
        $this->plugin_actions = $plugin_actions;
        $this->txt = $txt;

        parent::__construct($parent_gui);

        $this->setEnableTitle(true);
        $this->setTitle($this->txt("materials"));
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(false);
        $this->setRowTemplate("tpl.materials.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/");
        $this->setShowRowsSelector(false);
        $this->setLimit(0);

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->txt("material_article_number"), false);
        $this->addColumn($this->txt("material_title") . ' <span class="asterisk">*</span>', false);
        $this->counter = 0;
    }

    public function fillRow($a_set)
    {
        $object = $a_set["object"];
        $errors = $a_set["errors"];
        $message = $a_set["message"];

        $this->tpl->setVariable("ID", $this->counter);
        $this->tpl->setVariable("POST_VAR", ilPluginActions::F_IDS_TO_DEL);

        $this->tpl->setVariable("COUNTER", $this->counter);
        $this->tpl->setVariable("POST_VAR_IDS", ilPluginActions::F_CURRENT_MATERIAL_HIDDEN_IDS);
        $this->tpl->setVariable("HIDDEN_ID", $object->getId());

        require_once("Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new \ilTextInputGUI("", ilPluginActions::F_CURRENT_MATERIAL_ARTICLE_NUMBERS . "[]");
        $ti->setValue($object->getArticleNumber());
        $ti->setMaxLength(32);
        $this->tpl->setVariable("ARTICLE_NUMBER", $ti->render());
        $this->tpl->setVariable("POST_VAR_OLD_NUMBER", ilPluginActions::F_OLD_MATERIAL_ARTICLE_NUMBERS);
        $old_article_number = "";
        if ($object->getId() !== -1) {
            $old_article_number = $object->getArticleNumber();
        }
        $this->tpl->setVariable("ARTICLE_NUMBER_OLD", $old_article_number);

        $ti = new \ilTextInputGUI("", ilPluginActions::F_CURRENT_MATERIAL_TITLES . "[]");
        $ti->setValue($object->getTitle());
        $ti->setMaxLength(256);
        $this->tpl->setVariable("TITLE", $ti->render());

        if (array_key_exists("article_number", $errors)) {
            $article_number_errors = $errors["article_number"];
            $article_number_errors = array_map(function ($err) {
                return $this->plugin_actions->getPlugin()->txt($err);
            }, $article_number_errors);
            $this->tpl->setCurrentBlock("article_number_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->plugin_actions->getPlugin()->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $article_number_errors));
            $this->tpl->parseCurrentBlock();
        }

        if (array_key_exists("title", $errors)) {
            $title_errors = $errors["title"];
            $title_errors = array_map(function ($err) {
                return $this->plugin_actions->getPlugin()->txt($err);
            }, $title_errors);
            $this->tpl->setCurrentBlock("title_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->plugin_actions->getPlugin()->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $title_errors));
            $this->tpl->parseCurrentBlock();
        }

        if (count($message) > 0) {
            $message = array_map(function ($mes) {
                return $this->plugin_actions->getPlugin()->txt($mes);
            }, $message);
            $this->tpl->setCurrentBlock("message");
            $this->tpl->setVariable("MESSAGE_CSS_ROW", $this->css_row);
            $this->tpl->setVariable("MESSAGE", implode(",", $message));
            $this->tpl->parseCurrentBlock();
        }

        $this->counter++;
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code)
    {
        $txt = $this->txt;

        return $txt($code);
    }
}
