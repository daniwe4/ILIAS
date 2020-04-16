<?php

use \CaT\Plugins\MaterialList\ilPluginActions;
use \CaT\Plugins\MaterialList\Materials\Material;
use \CaT\Plugins\MaterialList\Materials\ilMaterialsTableGUI;
use \CaT\Plugins\MaterialList\TableProcessing\TableProcessor;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/classes/Materials/class.ilImportMaterialListGUI.php");

/**
 * Config GUI for material in plugin configuration
 *
 * @ilCtrl_Calls ilMaterialsGUI: ilImportMaterialListGUI
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilMaterialsGUI
{
    const CMD_MATERIALS = "materials";
    const CMD_BEHAVIOR = "behavior";
    const CMD_ADD_ENTRY = "addNewMaterail";
    const CMD_IMPORT_MAERIAL_LIST = "importMaterialList";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_DELETE_MATERIALS = "deleteMaterials";
    const CMD_SAVE_MATERIALS = "saveMaterials";
    const CMD_SHOW_BEHAVIOR = "showBehavior";
    const CMD_SAVE_BEHAVIOR = "saveBehavior";

    const SUB_TAB_MATERIAL = "sub_material";
    const SUB_TAB_BEHAVIOR = "sub_behavior";

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var \ilMaterialListConfigGUI
     */
    protected $parent_gui;

    /**
     * @var \CaT\Plugins\MaterialList\ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var TableProcessor
     */
    protected $table_processor;

    public function __construct(\ilMaterialListConfigGUI $parent_gui, ilPluginActions $plugin_actions, \Closure $txt, TableProcessor $table_processor)
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tabs = $DIC->tabs();
        $this->parent_gui = $parent_gui;
        $this->plugin_actions = $plugin_actions;
        $this->txt = $txt;
        $this->table_processor = $table_processor;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCMD(self::CMD_MATERIALS);
        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilimportmateriallistgui":
                $this->importMaterialList();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_MATERIALS:
                        $this->setSubtabs(self::SUB_TAB_MATERIAL);
                        $this->materials();
                        break;
                    case self::CMD_ADD_ENTRY:
                        $this->setSubtabs(self::SUB_TAB_MATERIAL);
                        $this->addNewMaterail();
                        break;
                    case ilImportMaterialListGUI::CMD_SHOW_FORM:
                        $this->importMaterialList();
                        break;
                    case self::CMD_SAVE_MATERIALS:
                        $this->setSubtabs(self::SUB_TAB_MATERIAL);
                        $this->saveMaterials();
                        break;
                    case self::CMD_DELETE_MATERIALS:
                        $this->deleteMaterials();
                        break;
                    case self::CMD_SAVE_BEHAVIOR:
                        $this->saveBehavior();
                        break;
                    case self::CMD_BEHAVIOR:
                        $this->setSubtabs(self::SUB_TAB_BEHAVIOR);
                        $this->behavior();
                        break;
                    case self::CMD_CONFIRM_DELETE:
                        $this->confirmDelete();
                        break;
                    default:
                        throw new \Exception(__METHOD__ . " unknown command: " . $cmd);
                }
        }
    }

    /**
     * Show available materials
     *
     * @return null
     */
    protected function materials()
    {
        $materials = $this->plugin_actions->getCurrentMaterials();
        $process_data = $this->createProcessingArray($materials);
        $this->renderMaterialTable($process_data);
    }

    /**
     * Add new entry rows in table
     *
     * @return null
     */
    protected function addNewMaterail()
    {
        $materials = $this->getProcessingOptionsFromPost();
        $new_materials = $this->createProcessingArray($this->getNewMaterialOptions());
        $materials = array_merge($materials, $new_materials);
        $this->renderMaterialTable($materials);
    }

    /**
     * Get empty material objects according to entered number of new
     *
     * @return \CaT\Plugins\MaterialList\Materials\Material[]
     */
    protected function getNewMaterialOptions()
    {
        $post = $_POST;
        $number_of_new = $post[ilPluginActions::F_NEW_MATERIAL_LINE];
        $ret = array();

        for ($i = 0; $i < $number_of_new; $i++) {
            $ret[] = $this->plugin_actions->getNewMaterial();
        }

        return $ret;
    }

    /**
     * Render the material gui table
     *
     * @param \CaT\Plugins\MaterialList\Materials\Material[] | [] 	$materials
     *
     * @return null
     */
    protected function renderMaterialTable(array $materials)
    {
        $this->setToolbar();
        $table = new ilMaterialsTableGUI($this->parent_gui, $this->txt, $this->plugin_actions);
        $table->setData($materials);
        $table->addCommandButton(self::CMD_SAVE_MATERIALS, $this->txt("save"));
        $table->addMulticommand(self::CMD_CONFIRM_DELETE, $this->txt("delete"));
        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Shows the form for beahvior of material
     *
     * @return null
     */
    protected function behavior()
    {
        $this->g_tpl->setContent($this->renderBeaviorForm());
    }

    /**
     */
    protected function renderBeaviorForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle("Verhalten");
        $form->addCommandButton(self::CMD_SAVE_BEHAVIOR, $this->txt("save"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $si = new \ilSelectInputGUI($this->txt("material_behavior"), ilPluginActions::F_MATERIAL_BEHAVIOR);
        $si->setOptions($this->getBehaviorOptions());
        $si->setValue($this->plugin_actions->getBehavior());
        $form->addItem($si);

        return $form->getHtml();
    }

    /**
     */
    protected function saveBehavior()
    {
        $this->plugin_actions->saveBehavior($_POST);
        \ilUtil::sendSuccess($this->txt("material_success_save_behavior"), true);
        $this->g_ctrl->redirect($this, self::CMD_BEHAVIOR);
    }

    /**
     * Save changes in materials table
     *
     * @return null
     */
    protected function saveMaterials()
    {
        $processing_objects = $this->getProcessingOptionsFromPost();
        $processing_objects = $this->table_processor->process($processing_objects, array(TableProcessor::ACTION_SAVE));

        $this->renderMaterialTable($processing_objects);
    }

    /**
     * Show confirm form before delete materials
     *
     * @return null
     */
    protected function confirmDelete()
    {
        $processing_objects = $this->getProcessingOptionsFromPost();
        $delete_objects = array_filter($processing_objects, function ($object) {
            if ($object["delete"] === true) {
                return $object;
            }
        });

        if (count($delete_objects)) {
            require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
            $confirmation = new \ilConfirmationGUI();

            $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
            $confirmation->setHeaderText($this->txt("confirm_delete_material"));
            $confirmation->setCancel($this->txt("cancel"), self::CMD_MATERIALS);
            $confirmation->setConfirm($this->txt("delete"), self::CMD_DELETE_MATERIALS);

            foreach ($delete_objects as $key => $delete_object) {
                $object = $delete_object["object"];
                $confirmation->addItem($key, $key, $object->getArticleNumber() . " - " . $object->getTitle());
            }

            $confirmation->addHiddenItem("processing_objects", base64_encode(serialize($processing_objects)));
            $this->g_tpl->setContent($confirmation->getHTML());
        } else {
            $this->renderMaterialTable($processing_objects);
        }
    }

    /**
     * Delete selected materials
     *
     * @return null
     */
    protected function deleteMaterials()
    {
        $processing_objects = unserialize(base64_decode($_POST["processing_objects"]));
        $worked_processing_objects = $this->table_processor->process($processing_objects, array(TableProcessor::ACTION_DELETE));

        if (count($processing_objects) > count($worked_processing_objects)) {
            \ilUtil::sendInfo($this->txt("materials_successfull_deleted"));
        }

        $this->renderMaterialTable($worked_processing_objects);
    }

    /**
     * Import materials
     *
     * @return null
     */
    protected function importMaterialList()
    {
        $gui = new ilImportMaterialListGUI($this, $this->plugin_actions, $this->txt);
        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * Set form action and elemnts to toolbar
     *
     * @return null
     */
    protected function setToolbar()
    {
        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this));
        $this->g_toolbar->setCloseFormTag(false);
        include_once "Services/Form/classes/class.ilTextInputGUI.php";
        $type = new ilTextInputGUI("", ilPluginActions::F_NEW_MATERIAL_LINE);
        $type->setValue(1);
        $this->g_toolbar->addInputItem($type);
        $this->g_toolbar->addFormButton($this->txt("material_add_entries"), self::CMD_ADD_ENTRY);
        $this->g_toolbar->addSeparator();
        $this->g_toolbar->addFormButton($this->txt("material_import_list"), ilImportMaterialListGUI::CMD_SHOW_FORM);
    }

    /**
     * Set subtabs
     *
     * @return null
     */
    protected function setSubtabs($active)
    {
        $link_material = $this->g_ctrl->getLinkTarget($this, self::CMD_MATERIALS);
        $link_behavior = $this->g_ctrl->getLinkTarget($this, self::CMD_BEHAVIOR);
        $this->g_tabs->addSubTab(self::SUB_TAB_MATERIAL, $this->txt("material_sub_tab_material"), $link_material);
        $this->g_tabs->addSubTab(self::SUB_TAB_BEHAVIOR, $this->txt("material_sub_tab_behavior"), $link_behavior);

        $this->g_tabs->activateSubTab($active);
    }

    /**
     * Get options for behavior of material lists
     *
     * @return string[]
     */
    protected function getBehaviorOptions()
    {
        $ret = array();

        foreach (ilPluginActions::$behavior as $value) {
            $ret[$value] = $this->txt($value);
        }

        return $ret;
    }

    /**
     * Get old Material from post
     *
     * @param string[]
     *
     * @return \CaT\Plugins\MaterialList\Materials\Material[] | []
     */
    protected function getObjectsFromPost()
    {
        $materials = array();
        $post = $_POST;

        $ids = $post[ilPluginActions::F_CURRENT_MATERIAL_HIDDEN_IDS];
        $article_numbers = $post[ilPluginActions::F_CURRENT_MATERIAL_ARTICLE_NUMBERS];
        $titles = $post[ilPluginActions::F_CURRENT_MATERIAL_TITLES];

        if ($ids && is_array($ids)) {
            foreach ($ids as $key => $id) {
                $materials[$key] = $this->plugin_actions->getMaterialObject((int) $id, $article_numbers[$key], $titles[$key]);
            }
        }

        return $materials;
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        assert('is_string($code)');

        $txt = $this->txt;

        return $txt($code);
    }


    /**
     * Get option for processing from post
     *
     * @return mixed[] | []
     */
    protected function getProcessingOptionsFromPost()
    {
        $ret = array();
        $post = $_POST;

        $del_array = array();
        if ($post["to_delete_ids"] && count($post["to_delete_ids"]) > 0) {
            $del_array = $post["to_delete_ids"];
        }

        if ($post[ilPluginActions::F_OLD_MATERIAL_ARTICLE_NUMBERS] && count($post[ilPluginActions::F_OLD_MATERIAL_ARTICLE_NUMBERS]) > 0) {
            $old_article_numbers = $post[ilPluginActions::F_OLD_MATERIAL_ARTICLE_NUMBERS];
        }

        foreach ($this->getObjectsFromPost() as $key => $object) {
            $ret[$key] = array("object" => $object, "errors" => array());
            $ret[$key]["delete"] = in_array($key, $del_array);
            $ret[$key]["old_article_number"] = $old_article_numbers[$key];
        }

        return $ret;
    }

    /**
     * Create an array according to processing needed form
     *
     * @param Material[] | [] $objects
     *
     * @return mixed[] | []
     */
    protected function createProcessingArray(array $objects)
    {
        $ret = array();

        foreach ($objects as $object) {
            $ret[] = array("object" => $object, "delete" => false, "errors" => array(), "message" => array());
        }

        return $ret;
    }
}
