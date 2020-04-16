<?php

use \CaT\Plugins\MaterialList\HeaderConfiguration;
use \CaT\Plugins\MaterialList\ilPluginActions;

/**
 * Shows all configurations entries in a table and provides option to add new
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilHeaderConfigurationGUI
{
    const CMD_VIEW_ENTRIES = "viewEntries";
    const CMD_CONFIGURE = "configure";
    const CMD_SHOW_TYPE_FORM = "showTypeForm";
    const CMD_SAVE_ENTRIES = "saveEntries";
    const CMD_SAVE_NEW_ENTRY = "saveNewEntry";
    const CMD_DELETE_ENTRIES = "deleteEntries";
    const CMD_CONFIRM_DELETE = "confirmDelete";

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var \ilMaterialListConfigGUI
     */
    protected $parent_object;

    /**
     * @var \ilMaterialListPlugin
     */
    protected $plugin_object;

    /**
     * @var \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry[] | []
     */
    protected $new_entries;

    /**
     * @var HeaderConfiguration\TypeFormFactory
     */
    protected $type_form_factory;

    /**
     * @var CaT\Plugins\MaterialList\HeaderConfiguration\ilDB
     */
    protected $header_db;

    public function __construct(
        \ilMaterialListConfigGUI $parent_object,
        \ilMaterialListPlugin $plugin_object,
        HeaderConfiguration\TypeFormFactory $type_form_factory
    ) {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_tpl = $DIC->UI()->mainTemplate();
        $this->g_db = $DIC->database();
        $this->parent_object = $parent_object;
        $this->plugin_object = $plugin_object;
        $this->header_db = $plugin_object->getHeaderConfigurationDB($this->g_db);
        $this->new_entries = array();
        $this->type_form_factory = $type_form_factory;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_VIEW_ENTRIES);

        switch ($cmd) {
            case self::CMD_CONFIGURE:
            case self::CMD_VIEW_ENTRIES:
                $this->setToolbar();
                $this->viewEntries();
                break;
            case self::CMD_SHOW_TYPE_FORM:
                $this->showTypeForm();
                break;
            case self::CMD_DELETE_ENTRIES:
                $this->deleteEntries();
                break;
            case self::CMD_SAVE_NEW_ENTRY:
                $this->saveNewEntry();
                break;
            case self::CMD_SAVE_ENTRIES:
                $this->saveEntries();
                break;
            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;
            default:
                throw new Exception(__METHOD__ . ": unknown command: " . $cmd);
        }
    }

    /**
     * Render table with available entries
     *
     * @return null
     */
    protected function viewEntries()
    {
        $table = $this->getTable();
        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Shows the needed type form gui
     *
     * @return null
     */
    protected function showTypeForm()
    {
        $new_entry = $_POST[ilPluginActions::F_NEW_ENTRY];
        $form_gui = $this->type_form_factory->getTypeFormByType($new_entry);
        $form_gui->addFormAction($this->g_ctrl->getFormAction($this));
        $form_gui->addCommandButton(self::CMD_SAVE_NEW_ENTRY, $this->plugin_object->txt("save"));
        $form_gui->addCommandButton(self::CMD_VIEW_ENTRIES, $this->plugin_object->txt("cancel"));
        $this->g_tpl->setContent($form_gui->getHtml());
    }

    /**
     * Save the configuration entries
     *
     * @return null
     */
    protected function saveNewEntry()
    {
        $new_entry = $_POST[ilPluginActions::F_NEW_ENTRY];
        $form_gui = $this->type_form_factory->getTypeFormByType($new_entry);
        $form_gui->addFormAction($this->g_ctrl->getFormAction($this));
        $form_gui->addCommandButton(self::CMD_SAVE_NEW_ENTRY, $this->plugin_object->txt("save"));
        $form_gui->addCommandButton(self::CMD_VIEW_ENTRIES, $this->plugin_object->txt("cancel"));

        if (!$form_gui->checkInput()) {
            $form_gui->setValuesByPost();
            $this->g_tpl->setContent($form_gui->getHtml());
            return;
        }

        $this->header_db->create($new_entry, $form_gui->getValue());
        \ilUtil::sendSuccess($this->plugin_object->txt("configuration_entry_new_success"), true);
        $this->g_ctrl->redirect($this);
    }

    /**
     * Callback for save button. Saves or updates entries
     *
     * @return null
     */
    protected function saveEntries()
    {
        $post = $_POST;

        $ids = $post[ilPluginActions::F_IDS];
        $types = $post[ilPluginActions::F_TYPES];
        $sources_for_value = $post[ilPluginActions::F_SOURCES_FOR_VALUE];

        foreach ($ids as $key => $id) {
            $configuration_entry = new HeaderConfiguration\ConfigurationEntry((int) $id, $types[$key], $sources_for_value[$key]);
            $this->header_db->update($configuration_entry);
        }

        \ilUtil::sendSuccess($this->plugin_object->txt("configuration_entry_update_success"), true);
        $this->g_ctrl->redirect($this);
    }

    /**
     * Show confirm form before delete materials
     *
     * @return null
     */
    protected function confirmDelete()
    {
        $ids = $_POST[ilPluginActions::F_IDS_TO_DEL];
        if (count($ids) > 0) {
            require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
            $confirmation = new \ilConfirmationGUI();

            $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
            $confirmation->setHeaderText($this->plugin_object->txt("confirm_delete_header_settings"));
            $confirmation->setCancel($this->plugin_object->txt("cancel"), self::CMD_VIEW_ENTRIES);
            $confirmation->setConfirm($this->plugin_object->txt("delete"), self::CMD_DELETE_ENTRIES);

            $confirmation->addHiddenItem(ilPluginActions::F_IDS_TO_DEL, base64_encode(serialize($ids)));
            $this->g_tpl->setContent($confirmation->getHTML());
        } else {
            $this->g_ctrl->redirect($this);
        }
    }

    /**
     * Deletes configuration entries
     *
     * @return null
     */
    protected function deleteEntries()
    {
        $ids = $_POST[ilPluginActions::F_IDS_TO_DEL];
        $ids = unserialize(base64_decode($ids));
        if ($ids !== null) {
            foreach ($ids as $key => $id) {
                $this->header_db->delete($id);
            }
        }

        \ilUtil::sendSuccess($this->plugin_object->txt("configuration_entry_delete_success"), true);
        $this->g_ctrl->redirect($this);
    }

    /**
     * Creates a toolbar entries
     *
     * @return null
     */
    protected function setToolbar()
    {
        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this, self::CMD_VIEW_ENTRIES));
        $this->g_toolbar->setCloseFormTag(false);
        include_once "Services/Form/classes/class.ilSelectInputGUI.php";
        $type = new ilSelectInputGUI("", ilPluginActions::F_NEW_ENTRY);
        $type->setOptions($this->getTypeOptions());
        $this->g_toolbar->addInputItem($type);
        $this->g_toolbar->addFormButton($this->plugin_object->txt("add_entry"), self::CMD_SHOW_TYPE_FORM);
    }

    /**
     * Get the table for all entries
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\ilHeaderConfigurationTableGUI
     */
    public function getTable()
    {
        $table = new HeaderConfiguration\ilHeaderConfigurationTableGUI($this, $this->plugin_object);
        $this->addCommandButtons($table);
        $this->addMulticommands($table);
        $table->setData($this->getData());

        return $table;
    }

    /**
     * Get data for table
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry[] | []
     */
    public function getData()
    {
        $current_entries = $this->header_db->selectAll();
        return array_merge($current_entries, $this->new_entries);
    }

    /**
     * Add multicommands to table
     *
     * @return null
     */
    protected function addMulticommands(HeaderConfiguration\ilHeaderConfigurationTableGUI &$table)
    {
        $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->plugin_object->txt("delete"));
    }

    /**
     * Add commands to table
     *
     * @return null
     */
    protected function addCommandButtons(HeaderConfiguration\ilHeaderConfigurationTableGUI &$table)
    {
        $table->addCommandButton(self::CMD_SAVE_ENTRIES, $this->plugin_object->txt("save"));
    }

    /**
     * Get possible type options for configuration entry
     *
     * @return array<string, string>
     */
    public function getTypeOptions()
    {
        $ret = array();
        $type_options = HeaderConfiguration\ConfigurationEntry::$type_options;

        foreach ($type_options as $key => $type_option) {
            $ret[$type_option] = $this->plugin_object->txt("type_" . $type_option);
        }

        return $ret;
    }
}
